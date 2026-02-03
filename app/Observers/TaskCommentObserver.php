<?php

namespace App\Observers;

use App\Models\TaskComment;
use App\Services\Tasks\TaskActivityService;

class TaskCommentObserver
{
    public function created(TaskComment $comment): void
    {
        $task = $comment->task;
        if (! $task) {
            return;
        }

        $author = $comment->author?->name ?? 'System';
        $label = $author . ' dodał(a) komentarz';

        app(TaskActivityService::class)->record($task, $comment->author_id, 'comment', $label);
    }
}
