<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskComment;
use App\Services\Tasks\TaskCommentNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function getCounts(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'tasks' => 0,
                'messages' => 0,
                'comments' => 0,
            ]);
        }
        
        $counts = NotificationService::getUnreadCountsForUser($user->id);
        
        return response()->json([
            'tasks' => $counts['tasks'],
            'messages' => $counts['messages'],
            'emails' => $counts['emails'] ?? 0,
            'comments' => $counts['task_comments'] ?? 0,
        ]);
    }

    public function openTaskComment(string $notification): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        /** @var DatabaseNotification|null $record */
        $record = $user->notifications()->where('id', $notification)->first();
        if (! $record) {
            return redirect()->route('filament.admin.pages.task-comments');
        }

        $data = $record->data ?? [];
        $taskId = $data['task_id'] ?? null;

        $record->markAsRead();

        if ($taskId) {
            return redirect(TaskResource::getUrl('edit', ['record' => $taskId]));
        }

        return redirect()->route('filament.admin.pages.task-comments');
    }

    public function replyToTaskComment(Request $request, Task $task): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:2'],
            'notification_id' => ['nullable', 'string'],
        ]);

        $comment = $task->comments()->create([
            'content' => $data['content'],
            'author_id' => $user->id,
        ]);

        $recipientIds = collect();

        if (! empty($data['notification_id'])) {
            $notification = $user->notifications()->where('id', $data['notification_id'])->first();
            if ($notification) {
                $payload = $notification->data ?? [];
                $authorId = $payload['author_id'] ?? null;
                if ($authorId) {
                    $recipientIds->push((int) $authorId);
                }
                $notification->markAsRead();
            }
        }

        $recipientIds = $recipientIds->unique()->values();

        if ($recipientIds->isNotEmpty()) {
            $comment->recipients()->sync($recipientIds->all());
            $comment->recipient_id = $recipientIds->first();
            $comment->save();
        }

        app(TaskCommentNotificationService::class)->notify($comment, $recipientIds->all());

        return redirect()->back();
    }
}
