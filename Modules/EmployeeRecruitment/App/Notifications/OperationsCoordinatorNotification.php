<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class OperationsCoordinatorNotification extends Notification
{
    use Queueable;

    public $workflow;

    /**
     * Create a new notification instance.
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
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Értesítés új belépési folyamatról')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line('Az alábbi felvétel van folyamatban:')
                    ->line('Név: ' . $this->workflow->name)
                    ->line('Csoport 1: ' . ($this->workflow->workgroup1->name ?? '') . ' (' . ($this->workflow->workgroup1->workgroup_number ?? '') . ')')
                    ->line('Csoport 2: ' . ($this->workflow->workgroup2->name ?? '') . ' (' . ($this->workflow->workgroup2->workgroup_number ?? '') . ')')
                    ->line('Jogviszony kezdete: ' . $this->workflow->employment_start_date)
                    ->line('Jogviszony vége: ' . $this->workflow->employment_end_date)
                    ->line('Javasolt email cím: ' . $this->workflow->email)
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
