<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EventBudgetSnapshot extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-budget-snapshot';

    public ?Model $record = null;

    public function getViewData(): array
    {
        /** @var Event $event */
        $event = $this->record;
        if (! $event) {
            return [];
        }

        $plannedCosts = (float) $event->costs()->withSum('allocations', 'amount')->get()->sum(fn ($c) => (float) $c->amount_pln);
        $paidCosts = (float) $event->costs()->where('is_paid', true)->get()->sum(fn ($c) => (float) $c->amount_pln);
        $payments = (float) $event->payments()->sum('amount');
        $allocated = (float) $event->paymentAllocations()->sum('amount');

        $marginPlan = $payments - $plannedCosts;
        $marginReal = $payments - $paidCosts;

        return [
            'plannedCosts' => $plannedCosts,
            'paidCosts' => $paidCosts,
            'payments' => $payments,
            'allocated' => $allocated,
            'marginPlan' => $marginPlan,
            'marginReal' => $marginReal,
        ];
    }
}
