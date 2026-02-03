<?php

namespace App\Filament\Resources\EventResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Event;

class EventPriceVariantsWidget extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-price-variants-widget';
    public ?Event $record = null;
    protected int | string | array $columnSpan = 'full';

    public $detailedCalculations = [];

    public function mount()
    {
        if ($this->record) {
            $this->loadCalculations();
        }
    }

    public function loadCalculations()
    {
        $this->detailedCalculations = [];

        $eventPrices = $this->record->pricePerPerson()->get();

        if ($eventPrices && $eventPrices->count() > 0) {
            foreach ($eventPrices as $ep) {
                $qty = $ep->event_template_qty_id ? null : ($ep->qty ?? null);
                $qtyLabel = $ep->event_template_qty_id ? 'Wariant szablonu' : (($ep->qty ?? null) ? $ep->qty . ' osób' : 'Domyślny');

                $this->detailedCalculations[] = [
                    'qty' => $qty,
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

        $engine = new \App\Services\EventCalculationEngine();

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
                'transport_cost' => $calculation['transport_cost'],
                'markup_amount' => $calculation['markup_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'total_cost' => $calculation['total_cost'],
                'cost_per_person' => $calculation['cost_per_person'],
            ];
        }
    }
}
