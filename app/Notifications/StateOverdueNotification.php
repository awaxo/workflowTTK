<?php

namespace App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class StateOverdueNotification extends Notification
{
    use Queueable;

    public $workflow;
    public $deadline;

    /**
     * Create a new notification instance.
     */
    public function __construct(AbstractWorkflow $workflow, $deadline)
    {
        $this->workflow = $workflow;
        $this->deadline = $deadline;
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
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Ügy státusz határidő lejárat')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('Az alábbi ügy az Ön jóváhagyására vár, több, mint ' . $this->deadline . ' órája.')
                    ->line('Ügy típusa: ' . $this->workflow->workflowType->name)
                    ->line('Jelenlegi státusz: ' .  __('states.' . $this->workflow->state))
                    ->action('Ügy megtekintése', $url)
                    ->line('Kérjük, mihamarabb lépjen be az Ügyintézési rendszerbe, és hozza meg döntését!')
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
