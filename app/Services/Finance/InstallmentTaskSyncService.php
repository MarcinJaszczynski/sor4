<?php

namespace App\Services\Finance;

use App\Models\ContractInstallment;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Carbon;

class InstallmentTaskSyncService
{
    public function sync(array $options = []): array
    {
        $daysAhead = (int) ($options['days_ahead'] ?? 14);
        $authorId = (int) ($options['author_id'] ?? 1);

        if (! User::query()->whereKey($authorId)->exists()) {
            $authorId = (int) (User::query()->orderBy('id')->value('id') ?? 0);
        }

        $today = now()->startOfDay();
        $soonUntil = now()->addDays(max(0, $daysAhead))->endOfDay();

        $defaultStatusId = TaskStatus::query()->where('is_default', true)->value('id')
            ?? TaskStatus::query()->orderBy('order')->value('id');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $installments = ContractInstallment::query()
            ->with(['contract.event'])
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $soonUntil)
            ->get();

        foreach ($installments as $inst) {
            $contract = $inst->contract;
            if (!$contract) {
                $skipped++;
                continue;
            }

            $marker = '[installment:' . $inst->id . ']';

            $taskQuery = Task::query()
                ->where('taskable_type', $contract::class)
                ->where('taskable_id', $contract->id)
                ->where('description', 'like', '%' . $marker . '%');

            /** @var Task|null $existing */
            $existing = $taskQuery->first();

            $dueDate = $inst->due_date ? Carbon::parse($inst->due_date)->setTime(9, 0) : null;

            // Raty są filtrowane po due_date i is_paid w zapytaniu.

            $isOverdue = Carbon::parse($inst->due_date)->lt($today);
            $isSoon = Carbon::parse($inst->due_date)->between($today, $soonUntil);

            if (!$isOverdue && !$isSoon) {
                // nie tworzymy zadań daleko w przyszłości
                continue;
            }

            $contractNumber = (string) ($contract->contract_number ?? ('ID:' . $contract->id));
            $dueStr = Carbon::parse($inst->due_date)->format('Y-m-d');

            $eventInfo = null;
            if ($contract->event) {
                $eventName = (string) ($contract->event->name ?? '');
                $eventCode = (string) ($contract->event->public_code ?? '');
                $eventInfo = trim($eventName . ($eventCode ? ' (' . $eventCode . ')' : ''));
            }

            $title = $isOverdue
                ? "Rata przeterminowana: {$contractNumber} ({$dueStr})"
                : "Rata do zapłaty: {$contractNumber} ({$dueStr})";

            $priority = $isOverdue ? 'high' : 'medium';
            $assigneeId = (int) ($contract->event?->assigned_to ?? 0);
            $assigneeId = $assigneeId > 0 ? $assigneeId : null;

            $description = trim(
                ($isOverdue ? 'Przeterminowana rata umowy.' : 'Rata do zapłaty wkrótce.')
                . ($eventInfo ? "\nImpreza: {$eventInfo}" : '')
                . "\nUmowa: {$contractNumber}"
                . "\nTermin: {$dueStr}"
                . "\nKwota: " . number_format((float) ($inst->amount ?? 0), 2, ',', ' ') . ' PLN'
                . "\n{$marker}"
            );

            if (!$existing) {
                if (!$defaultStatusId) {
                    $skipped++;
                    continue;
                }

                if ($authorId <= 0) {
                    $skipped++;
                    continue;
                }

                Task::create([
                    'title' => $title,
                    'description' => $description,
                    'due_date' => $dueDate,
                    'status_id' => $defaultStatusId,
                    'priority' => $priority,
                    'author_id' => $authorId,
                    'assignee_id' => $assigneeId,
                    'taskable_type' => $contract::class,
                    'taskable_id' => $contract->id,
                ]);

                $created++;
                continue;
            }

            $dirty = false;
            if ((string) $existing->title !== $title) {
                $existing->title = $title;
                $dirty = true;
            }
            if ((string) ($existing->priority ?? '') !== $priority) {
                $existing->priority = $priority;
                $dirty = true;
            }
            if ($dueDate && (!$existing->due_date || $existing->due_date->ne($dueDate))) {
                $existing->due_date = $dueDate;
                $dirty = true;
            }
            if ((string) ($existing->description ?? '') !== $description) {
                $existing->description = $description;
                $dirty = true;
            }
            if ($assigneeId && (int) ($existing->assignee_id ?? 0) !== (int) $assigneeId) {
                $existing->assignee_id = $assigneeId;
                $dirty = true;
            }

            if ($dirty) {
                $existing->save();
                $updated++;
            }
        }

        $closed = 0;

        return compact('created', 'updated', 'closed', 'skipped');
    }
}
