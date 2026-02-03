<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContractResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\TaskResource;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DashboardStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $upcomingEventsCount = Event::where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays(30))
            ->count();

        $activeParticipantsCount = Event::where('start_date', '>=', now())
             ->where('start_date', '<=', now()->addDays(30))
             ->sum('participant_count');
        
        // Kwota zaległych płatności (Contract - total vs paid)
        $unpaidContracts = Contract::all()->filter(function($c) {
            return !$c->is_fully_paid;
        });
        
        $unpaidAmount = $unpaidContracts->sum(function($c) {
            return $c->total_amount - $c->paid_amount;
        });

        // Zadania przypisane do zalogowanego usera
        $myTasksCount = Task::where('assignee_id', Auth::id())
            ->whereHas('status', fn($q) => $q->where('name', '!=', 'Completed')) // Zakładając że jest status Completed
            ->count();

        return [
            Stat::make('Nadchodzące Wyjazdy (30 dni)', $upcomingEventsCount)
                ->description('W najbliższym miesiącu')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->url(EventResource::getUrl()),

            Stat::make('Uczestnicy (30 dni)', $activeParticipantsCount)
                ->description('Osób na wyjazdach')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Zaległe Płatności', number_format($unpaidAmount, 2) . ' PLN')
                ->description('Kliknij, aby zobaczyć umowy')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger')
                ->url(ContractResource::getUrl()),
            
            Stat::make('Moje Zadania', $myTasksCount)
                ->description('Zadania do wykonania')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning')
                ->url(TaskResource::getUrl()),
        ];
    }
}
