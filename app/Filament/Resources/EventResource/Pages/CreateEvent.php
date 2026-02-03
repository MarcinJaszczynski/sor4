<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Request;
use App\Models\EventTemplate;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected ?EventTemplate $template = null;

    public function mount(): void
    {
        parent::mount();

        // Prefill dates when opened from calendar (e.g. /admin/events/create?start_date=2026-01-15&end_date=2026-01-16)
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date');
        if ($startDate) {
            $this->form->fill([
                'start_date' => $startDate,
                'end_date' => $endDate ?: null,
            ]);
        }

        $templateId = request()->query('template');
        if ($templateId) {
            $this->template = EventTemplate::find($templateId);
            if ($this->template) {
                // Prefill some form data from template
                $this->form->fill([
                    'event_template_id' => $this->template->id,
                    'duration_days' => $this->template->duration_days,
                    'transfer_km' => $this->template->transfer_km,
                    'program_km' => $this->template->program_km,
                    'bus_id' => $this->template->bus_id,
                    'markup_id' => $this->template->markup_id,
                    'name' => $this->template->name,
                ]);
            }
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Try to resolve template from data if property is lost (Livewire doesn't persist protected props)
        $template = $this->template;
        if (!$template && !empty($data['event_template_id'])) {
            $template = EventTemplate::find($data['event_template_id']);
        }

        // If template present, use Event::createFromTemplate to ensure deep-copy behavior
        if ($template) {
            // Log minimal, non-sensitive input data coming from the UI to help debug
            Log::info('CreateEvent:web:before_create_from_template', [
                'event_template_id' => $template->id,
                'name' => $data['name'] ?? null,
                'participant_count' => $data['participant_count'] ?? ($data['calculation_qtys'][0]['qty'] ?? null),
                'start_date' => $data['start_date'] ?? null,
                'duration_days' => $data['duration_days'] ?? null,
                'bus_id' => $data['bus_id'] ?? null,
            ]);

            $event = Event::createFromTemplate($template, $data);
            Log::info('CreateEvent:web:after_create_from_template', ['event_id' => $event->id]);
            return $event;
        }

        return parent::handleRecordCreation($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!Schema::hasColumn('events', 'guide_count')) {
            unset($data['guide_count']);
        }

        return $data;
    }
}
