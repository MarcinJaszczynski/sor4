<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Panel główny';

    protected static string $view = 'filament.pages.dashboard';

    public function getColumns(): int | array
    {
        return 3;
    }

    public function getLeftWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \App\Filament\Widgets\QuickActionsWidget::class,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStatsOverview::class,
        ];
    }

    public function getCenterWidgets(): array
    {
        return [
            \App\Filament\Widgets\LatestEventsWidget::class,
        ];
    }

    public function getRightWidgets(): array
    {
        return [
            \App\Filament\Widgets\TodayFocusWidget::class,
            \App\Filament\Widgets\MessageCenterWidget::class,
            \App\Filament\Widgets\CalendarOrganizerWidget::class,
        ];
    }
}
