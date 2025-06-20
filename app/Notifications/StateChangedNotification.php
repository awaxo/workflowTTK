<?php

namespace App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * StateChangedNotification is a notification class that sends an email
 * to the user when the state of a workflow changes.
 * It includes the details of the workflow and the state change.
 */
class StateChangedNotification extends Notification
{
    use Queueable;

    public $workflow;
    public $previousState;
    public $currentState;

    /**
     * Create a new notification instance.
     */
    public function __construct(AbstractWorkflow $workflow, string $previousState, string $currentState)
    {
        $this->workflow = $workflow;
        $this->previousState = $previousState;
        $this->currentState = $currentState;
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
                    ->subject('Ügy státusz változás')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('Az alábbi ügy státusza megváltozott:')
                    ->line('Ügy típusa: ' . $this->workflow->workflowType->name)
                    ->line('Korábbi státusz: ' . $this->previousState)
                    ->line('Jelenlegi státusz: ' . $this->currentState)
                    ->line('')
                    ->action('Ügy megtekintése', $url)
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
