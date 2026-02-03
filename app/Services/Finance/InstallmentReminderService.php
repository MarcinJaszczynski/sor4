<?php

namespace App\Services\Finance;

use App\Models\ContractInstallment;
use App\Models\ContractInstallmentReminder;
use App\Models\User;
use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InstallmentReminderService
{
    public function __construct(
        private readonly SmsGatewayInterface $smsGateway,
    ) {
    }

    /**
     * @return array{sms_sent:int,email_sent:int,skipped:int}
     */
    public function send(ContractInstallment $installment, array $channels, ?User $sender = null, ?string $customMessageTemplate = null, bool $force = false): array
    {
        $channels = array_values(array_unique(array_filter($channels)));

        $smsSent = 0;
        $emailSent = 0;
        $skipped = 0;

        $message = $customMessageTemplate
            ? $this->renderTemplate($installment, $customMessageTemplate)
            : $this->buildDefaultMessage($installment);

        $source = $sender ? 'manual' : 'auto';

        foreach ($channels as $channel) {
            if (! in_array($channel, ['sms', 'email'], true)) {
                $skipped++;
                continue;
            }

            if (! $force && $this->wasSentToday($installment, $channel)) {
                $skipped++;
                continue;
            }

            if ($channel === 'sms') {
                $phone = $this->resolvePhone($installment);
                if (! $phone) {
                    $skipped++;
                    continue;
                }

                $ok = $this->smsGateway->send($phone, $this->smsSafe($message));
                if ($ok) {
                    ContractInstallmentReminder::create([
                        'contract_installment_id' => $installment->id,
                        'channel' => 'sms',
                        'recipient' => $phone,
                        'message' => $message,
                        'user_id' => $sender?->id,
                        'source' => $source,
                        'sent_at' => now(),
                    ]);
                    $smsSent++;
                }

                continue;
            }

            if ($channel === 'email') {
                $email = $this->resolveEmail($installment);
                if (! $email) {
                    $skipped++;
                    continue;
                }

                Mail::raw($message, function ($m) use ($email, $installment) {
                    $subject = 'Przypomnienie o wpłacie';
                    $contractNumber = (string) ($installment->contract?->contract_number ?? '');
                    if ($contractNumber) {
                        $subject .= ' • Umowa ' . $contractNumber;
                    }
                    $m->to($email)->subject($subject);
                });

                ContractInstallmentReminder::create([
                    'contract_installment_id' => $installment->id,
                    'channel' => 'email',
                    'recipient' => $email,
                    'message' => $message,
                    'user_id' => $sender?->id,
                    'source' => $source,
                    'sent_at' => now(),
                ]);

                $emailSent++;
            }
        }

        return [
            'sms_sent' => $smsSent,
            'email_sent' => $emailSent,
            'skipped' => $skipped,
        ];
    }

    public function buildDefaultMessage(ContractInstallment $installment): string
    {
        $contract = $installment->contract;
        $event = $contract?->event;

        $contractNumber = (string) ($contract?->contract_number ?? ('ID:' . $installment->contract_id));
        $due = $installment->due_date ? $installment->due_date->format('Y-m-d') : '-';
        $amount = number_format((float) ($installment->amount ?? 0), 2, ',', ' ') . ' PLN';

        $eventName = (string) ($event?->name ?? '');
        $eventCode = (string) ($event?->public_code ?? '');
        $eventInfo = trim($eventName . ($eventCode ? ' (' . $eventCode . ')' : ''));

        $lines = [];
        $lines[] = 'Przypomnienie o wpłacie raty.';
        if ($eventInfo) {
            $lines[] = 'Impreza: ' . $eventInfo;
        }
        $lines[] = 'Umowa: ' . $contractNumber;
        $lines[] = 'Termin: ' . $due;
        $lines[] = 'Kwota: ' . $amount;
        $lines[] = '';
        $lines[] = 'Jeśli wpłata została już wykonana – prosimy o zignorowanie wiadomości.';

        return implode("\n", $lines);
    }

    public function renderTemplate(ContractInstallment $installment, string $template): string
    {
        $contract = $installment->contract;
        $event = $contract?->event;

        $replacements = [
            '{contract_number}' => (string) ($contract?->contract_number ?? ''),
            '{due_date}' => $installment->due_date ? $installment->due_date->format('Y-m-d') : '',
            '{amount}' => number_format((float) ($installment->amount ?? 0), 2, ',', ' ') . ' PLN',
            '{event_name}' => (string) ($event?->name ?? ''),
            '{event_code}' => (string) ($event?->public_code ?? ''),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function resolvePhone(ContractInstallment $installment): ?string
    {
        $event = $installment->contract?->event;

        $phone = trim((string) ($event?->client_phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }

        $phone = trim((string) ($event?->contact?->phone ?? ''));
        return $phone !== '' ? $phone : null;
    }

    private function resolveEmail(ContractInstallment $installment): ?string
    {
        $event = $installment->contract?->event;

        $email = trim((string) ($event?->client_email ?? ''));
        if ($email !== '') {
            return $email;
        }

        $email = trim((string) ($event?->contact?->email ?? ''));
        return $email !== '' ? $email : null;
    }

    private function wasSentToday(ContractInstallment $installment, string $channel): bool
    {
        return $installment->reminders()
            ->where('channel', $channel)
            ->where('sent_at', '>=', now()->startOfDay())
            ->exists();
    }

    private function smsSafe(string $message): string
    {
        $message = str_replace(["\r\n", "\r"], "\n", $message);
        $message = preg_replace("/\n{3,}/", "\n\n", $message) ?: $message;
        $message = Str::limit($message, 450, '…');

        return $message;
    }
}
