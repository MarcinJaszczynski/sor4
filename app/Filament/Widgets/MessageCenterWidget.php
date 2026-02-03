<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\ContractInstallmentReminder;
use App\Models\EmailMessage;
use App\Models\Event;
use App\Models\EventHistory;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\TaskStatus;
use App\Models\User;
use App\Filament\Resources\ContractResource;
use App\Filament\Pages\Finance\InstallmentControl;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MessageCenterWidget extends Widget
{
    protected static string $view = 'filament.widgets.message-center-widget';

    protected int | string | array $columnSpan = 1;

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '10s';

    protected function normalizeHistoryValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            // If value is JSON-encoded scalar, decode for nicer display.
            if ((Str::startsWith($trimmed, '"') && Str::endsWith($trimmed, '"')) ||
                Str::startsWith($trimmed, '{') ||
                Str::startsWith($trimmed, '[')) {
                try {
                    $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
                    return $decoded;
                } catch (\Throwable $_) {
                    // ignore
                }
            }
        }

        return $value;
    }

    protected function formatTaskHistory(TaskHistory $history): TaskHistory
    {
        $field = $history->field;
        $old = $this->normalizeHistoryValue($history->old_value);
        $new = $this->normalizeHistoryValue($history->new_value);

        $fieldLabel = match ($field) {
            'status_id' => 'Status',
            'due_date' => 'Termin',
            'title' => 'Tytuł',
            'description' => 'Opis',
            'priority' => 'Priorytet',
            'assignee_id' => 'Przypisanie',
            default => $field ?: 'Zmiana',
        };

        $oldDisplay = $old;
        $newDisplay = $new;

        if ($field === 'due_date') {
            $oldDisplay = $old ? \Illuminate\Support\Carbon::parse($old)->format('d.m.Y H:i') : 'brak';
            $newDisplay = $new ? \Illuminate\Support\Carbon::parse($new)->format('d.m.Y H:i') : 'brak';
        } elseif ($field === 'status_id') {
            $ids = collect([$old, $new])
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            $byId = $ids->isEmpty()
                ? collect()
                : TaskStatus::query()->whereIn('id', $ids)->pluck('name', 'id');

            $oldDisplay = is_numeric($old) ? ($byId[(int) $old] ?? $old) : ($old ?: '—');
            $newDisplay = is_numeric($new) ? ($byId[(int) $new] ?? $new) : ($new ?: '—');
        } elseif ($field === 'assignee_id') {
            $ids = collect([$old, $new])
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            $byId = $ids->isEmpty()
                ? collect()
                : User::query()->whereIn('id', $ids)->pluck('name', 'id');

            $oldDisplay = is_numeric($old) ? ($byId[(int) $old] ?? $old) : ($old ?: 'brak');
            $newDisplay = is_numeric($new) ? ($byId[(int) $new] ?? $new) : ($new ?: 'brak');
        }

        if (is_string($oldDisplay)) {
            $oldDisplay = Str::limit($oldDisplay, 80);
        }
        if (is_string($newDisplay)) {
            $newDisplay = Str::limit($newDisplay, 80);
        }

        $history->field_label = $fieldLabel;
        $history->old_display = is_scalar($oldDisplay) || is_null($oldDisplay) ? (string) ($oldDisplay ?? '—') : json_encode($oldDisplay);
        $history->new_display = is_scalar($newDisplay) || is_null($newDisplay) ? (string) ($newDisplay ?? '—') : json_encode($newDisplay);

        $verb = $history->user?->name ? $history->user->name . ' zmienił(a)' : 'Zmieniono';
        $taskTitle = $history->task?->title ? ' „' . $history->task->title . '”' : '';

        $history->summary = match ($field) {
            'due_date' => $verb . ' termin' . $taskTitle . ': ' . $history->old_display . ' → ' . $history->new_display,
            'status_id' => $verb . ' status' . $taskTitle . ': ' . $history->old_display . ' → ' . $history->new_display,
            'assignee_id' => $verb . ' przypisanie' . $taskTitle . ': ' . $history->old_display . ' → ' . $history->new_display,
            'title' => $verb . ' tytuł' . $taskTitle,
            'description' => $verb . ' opis' . $taskTitle,
            default => ($history->description ?: ($verb . ' ' . Str::lower($fieldLabel) . $taskTitle)),
        };

        $history->icon = match ($field) {
            'due_date' => 'calendar-days',
            'status_id' => 'arrow-path',
            'assignee_id' => 'user-plus',
            'title' => 'pencil-square',
            'description' => 'document-text',
            default => 'bolt',
        };

        return $history;
    }

    protected function getViewData(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return [
                'user' => null,
                'events' => collect(),
                'tasks' => collect(),
                'overdueInstallmentTasks' => collect(),
                'taskChanges' => collect(),
                'statusChanges' => collect(),
                'emails' => collect(),
                'conversations' => collect(),
                'installmentReminders' => collect(),
            ];
        }

        $userId = $user->id;
        $isManager = $user->hasRole(['super_admin', 'admin', 'manager']);
        $scopeKey = $isManager ? 'manager' : 'office';
        $cacheKey = "message_center_widget_{$userId}_{$scopeKey}";

        // Build data live (no cache) to reflect latest changes
        $eventsQuery = Event::query()
            ->select(['id', 'name', 'start_date', 'created_at', 'status', 'assigned_to', 'created_by'])
            ->with('creator:id,name')
            ->latest()
            ->limit(5);

        if (! $isManager) {
            $eventsQuery->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId);
            });
        }

        $events = $eventsQuery->get();

        $tasksQuery = Task::query()
            ->select(['id', 'title', 'due_date', 'status_id', 'assignee_id'])
            ->with('status')
            ->latest('created_at')
            ->limit(5);

        if (! $isManager) {
            $tasksQuery->where('assignee_id', $userId);
        }

        $tasks = $tasksQuery->get();

        $overdueTasksQuery = Task::query()
            ->select(['id', 'title', 'due_date', 'status_id', 'assignee_id'])
            ->with('status')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->startOfDay())
            ->where(function ($q) {
                $q->where('title', 'like', '%[installment:%')
                    ->orWhere('description', 'like', '%[installment:%');
            })
            ->latest('due_date')
            ->limit(5);

        if (! $isManager) {
            $overdueTasksQuery->where('assignee_id', $userId);
        }

        $overdueInstallmentTasks = $overdueTasksQuery->get();

        // Recent task updates (to surface status changes / edits)
        $taskChangesQuery = TaskHistory::query()
            ->select(['id', 'task_id', 'user_id', 'field', 'old_value', 'new_value', 'description', 'created_at'])
            ->with(['task:id,title,due_date,status_id,assignee_id', 'task.status:id,name', 'task.assignee:id,name', 'user:id,name'])
            ->latest('created_at')
            ->limit(5);

        if (! $isManager) {
            $taskChangesQuery->whereHas('task', function ($q) use ($userId) {
                $q->where('assignee_id', $userId)
                    ->orWhere('author_id', $userId);
            });
        }

        $taskChanges = $taskChangesQuery->get();
        $taskChanges = $taskChanges->map(fn (TaskHistory $h) => $this->formatTaskHistory($h));

        $remindersQuery = ContractInstallmentReminder::query()
            ->select(['id', 'contract_installment_id', 'channel', 'recipient', 'source', 'user_id', 'sent_at', 'created_at'])
            ->with([
                'installment:id,contract_id,due_date,amount',
                'installment.contract:id,event_id,contract_number',
                'installment.contract.event:id,name,public_code,assigned_to,created_by',
                'user:id,name',
            ])
            ->latest('sent_at')
            ->limit(5);

        if (! $isManager) {
            $remindersQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('installment.contract.event', function ($eq) use ($userId) {
                        $eq->where('assigned_to', $userId)
                            ->orWhere('created_by', $userId);
                    });
            });
        }

        $installmentReminders = $remindersQuery->get()->map(function (ContractInstallmentReminder $r) {
            $inst = $r->installment;
            $contract = $inst?->contract;
            $event = $contract?->event;

            $contractNumber = (string) ($contract?->contract_number ?? '');
            $eventName = (string) ($event?->name ?? '');
            $eventCode = (string) ($event?->public_code ?? '');
            $eventInfo = trim($eventName . ($eventCode ? ' (' . $eventCode . ')' : ''));

            $verb = $r->user?->name ? ($r->user->name . ' wysłał(a)') : 'Wysłano';
            $channel = strtoupper((string) $r->channel);

            $r->summary = trim(
                $verb
                . ' ' . $channel . ' przypomnienie o racie'
                . ($contractNumber ? ' • Umowa ' . $contractNumber : '')
                . ($eventInfo ? ' • ' . $eventInfo : '')
            );

            $r->contract_url = $contract?->id
                ? ContractResource::getUrl('edit', ['record' => $contract->id])
                : null;

            $r->installments_url = $contractNumber
                ? InstallmentControl::getUrl(['scope' => 'all', 'contract_number' => $contractNumber])
                : null;

            return $r;
        });

        $statusQuery = EventHistory::query()
            ->select(['id', 'event_id', 'user_id', 'created_at', 'new_value', 'old_value', 'description'])
            ->where('action', 'status_changed')
            ->with(['event:id,name', 'user:id,name'])
            ->latest()
            ->limit(5);

        if (! $isManager) {
            $statusQuery->whereHas('event', function ($q) use ($userId) {
                $q->where('assigned_to', $userId)
                    ->orWhere('created_by', $userId);
            });
        }

        $statusChanges = $statusQuery->get();

        $emails = EmailMessage::query()
            ->select(['id', 'subject', 'from_address', 'date', 'email_account_id', 'is_read'])
            ->where(function ($q) use ($userId) {
                $q->whereHas('account', fn ($aq) => $aq->forUser($userId))
                    ->orWhereHas('sharedUsers', fn ($sq) => $sq->where('user_id', $userId));
            })
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $conversations = Conversation::query()
            ->select(['id', 'title', 'type', 'last_message_at'])
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with('lastMessage')
            ->orderBy('last_message_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'events' => $events,
            'tasks' => $tasks,
            'overdueInstallmentTasks' => $overdueInstallmentTasks,
            'taskChanges' => $taskChanges,
            'statusChanges' => $statusChanges,
            'emails' => $emails,
            'conversations' => $conversations,
            'installmentReminders' => $installmentReminders,
            'user' => $user,
        ];
    }
}
