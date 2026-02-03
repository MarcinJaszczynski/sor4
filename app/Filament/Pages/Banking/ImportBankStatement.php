<?php

namespace App\Filament\Pages\Banking;

use App\Services\Banking\BankReconciliationService;
use App\Services\Banking\Parsers\CsvBankStatementParser;
use App\Services\Banking\Parsers\Mt940BankStatementParser;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use App\Models\Contract;
use App\Models\EventPayment;
use App\Models\Participant;
use Filament\Notifications\Notification;

use App\Services\Finance\PaymentDistributionService;

class ImportBankStatement extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?string $navigationLabel = 'Import Wyciągów';
    protected static ?string $title = 'Import Wyciągów Bankowych';
    protected static string $view = 'filament.pages.banking.import-bank-statement';

    public ?array $data = [];
    public array $reconciliationResults = []; // Wyniki parowania do wyświetlenia w tabeli
    public ?int $manualSelection = null; // index of row being manually assigned
    public ?int $manualContractId = null;
    public string $manualContractSearch = '';
    public array $manualContractOptions = [];

    // Sorting and Searching
    public string $sortField = 'date';
    public string $sortDirection = 'desc';
    public string $searchQuery = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getFilteredResults()
    {
        $results = collect($this->reconciliationResults);

        if ($this->searchQuery) {
            $results = $results->filter(function ($item) {
                return str_contains(strtolower($item['title'] ?? ''), strtolower($this->searchQuery)) ||
                       str_contains(strtolower($item['sender'] ?? ''), strtolower($this->searchQuery)) ||
                       str_contains(strtolower((string)$item['amount']), strtolower($this->searchQuery));
            });
        }

        return $results->sortBy([
            [$this->sortField, $this->sortDirection]
        ]);
    }
    
    public function getIncomesProperty()
    {
        return $this->getFilteredResults()->filter(fn($item) => $item['amount'] > 0);
    }

    public function getExpensesProperty()
    {
        return $this->getFilteredResults()->filter(fn($item) => $item['amount'] < 0);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('format')
                    ->label('Format pliku')
                    ->options([
                        'csv' => 'CSV (Lista operacji)',
                        'mt940' => 'MT940 (Standard bankowy)',
                    ])
                    ->required()
                    ->default('csv'),
                FileUpload::make('statement_file')
                    ->label('Plik wyciągu')
                    ->disk('local') 
                    ->directory('temp-statements')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                    ->preserveFilenames(),
            ])
            ->statePath('data');
    }

    public function import()
    {
        $data = $this->form->getState();
        $filePath = Storage::disk('local')->path($data['statement_file']);
        
        // Wybór parsera
        $parser = match($data['format']) {
            'csv' => new CsvBankStatementParser(),
            'mt940' => new Mt940BankStatementParser(),
            default => new CsvBankStatementParser(), // Fallback
        };

        // Parsowanie
        $transactions = $parser->parse($filePath);

        // Rekoncyliacja (Parowanie)
        $service = new BankReconciliationService();
        $results = $service->reconcile($transactions);

        // Zapisz wyniki do właściwości, aby wyświetlić w tabeli
        $this->reconciliationResults = $results->map(function($item, $key) {
             return [
                 'id' => $key, // Klucz w kolekcji
                 'date' => $item['transaction']->transactionDate,
                 'amount' => $item['transaction']->amount,
                 'sender' => $item['transaction']->senderName,
                 'transaction_id' => $item['transaction']->transactionId ?? null,
                 'title' => $item['transaction']->title,
                 'match_found' => $item['match_found'],
                 'contract_id' => $item['matched_contract']?->id,
                 'confidence' => $item['confidence'] ?? 'none',
                 'reason' => $item['match_reason'] ?? ($item['parsed_keys']['reason'] ?? null),
                 'score' => $item['match_score'] ?? ($item['parsed_keys']['score'] ?? null),
                 'parsed_keys' => $item['parsed_keys'] ?? [],
                 'status' => $item['match_found'] ? 'Zidentyfikowano' : 'Nieznany',
                 'already_exists' => $item['already_exists'] ?? false,
             ];
        })->toArray();
        
        Notification::make()
            ->title('Plik przetworzony')
            ->body('Znaleziono ' . count($transactions) . ' transakcji. Sprawdź tabelę poniżej.')
            ->success()
            ->send();
    }

    public function approveMatch($index)
    {
        if (!isset($this->reconciliationResults[$index])) {
            return;
        }

        $row = $this->reconciliationResults[$index];
        if (!$row['match_found'] || !$row['contract_id']) {
            Notification::make()->title('Błąd: Brak dopasowania do zatwierdzenia')->danger()->send();
            return;
        }

        $contract = Contract::find($row['contract_id']);
        if (!$contract) {
            Notification::make()->title('Błąd: Nie znaleziono umowy')->danger()->send();
            return;
        }
        // Sprawdź duplikat po transaction_id (invoice_number)
        if (!empty($row['transaction_id'])) {
            $exists = EventPayment::where('invoice_number', $row['transaction_id'])->exists();
            if ($exists) {
                Notification::make()->title('Transakcja już zaksięgowana (invoice_number)')->danger()->send();
                return;
            }
        }

        // Utwórz płatność i zapisz invoice_number jako transaction_id
        $payment = EventPayment::create([
            'event_id' => $contract->event_id,
            'created_by_user_id' => auth()->id(),
            'contract_id' => $contract->id,
            'amount' => $row['amount'],
            'currency' => 'PLN',
            'payment_date' => $row['date'] ?? now(),
            'description' => $row['title'] . ' (Umowa #' . $contract->contract_number . ')',
            'invoice_number' => $row['transaction_id'] ?? null,
            'is_advance' => str_contains(strtolower($row['title']), 'zaliczka'),
            'source' => 'office',
        ]);

        // Uruchom Split Payment / Dystrybucję
        $distributor = new PaymentDistributionService();
        $logs = $distributor->distribute($payment, $contract);

        $msgBody = 'Zaksięgowano płatność.';
        if (!empty($logs)) {
            $msgBody .= ' (System Split Payment przeanalizował ubezpieczenia)';
        }

        Notification::make()
            ->title($msgBody)
            ->success()
            ->send();

        // Usuń wiersz z wyników lub oznacz jako przetworzony
        unset($this->reconciliationResults[$index]);
    }

    public function createParticipantFromTransaction($index): void
    {
        if (!isset($this->reconciliationResults[$index])) {
            Notification::make()->title('Błąd: brak wiersza')->danger()->send();
            return;
        }

        $row = $this->reconciliationResults[$index];
        if (empty($row['contract_id'])) {
            Notification::make()->title('Błąd: brak przypisanej umowy')->danger()->send();
            return;
        }

        if ((float) ($row['amount'] ?? 0) <= 0) {
            Notification::make()->title('Tylko wpływy mogą tworzyć uczestnika')->warning()->send();
            return;
        }

        $contract = Contract::find((int) $row['contract_id']);
        if (!$contract) {
            Notification::make()->title('Błąd: nie znaleziono umowy')->danger()->send();
            return;
        }

        $sender = trim((string) ($row['sender'] ?? ''));
        [$firstName, $lastName] = $this->parsePersonNameFromSender($sender);

        if ($firstName === '' && $lastName === '') {
            Notification::make()->title('Nie udało się wyciągnąć imienia i nazwiska z nadawcy')->danger()->send();
            return;
        }

        $exists = Participant::query()
            ->where('contract_id', $contract->id)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->exists();

        if ($exists) {
            Notification::make()->title('Uczestnik już istnieje w tej umowie')->warning()->send();
            return;
        }

        Participant::create([
            'contract_id' => $contract->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active',
        ]);

        Notification::make()->title('Utworzono uczestnika na podstawie nadawcy')->success()->send();
    }

    public function bulkCreateParticipantsFromMatchedIncomes(): void
    {
        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($this->reconciliationResults as $index => $row) {
            try {
                if (empty($row['match_found']) || empty($row['contract_id'])) {
                    continue;
                }

                if ((float) ($row['amount'] ?? 0) <= 0) {
                    continue;
                }

                $contract = Contract::find((int) $row['contract_id']);
                if (!$contract) {
                    $errors++;
                    continue;
                }

                $sender = trim((string) ($row['sender'] ?? ''));
                [$firstName, $lastName] = $this->parsePersonNameFromSender($sender);
                if ($firstName === '' && $lastName === '') {
                    $skipped++;
                    continue;
                }

                $exists = Participant::query()
                    ->where('contract_id', $contract->id)
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Participant::create([
                    'contract_id' => $contract->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => 'active',
                ]);

                $created++;
            } catch (\Throwable $_) {
                $errors++;
            }
        }

        $body = "Utworzono: {$created}, pominięto: {$skipped}";
        if ($errors > 0) {
            $body .= ", błędy: {$errors}";
        }

        Notification::make()
            ->title('Uczestnicy z dopasowanych wpływów')
            ->body($body)
            ->success()
            ->send();
    }

    public function bulkApproveMatchedIncomes(): void
    {
        $created = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = 0;

        $indexesToRemove = [];
        $distributor = new PaymentDistributionService();

        foreach ($this->reconciliationResults as $index => $row) {
            try {
                if (empty($row['match_found']) || empty($row['contract_id'])) {
                    continue;
                }

                if ((float) ($row['amount'] ?? 0) <= 0) {
                    continue;
                }

                if (!empty($row['already_exists'])) {
                    $duplicates++;
                    $indexesToRemove[] = $index;
                    continue;
                }

                $contract = Contract::find((int) $row['contract_id']);
                if (!$contract) {
                    $errors++;
                    continue;
                }

                $transactionId = $row['transaction_id'] ?? null;
                if (!empty($transactionId)) {
                    $exists = EventPayment::where('invoice_number', $transactionId)->exists();
                    if ($exists) {
                        $duplicates++;
                        $indexesToRemove[] = $index;
                        continue;
                    }
                }

                $payment = EventPayment::create([
                    'event_id' => $contract->event_id,
                    'created_by_user_id' => auth()->id(),
                    'contract_id' => $contract->id,
                    'amount' => $row['amount'],
                    'currency' => 'PLN',
                    'payment_date' => $row['date'] ?? now(),
                    'description' => ($row['title'] ?? '') . ' (Umowa #' . $contract->contract_number . ')',
                    'invoice_number' => $transactionId,
                    'is_advance' => str_contains(strtolower((string) ($row['title'] ?? '')), 'zaliczka'),
                    'source' => 'office',
                ]);

                // Uruchom Split Payment / Dystrybucję
                $distributor->distribute($payment, $contract);

                $created++;
                $indexesToRemove[] = $index;
            } catch (\Throwable $_) {
                $errors++;
            }
        }

        foreach ($indexesToRemove as $i) {
            unset($this->reconciliationResults[$i]);
        }

        $body = "Zaksięgowano: {$created}, duplikaty: {$duplicates}, pominięto: {$skipped}";
        if ($errors > 0) {
            $body .= ", błędy: {$errors}";
        }

        Notification::make()
            ->title('Masowe księgowanie dopasowanych wpływów')
            ->body($body)
            ->success()
            ->send();
    }

    public function manualMatch($index)
    {
        // Otwórz formularz ręcznego przypisania dla wskazanego wiersza
        $this->manualSelection = $index;
        $this->manualContractId = null;
        $this->manualContractSearch = '';
        $this->manualContractOptions = [];
    }

    public function updatedManualContractSearch()
    {
        $term = trim($this->manualContractSearch);
        $query = \App\Models\Contract::query();
        if ($term !== '') {
            $query->where(function($q) use ($term) {
                $q->where('contract_number', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%")
                  ->orWhereHas('event', function($qe) use ($term) {
                      $qe->where('name', 'like', "%{$term}%")->orWhere('public_code', 'like', "%{$term}%");
                  });
            });
        }

        $this->manualContractOptions = $query->limit(50)->get()->mapWithKeys(function($c) {
            $label = ($c->contract_number ?? 'ID:'.$c->id) . ' — ' . optional($c->event)->name;
            return [$c->id => $label];
        })->toArray();
    }

    public function confirmManualAssign($index)
    {
        if (!isset($this->reconciliationResults[$index])) {
            Notification::make()->title('Błąd: brak wiersza')->danger()->send();
            return;
        }

        if (!$this->manualContractId) {
            Notification::make()->title('Wybierz umowę przed przypisaniem')->danger()->send();
            return;
        }

        $row = $this->reconciliationResults[$index];
        $contract = \App\Models\Contract::find($this->manualContractId);
        if (!$contract) {
            Notification::make()->title('Nie znaleziono umowy')->danger()->send();
            return;
        }

        // Sprawdź duplikat po transaction_id
        if (!empty($row['transaction_id'])) {
            $exists = EventPayment::where('invoice_number', $row['transaction_id'])->exists();
            if ($exists) {
                Notification::make()->title('Transakcja już zaksięgowana (invoice_number)')->danger()->send();
                return;
            }
        }

        $payment = EventPayment::create([
            'event_id' => $contract->event_id,
            'created_by_user_id' => auth()->id(),
            'contract_id' => $contract->id,
            'amount' => $row['amount'],
            'currency' => 'PLN',
            'payment_date' => $row['date'] ?? now(),
            'description' => ($row['title'] ?? '') . ' (ręczne przypisanie umowa #' . $contract->contract_number . ')',
            'invoice_number' => $row['transaction_id'] ?? null,
            'is_advance' => str_contains(strtolower($row['title'] ?? ''), 'zaliczka'),
            'source' => 'office',
        ]);

        $distributor = new PaymentDistributionService();
        $distributor->distribute($payment, $contract);

        Notification::make()->title('Zaksięgowano płatność (ręczne przypisanie)')->success()->send();

        unset($this->reconciliationResults[$index]);
        $this->manualSelection = null;
        $this->manualContractId = null;
        $this->manualContractOptions = [];
    }

    private function parsePersonNameFromSender(string $sender): array
    {
        $sender = trim($sender);
        if ($sender === '') {
            return ['', ''];
        }

        // Usuń nadmiarowe znaki i numeracje
        $sender = preg_replace('/\s+/u', ' ', $sender);
        $sender = preg_replace('/[^\p{L}\p{M}\s\-]+/u', ' ', $sender);
        $sender = preg_replace('/\s+/u', ' ', trim((string) $sender));

        $parts = array_values(array_filter(explode(' ', $sender), fn ($p) => trim($p) !== ''));
        if (count($parts) < 2) {
            return [trim((string) ($parts[0] ?? '')), ''];
        }

        $first = array_shift($parts);
        $last = implode(' ', $parts);

        return [trim((string) $first), trim((string) $last)];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\EventPayment::query()->whereRaw('1=0')) // Dummy query, bo używamy custom rows (array) w Filament v3 jest trudniej z array driverem, więc często stosuje się hack lub View table.
            // UWAGA: Filament v3 wymaga QueryBuilder dla tabeli.
            // Aby wyświetlić tablicę $reconciliationResults, najprościej użyć widoku Blade z tabelą HTML lub specjalnego pluginu 'sushi' (array driver).
            // W tym demo, dla uproszczenia, użyjemy prostej tabeli w widoku Blade (poniżej), zamiast komponentu Table Buildera, który jest ściśle spięty z Eloquentem.
            ->columns([
                // To nie zadziała bez Sushi/Array drivera dla Eloquent.
                // Dlatego w widoku zrobimy @foreach po $reconciliationResults.
            ]);
    }
}
