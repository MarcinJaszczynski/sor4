<?php

namespace App\Filament\Resources\CalendarNoteResource\Pages;

use App\Filament\Resources\CalendarNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalendarNotes extends ListRecords
{
    protected static string $resource = CalendarNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
