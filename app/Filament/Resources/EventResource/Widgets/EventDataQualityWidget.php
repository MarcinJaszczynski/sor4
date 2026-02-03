<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Filament\Pages\Finance\InstallmentControl;
use App\Filament\Resources\EventPaymentResource;
use App\Filament\Resources\EventResource;
use App\Models\Event;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EventDataQualityWidget extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-data-quality';

    public ?Model $record = null;

    public function getViewData(): array
    {
        /** @var Event $event */
        $event = $this->record;
        if (! $event) {
            return [];
        }

        $issues = [];

        if (! $event->assigned_to) {
            $issues[] = [
                'label' => 'Brak przypisanego opiekuna',
                'url' => EventResource::getUrl('edit', ['record' => $event->id]),
            ];
        }

        if ($event->contracts()->count() === 0) {
            $issues[] = [
                'label' => 'Brak umów dla imprezy',
                'url' => EventResource::getUrl('edit', ['record' => $event->id]),
            ];
        }

        $installmentsCount = $event->contracts()->withCount('installments')->get()->sum('installments_count');
        if ($installmentsCount === 0) {
            $issues[] = [
                'label' => 'Brak harmonogramu rat',
                'url' => InstallmentControl::getUrl(['scope' => 'all', 'event_code' => $event->public_code]),
            ];
        }

        if ($event->payments()->sum('amount') <= 0) {
            $issues[] = [
                'label' => 'Brak zaksięgowanych wpłat',
                'url' => EventPaymentResource::getUrl('index', [
                    'tableFilters' => [
                        'event' => ['value' => $event->id],
                    ],
                ]),
            ];
        }

        if ($event->costs()->count() === 0) {
            $issues[] = [
                'label' => 'Brak kosztów',
                'url' => EventResource::getUrl('edit', ['record' => $event->id]),
            ];
        }

        return [
            'issues' => $issues,
        ];
    }
}
