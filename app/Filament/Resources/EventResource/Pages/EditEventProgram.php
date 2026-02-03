<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Traits\HasEventHeaderActions;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use App\Models\Event;

class EditEventProgram extends Page
{
    use HasEventHeaderActions;

    protected static string $resource = EventResource::class;
    protected static string $view = 'filament.resources.event-resource.pages.edit-event-program';

    public $record;
    public Event $event;

    public function mount($record): void
    {
        $this->record = $record;
        // Konwersja ID na model jeśli przekazano ID
        if (is_numeric($record)) {
             $this->record = Event::findOrFail($record);
        }
        $this->event = $this->record;
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getNavigationActions(),
        ];
    }
}
