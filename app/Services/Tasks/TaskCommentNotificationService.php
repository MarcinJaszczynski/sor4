<?php

namespace App\Services\Tasks;

use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskCommentNotification;
use Illuminate\Support\Str;

class TaskCommentNotificationService
{
    public function notify(TaskComment $comment, array $recipientIds): void
    {
        $comment->loadMissing(['task', 'author']);
        $task = $comment->task;
        if (! $task) {
            return;
        }

        $comment->loadMissing(['parent']);
        $task->loadMissing(['taskable', 'author:id,name', 'assignee:id,name']);

        $authorName = $comment->author?->name ?? 'System';
        $commentExcerpt = Str::limit(strip_tags((string) $comment->content), 140);

        $eventName = null;
        $eventCode = null;
        $taskableType = null;
        $taskableId = null;

        if ($task->taskable) {
            $taskableType = get_class($task->taskable);
            $taskableId = $task->taskable->id ?? null;

            if ($task->taskable instanceof \App\Models\Event) {
                $eventName = $task->taskable->name ?? null;
                $eventCode = $task->taskable->public_code ?? null;
            }
        }

        $baseRecipients = collect([
            $task->author_id,
            $task->assignee_id,
        ])->filter();

        $recipientIds = collect($recipientIds)
            ->filter()
            ->merge($baseRecipients)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $comment->author_id && (int) $id === (int) $comment->author_id)
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $recipientNames = User::query()
            ->whereIn('id', $recipientIds)
            ->pluck('name')
            ->toArray();

        $parentExcerpt = $comment->parent
            ? Str::limit(strip_tags((string) $comment->parent->content), 140)
            : null;

        foreach ($recipientIds as $recipientId) {
            $recipient = User::find($recipientId);
            if (! $recipient) {
                continue;
            }

            $recipientName = $recipient->name ?? 'Użytkownik';

            $recipient->notify(new TaskCommentNotification(
                taskId: $task->id,
                taskTitle: (string) ($task->title ?? 'Zadanie'),
                commentId: $comment->id,
                commentExcerpt: $commentExcerpt,
                authorId: (int) ($comment->author_id ?? 0),
                authorName: $authorName,
                recipientId: (int) $recipientId,
                recipientName: $recipientName,
                recipientNames: $recipientNames,
                eventName: $eventName,
                eventCode: $eventCode,
                taskableType: $taskableType,
                taskableId: $taskableId,
                taskAuthorName: $task->author?->name,
                taskAssigneeName: $task->assignee?->name,
                parentCommentId: $comment->parent_id,
                parentExcerpt: $parentExcerpt,
            ));
        }
    }
}
