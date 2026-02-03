<?php

namespace App\Filament\Pages\Participants;

use App\Services\Participants\ParticipantGenerationFromPaymentsService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GenerateParticipantsFromPayments extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Imprezy';
    protected static ?string $navigationLabel = 'Uczestnicy z wpłat';
    protected static ?string $title = 'Generuj uczestników z wpłat';
    protected static string $view = 'filament.pages.participants.generate-participants-from-payments';

    public ?array $data = [];

    /** @var array{ok?:bool,created?:int,skipped?:int,errors?:array,error?:string}|null */
    public ?array $lastResult = null;

    public function mount(): void
    {
        $matchMode = (string) request()->query('match_mode', 'event_code');
        $key = (string) request()->query('key', '');

        if (!in_array($matchMode, ['event_code', 'contract_number'], true)) {
            $matchMode = 'event_code';
        }

        $this->form->fill([
            'match_mode' => $matchMode,
            'key' => $key,
            'create_from_descriptions' => true,
            'create_placeholders_to_expected' => false,
            'dry_run' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('match_mode')
                    ->label('Zakres')
                    ->options([
                        'event_code' => 'Impreza (public_code)',
                        'contract_number' => 'Pojedyncza umowa (contract_number)',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('key')
                    ->label(fn ($get) => ($get('match_mode') === 'contract_number') ? 'Numer umowy' : 'Kod imprezy')
                    ->required()
                    ->helperText('Dla imprezy: podaj public_code. Dla umowy: podaj contract_number.'),
                Toggle::make('create_from_descriptions')
                    ->label('Twórz uczestników z opisów wpłat (heurystyka)')
                    ->default(true)
                    ->inline(false),
                Toggle::make('create_placeholders_to_expected')
                    ->label('Uzupełnij placeholdery do oczekiwanej liczby (total_amount / locked_price_per_person)')
                    ->default(false)
                    ->inline(false),
                Toggle::make('dry_run')
                    ->label('Symulacja (bez zapisu)')
                    ->default(false)
                    ->inline(false),
            ])
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        $service = app(ParticipantGenerationFromPaymentsService::class);
        $result = $service->generate([
            'match_mode' => $data['match_mode'] ?? 'event_code',
            'key' => $data['key'] ?? '',
            'create_from_descriptions' => (bool) ($data['create_from_descriptions'] ?? true),
            'create_placeholders_to_expected' => (bool) ($data['create_placeholders_to_expected'] ?? false),
            'dry_run' => (bool) ($data['dry_run'] ?? false),
        ]);

        $this->lastResult = $result;

        if (!($result['ok'] ?? false)) {
            Notification::make()
                ->title('Operacja nieudana')
                ->body($result['error'] ?? 'Nieznany błąd')
                ->danger()
                ->send();
            return;
        }

        $body = 'Utworzono: ' . ($result['created'] ?? 0) . ', pominięto: ' . ($result['skipped'] ?? 0);
        if (!empty($result['errors'])) {
            $body .= '. Błędy: ' . count($result['errors']);
        }

        Notification::make()
            ->title(($data['dry_run'] ?? false) ? 'Symulacja zakończona' : 'Generowanie zakończone')
            ->body($body)
            ->success()
            ->send();
    }
}
