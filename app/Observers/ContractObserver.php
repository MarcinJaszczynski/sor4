<?php

namespace App\Observers;

use App\Models\Contract;
use App\Services\Finance\InstallmentAutoGenerator;
use App\Services\Finance\InstallmentTaskSyncService;
use App\Services\EventChecklistService;
use Illuminate\Support\Facades\Auth;

class ContractObserver
{
    public function updated(Contract $contract): void
    {
        if (! $contract->wasChanged('status')) {
            if ($contract->event) {
                app(EventChecklistService::class)->updateAuto($contract->event);
            }
            return;
        }

        if (! in_array($contract->status, ['generated', 'signed'], true)) {
            return;
        }

        if ($contract->installments()->exists()) {
            return;
        }

        app(InstallmentAutoGenerator::class)->generate($contract, [
            'deposit_percent' => 30,
            'deposit_due_date' => now()->toDateString(),
            'final_due_days_before_start' => 14,
        ]);

        app(InstallmentTaskSyncService::class)->sync([
            'days_ahead' => 14,
            'author_id' => Auth::id() ?? 1,
        ]);

        if ($contract->event) {
            app(EventChecklistService::class)->updateAuto($contract->event);
        }
    }

    public function created(Contract $contract): void
    {
        if (in_array($contract->status, ['generated', 'signed'], true)
            && ! $contract->installments()->exists()) {
            app(InstallmentAutoGenerator::class)->generate($contract, [
                'deposit_percent' => 30,
                'deposit_due_date' => now()->toDateString(),
                'final_due_days_before_start' => 14,
            ]);

            app(InstallmentTaskSyncService::class)->sync([
                'days_ahead' => 14,
                'author_id' => Auth::id() ?? 1,
            ]);
        }

        if ($contract->event) {
            app(EventChecklistService::class)->updateAuto($contract->event);
        }
    }
}
