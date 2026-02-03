<?php

namespace App\Observers;

use App\Models\TaskHistory;
use App\Services\Tasks\TaskActivityService;

class TaskHistoryObserver
{
    public function created(TaskHistory $history): void
    {
        $task = $history->task;
        if (! $task) {
            return;
        }

        $label = app(TaskActivityService::class)->labelForHistory($task, (string) $history->field, $history->new_value, $history->user_id);
        app(TaskActivityService::class)->record($task, $history->user_id, 'history', $label);
    }
}
