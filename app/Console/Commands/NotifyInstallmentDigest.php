<?php

namespace App\Console\Commands;

use App\Filament\Pages\Finance\InstallmentControl;
use App\Models\ContractInstallment;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyInstallmentDigest extends Command
{
    protected $signature = 'installments:notify {--days=14 : Ile dni do przodu uwzględniać jako "nadchodzące"}';

    protected $description = 'Wysyła dzienny digest (do bazy powiadomień) o przeterminowanych i nadchodzących ratach.';

    public function handle(): int
    {
        $daysAhead = max(0, (int) $this->option('days'));

        $today = now()->startOfDay();
        $until = now()->addDays($daysAhead)->endOfDay();

        $installments = ContractInstallment::query()
            ->with(['contract.event'])
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $until)
            ->get();

        $overdueUrl = InstallmentControl::getUrl(['scope' => 'overdue', 'days' => $daysAhead]);
        $soonUrl = InstallmentControl::getUrl(['scope' => 'soon', 'days' => $daysAhead]);

        $byAssignee = []; // userId => ['overdue' => n, 'soon' => n, 'examples' => []]
        $unassigned = ['overdue' => 0, 'soon' => 0, 'examples' => []];

        foreach ($installments as $inst) {
            $due = $inst->due_date ? Carbon::parse($inst->due_date)->startOfDay() : null;
            if (! $due) {
                continue;
            }

            $isOverdue = $due->lt($today);
            $isSoon = ! $isOverdue && $due->between($today, $until);

            if (! $isOverdue && ! $isSoon) {
                continue;
            }

            $assigneeId = (int) ($inst->contract?->event?->assigned_to ?? 0);

            $bucket = $assigneeId > 0
                ? ($byAssignee[$assigneeId] ??= ['overdue' => 0, 'soon' => 0, 'examples' => []])
                : $unassigned;

            if ($isOverdue) {
                $bucket['overdue']++;
            } else {
                $bucket['soon']++;
            }

            if (count($bucket['examples']) < 3) {
                $contractNumber = (string) ($inst->contract?->contract_number ?? ('ID:' . $inst->contract_id));
                $eventCode = (string) ($inst->contract?->event?->public_code ?? '');
                $eventName = (string) ($inst->contract?->event?->name ?? '');
                $eventInfo = trim($eventName . ($eventCode ? ' (' . $eventCode . ')' : ''));

                $bucket['examples'][] = trim($contractNumber
                    . ' • ' . $due->format('Y-m-d')
                    . ' • ' . number_format((float) ($inst->amount ?? 0), 2, ',', ' ') . ' PLN'
                    . ($eventInfo ? ' • ' . $eventInfo : ''));
            }

            if ($assigneeId > 0) {
                $byAssignee[$assigneeId] = $bucket;
            } else {
                $unassigned = $bucket;
            }
        }

        $admins = User::query()->role(['super_admin', 'admin'])->get();

        // Powiadom opiekunów (assigned_to)
        $sent = 0;
        foreach ($byAssignee as $assigneeId => $data) {
            $user = User::find($assigneeId);
            if (! $user) {
                continue;
            }

            $body = $this->formatBody($data['overdue'], $data['soon'], $daysAhead, $data['examples'], $overdueUrl, $soonUrl);

            Notification::make()
                ->title('Kontrola rat (dzisiaj)')
                ->body($body)
                ->color(($data['overdue'] ?? 0) > 0 ? 'danger' : 'warning')
                ->sendToDatabase($user);

            $sent++;
        }

        // Powiadom adminów o nieprzypisanych ratach (żeby nic nie zginęło)
        if (($unassigned['overdue'] ?? 0) > 0 || ($unassigned['soon'] ?? 0) > 0) {
            $body = "Nieprzypisane do opiekuna (event.assigned_to):\n"
                . $this->formatBody($unassigned['overdue'], $unassigned['soon'], $daysAhead, $unassigned['examples'], $overdueUrl, $soonUrl);

            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Kontrola rat: nieprzypisane')
                    ->body($body)
                    ->color(($unassigned['overdue'] ?? 0) > 0 ? 'danger' : 'warning')
                    ->sendToDatabase($admin);
                $sent++;
            }
        }

        $this->info('OK');
        $this->line('Wysłane powiadomienia: ' . $sent);

        return self::SUCCESS;
    }

    private function formatBody(int $overdue, int $soon, int $daysAhead, array $examples, string $overdueUrl, string $soonUrl): string
    {
        $lines = [];
        $lines[] = 'Przeterminowane: ' . $overdue;
        $lines[] = 'Nadchodzące (do ' . $daysAhead . ' dni): ' . $soon;

        if (! empty($examples)) {
            $lines[] = '';
            $lines[] = 'Przykłady:';
            foreach ($examples as $ex) {
                $lines[] = '- ' . $ex;
            }
        }

        $lines[] = '';
        $lines[] = 'Lista przeterminowanych: ' . $overdueUrl;
        $lines[] = 'Lista nadchodzących: ' . $soonUrl;

        return implode("\n", $lines);
    }
}
