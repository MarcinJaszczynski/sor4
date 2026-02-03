<?php

namespace App\Filament\Resources\EventCostResource\Pages;

use App\Filament\Resources\EventCostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventCost extends EditRecord
{
    protected static string $resource = EventCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
