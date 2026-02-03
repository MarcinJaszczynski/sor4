<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\Sms\SmsGatewayInterface;

class SmsChannel
{
    protected $smsGateway;

    public function __construct(SmsGatewayInterface $smsGateway)
    {
        $this->smsGateway = $smsGateway;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        // Pobierz numer telefonu (zakładamy, że model User/Klient ma pole phone lub phone_number)
        $to = $notifiable->phone ?? $notifiable->phone_number;

        if (!$to) {
            return; // Brak numeru, pomijamy
        }

        $message = $notification->toSms($notifiable);

        $this->smsGateway->send($to, $message);
    }
}
