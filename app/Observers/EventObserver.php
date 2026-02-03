<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\EventChecklistService;
use Illuminate\Support\Facades\Auth;

class EventObserver
{
    public function saving(Event $event): void
    {
        try {
            $engine = new \App\Services\EventCalculationEngine();
            // Calculation might be heavy or rely on relations not yet loaded.
            // Be careful about infinite loops if calculate saves event (it shouldn't).
            // It modifies event attributes.
            
            $result = $engine->calculate($event);
            
            // Assign results to Event attributes
            // We only update if they changed to avoid dirty checks loop? 
            // Saving event triggers saving.
            
            $event->total_cost = $result['total_cost'];
            if (isset($result['final_price_per_person'])) {
                 $event->calculated_price_per_person = $result['final_price_per_person'];
            }
        } catch (\Exception $e) {
            // Log error but don't stop saving - or maybe stop?
            // Usually safe to ignore calculation fails and keep old values if critical
            \Illuminate\Support\Facades\Log::error("Event calculation failed for event {$event->id}: " . $e->getMessage());
        }
    }

    public function creating(Event $event): void
    {
        if (! $event->assigned_to) {
            $event->assigned_to = Auth::id();
        }
    }

    public function created(Event $event): void
    {
        app(EventChecklistService::class)->ensureDefaults($event);
        app(EventChecklistService::class)->updateAuto($event);
    }

    public function updated(Event $event): void
    {
        app(EventChecklistService::class)->updateAuto($event);
    }
}
