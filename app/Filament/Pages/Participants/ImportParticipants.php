<?php

namespace App\Filament\Pages\Participants;

use App\Services\Participants\ParticipantImportService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ImportParticipants extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Imprezy';
    protected static ?string $navigationLabel = 'Import uczestników';
    protected static ?string $title = 'Import uczestników (CSV)';
    protected static string $view = 'filament.pages.participants.import-participants';

    public ?array $data = [];

    /** @var array{ok?:bool,created?:int,updated?:int,skipped?:int,errors?:array,error?:string}|null */
    public ?array $lastResult = null;

    public function mount(): void
    {
        $matchMode = (string) request()->query('match_mode', 'contract_number');
        $key = (string) request()->query('key', '');

        if (!in_array($matchMode, ['contract_number', 'event_code'], true)) {
            $matchMode = 'contract_number';
        }

        $this->form->fill([
            'match_mode' => $matchMode,
            'key' => $key,
            'delimiter' => ';',
            'has_header' => true,
            'dry_run' => false,
            'update_existing' => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('match_mode')
                    ->label('Dopasuj po')
                    ->options([
                        'contract_number' => 'Numer umowy',
                        'event_code' => 'Kod imprezy (public_code)',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('key')
                    ->label(fn ($get) => ($get('match_mode') === 'event_code') ? 'Kod imprezy' : 'Numer umowy')
                    ->required()
                    ->helperText('Możesz też podać numer umowy w kolumnie CSV (np. nr_umowy).'),
                FileUpload::make('csv_file')
                    ->label('Plik CSV')
                    ->disk('local')
                    ->directory('temp-imports')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                    ->preserveFilenames(),
                Select::make('delimiter')
                    ->label('Separator')
                    ->options([
                        ';' => 'Średnik ;',
                        ',' => 'Przecinek ,',
                        "\t" => 'Tabulator',
                    ])
                    ->required(),
                Toggle::make('has_header')
                    ->label('Pierwszy wiersz to nagłówki')
                    ->default(true)
                    ->inline(false),
                Toggle::make('update_existing')
                    ->label('Aktualizuj istniejących (po PESEL/email)')
                    ->default(true)
                    ->inline(false),
                Toggle::make('dry_run')
                    ->label('Symulacja (bez zapisu)')
                    ->default(false)
                    ->inline(false),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        $filePath = Storage::disk('local')->path($data['csv_file']);

        $service = app(ParticipantImportService::class);
        $result = $service->importCsv($filePath, [
            'match_mode' => $data['match_mode'] ?? 'contract_number',
            'key' => $data['key'] ?? '',
            'delimiter' => $data['delimiter'] ?? ';',
            'has_header' => (bool) ($data['has_header'] ?? true),
            'dry_run' => (bool) ($data['dry_run'] ?? false),
            'update_existing' => (bool) ($data['update_existing'] ?? true),
        ]);

        $this->lastResult = $result;

        if (!($result['ok'] ?? false)) {
            Notification::make()
                ->title('Import nieudany')
                ->body($result['error'] ?? 'Nieznany błąd')
                ->danger()
                ->send();
            return;
        }

        $body = 'Utworzono: ' . ($result['created'] ?? 0)
            . ', zaktualizowano: ' . ($result['updated'] ?? 0)
            . ', pominięto: ' . ($result['skipped'] ?? 0);

        if (!empty($result['errors'])) {
            $body .= '. Błędy: ' . count($result['errors']);
        }

        Notification::make()
            ->title(($data['dry_run'] ?? false) ? 'Symulacja importu zakończona' : 'Import zakończony')
            ->body($body)
            ->success()
            ->send();
    }
}
