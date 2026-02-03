<?php

namespace App\Filament\Resources\EmailMessageResource\Pages;

use App\Filament\Resources\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailMessage extends CreateRecord
{
    protected static string $resource = EmailMessageResource::class;
}
