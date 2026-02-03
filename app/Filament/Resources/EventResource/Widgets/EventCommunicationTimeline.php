<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\ContractInstallmentReminder;
use App\Models\EmailMessage;
use App\Models\Event;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class EventCommunicationTimeline extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-communication-timeline';

    public ?Model $record = null;

    public function getViewData(): array
    {
        /** @var Event $event */
        $event = $this->record;
        if (! $event) {
            return [];
        }

        $emails = $event->emails()->latest('emailables.created_at')->limit(5)->get();

        $reminders = ContractInstallmentReminder::query()
            ->with(['installment.contract'])
            ->whereHas('installment.contract', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->latest('sent_at')
            ->limit(5)
            ->get();

        return [
            'emails' => $emails,
            'reminders' => $reminders,
        ];
    }
}
