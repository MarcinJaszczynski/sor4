<?php

namespace App\Filament\Resources\PilotExpenseResource\Pages;

use App\Filament\Resources\PilotExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePilotExpense extends CreateRecord
{
    protected static string $resource = PilotExpenseResource::class;
}
