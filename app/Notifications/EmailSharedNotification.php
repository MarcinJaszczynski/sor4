<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailSharedNotification extends Notification
{
    use Queueable;

    public int $emailMessageId;
    public string $subject;

    public function __construct(int $emailMessageId, string $subject)
    {
        $this->emailMessageId = $emailMessageId;
        $this->subject = $subject;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Wiadomość została Ci udostępniona: ' . $this->subject,
            'email_message_id' => $this->emailMessageId,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Wiadomość udostępniona: ' . $this->subject)
            ->line('Wiadomość została udostępniona Tobie w panelu.')
            ->action('Zobacz wiadomość', url('/admin/resources/email-messages/' . $this->emailMessageId . '/edit'));
    }
}
