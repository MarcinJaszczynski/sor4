<?php

namespace App\Services\Finance;

use App\Models\Contract;
use Illuminate\Support\Carbon;

class InstallmentPaymentMatcher
{
    public function syncFromPayments(Contract $contract): void
    {
        $totalPaid = (float) $contract->payments()->sum('amount');
        $remaining = $totalPaid;

        $installments = $contract->installments()
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        foreach ($installments as $inst) {
            $amount = (float) ($inst->amount ?? 0);
            $shouldBePaid = $amount > 0 && $remaining >= $amount;

            if ($shouldBePaid) {
                if (! (bool) $inst->is_paid) {
                    $inst->is_paid = true;
                    $inst->paid_at = $inst->paid_at ?? Carbon::now();
                    $inst->save();
                }
                $remaining -= $amount;
                continue;
            }

            if ((bool) $inst->is_paid) {
                $inst->is_paid = false;
                $inst->paid_at = null;
                $inst->save();
            }
        }
    }
}