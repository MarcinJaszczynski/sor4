<?php

namespace App\Services;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailService
{
    /**
     * Sends an email using the provided EmailAccount settings.
     */
    public function send(EmailAccount $account, string $to, string $subject, string $body, array $attachments = [], $relatedTo = null)
    {
        // Dynamiczna konfiguracja mailera
        $config = [
            'transport' => 'smtp',
            'host' => $account->smtp_host,
            'port' => $account->smtp_port,
            'encryption' => $account->smtp_encryption,
            'username' => $account->username ?: $account->email,
            'password' => $account->password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ];

        Config::set('mail.mailers.dynamic', $config);

        Mail::mailer('dynamic')->html($body, function ($message) use ($account, $to, $subject, $attachments) {
            $message->from($account->email, $account->account_name)
                ->to($to)
                ->subject($subject);

            foreach ($attachments as $attachment) {
                $message->attach($attachment);
            }
        });

        // Opcjonalnie: zapisz wysłaną wiadomość do bazy (Sent)
        $email = EmailMessage::create([
            'email_account_id' => $account->id,
            'subject'          => $subject,
            'from_address'     => $account->email,
            'from_name'        => $account->account_name,
            'to_address'       => $to,
            'body_html'        => $body,
            'date'             => now(),
            'is_read'          => true,
            'is_sent'          => true,
        ]);

        if ($relatedTo) {
            if ($relatedTo instanceof \App\Models\Event) {
                $email->relatedEvents()->attach($relatedTo->id);
            } elseif ($relatedTo instanceof \App\Models\Task) {
                $email->relatedTasks()->attach($relatedTo->id);
            }
        }

        return $email;
    }
}
