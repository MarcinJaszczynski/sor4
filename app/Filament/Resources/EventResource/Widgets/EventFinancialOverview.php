<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Models\Currency;
use App\Models\Contract;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EventFinancialOverview extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-financial-overview';

    public ?Model $record = null;
    
    // Fallback if record is not injected automatically (though in Resource Pages it usually is if handled right)
    // We might need to receive the record from the page.

    public function mount($record = null) {
        // In Filament v3 widgets on Edit page don't always get $record automatically via prop if they are simple widgets. 
        // But if placed in getHeaderWidgets() or getFooterWidgets() of EditRecord page, $this->record should be available if we handle it.
        // Actually, often we rely on the `$record` property being set by the page.
    }

    public function getViewData(): array
    {
        if (!$this->record) {
             return [];
        }

        $event = $this->record;

        // 1. WPŁATY (INCOMES)
        // Contracts
        $contracts = $event->contracts()->with('participants')->get();
        
        // Suma kontraktów (oczekiwany przychód)
        $expectedIncome = 0;

        // Sprawdź czy mamy globalne rezygnacje (EventCancellation)
        $globalCancellations = $event->cancellations;
        $globalCancellationsQty = 0;
        $globalCancellationsFees = 0;
        
        if ($globalCancellations) {
            $globalCancellationsQty = $globalCancellations->sum('qty');
            $globalCancellationsFees = $globalCancellations->sum('amount');
        }

        $hasDetailedParticipants = false;

        foreach ($contracts as $contract) {
             $allParticipantsCount = $contract->participants->count();
             
             if ($allParticipantsCount > 0) {
                 $hasDetailedParticipants = true;
                 
                 $basePrice = 0;
                 if ($contract->locked_price_per_person > 0) {
                     $basePrice = $contract->locked_price_per_person;
                 } else {
                     if ($contract->total_amount > 0) {
                          $basePrice = $contract->total_amount / $allParticipantsCount;
                     }
                 }

                 $cancelledParticipants = $contract->participants->where('status', 'cancelled');
                 $cancelledCount = $cancelledParticipants->count();
                 $activeCount = $allParticipantsCount - $cancelledCount;

                 $activeParticipantsIncome = $activeCount * $basePrice;
                 $cancellationFees = $cancelledParticipants->sum('cancellation_fee');

                 $expectedIncome += ($activeParticipantsIncome + $cancellationFees);
             }
        }

        if (!$hasDetailedParticipants) {
             // TRYB AGREGUJĄCY (Brak listy uczestników)
             $totalContractsAmount = $contracts->sum('total_amount');
             $currentCount = $event->participant_count;
             
             $originalCount = $currentCount + $globalCancellationsQty;
             
             $pricePerPerson = 0;
             $mainContract = $contracts->sortByDesc('total_amount')->first();
             
             if ($mainContract && $mainContract->locked_price_per_person > 0) {
                 $pricePerPerson = $mainContract->locked_price_per_person;
             } elseif ($originalCount > 0) {
                 $pricePerPerson = $totalContractsAmount / $originalCount;
             }

             $expectedIncome = ($currentCount * $pricePerPerson) + $globalCancellationsFees;
        } else {
             $expectedIncome += $globalCancellationsFees;
        }
        
        // Suma wpłacona (EventPayments)
        $receivedIncome = (float) $event->payments()->sum('amount');

        $pilotCashTotals = $event->payments()
            ->where('source', 'pilot_cash')
            ->get()
            ->groupBy(fn ($payment) => $payment->currency ?: 'PLN')
            ->map(fn ($items) => (float) $items->sum('amount'))
            ->toArray();

        // Alokacje wpłat -> koszty (w PLN)
        $allocatedToCosts = (float) $event->paymentAllocations()->sum('amount');
        $unallocatedPayments = max(0.0, (float) $receivedIncome - (float) $allocatedToCosts);


        // Alerts for Incomes
        $incomeAlerts = [];
        $daysToStart = now()->diffInDays($event->start_date, false);
        
        // Reguła: 100% wpłat na 14 dni przed
        $missingIncome = $expectedIncome - $receivedIncome;
        if ($missingIncome > 1 && $daysToStart <= 14 && $daysToStart >= 0) {
             $incomeAlerts[] = "Brakuje wpłat na kwotę: " . number_format($missingIncome, 2) . " PLN (Impreza za $daysToStart dni)";
        }
        if ($missingIncome > 1 && $daysToStart < 0) {
             $incomeAlerts[] = "Impreza rozpoczęta/zakończona, a brakuje wpłat: " . number_format($missingIncome, 2) . " PLN";
        }


        // 2. WYDATKI (EXPENSES)
        // Koszty zdefiniowane w EventCost (przeliczamy na PLN wg kursu waluty)
        $plnIds = Currency::plnIds();

        $costs = $event->costs()->with('currency')->get();
        $plannedCosts = 0.0;
        $paidCosts = 0.0;

        foreach ($costs as $cost) {
            $amount = (float) ($cost->amount ?? 0);

            $rate = 1.0;
            if ($cost->currency_id && ! in_array((int) $cost->currency_id, $plnIds, true)) {
                $rate = (float) ($cost->currency?->exchange_rate ?? 1);
                if ($rate <= 0) {
                    $rate = 1.0;
                }
            }

            $amountPln = $amount * $rate;
            $plannedCosts += $amountPln;
            if ((bool) $cost->is_paid) {
                $paidCosts += $amountPln;
            }
        }

        $uncoveredCosts = max(0.0, (float) $plannedCosts - (float) $allocatedToCosts);

        $allocationAlerts = [];
        if ($uncoveredCosts > 1 && $unallocatedPayments > 1) {
            $allocationAlerts[] = 'Masz niezaalokowane środki z wpłat oraz koszty niepokryte alokacjami — użyj „Auto-alokuj wpłaty” lub alokuj ręcznie.';
        } elseif ($uncoveredCosts > 1 && $unallocatedPayments <= 1) {
            $allocationAlerts[] = 'Koszty nie są w pełni pokryte alokacjami, a nie ma wolnych środków z wpłat — brakuje wpłat lub alokacje są niepełne.';
        }
        
        // Check overdue costs
        $expenseAlerts = [];
        foreach ($costs as $cost) {
            if (!$cost->is_paid && $cost->payment_date && $cost->payment_date->isPast()) {
                $expenseAlerts[] = "Przeterminowana płatność dla: {$cost->name} ({$cost->amount} PLN, termin: {$cost->payment_date->format('Y-m-d')})";
            }
        }

        $expectedProfit = (float) $expectedIncome - (float) $plannedCosts;
        $realProfit = (float) $receivedIncome - (float) $paidCosts;
        $cashBalance = (float) $receivedIncome - (float) $paidCosts;

        $contractsSummary = $event->contracts()
            ->withSum('payments', 'amount')
            ->withCount('participants')
            ->orderBy('contract_number')
            ->get()
            ->map(function ($contract) {
                $expected = (float) ($contract->total_amount ?? 0);
                $paid = (float) ($contract->payments_sum_amount ?? 0);
                $missing = max(0.0, $expected - $paid);

                return [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'participants_count' => (int) ($contract->participants_count ?? 0),
                    'expected' => $expected,
                    'paid' => $paid,
                    'missing' => $missing,
                    'is_fully_paid' => $expected > 0 && $missing <= 0.01,
                ];
            })
            ->toArray();

        $today = now()->startOfDay();
        $soonUntil = now()->addDays(14)->endOfDay();

        $contractsForInstallments = $event->contracts()->with('installments')->get();
        $overdueInstallmentsAmount = 0.0;
        $dueSoonInstallmentsAmount = 0.0;
        $overdueInstallmentsList = [];

        foreach ($contractsForInstallments as $contract) {
            foreach ($contract->installments as $inst) {
                if ((bool) ($inst->is_paid ?? false)) {
                    continue;
                }

                $due = $inst->due_date;
                if (!$due) {
                    continue;
                }

                $amount = (float) ($inst->amount ?? 0);

                if ($due->lt($today)) {
                    $overdueInstallmentsAmount += $amount;
                    $overdueInstallmentsList[] = [
                        'contract_number' => $contract->contract_number,
                        'due_date' => $due,
                        'amount' => $amount,
                        'url' => \App\Filament\Resources\ContractResource::getUrl('edit', ['record' => $contract->id]),
                    ];
                } elseif ($due->between($today, $soonUntil)) {
                    $dueSoonInstallmentsAmount += $amount;
                }
            }
        }

        usort($overdueInstallmentsList, fn ($a, $b) => ($a['due_date'] <=> $b['due_date']));
        $overdueInstallmentsList = array_slice($overdueInstallmentsList, 0, 7);


        return [
            'expectedIncome' => $expectedIncome,
            'receivedIncome' => $receivedIncome,
            'pilotCashTotals' => $pilotCashTotals,
            'incomeAlerts' => $incomeAlerts,
            'plannedCosts' => $plannedCosts,
            'paidCosts' => $paidCosts,
            'expenseAlerts' => $expenseAlerts,
            'expectedProfit' => $expectedProfit,
            'realProfit' => $realProfit,
            'cashBalance' => $cashBalance,
            'allocatedToCosts' => $allocatedToCosts,
            'unallocatedPayments' => $unallocatedPayments,
            'uncoveredCosts' => $uncoveredCosts,
            'allocationAlerts' => $allocationAlerts,
            'contractsSummary' => $contractsSummary,
            'overdueInstallmentsAmount' => $overdueInstallmentsAmount,
            'dueSoonInstallmentsAmount' => $dueSoonInstallmentsAmount,
            'overdueInstallmentsList' => $overdueInstallmentsList,
        ];
    }
}
