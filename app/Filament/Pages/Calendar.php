<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Calendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Kalendarz';

    protected static ?string $title = 'Kalendarz';

    protected static ?string $navigationGroup = 'Komunikacja';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.calendar';
}
