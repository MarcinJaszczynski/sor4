<?php

namespace App\Filament\Resources\PilotExpenseResource\Pages;

use App\Filament\Resources\PilotExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPilotExpense extends ViewRecord
{
    protected static string $resource = PilotExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
