<?php

namespace App\Console\Commands;

use App\Models\ContractInstallment;
use App\Services\Finance\InstallmentReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendInstallmentRemindersToClients extends Command
{
    protected $signature = 'installments:remind-clients
        {--days=3 : Ile dni przed terminem wysłać przypomnienie}
        {--include-overdue=1 : Czy obejmować przeterminowane raty}
        {--channels=sms,email : Preferowane kanały (kolejność ma znaczenie), np. sms,email albo email,sms}
        {--force=0 : Ignoruj limit 1/dzień/kanał}';

    protected $description = 'Automatycznie wysyła przypomnienia do klienta o ratach (nadchodzących i opcjonalnie przeterminowanych).';

    public function handle(InstallmentReminderService $service): int
    {
        $days = max(0, (int) $this->option('days'));
        $includeOverdue = (bool) ((int) $this->option('include-overdue'));
        $force = (bool) ((int) $this->option('force'));

        $channels = array_filter(array_map('trim', explode(',', (string) $this->option('channels'))));
        if (empty($channels)) {
            $channels = ['sms', 'email'];
        }

        $today = now()->startOfDay();
        $until = now()->addDays($days)->endOfDay();

        $query = ContractInstallment::query()
            ->with(['contract.event', 'contract.event.contact'])
            ->where('is_paid', false)
            ->whereNotNull('due_date');

        if ($includeOverdue) {
            $query->whereDate('due_date', '<=', $until);
        } else {
            $query->whereDate('due_date', '>=', $today)
                ->whereDate('due_date', '<=', $until);
        }

        $installments = $query->get();

        $sentSms = 0;
        $sentEmail = 0;
        $skipped = 0;

        foreach ($installments as $inst) {
            // Jeżeli nie ma żadnego kontaktu, pomiń.
            $hasPhone = trim((string) ($inst->contract?->event?->client_phone ?? $inst->contract?->event?->contact?->phone ?? '')) !== '';
            $hasEmail = trim((string) ($inst->contract?->event?->client_email ?? $inst->contract?->event?->contact?->email ?? '')) !== '';

            if (! $hasPhone && ! $hasEmail) {
                $skipped++;
                continue;
            }

            // Preferencja kanałów: wybieramy takie, które mają dane.
            $chosen = [];
            foreach ($channels as $ch) {
                if ($ch === 'sms' && $hasPhone) {
                    $chosen[] = 'sms';
                }
                if ($ch === 'email' && $hasEmail) {
                    $chosen[] = 'email';
                }
            }

            if (empty($chosen)) {
                $skipped++;
                continue;
            }

            $result = $service->send($inst, $chosen, null, null, $force);

            $sentSms += (int) ($result['sms_sent'] ?? 0);
            $sentEmail += (int) ($result['email_sent'] ?? 0);
            $skipped += (int) ($result['skipped'] ?? 0);
        }

        $this->info('OK');
        $this->line('SMS wysłane: ' . $sentSms);
        $this->line('E-mail wysłane: ' . $sentEmail);
        $this->line('Pominięte: ' . $skipped);

        return self::SUCCESS;
    }
}
