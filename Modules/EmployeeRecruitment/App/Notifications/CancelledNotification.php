<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class CancelledNotification
 * This notification is sent to users when a workflow has been cancelled.
 * It extends the base Notification class and uses the Queueable trait for queueing.
 */
class CancelledNotification extends Notification
{
    use Queueable;

    public $workflow;
    public $ccEmails;

    /**
     * Create a new notification instance.
     *
     * @param AbstractWorkflow $workflow
     * @param array<int, string> $ccEmails
     */
    public function __construct(AbstractWorkflow $workflow, array $ccEmails = [])
    {
        $this->workflow = $workflow;
        $this->ccEmails = $ccEmails;
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
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        $mailMessage = (new MailMessage)
                    ->subject('Ügy sztornózva')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('Az alábbi ügy sztornózásra került.')
                    ->line('')
                    ->action('Ügy megtekintése', $url)
                    ->line('Üdvözlettel,')
                    ->line('Ügyintézési rendszer');

        // Add CC recipients
        foreach ($this->ccEmails as $ccEmail) {
            $mailMessage->cc($ccEmail);
        }

        return $mailMessage;
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
