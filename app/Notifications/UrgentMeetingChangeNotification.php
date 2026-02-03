<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\SmsChannel;

class UrgentMeetingChangeNotification extends Notification
{
    use Queueable;

    public string $eventName;
    public string $newTime;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $eventName, string $newTime)
    {
        $this->eventName = $eventName;
        $this->newTime = $newTime;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // HYBRYDA: Zawsze email, SMS tylko jeśli to pilne (tutaj zakładamy, że ta klasa jest zawsze pilna)
        return ['mail', SmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PILNE: Zmiana godziny zbiórki - ' . $this->eventName)
            ->greeting('Dzień dobry ' . $notifiable->name . ',')
            ->line('Informujemy o zmianie godziny zbiórki dla wyjazdu: ' . $this->eventName)
            ->line('Nowa godzina to: ' . $this->newTime)
            ->line('Prosimy o punktualne przybycie.')
            ->action('Zobacz szczegóły w panelu', url('/admin'))
            ->priority(1); // High priority email
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): string
    {
        // SMS powinien być krótki i treściwy
        return "BP RAFA: PILNE! Zmiana zbiorki {$this->eventName}. Nowa godz: {$this->newTime}. Spr maila.";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
