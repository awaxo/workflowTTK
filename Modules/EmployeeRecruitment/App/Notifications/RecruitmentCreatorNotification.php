<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/*
 * Class RecruitmentCreatorNotification
 * This notification is sent to the creator of a recruitment workflow when the occupational health referral is available for download.
 * It extends the base Notification class and uses the Queueable trait for queueing.
 */
class RecruitmentCreatorNotification extends Notification
{
    use Queueable;

    public $workflow;

    /*
     * Create a new notification instance.
     *
     * @param RecruitmentWorkflow $workflow
     */
    public function __construct(RecruitmentWorkflow $workflow)
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
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Foglalkozás-egészségügyi orvosi beutaló letölthető')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line('Az alábbi felvételhez kapcsolódóan a foglalkozás-egészségügyi orvosi beutaló letölthető:')
                    ->line('Név: ' . $this->workflow->name)
                    ->line('Ügy ID: ' . $this->workflow->pseudo_id . '/' . date('Y', strtotime($this->workflow->created_at)))
                    ->line('')
                    ->action('Ügy megtekintése', $url)
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
