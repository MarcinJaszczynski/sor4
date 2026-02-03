<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Finance\InstallmentControl;
use App\Filament\Resources\TaskResource;
use App\Models\ContractInstallment;
use App\Models\Task;
use Filament\Widgets\Widget;

class TodayFocusWidget extends Widget
{
    protected static string $view = 'filament.widgets.today-focus-widget';

    public function getViewData(): array
    {
        $today = now()->startOfDay();

        $overdue = ContractInstallment::query()
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->get();

        $dueToday = ContractInstallment::query()
            ->where('is_paid', false)
            ->whereDate('due_date', $today)
            ->get();

        $overdueTasks = Task::query()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->where(function ($q) {
                $q->where('title', 'like', '%[installment:%')
                    ->orWhere('description', 'like', '%[installment:%');
            })
            ->count();

        return [
            'overdueCount' => $overdue->count(),
            'overdueAmount' => $overdue->sum(fn ($i) => (float) ($i->amount ?? 0)),
            'dueTodayCount' => $dueToday->count(),
            'dueTodayAmount' => $dueToday->sum(fn ($i) => (float) ($i->amount ?? 0)),
            'overdueTasks' => $overdueTasks,
        ];
    }

    public function getViewDataActions(): array
    {
        return [];
    }
}
