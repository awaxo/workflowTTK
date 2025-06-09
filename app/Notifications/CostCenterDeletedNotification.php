<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * CostCenterDeletedNotification is a notification class that sends an email
 * to the cost center admin users when cost centers are deleted.
 * It includes the details of the deleted cost centers in the email.
 */
class CostCenterDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted cost centers
     *
     * @var Collection
     */
    protected $costCenters;

    /**
     * The cost center admin users (for CC)
     *
     * @var Collection
     */
    protected $costCenterAdmins;

    /**
     * Create a new notification instance.
     *
     * @param Collection $costCenters
     * @param Collection $costCenterAdmins
     * @return void
     */
    public function __construct(Collection $costCenters, Collection $costCenterAdmins)
    {
        $this->costCenters = $costCenters;
        $this->costCenterAdmins = $costCenterAdmins;
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
            ->line('Az alábbi, Ön által koordinált költséghelyek automatikusan lezárásra kerültek, mert a csoportjuk lezárásra került:')
            ->line('');

        // Add each cost center to the email
        foreach ($this->costCenters as $costCenter) {
            $mailMessage->line("- {$costCenter->cost_center_code}: {$costCenter->name}");
        }

        $mailMessage
            ->line('')
            ->line('A lezárt költséghelyeken további műveletek nem végezhetők.')
            ->line('')
            ->line('Üdvözlettel,')
            ->line('Ügyintézési rendszer');

        // Add each cost center admin as CC
        foreach ($this->costCenterAdmins as $admin) {
            $mailMessage->cc($admin->email, $admin->name);
        }

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
                    'name' => $center->name
                ];
            })->toArray()
        ];
    }
}