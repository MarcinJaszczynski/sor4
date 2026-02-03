<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class TaskActivityService
{
    public function record(Task $task, ?int $actorId, string $type, string $label): void
    {
        $task->withoutEvents(function () use ($task, $actorId, $type, $label) {
            $task->last_activity_at = now();
            $task->last_activity_user_id = $actorId;
            $task->last_activity_type = $type;
            $task->last_activity_label = $label;
            $task->save();
        });

        $this->notifyParties($task, $actorId, $label);
    }

    public function labelForHistory(Task $task, string $field, mixed $newValue, ?int $actorId): string
    {
        $actor = $actorId ? User::find($actorId)?->name : 'System';

        return match ($field) {
            'status_id' => $actor . ' zmienił(a) status na ' . ($this->resolveStatusName($newValue) ?? '—'),
            'due_date' => $actor . ' zmienił(a) termin',
            'title' => $actor . ' zmienił(a) tytuł',
            'description' => $actor . ' zmienił(a) opis',
            'priority' => $actor . ' zmienił(a) priorytet',
            'assignee_id' => $actor . ' zmienił(a) przypisanie',
            default => $actor . ' zaktualizował(a) zadanie',
        };
    }

    private function resolveStatusName(mixed $statusId): ?string
    {
        if (! is_numeric($statusId)) {
            return null;
        }

        return TaskStatus::query()->whereKey((int) $statusId)->value('name');
    }

    private function notifyParties(Task $task, ?int $actorId, string $label): void
    {
        $recipients = collect([
            $task->author_id,
            $task->assignee_id,
        ])
            ->filter()
            ->unique()
            ->reject(fn ($id) => $actorId && (int) $id === (int) $actorId)
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $url = \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task->id]);

        foreach ($recipients as $userId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            Notification::make()
                ->title('Zmiana w zadaniu')
                ->body($label)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('open')
                        ->label('Otwórz zadanie')
                        ->url($url)
                        ->openUrlInNewTab(),
                ])
                ->sendToDatabase($user);
        }
    }
}
