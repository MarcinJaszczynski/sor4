<?php

namespace App\Filament\Resources\EventTemplateResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\EventTemplate;

class EventTemplatePriceVariantsWidget extends Widget
{
    protected static string $view = 'filament.resources.event-template-resource.widgets.event-template-price-variants-widget';
    public ?EventTemplate $record = null;
    protected int | string | array $columnSpan = 'full';

    public $priceVariants = [];

    public function mount()
    {
        if ($this->record) {
            $this->loadVariants();
        }
    }

    public function loadVariants()
    {
        $this->priceVariants = $this->record->qtyVariants()
            ->with('currency')
            ->orderBy('qty')
            ->get()
            ->map(function ($variant) {
                return [
                    'qty' => $variant->qty,
                    'name' => $variant->qty . ' osób',
                    'unit_price' => $variant->unit_price ?? 0,
                    'currency' => $variant->currency?->code ?? 'PLN',
                    'total_price' => ($variant->unit_price ?? 0) * $variant->qty,
                ];
            })
            ->toArray();
    }
}
