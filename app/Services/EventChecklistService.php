<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventChecklistItem;
use Illuminate\Support\Carbon;

class EventChecklistService
{
    public function ensureDefaults(Event $event): void
    {
        if ($event->checklistItems()->exists()) {
            return;
        }

        $defaults = [
            ['stage' => 'Planowanie', 'key' => 'event_details', 'label' => 'Uzupełnij dane imprezy', 'order' => 10],
            ['stage' => 'Planowanie', 'key' => 'assigned_owner', 'label' => 'Przypisz opiekuna', 'order' => 20],
            ['stage' => 'Umowy', 'key' => 'contract_created', 'label' => 'Utwórz umowę', 'order' => 30],
            ['stage' => 'Umowy', 'key' => 'installments_created', 'label' => 'Utwórz harmonogram rat', 'order' => 40],
            ['stage' => 'Realizacja', 'key' => 'participants_added', 'label' => 'Dodaj uczestników', 'order' => 50],
            ['stage' => 'Realizacja', 'key' => 'costs_added', 'label' => 'Dodaj koszty', 'order' => 60],
            ['stage' => 'Rozliczenie', 'key' => 'payments_booked', 'label' => 'Zaksięguj wpłaty', 'order' => 70],
        ];

        foreach ($defaults as $row) {
            EventChecklistItem::create([
                'event_id' => $event->id,
                'stage' => $row['stage'],
                'key' => $row['key'],
                'label' => $row['label'],
                'order' => $row['order'],
            ]);
        }
    }

    public function updateAuto(Event $event): void
    {
        $checks = $this->computeChecks($event);

        foreach ($checks as $key => $shouldBeDone) {
            if (! $shouldBeDone) {
                continue;
            }

            $item = $event->checklistItems()->where('key', $key)->first();
            if (! $item || $item->is_done) {
                continue;
            }

            $item->is_done = true;
            $item->done_at = Carbon::now();
            $item->save();
        }
    }

    private function computeChecks(Event $event): array
    {
        $contractsCount = $event->contracts()->count();
        $participantsCount = (int) $event->contracts()->withCount('participants')->get()->sum('participants_count');
        $installmentsCount = $event->contracts()->withCount('installments')->get()->sum('installments_count');
        $costsCount = $event->costs()->count();
        $paymentsSum = (float) $event->payments()->sum('amount');

        return [
            'assigned_owner' => (bool) $event->assigned_to,
            'contract_created' => $contractsCount > 0,
            'installments_created' => $installmentsCount > 0,
            'participants_added' => $participantsCount > 0,
            'costs_added' => $costsCount > 0,
            'payments_booked' => $paymentsSum > 0,
        ];
    }
}
