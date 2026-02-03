<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Models\EventCost;
use App\Services\EventCalculationEngine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class EventCostsStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        // Ensure we have an Event record
        // In RelationManager context, usually $record is passed as the Owner Record (The Event)
        if (!$this->record || !($this->record instanceof Event)) {
            return [
                Stat::make('Błąd', 'Brak kontekstu imprezy'),
            ];
        }

        $event = $this->record;

        // 1. Calculate Estimated Costs (Planned)
        $engine = new EventCalculationEngine();
        $pax = $event->participant_count ?? 0;
        
        // Run calculation
        // Note: This relies on Event having all necessary fields (transport, start_place, etc.)
        $calc = $engine->calculate(
            $event, 
            $pax, 
            $event->gratis_count ?? 0, 
            $event->staff_count ?? 0, 
            $event->driver_count ?? 0, 
            $event->guide_count ?? 0
        );
        
        // Sum up cost components (Expeditures)
        $estimatedCostPLN = 
            ($calc['program_cost'] ?? 0) + 
            ($calc['accommodation_cost'] ?? 0) + 
            ($calc['insurance_cost'] ?? 0) + 
            ($calc['transport_cost'] ?? 0);

        // 2. Actual Costs (Sum of Expenses)
        $actualCostsPLN = 0;
        
        // Fetch all costs related to this event
        $costs = EventCost::where('event_id', $event->id)->with('currency')->get();
        
        foreach ($costs as $cost) {
            $amount = $cost->amount;
            $rate = 1;
            
            // Simple currency conversion based on stored rate or current DB rate
            if ($cost->currency_id && $cost->currency) {
                 if ($cost->currency->symbol !== 'PLN') {
                     $rate = $cost->currency->exchange_rate ?? 1;
                 }
            }
            // If currency_id is missing but we assume PLN? Or skip?
            // Usually field required.
            
            $actualCostsPLN += ($amount * $rate);
        }

        $balance = $estimatedCostPLN - $actualCostsPLN;

        return [
            Stat::make('Planowane koszty', number_format($estimatedCostPLN, 2) . ' PLN')
                ->description('Według kalkulacji dla ' . $pax . ' os.')
                ->descriptionIcon('heroicon-m-calculator')
                ->chart([$estimatedCostPLN, $estimatedCostPLN])
                ->color('gray'),

            Stat::make('Poniesione wydatki', number_format($actualCostsPLN, 2) . ' PLN')
                ->description('Suma wprowadzonych kosztów')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([0, $actualCostsPLN])
                ->color($actualCostsPLN > $estimatedCostPLN ? 'danger' : 'success'),

            Stat::make('Bilans (Budżet)', number_format($balance, 2) . ' PLN')
                ->description($balance < 0 ? 'Przekroczenie budżetu' : 'Pozostało do wykorzystania')
                ->color($balance < 0 ? 'danger' : 'success'),
        ];
    }
}
