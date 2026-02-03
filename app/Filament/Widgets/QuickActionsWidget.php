<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions-widget';
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 1; // High priority
}
