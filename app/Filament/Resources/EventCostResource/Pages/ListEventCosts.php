<?php

namespace App\Filament\Resources\EventCostResource\Pages;

use App\Filament\Resources\EventCostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventCosts extends ListRecords
{
    protected static string $resource = EventCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
