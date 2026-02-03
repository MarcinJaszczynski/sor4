<?php

namespace App\Filament\Resources\EventPaymentCostAllocationResource\Pages;

use App\Filament\Resources\EventPaymentCostAllocationResource;
use App\Models\EventCost;
use App\Models\EventPayment;
use App\Models\EventPaymentCostAllocation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateEventPaymentCostAllocation extends CreateRecord
{
    protected static string $resource = EventPaymentCostAllocationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $payment = EventPayment::query()->find($data['event_payment_id'] ?? null);
        $cost = EventCost::query()->with('currency')->find($data['event_cost_id'] ?? null);

        if (! $payment || ! $cost) {
            Notification::make()->title('Brak wpłaty lub kosztu')->danger()->send();
            abort(422);
        }

        if ((int) $payment->event_id !== (int) $data['event_id'] || (int) $cost->event_id !== (int) $data['event_id']) {
            Notification::make()->title('Wpłata i koszt muszą dotyczyć tej samej imprezy')->danger()->send();
            abort(422);
        }

        $alreadyAllocatedFromPayment = (float) EventPaymentCostAllocation::query()
            ->where('event_payment_id', $payment->id)
            ->sum('amount');
        $availableFromPayment = max(0.0, (float) $payment->amount - $alreadyAllocatedFromPayment);

        $alreadyAllocatedToCost = (float) EventPaymentCostAllocation::query()
            ->where('event_cost_id', $cost->id)
            ->sum('amount');
        $remainingForCost = max(0.0, (float) $cost->amount_pln - $alreadyAllocatedToCost);

        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            Notification::make()->title('Kwota alokacji musi być większa od 0')->danger()->send();
            abort(422);
        }
        if ($amount - $availableFromPayment > 0.0001) {
            Notification::make()->title('Kwota przekracza dostępne środki wpłaty')->danger()->body('Pozostało do alokacji: ' . number_format($availableFromPayment, 2, '.', ' ') . ' PLN')->send();
            abort(422);
        }
        if ($amount - $remainingForCost > 0.0001) {
            Notification::make()->title('Kwota przekracza pozostałą kwotę kosztu')->danger()->body('Pozostało do pokrycia: ' . number_format($remainingForCost, 2, '.', ' ') . ' PLN')->send();
            abort(422);
        }

        if (empty($data['contract_id']) && ! empty($payment->contract_id)) {
            $data['contract_id'] = $payment->contract_id;
        }

        if (empty($data['allocated_at'])) {
            $data['allocated_at'] = now();
        }

        return EventPaymentCostAllocation::query()->create($data);
    }
}
