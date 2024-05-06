<?php

namespace App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusChangeNotification extends Notification
{
    use Queueable;

    protected $workflow;

    /**
     * Create a new notification instance.
     */
    public function __construct(AbstractWorkflow $workflow)
    {
        $this->workflow = $workflow;
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
        $url = url('/folyamat/megtekintés/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Ügy státusz változás')
                    ->greeting('Kedves ' . $notifiable->name . '!')
                    ->line('Az alábbi ügy státusza megváltozott:')
                    ->line('Ügy típusa: ' . $this->workflow->workflowType->name)
                    ->line('Státusz: ' . $this->workflow->state)
                    ->action('Ügy megtekintése', $url)
                    ->line('Üdvözlettel,')
                    ->line('Workflow rendszer');
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
