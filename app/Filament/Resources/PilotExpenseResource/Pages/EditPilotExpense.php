<?php

namespace App\Filament\Resources\PilotExpenseResource\Pages;

use App\Filament\Resources\PilotExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPilotExpense extends EditRecord
{
    protected static string $resource = PilotExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
