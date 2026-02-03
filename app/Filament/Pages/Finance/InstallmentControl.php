<?php

namespace App\Filament\Pages\Finance;

use App\Filament\Resources\ContractResource;
use App\Filament\Resources\EventPaymentResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\TaskResource;
use App\Models\Contract;
use App\Models\ContractInstallment;
use App\Models\User;
use App\Services\Finance\InstallmentAutoGenerator;
use App\Services\Finance\InstallmentTaskSyncService;
use App\Services\Finance\InstallmentReminderService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class InstallmentControl extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?string $navigationLabel = 'Kontrola rat';
    protected static ?string $title = 'Kontrola rat';

    protected static string $view = 'filament.pages.finance.installment-control';

    public string $scope = 'overdue';
    public int $daysAhead = 14;
    public string $eventCode = '';
    public string $contractNumber = '';

    public function mount(): void
    {
        $this->scope = (string) request()->query('scope', $this->scope);
        $this->daysAhead = (int) request()->query('days', $this->daysAhead);
        $this->eventCode = (string) request()->query('event_code', $this->eventCode);
        $this->contractNumber = (string) request()->query('contract_number', $this->contractNumber);

        if (! in_array($this->scope, ['overdue', 'soon', 'all'], true)) {
            $this->scope = 'overdue';
        }

        if ($this->daysAhead < 0) {
            $this->daysAhead = 0;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('scopeOverdue')
                ->label('Przeterminowane')
                ->color($this->scope === 'overdue' ? 'danger' : 'gray')
                ->action(fn () => $this->scope = 'overdue'),

            \Filament\Actions\Action::make('scopeSoon')
                ->label('Do ' . $this->daysAhead . ' dni')
                ->color($this->scope === 'soon' ? 'warning' : 'gray')
                ->action(fn () => $this->scope = 'soon'),

            \Filament\Actions\Action::make('scopeAll')
                ->label('Wszystkie nieopłacone')
                ->color($this->scope === 'all' ? 'primary' : 'gray')
                ->action(fn () => $this->scope = 'all'),

            \Filament\Actions\Action::make('syncTasks')
                ->label('Odśwież zadania')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (InstallmentTaskSyncService $service): void {
                    $result = $service->sync([
                        'days_ahead' => $this->daysAhead,
                        'author_id' => Auth::id() ?? 1,
                    ]);

                    Notification::make()
                        ->title('Synchronizacja rat → zadania')
                        ->body('Utworzone: ' . ($result['created'] ?? 0)
                            . ' • Zaktualizowane: ' . ($result['updated'] ?? 0)
                            . ' • Pominięte: ' . ($result['skipped'] ?? 0))
                        ->success()
                        ->send();
                }),

            \Filament\Actions\Action::make('autoGenerateInstallments')
                ->label('Auto-utwórz raty')
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\Toggle::make('replace_existing')
                        ->label('Usuń istniejące raty i utwórz od nowa')
                        ->default(false)
                        ->inline(false),
                    \Filament\Forms\Components\TextInput::make('deposit_percent')
                        ->label('Zaliczka (%)')
                        ->numeric()
                        ->default(30)
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('deposit_due_date')
                        ->label('Termin zaliczki')
                        ->default(now())
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('final_due_days_before_start')
                        ->label('Ile dni przed startem termin dopłaty')
                        ->numeric()
                        ->default(14)
                        ->required(),
                    \Filament\Forms\Components\Toggle::make('sync_tasks')
                        ->label('Od razu odśwież zadania rat')
                        ->default(true)
                        ->inline(false),
                ])
                ->action(function (array $data, InstallmentAutoGenerator $generator, InstallmentTaskSyncService $sync): void {
                    $contractsQuery = Contract::query()->with('event');

                    if (filled($this->eventCode)) {
                        $contractsQuery->whereHas('event', function (Builder $q) {
                            $q->where('public_code', $this->eventCode);
                        });
                    }

                    if (filled($this->contractNumber)) {
                        $contractsQuery->where('contract_number', $this->contractNumber);
                    }

                    $contracts = $contractsQuery->get();

                    if ($contracts->isEmpty()) {
                        Notification::make()
                            ->title('Brak umów do wygenerowania rat')
                            ->warning()
                            ->send();
                        return;
                    }

                    $created = 0;
                    foreach ($contracts as $contract) {
                        $created += $generator->generate($contract, $data);
                    }

                    if (!empty($data['sync_tasks'])) {
                        $sync->sync([
                            'days_ahead' => $this->daysAhead,
                            'author_id' => Auth::id() ?? 1,
                        ]);
                    }

                    Notification::make()
                        ->title('Utworzono raty: ' . $created)
                        ->success()
                        ->send();
                }),

            \Filament\Actions\Action::make('resetFilters')
                ->label('Reset filtrów')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function (): void {
                    $this->eventCode = '';
                    $this->contractNumber = '';
                    $this->dispatch('$refresh');
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getQuery())
            ->defaultSort('due_date')
            ->emptyStateHeading('Brak rat do kontroli')
            ->emptyStateDescription('Dodaj harmonogram rat w umowie lub użyj „Auto-utwórz raty” powyżej. System działa na raty (nie na same wpłaty).')
            ->emptyStateActions([
                \Filament\Tables\Actions\Action::make('emptyAutoGenerateInstallments')
                    ->label('Auto-utwórz raty')
                    ->icon('heroicon-o-bolt')
                    ->color('gray')
                    ->action(function () {
                        $this->mountAction('autoGenerateInstallments');
                    }),
            ])
            ->columns([
                TextColumn::make('contract.event.public_code')
                    ->label('Kod')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contract.event.name')
                    ->label('Impreza')
                    ->limit(35)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('contract.contract_number')
                    ->label('Umowa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('due_date')
                    ->label('Termin')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Kwota')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', ' ') . ' PLN')
                    ->sortable(),

                TextColumn::make('reminders_max_sent_at')
                    ->label('Ostatnie przyp.')
                    ->since()
                    ->toggleable()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('latestReminder.source')
                    ->label('Źródło')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(function (?string $state): string {
                        return $state === 'auto' ? 'AUTO' : ($state === 'manual' ? 'RĘCZNIE' : '-');
                    })
                    ->color(function (?string $state): string {
                        return match ($state) {
                            'auto' => 'info',
                            'manual' => 'success',
                            default => 'gray',
                        };
                    })
                    ->toggleable(),

                TextColumn::make('status_calc')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (ContractInstallment $record): string {
                        $due = $record->due_date ? Carbon::parse($record->due_date)->startOfDay() : null;
                        if (! $due) {
                            return 'brak terminu';
                        }

                        $today = now()->startOfDay();
                        if ($due->lt($today)) {
                            $days = $today->diffInDays($due);
                            return 'przeterminowana (' . $days . ' dni)';
                        }

                        $days = $today->diffInDays($due);
                        return 'w terminie (za ' . $days . ' dni)';
                    })
                    ->color(function (string $state): string {
                        if (str_contains($state, 'przeterminowana')) {
                            return 'danger';
                        }
                        if (str_contains($state, 'w terminie')) {
                            return 'warning';
                        }
                        return 'gray';
                    }),
            ])
            ->actions([
                Action::make('reminderHistory')
                    ->label('Historia')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Historia przypomnień')
                    ->modalContent(function (ContractInstallment $record) {
                        $reminders = $record->reminders()
                            ->with('user:id,name')
                            ->latest('sent_at')
                            ->limit(20)
                            ->get();

                        return view('filament.pages.finance.partials.installment-reminders-history', [
                            'reminders' => $reminders,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zamknij'),

                Action::make('openContract')
                    ->label('Umowa')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (ContractInstallment $record) => ContractResource::getUrl('edit', ['record' => $record->contract_id]))
                    ->openUrlInNewTab(),

                Action::make('openEvent')
                    ->label('Impreza')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->visible(fn (ContractInstallment $record) => (bool) ($record->contract?->event_id))
                    ->url(fn (ContractInstallment $record) => EventResource::getUrl('edit', ['record' => $record->contract->event_id]))
                    ->openUrlInNewTab(),

                Action::make('openPayments')
                    ->label('Wpłaty')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->visible(fn (ContractInstallment $record) => (bool) ($record->contract?->event_id))
                    ->url(fn (ContractInstallment $record) => EventPaymentResource::getUrl('index', [
                        'tableFilters' => [
                            'event' => [
                                'value' => $record->contract->event_id,
                            ],
                        ],
                    ]))
                    ->openUrlInNewTab(),

                Action::make('openInstallmentTasks')
                    ->label('Zadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('gray')
                    ->url(fn (ContractInstallment $record) => TaskResource::getUrl('index', [
                        'q' => (string) ($record->contract?->contract_number ?? ''),
                        'installments' => 1,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('markPaid')
                    ->label('Oznacz opłaconą')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ContractInstallment $record): void {
                        $record->is_paid = true;
                        $record->paid_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Rata oznaczona jako opłacona')
                            ->success()
                            ->send();
                    }),

                Action::make('sendReminder')
                    ->label('Przypomnienie')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\CheckboxList::make('channels')
                            ->label('Kanały')
                            ->options([
                                'sms' => 'SMS',
                                'email' => 'E-mail',
                            ])
                            ->columns(2)
                            ->required()
                            ->default(['sms']),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Treść')
                            ->helperText('Możesz użyć placeholderów: {contract_number}, {due_date}, {amount}, {event_name}, {event_code}.')
                            ->rows(7)
                            ->default(fn (ContractInstallment $record, InstallmentReminderService $svc) => $svc->buildDefaultMessage($record))
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ContractInstallment $record, array $data, InstallmentReminderService $svc): void {
                        $channels = (array) ($data['channels'] ?? []);
                        $template = trim((string) ($data['message'] ?? ''));

                        $user = request()->user();
                        $sender = $user instanceof User ? $user : null;

                        $result = $svc->send($record, $channels, $sender, $template !== '' ? $template : null);

                        Notification::make()
                            ->title('Przypomnienie wysłane')
                            ->body('SMS: ' . ($result['sms_sent'] ?? 0)
                                . ' • E-mail: ' . ($result['email_sent'] ?? 0)
                                . ' • Pominięte: ' . ($result['skipped'] ?? 0))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkSendReminders')
                        ->label('Wyślij przypomnienia')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->form([
                            \Filament\Forms\Components\CheckboxList::make('channels')
                                ->label('Kanały')
                                ->options([
                                    'sms' => 'SMS',
                                    'email' => 'E-mail',
                                ])
                                ->columns(2)
                                ->required()
                                ->default(['sms']),
                            \Filament\Forms\Components\Toggle::make('force')
                                ->label('Wyślij mimo wysyłki dziś (admin)')
                                ->default(false)
                                ->visible(function (): bool {
                                    $user = request()->user();

                                    return $user instanceof User
                                        ? $user->hasRole(['super_admin', 'admin'])
                                        : false;
                                }),
                            \Filament\Forms\Components\Textarea::make('message')
                                ->label('Szablon treści (opcjonalnie)')
                                ->helperText('Placeholders: {contract_number}, {due_date}, {amount}, {event_name}, {event_code}. Jeśli puste, użyję domyślnego szablonu.')
                                ->rows(7),
                        ])
                        ->requiresConfirmation()
                        ->action(function (Collection $records, array $data, InstallmentReminderService $svc): void {
                            $channels = (array) ($data['channels'] ?? []);
                            $template = trim((string) ($data['message'] ?? ''));
                            $force = (bool) ($data['force'] ?? false);

                            $user = request()->user();
                            $sender = $user instanceof User ? $user : null;

                            $sms = 0;
                            $email = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                /** @var ContractInstallment $record */
                                $result = $svc->send($record, $channels, $sender, $template !== '' ? $template : null, $force);
                                $sms += (int) ($result['sms_sent'] ?? 0);
                                $email += (int) ($result['email_sent'] ?? 0);
                                $skipped += (int) ($result['skipped'] ?? 0);
                            }

                            Notification::make()
                                ->title('Przypomnienia wysłane')
                                ->body('SMS: ' . $sms . ' • E-mail: ' . $email . ' • Pominięte: ' . $skipped)
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulkMarkPaid')
                        ->label('Oznacz opłacone')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                /** @var ContractInstallment $record */
                                $record->is_paid = true;
                                $record->paid_at = now();
                                $record->save();
                            }

                            Notification::make()
                                ->title('Raty oznaczone jako opłacone')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    private function getQuery(): Builder
    {
        $query = ContractInstallment::query()
            ->with(['contract.event', 'contract.event.contact', 'latestReminder'])
            ->withMax('reminders', 'sent_at')
            ->where('is_paid', false)
            ->whereNotNull('due_date');

        if (filled($this->eventCode)) {
            $query->whereHas('contract.event', function (Builder $q) {
                $q->where('public_code', $this->eventCode);
            });
        }

        if (filled($this->contractNumber)) {
            $query->whereHas('contract', function (Builder $q) {
                $q->where('contract_number', $this->contractNumber);
            });
        }

        $today = now()->startOfDay();

        if ($this->scope === 'overdue') {
            $query->whereDate('due_date', '<', $today);
        } elseif ($this->scope === 'soon') {
            $until = now()->addDays($this->daysAhead)->endOfDay();
            $query->whereDate('due_date', '>=', $today)
                ->whereDate('due_date', '<=', $until);
        }

        return $query;
    }
}
