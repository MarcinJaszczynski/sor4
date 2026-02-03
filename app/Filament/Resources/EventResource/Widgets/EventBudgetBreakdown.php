<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Models\EventCost;
use App\Services\EventCalculationEngine;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EventBudgetBreakdown extends Widget
{
    protected static string $view = 'filament.widgets.event-budget-breakdown';
    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    protected function getViewData(): array
    {
        if (!$this->record || !($this->record instanceof Event)) {
            return [
                'budgetRows' => [],
                'totalPlanned' => 0,
                'totalActual' => 0,
                'totalDiff' => 0,
                'currency' => 'PLN',
            ];
        }

        $event = $this->record;

        // 1. Get Plan
        $engine = new EventCalculationEngine();
        $pax = $event->participant_count ?? 0;
        $calc = $engine->calculate(
            $event, 
            $pax, 
            $event->gratis_count ?? 0, 
            $event->staff_count ?? 0, 
            $event->driver_count ?? 0, 
            $event->guide_count ?? 0
        );

        $planAccommodation = $calc['accommodation_cost'] ?? 0;
        $planTransport = $calc['transport_cost'] ?? 0;
        $planInsurance = $calc['insurance_cost'] ?? 0;
        $planProgramTotal = $calc['program_cost'] ?? 0;
        
        // Detailed program point plan if possible
        // $calc['program_points_breakdown'] contains ID => cost
        $planProgramBreakdown = $calc['program_points_breakdown'] ?? [];


        // 2. Get Actuals
        $costs = EventCost::where('event_id', $event->id)->with('currency')->get();
        
        $actualAccommodation = 0;
        $actualTransport = 0;
        $actualInsurance = 0;
        $actualOther = 0;
        $actualProgramMap = []; // point_id => amount

        foreach ($costs as $cost) {
            // Convert to PLN
            $rate = 1;
            if ($cost->currency_id && $cost->currency && $cost->currency->symbol !== 'PLN') {
                 $rate = $cost->currency->exchange_rate ?? 1;
            }
            // Use Invoice Amount for "Cost Incurred" (Rzeczywiste poniesione koszty / Faktury)
            $amountPLN = $cost->amount * $rate;
            
            // Note: If user wants "Cash Flow" (Paid Amount), we would use $cost->paid_amount
            // But usually Budget Comparison matches "Invoice Total" vs "Budget".
            // However, the user asked for "druga kwota zaplacona i obie powinny sie wyswietlac w koszty i rozliczenia pozniej"
            // The request "niech bedzie to tez widoczne w koszty i rozliczenia" referred to the Stats/Table? 
            // The previous request was about "planowana vs rzeczywista".
            // I will assume the budget table should remain "Invoice vs Budget". 
            // The table has now been updated with 'amount' and 'paid_amount'.
            
            // Allocation logic
            if ($cost->event_program_point_id) {
                // It's a specific program point
                if (!isset($actualProgramMap[$cost->event_program_point_id])) {
                    $actualProgramMap[$cost->event_program_point_id] = 0;
                }
                $actualProgramMap[$cost->event_program_point_id] += $amountPLN;
            } else {
                // Use Category
                switch ($cost->category) {
                    case 'accommodation':
                        $actualAccommodation += $amountPLN;
                        break;
                    case 'transport':
                        $actualTransport += $amountPLN;
                        break;
                    case 'insurance':
                        $actualInsurance += $amountPLN;
                        break;
                    default:
                        $actualOther += $amountPLN;
                        break;
                }
            }
        }

        // 3. Build Rows
        $rows = [];

        // Accommodation
        $diff = $planAccommodation - $actualAccommodation;
        $rows[] = [
            'name' => 'Noclegi i wyżywienie (Hotel)',
            'planned' => $planAccommodation,
            'actual' => $actualAccommodation,
            'diff' => $diff
        ];

        // Transport
        $diff = $planTransport - $actualTransport;
        $rows[] = [
            'name' => 'Transport (Autokar)',
            'planned' => $planTransport,
            'actual' => $actualTransport,
            'diff' => $diff
        ];

        // Insurance
        $diff = $planInsurance - $actualInsurance;
        $rows[] = [
            'name' => 'Ubezpieczenie',
            'planned' => $planInsurance,
            'actual' => $actualInsurance,
            'diff' => $diff
        ];

        // Program Points
        // Get all point names
        $pointIds = array_unique(array_merge(array_keys($planProgramBreakdown), array_keys($actualProgramMap)));
        if (!empty($pointIds)) {
             $ppNames = \App\Models\EventProgramPoint::whereIn('id', $pointIds)->pluck('name', 'id');
             
             foreach ($pointIds as $pid) {
                 $pPlan = $planProgramBreakdown[$pid] ?? 0;
                 $pActual = $actualProgramMap[$pid] ?? 0;
                 $rows[] = [
                     'name' => 'Program: ' . ($ppNames[$pid] ?? 'Punkt #' . $pid),
                     'planned' => $pPlan,
                     'actual' => $pActual,
                     'diff' => $pPlan - $pActual
                 ];
             }
        }

        // Other (Uncategorized)
        if ($actualOther > 0) {
            $rows[] = [
                'name' => 'Inne / Nieprzypisane',
                'planned' => 0,
                'actual' => $actualOther,
                'diff' => 0 - $actualOther
            ];
        }

        $totalPlanned = $planAccommodation + $planTransport + $planInsurance + $planProgramTotal; // Program Total usually matches sum of points
        $totalActual = $actualAccommodation + $actualTransport + $actualInsurance + array_sum($actualProgramMap) + $actualOther;
        $totalDiff = $totalPlanned - $totalActual;

        return [
            'budgetRows' => $rows,
            'totalPlanned' => $totalPlanned,
            'totalActual' => $totalActual,
            'totalDiff' => $totalDiff,
            'currency' => 'PLN',
        ];
    }
}
