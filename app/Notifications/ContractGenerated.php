<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractGenerated extends Notification
{
    use Queueable;

    public int $contractId;

    public function __construct(int $contractId)
    {
        $this->contractId = $contractId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Umowa została wygenerowana',
            'contract_id' => $this->contractId,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Umowa wygenerowana')
            ->line('Umowa została wygenerowana i jest dostępna w panelu.')
            ->action('Zobacz umowę', url('/admin/resources/contracts'));
    }
}
