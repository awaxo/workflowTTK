<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CancelledNotification extends Notification
{
    use Queueable;

    public $workflow;

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
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Ügy sztornózva')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('Az alábbi ügy sztornózásra került.')
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
