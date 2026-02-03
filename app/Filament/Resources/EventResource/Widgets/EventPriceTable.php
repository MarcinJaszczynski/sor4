<?php

namespace App\Filament\Resources\EventResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Event;
use App\Models\Currency;
use App\Models\EventProgramPoint;

class EventPriceTable extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-price-table';
    public ?Event $record = null;
    protected int | string | array $columnSpan = 'full';
    
    public $calculations = [];
    public $programPoints;
    public $costsByDay;
    public $transportCost = 0;
    public $detailedCalculations = [];
    public $editingPrice = null; // holds EventPricePerPerson model data for inline editing
    
    public function mount()
    {
        if ($this->record) {
            $this->loadCalculations();
        }
    }

    public function loadCalculations()
    {
        // Załaduj punkty programu z kosztami
        $this->programPoints = $this->record->programPoints()
            ->with('templatePoint')
            ->where('active', true)
            ->orderBy('day')
            ->orderBy('order')
            ->get();

        // Oblicz koszty transportu (podobnie jak w EventTemplate)
        $this->calculateTransportCost();

        // Oblicz koszty według dni
        $this->costsByDay = $this->programPoints
            ->groupBy('day')
            ->map(function ($points) {
                $totalCostPln = 0.0;
                $programCostPln = 0.0;
                $foreignTotals = [];

                foreach ($points as $point) {
                    $amount = (float) ($point->total_price ?? 0);
                    $baseCurrency = $this->getPointBaseCurrencySymbol($point);
                    $rate = $this->getPointBaseCurrencyRate($point);
                    $convert = (bool) ($point->convert_to_pln ?? false);

                    if ($baseCurrency === 'PLN' || $convert) {
                        if ($baseCurrency !== 'PLN' && $convert) {
                            $amount *= $rate;
                        }
                        $totalCostPln += $amount;
                        if ($point->include_in_program) {
                            $programCostPln += $amount;
                        }
                    } else {
                        $foreignTotals[$baseCurrency] = ($foreignTotals[$baseCurrency] ?? 0) + $amount;
                    }
                }

                return [
                    'points_count' => $points->count(),
                    'total_cost' => $totalCostPln,
                    'program_cost' => $programCostPln,
                    'foreign_totals' => $foreignTotals,
                    'calculation_points' => $points->filter(function ($p) { return (bool)($p->include_in_calculation ?? true); })->count(),
                    'program_points' => $points->where('include_in_program', true)->count(),
                    'points' => $points,
                ];
            });

        // Oblicz główne kalkulacje używając silnika
        $engine = new \App\Services\EventCalculationEngine();
        $mainCalculation = $engine->calculate($this->record);

        $this->calculations = [
            'total_points' => $this->programPoints->count(),
            'active_points' => $this->programPoints->where('active', true)->count(),
            'calculation_points' => \App\Services\ProgramPointHelper::countIncluded($this->programPoints),
            'program_points' => $this->programPoints->where('include_in_program', true)->count(),
            'total_program_cost' => $mainCalculation['program_cost'],
            'transport_cost' => $mainCalculation['transport_cost'],
            'accommodation_cost' => $mainCalculation['accommodation_cost'] ?? 0,
            'tax_amount' => $mainCalculation['tax_amount'] ?? 0,
            'markup_amount' => $mainCalculation['markup_amount'] ?? 0,
            'total_cost' => $mainCalculation['total_cost'],
            'program_cost' => $mainCalculation['program_cost'],
            'cost_per_person' => $mainCalculation['cost_per_person'],
            'currencies' => $mainCalculation['currencies'] ?? [],
            'currencies_per_person' => $mainCalculation['currencies_per_person'] ?? [],
            'days_count' => $this->costsByDay->count(),
            'event_data' => [
                'name' => $this->record->name,
                'client_name' => $this->record->client_name,
                'participant_count' => $this->record->participant_count,
                'start_date' => $this->record->start_date,
                'end_date' => $this->record->end_date,
                'duration_days' => $this->record->duration_days,
                'transfer_km' => $this->record->transfer_km,
                'program_km' => $this->record->program_km,
                'status' => $this->record->status,
                'template_name' => $this->record->eventTemplate->name,
                'bus_name' => $this->record->bus?->name,
                'markup_name' => $this->record->markup?->name,
            ],
        ];

        // Oblicz szczegółowe kalkulacje z uwzględnieniem różnych wariantów
        $this->calculateDetailedPricing();
    }

    public function getPointBaseCurrencySymbol(EventProgramPoint $point): string
    {
        if ($point->currency_id) {
            $currency = Currency::find($point->currency_id);
            return $currency?->symbol ?? 'PLN';
        }

        if ($point->templatePoint?->currency) {
            return $point->templatePoint->currency->symbol ?? 'PLN';
        }

        return 'PLN';
    }

    public function getPointBaseCurrencyRate(EventProgramPoint $point): float
    {
        if ($point->currency_id) {
            $currency = Currency::find($point->currency_id);
            return (float) ($currency?->exchange_rate ?? 1);
        }

        if ($point->templatePoint?->currency) {
            return (float) ($point->templatePoint->currency->exchange_rate ?? 1);
        }

        return 1.0;
    }

    public function getPointDisplayCurrencySymbol(EventProgramPoint $point): string
    {
        $base = $this->getPointBaseCurrencySymbol($point);
        if (($point->convert_to_pln ?? false) && $base !== 'PLN') {
            return 'PLN';
        }
        return $base;
    }

    public function getPointDisplayAmount(EventProgramPoint $point, ?float $amount = null): float
    {
        $amount = $amount ?? (float) ($point->total_price ?? 0);
        $base = $this->getPointBaseCurrencySymbol($point);
        if (($point->convert_to_pln ?? false) && $base !== 'PLN') {
            return $amount * $this->getPointBaseCurrencyRate($point);
        }
        return $amount;
    }

    public function calculateTransportCost()
    {
        // Transport cost is now calculated in EventCalculationEngine, 
        // but we might keep this method if it's used elsewhere or just for compatibility.
        // However, loadCalculations calls it.
        // Since we use EventCalculationEngine in loadCalculations, we can rely on it.
        // But calculateDetailedPricing might use $this->transportCost if we fallback?
        // No, I will update calculateDetailedPricing to use Engine too.
        
        // Let's keep this method but maybe update it to match Engine logic or just leave it as is
        // since we overwrite transport_cost in calculations array anyway.
        // But $this->transportCost property is public and might be used in view?
        // View uses $transportCost variable passed from render? No, it uses $calculations array mostly.
        // But wait, view has: {{ number_format($transportCost ?? 0, 2) }} PLN
        // And in render/mount?
        // Widget renders view. View has access to public properties.
        // So $this->transportCost is used.
        
        $this->transportCost = 0;
        
        if (!$this->record->bus) {
            return;
        }

        $bus = $this->record->bus;
        $transferKm = $this->record->transfer_km ?? 0;
        $programKm = $this->record->program_km ?? 0;
        $duration = $this->record->duration_days ?? 1;

        $totalKm = 2 * $transferKm + $programKm;
        $includedKm = $duration * ($bus->package_km_per_day ?? 0);
        $baseCost = $duration * ($bus->package_price_per_day ?? 0);

        if ($totalKm <= $includedKm) {
            $this->transportCost = $baseCost;
        } else {
            $extraKm = $totalKm - $includedKm;
            $this->transportCost = $baseCost + ($extraKm * ($bus->extra_km_price ?? 0));
        }

        // Przelicz na PLN jeśli autokar ma inną walutę
        if ($bus->currency && $bus->currency !== 'PLN') {
            // Znajdź walutę w tabeli currencies po symbolu
            $currency = \App\Models\Currency::where('symbol', $bus->currency)->first();
            $exchangeRate = $currency?->exchange_rate ?? 1;
            $this->transportCost *= $exchangeRate;
        }
    }

    public function calculateDetailedPricing()
    {
        $this->detailedCalculations = [];

        // Najpierw spróbuj użyć event-scoped pricePerPerson gdy istnieją
        $eventPrices = $this->record->pricePerPerson()->get();

        if ($eventPrices && $eventPrices->count() > 0) {
            foreach ($eventPrices as $ep) {
                $qty = $ep->event_template_qty_id ? null : ($ep->qty ?? null);
                $qtyLabel = $ep->event_template_qty_id ? 'Wariant szablonu' : ( ($ep->qty ?? null) ? $ep->qty . ' osób' : 'Domyślny');

                $this->detailedCalculations[] = [
                    'qty' => $ep->event_template_qty_id ? null : ($ep->qty ?? null),
                    'name' => $qtyLabel,
                    'program_cost' => $ep->price_base ?? 0,
                    'transport_cost' => $ep->transport_cost ?? 0,
                    'markup_amount' => $ep->markup_amount ?? 0,
                    'tax_amount' => $ep->tax_amount ?? 0,
                    'total_cost' => $ep->price_with_tax ?? ($ep->price_per_person * ($ep->qty ?? 1)),
                    'cost_per_person' => $ep->price_per_person ?? 0,
                ];
            }

            return;
        }

        // Użyj nowego silnika kalkulacji dla Event
        $engine = new \App\Services\EventCalculationEngine();
        
        // Generuj warianty: 10, 20, 30, 40, 50 oraz aktualna liczba uczestników
        $variants = collect([10, 20, 30, 40, 50]);
        if ($this->record->participant_count > 0) {
            $variants->push($this->record->participant_count);
        }
        $variants = $variants->unique()->sort()->values();

        foreach ($variants as $qty) {
            $calculation = $engine->calculate($this->record, $qty);
            
            $this->detailedCalculations[] = [
                'qty' => $qty,
                'name' => $qty . ' osób',
                'program_cost' => $calculation['program_cost'],
                'insurance_cost' => $calculation['insurance_cost'] ?? 0,
                'transport_cost' => $calculation['transport_cost'],
                'accommodation_cost' => $calculation['accommodation_cost'] ?? 0,
                'markup_amount' => $calculation['markup_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'total_cost' => $calculation['total_cost'],
                'cost_per_person' => $calculation['cost_per_person'],
                'program_points_breakdown' => $calculation['program_points_breakdown'] ?? [],
            ];
        }
    }

    public function refreshCalculations()
    {
        $this->record->refresh();
        
        // Recalculate using engine
        $engine = new \App\Services\EventCalculationEngine();
        $calculation = $engine->calculate($this->record);
        
        // Update Event total_cost
        $this->record->update(['total_cost' => $calculation['total_cost']]);
        
        // Update stored prices if they exist (only for the main event price without template_qty_id)
        $eventPrices = $this->record->pricePerPerson()->get();
        foreach ($eventPrices as $ep) {
            if (!$ep->event_template_qty_id) {
                $ep->update([
                    'price_base' => $calculation['program_cost'] + $calculation['transport_cost'],
                    'transport_cost' => $calculation['transport_cost'],
                    'markup_amount' => $calculation['markup_amount'],
                    'tax_amount' => $calculation['tax_amount'],
                    'price_with_tax' => $calculation['total_cost'],
                    'price_per_person' => $calculation['cost_per_person'],
                ]);
            }
        }

        $this->loadCalculations();
    }

    // --- Price editing helpers (can be called from front-end Livewire actions) ---
    public function editPrice(int $id)
    {
        $price = \App\Models\EventPricePerPerson::find($id);
        if (!$price || $price->event_id !== $this->record->id) {
            $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => 'Nie znaleziono ceny.']);
            return;
        }

        $this->editingPrice = $price->toArray();
    }

    public function saveEditingPrice($data = null)
    {
        // allow calling without params when modal is bound to $this->editingPrice
        if (is_null($data)) {
            $data = $this->editingPrice ?? [];
        }

        if (empty($data['id'])) {
            $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => 'Brak identyfikatora ceny.']);
            return;
        }

        $price = \App\Models\EventPricePerPerson::find($data['id']);
        if (!$price || $price->event_id !== $this->record->id) {
            $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => 'Nieprawidłowy rekord ceny.']);
            return;
        }

        // Validate basic numeric fields
        $errors = [];
        if (isset($data['price_per_person']) && !is_numeric($data['price_per_person'])) {
            $errors[] = 'Cena za osobę musi być liczbą.';
        }
        if (isset($data['transport_cost']) && !is_numeric($data['transport_cost'])) {
            $errors[] = 'Koszt transportu musi być liczbą.';
        }
        if (isset($data['price_with_tax']) && !is_numeric($data['price_with_tax'])) {
            $errors[] = 'Cena z podatkiem musi być liczbą.';
        }

        // Try to parse tax_breakdown if provided
        if (isset($data['tax_breakdown']) && $data['tax_breakdown'] !== null && $data['tax_breakdown'] !== '') {
            if (is_string($data['tax_breakdown'])) {
                $decoded = json_decode($data['tax_breakdown'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['tax_breakdown'] = $decoded;
                } else {
                    $errors[] = 'Nieprawidłowy format JSON dla rozbicia VAT.';
                }
            }
        }

        if (!empty($errors)) {
            $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => implode(' ', $errors)]);
            return;
        }

        // Zaktualizuj tylko bezpieczne pola
        $price->price_per_person = isset($data['price_per_person']) ? (float)$data['price_per_person'] : $price->price_per_person;
        $price->transport_cost = isset($data['transport_cost']) ? (float)$data['transport_cost'] : $price->transport_cost;
        $price->price_with_tax = isset($data['price_with_tax']) ? (float)$data['price_with_tax'] : $price->price_with_tax;
        $price->tax_breakdown = array_key_exists('tax_breakdown', $data) ? $data['tax_breakdown'] : $price->tax_breakdown;
        $price->save();

        $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Cena zapisana']);
        $this->editingPrice = null;
        $this->refreshCalculations();
    }

    public function deletePrice(int $id)
    {
        $price = \App\Models\EventPricePerPerson::find($id);
        if (!$price || $price->event_id !== $this->record->id) {
            $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => 'Nie znaleziono ceny.']);
            return;
        }

        $price->delete();
        $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Cena usunięta']);
        $this->refreshCalculations();
    }
}
