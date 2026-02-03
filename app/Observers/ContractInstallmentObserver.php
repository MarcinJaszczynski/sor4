<?php

namespace App\Observers;

use App\Models\ContractInstallment;
use App\Models\Task;
use App\Models\TaskStatus;

class ContractInstallmentObserver
{
    public function updated(ContractInstallment $installment): void
    {
        if (! $installment->wasChanged('is_paid')) {
            return;
        }

        if (! (bool) $installment->is_paid) {
            return;
        }

        $contract = $installment->contract;
        if (! $contract) {
            return;
        }

        $doneStatusId = TaskStatus::query()->where('name', 'Zakończone')->value('id');
        if (! $doneStatusId) {
            return;
        }

        $marker = '[installment:' . $installment->id . ']';

        $task = Task::query()
            ->where('taskable_type', $contract::class)
            ->where('taskable_id', $contract->id)
            ->where('description', 'like', '%' . $marker . '%')
            ->first();

        if (! $task) {
            return;
        }

        if ((int) $task->status_id === (int) $doneStatusId) {
            return;
        }

        $task->status_id = $doneStatusId;
        $task->save();
    }
}
