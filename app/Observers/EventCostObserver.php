<?php

namespace App\Observers;

use App\Models\EventCost;
use App\Services\EventChecklistService;

class EventCostObserver
{
    public function created(EventCost $cost): void
    {
        if ($cost->event) {
            app(EventChecklistService::class)->updateAuto($cost->event);
        }
    }

    public function updated(EventCost $cost): void
    {
        if ($cost->event) {
            app(EventChecklistService::class)->updateAuto($cost->event);
        }
    }
}
