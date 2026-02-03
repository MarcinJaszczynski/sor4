<?php

namespace App\Services\Finance;

use App\Models\Contract;
use Illuminate\Support\Carbon;

class InstallmentAutoGenerator
{
    public function generate(Contract $contract, array $data = []): int
    {
        $total = (float) ($contract->total_amount ?? 0);
        if ($total <= 0) {
            $pp = (float) ($contract->locked_price_per_person ?? 0);
            $cnt = (int) $contract->participants()->count();
            if ($pp > 0 && $cnt > 0) {
                $total = $pp * $cnt;
            }
        }

        if ($total <= 0) {
            return 0;
        }

        $depositPercent = max(0.0, min(100.0, (float) ($data['deposit_percent'] ?? 30)));
        $depositAmount = round($total * ($depositPercent / 100.0), 2);
        $finalAmount = max(0.0, round($total - $depositAmount, 2));

        if (!empty($data['replace_existing'])) {
            $contract->installments()->delete();
        }

        $depositDue = Carbon::parse($data['deposit_due_date'] ?? now())->toDateString();

        $startDate = $contract->event?->start_date;
        $finalDueDays = (int) ($data['final_due_days_before_start'] ?? 14);
        $finalDue = $startDate
            ? Carbon::parse($startDate)->subDays(max(0, $finalDueDays))->toDateString()
            : Carbon::now()->addDays(14)->toDateString();

        $created = 0;

        if ($depositAmount > 0) {
            $contract->installments()->create([
                'due_date' => $depositDue,
                'amount' => $depositAmount,
                'is_paid' => false,
                'note' => 'Zaliczka (' . number_format($depositPercent, 0) . '%)',
            ]);
            $created++;
        }

        if ($finalAmount > 0) {
            $contract->installments()->create([
                'due_date' => $finalDue,
                'amount' => $finalAmount,
                'is_paid' => false,
                'note' => 'Dopłata',
            ]);
            $created++;
        }

        return $created;
    }
}
