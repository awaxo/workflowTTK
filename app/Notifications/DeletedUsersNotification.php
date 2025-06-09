<?php

namespace App\Notifications;

use App\Models\Workgroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * DeletedUsersNotification is a notification class that sends an email
 * to the workgroup admin users when users are automatically deleted
 * due to the closure of a workgroup.
 * It includes the details of the deleted users and the workgroup.
 */
class DeletedUsersNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted users
     *
     * @var Collection
     */
    protected $deletedUsers;

    /**
     * The deleted workgroup
     *
     * @var Workgroup
     */
    protected $workgroup;

    /**
     * Create a new notification instance.
     *
     * @param Collection $deletedUsers
     * @param Workgroup $workgroup
     * @return void
     */
    public function __construct(Collection $deletedUsers, Workgroup $workgroup)
    {
        $this->deletedUsers = $deletedUsers;
        $this->workgroup = $workgroup;
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
            ->subject('Felhasználók automatikus lezárása')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('A(z) ' . $this->workgroup->workgroup_number . ' - ' . $this->workgroup->name . ' csoport lezárása miatt az alábbi felhasználók automatikusan lezárásra kerültek:')
            ->line('');

        // Add each user to the email
        foreach ($this->deletedUsers as $user) {
            $mailMessage->line("- {$user->name} ({$user->email})");
        }

        $mailMessage
            ->line('')
            ->line('Kérjük, hogy az érintett felhasználók jogosultságainak kezelését a megfelelő rendszerekben végezzék el.')
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
            'workgroup' => [
                'id' => $this->workgroup->id,
                'number' => $this->workgroup->workgroup_number,
                'name' => $this->workgroup->name
            ],
            'deleted_users' => $this->deletedUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            })->toArray()
        ];
    }
}