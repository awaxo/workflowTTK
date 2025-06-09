<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Class ExpiredCostCentersNotification
 * This notification is sent to users when their cost centers have expired and are automatically closed.
 * It extends the base Notification class and uses the Queueable trait for queueing.
 */
class ExpiredCostCentersNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Collection of expired cost centers
     *
     * @var Collection
     */
    protected $costCenters;

    /**
     * Create a new notification instance.
     *
     * @param Collection $costCenters
     * @return void
     */
    public function __construct(Collection $costCenters)
    {
        $this->costCenters = $costCenters;
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
        $mailMessage = (new MailMessage)
            ->subject('Költséghelyek automatikus lezárása')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('Az alábbi, Ön által koordinált költséghelyek lejárati dátuma elérkezett, ezért automatikusan lezárásra kerültek:')
            ->line('');

        // Add each cost center to the email
        foreach ($this->costCenters as $costCenter) {
            $mailMessage->line("- {$costCenter->cost_center_code}: {$costCenter->name} (Lejárat: {$costCenter->due_date})");
        }

        $mailMessage
            ->line('')
            ->line('A lezárt költséghelyeken további műveletek nem végezhetők.')
            ->line('')
            ->line('Üdvözlettel,')
            ->line('Ügyintézési rendszer');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'cost_centers' => $this->costCenters->map(function ($center) {
                return [
                    'id' => $center->id,
                    'code' => $center->cost_center_code,
                    'name' => $center->name,
                    'due_date' => $center->due_date
                ];
            })->toArray()
        ];
    }
}