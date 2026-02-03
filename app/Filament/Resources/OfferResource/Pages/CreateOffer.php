<?php

namespace App\Filament\Resources\OfferResource\Pages;

use App\Filament\Resources\OfferResource;
use App\Models\Event;
use App\Models\OfferItem;
use App\Models\OfferTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;

    public function mount(): void
    {
        parent::mount();

        $eventId = request()->query('event_id');
        if (! $eventId) {
            return;
        }

        $event = Event::query()->find($eventId);
        if (! $event) {
            return;
        }

        $this->form->fill([
            'event_id' => $event->id,
            'participant_count' => $event->participant_count ?: 1,
            'name' => 'Oferta: ' . ($event->name ?? ('Impreza #' . $event->id)),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        if (empty($data['offer_template_id'])) {
            $data['offer_template_id'] = OfferTemplate::query()->where('is_default', true)->value('id');
        }

        if (! empty($data['offer_template_id'])) {
            $template = OfferTemplate::query()->find($data['offer_template_id']);
            if ($template) {
                if (empty($data['introduction'])) {
                    $data['introduction'] = $template->introduction;
                }
                if (empty($data['terms'])) {
                    $data['terms'] = $template->terms;
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $offer = $this->record;
        $event = $offer?->event;
        if (! $offer || ! $event) {
            return;
        }

        $programPoints = $event->programPoints;
        foreach ($programPoints as $point) {
            OfferItem::query()->create([
                'offer_id' => $offer->id,
                'event_program_point_id' => $point->id,
                'is_optional' => false,
                'is_included' => true,
                'quantity' => $point->quantity ?? 1,
                'custom_price' => null,
                'custom_description' => null,
            ]);
        }
    }
}
