<?php

namespace App\Notifications;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * DelegationRejectedNotification is a notification class that sends an email
 * to the user when a delegation is rejected by another user.
 * It includes the details of the delegation and the rejecting user.
 */
class DelegationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Delegation $delegation;
    public User $delegateUser;
    public string $readableType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Delegation $delegation, User $delegateUser, string $readableType)
    {
        $this->delegation = $delegation;
        $this->delegateUser = $delegateUser;
        $this->readableType = $readableType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/profil');
        $start_date = date('Y.m.d', strtotime($this->delegation->start_date));
        $end_date = date('Y.m.d', strtotime($this->delegation->end_date));

        return (new MailMessage)
                    ->subject('Helyettesítési megbízás törölve')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line($this->delegateUser->name . ' törölte a helyettesítési megbízást az alábbiak szerint:')
                    ->line('Helyettesített funkció: ' . $this->readableType)
                    ->line('Időszak: ' . $start_date . ' - ' . $end_date)
                    ->line('')
                    ->action('Profil megtekintése', $url)
                    ->line('')
                    ->line('Üdvözlettel,')
                    ->line('Ügyintézési rendszer');
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