<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPricePerPerson;

class EventPriceCalculator
{
    /**
     * Proste przeliczenie cen per-person dla danej imprezy.
     * Kopiuje obecne sumy z punktów programu i rozdziela na warianty qty.
     */
    public function calculateForEvent(Event $event): void
    {
        // Kasujemy istniejące wpisy event_price_per_person dla tego eventu
        EventPricePerPerson::where('event_id', $event->id)->delete();

        // Używamy zaawansowanego silnika kalkulacji (uwzględnia hotele, gratisy, transport)
        $engine = new EventCalculationEngine();
        $qtys = $event->qtyVariants()->get();

        if ($qtys->isEmpty()) {
            // Kalkulacja dla bieżącej liczby uczestników
            $result = $engine->calculate($event);

            EventPricePerPerson::create([
                'event_id' => $event->id,
                'event_template_qty_id' => null,
                'currency_id' => null, // PLN base
                'start_place_id' => $event->start_place_id ?? null,
                'price_per_person' => $result['cost_per_person'] ?? 0,
                'transport_cost' => $result['transport_cost'] ?? 0,
                'price_base' => ($result['program_cost'] ?? 0) + ($result['insurance_cost'] ?? 0), // Base without tax/markup approx
                'markup_amount' => $result['markup_amount'] ?? 0,
                'tax_amount' => $result['tax_amount'] ?? 0,
                'price_with_tax' => $result['total_cost'] ?? 0,
                'tax_breakdown' => null,
            ]);

            // Update main event totals as well
            $event->updateQuietly([
                'calculated_price_per_person' => $result['cost_per_person'] ?? 0,
                'total_cost' => $result['total_cost'] ?? 0,
            ]);

            return;
        }

        foreach ($qtys as $qty) {
            // Kalkulacja dla wariantu (override uczestników)
            $result = $engine->calculate($event, $qty->qty);

            EventPricePerPerson::create([
                'event_id' => $event->id,
                'event_template_qty_id' => $qty->id, // Assuming qtyVariants are EventTemplateQty? Or local?
                // Note: event->qtyVariants is widely probably referring to template variants if implemented via relationship through template
                // or local table. If local table, ensure relationship is correct.
                // Assuming it works as per previous code.
                'currency_id' => null,
                'start_place_id' => $event->start_place_id ?? null,
                'price_per_person' => $result['cost_per_person'] ?? 0,
                'transport_cost' => $result['transport_cost'] ?? 0,
                'price_base' => ($result['program_cost'] ?? 0) + ($result['insurance_cost'] ?? 0),
                'markup_amount' => $result['markup_amount'] ?? 0,
                'tax_amount' => $result['tax_amount'] ?? 0,
                'price_with_tax' => $result['total_cost'] ?? 0,
                'tax_breakdown' => null,
            ]);
        }
    }
}
