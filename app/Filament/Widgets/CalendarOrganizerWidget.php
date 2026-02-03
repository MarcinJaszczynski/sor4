<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CalendarOrganizerWidget extends Widget
{
    protected static string $view = 'filament.widgets.calendar-organizer-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = '30s';

    protected function getViewData(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'days' => [],
            ];
        }

        $userId = $user->id;
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->addDays(14)->endOfDay();

        // Live (no cache) so right column stays accurate
        $events = Event::query()
            ->select(['id', 'name', 'start_date', 'end_date', 'assigned_to', 'created_by'])
            ->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                    ->orWhere('created_by', $userId);
            })
            ->whereBetween('start_date', [$start, $end])
            ->orderBy('start_date')
            ->limit(100)
            ->get();

        $tasks = Task::query()
            ->select(['id', 'title', 'due_date', 'assignee_id'])
            ->where('assignee_id', $userId)
            ->whereBetween('due_date', [$start, $end])
            ->orderBy('due_date')
            ->limit(100)
            ->get();

        $days = [];
        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->format('Y-m-d');
            $days[$key] = [
                'date' => $day->copy(),
                'events' => [],
                'tasks' => [],
            ];
        }

        foreach ($events as $event) {
            $dateKey = optional($event->start_date)->format('Y-m-d');
            if ($dateKey && isset($days[$dateKey])) {
                $days[$dateKey]['events'][] = $event;
            }
        }

        foreach ($tasks as $task) {
            $dateKey = optional($task->due_date)->format('Y-m-d');
            if ($dateKey && isset($days[$dateKey])) {
                $days[$dateKey]['tasks'][] = $task;
            }
        }

        return [
            'days' => $days,
        ];
    }
}
