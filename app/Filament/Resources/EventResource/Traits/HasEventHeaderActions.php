<?php

namespace App\Filament\Resources\EventResource\Traits;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use App\Models\EventCost;
use App\Models\EventPayment;
use App\Models\EventPaymentCostAllocation;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Pages\Finance\InstallmentControl;
use App\Services\EventSettlementExportService;

trait HasEventHeaderActions
{
    protected function getNavigationActions(): array
    {
        return [
            Actions\Action::make('edit_event')
                ->label('Dane imprezy')
                ->icon('heroicon-o-pencil')
                ->url(fn() => EventResource::getUrl('edit', ['record' => $this->record]))
                ->hidden(fn() => request()->routeIs('filament.admin.resources.events.edit'))
                ->color('gray'),

            // 'program' action removed: hide the "Program" link from header actions
        ];
    }

    protected function getParticipantsActionsGroup(): Actions\ActionGroup
    {
        return Actions\ActionGroup::make([
            Actions\Action::make('participantsFromPayments')
                ->label('Generuj z wpłat')
                ->icon('heroicon-o-user-group')
                ->url(fn() => \App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                    'match_mode' => 'event_code',
                    'key' => $this->record->public_code,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('participantsImportCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(fn() => \App\Filament\Pages\Participants\ImportParticipants::getUrl([
                    'match_mode' => 'event_code',
                    'key' => $this->record->public_code,
                ]))
                ->openUrlInNewTab(),
        ])
            ->label('Uczestnicy')
            ->icon('heroicon-m-users')
            ->color('gray')
            ->button();
    }

    protected function getFinanceActionsGroup(): Actions\ActionGroup
    {
        return Actions\ActionGroup::make([
            Actions\Action::make('autoAllocate')
                ->label('Auto-alokacja wpłat')
                ->icon('heroicon-o-arrows-right-left')
                ->modalHeading('Auto-alokacja wpłat do kosztów')
                ->modalDescription('System spróbuje przypisać dostępne środki z wpłat do niepokrytych kosztów (w PLN).')
                ->form([
                    \Filament\Forms\Components\Select::make('strategy')
                        ->label('Strategia')
                        ->options([
                            'by_due_date' => 'Po terminach kosztów (najpierw najbliższe)',
                            'insurance_first' => 'Najpierw ubezpieczenia, potem reszta',
                        ])
                        ->default('by_due_date')
                        ->required(),
                    \Filament\Forms\Components\Toggle::make('only_unpaid_costs')
                        ->label('Tylko koszty nieopłacone')
                        ->default(true)
                        ->inline(false),
                    \Filament\Forms\Components\Toggle::make('dry_run')
                        ->label('Tylko symulacja (bez zapisu)')
                        ->default(false)
                        ->inline(false),
                ])
                ->action(function (array $data) {
                    $event = $this->record;
                    $strategy = $data['strategy'] ?? 'by_due_date';
                    $onlyUnpaid = (bool) ($data['only_unpaid_costs'] ?? true);
                    $dryRun = (bool) ($data['dry_run'] ?? false);

                    $payments = $event->payments()
                        ->withSum('allocations', 'amount')
                        ->orderBy('payment_date')
                        ->orderBy('id')
                        ->get();

                    $costsQuery = $event->costs()->with(['currency'])->withSum('allocations', 'amount');
                    if ($onlyUnpaid) {
                        $costsQuery->where('is_paid', false);
                    }

                    // Sortowanie kosztów (terminy, a jeśli brak daty, na koniec)
                    $costs = $costsQuery
                        ->orderByRaw('payment_date is null')
                        ->orderBy('payment_date')
                        ->orderBy('id')
                        ->get();

                    if ($strategy === 'insurance_first') {
                        $costs = $costs->sortByDesc(function (EventCost $c) {
                            return str_contains(mb_strtolower((string) $c->name), 'ubezpiec');
                        })->values();
                    }

                    $paymentRemaining = [];
                    foreach ($payments as $p) {
                        $allocated = (float) ($p->allocations_sum_amount ?? 0);
                        $paymentRemaining[$p->id] = max(0.0, (float) $p->amount - $allocated);
                    }

                    $costRemaining = [];
                    foreach ($costs as $c) {
                        $allocated = (float) ($c->allocations_sum_amount ?? 0);
                        $costRemaining[$c->id] = max(0.0, (float) $c->amount_pln - $allocated);
                    }

                    $created = 0;
                    $allocatedTotal = 0.0;

                    foreach ($costs as $cost) {
                        $need = $costRemaining[$cost->id] ?? 0.0;
                        if ($need <= 0) {
                            continue;
                        }

                        foreach ($payments as $payment) {
                            $avail = $paymentRemaining[$payment->id] ?? 0.0;
                            if ($avail <= 0) {
                                continue;
                            }


                            if ($need <= 0) {
                                break;
                            }

                            $amount = min($avail, $need);
                            if ($amount <= 0) {
                                continue;
                            }

                            $paymentRemaining[$payment->id] = $avail - $amount;
                            $need -= $amount;
                            $costRemaining[$cost->id] = $need;
                            $allocatedTotal += $amount;

                            if (! $dryRun) {
                                EventPaymentCostAllocation::query()->create([
                                    'event_id' => $event->id,
                                    'event_payment_id' => $payment->id,
                                    'event_cost_id' => $cost->id,
                                    'contract_id' => $payment->contract_id,
                                    'user_id' => Auth::id(),
                                    'amount' => $amount,
                                    'note' => 'Auto-alokacja (' . $strategy . ')' . ($onlyUnpaid ? ' • only_unpaid' : ''),
                                    'allocated_at' => now(),
                                ]);
                            }

                            $created++;
                        }
                    }

                    $unallocated = array_sum($paymentRemaining);
                    $uncovered = array_sum($costRemaining);

                    $title = $dryRun ? 'Symulacja alokacji zakończona' : 'Auto-alokacja zakończona';
                    $body = 'Utworzono: ' . $created . ' alokacji • Zaalokowano: ' . number_format($allocatedTotal, 2, '.', ' ') . ' PLN'
                        . ' • Pozostało (wpłaty): ' . number_format($unallocated, 2, '.', ' ') . ' PLN'
                        . ' • Brakuje (koszty): ' . number_format($uncovered, 2, '.', ' ') . ' PLN';

                    Notification::make()->title($title)->success()->body($body)->send();
                }),

            Actions\Action::make('installmentsControl')
                ->label('Kontrola rat')
                ->icon('heroicon-o-calendar-days')
                ->visible(fn() => (bool) ($this->record->public_code ?? null))
                ->url(fn() => InstallmentControl::getUrl([
                    'scope' => 'overdue',
                    'event_code' => $this->record->public_code,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('installmentTasks')
                ->label('Zadania windykacyjne')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(fn() => \App\Filament\Resources\TaskResource::getUrl('index', [
                    'installments' => 1,
                    'event_code' => (string) ($this->record->public_code ?? ''),
                    'q' => $this->record->public_code ? null : $this->record->name,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('finalSettlement')
                ->label('Pobierz rozliczenie (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (EventSettlementExportService $exporter) {
                    return $exporter->exportCsv($this->record);
                }),
        ])
            ->label('Finanse')
            ->icon('heroicon-m-banknotes')
            ->color('gray')
            ->button();
    }
}
