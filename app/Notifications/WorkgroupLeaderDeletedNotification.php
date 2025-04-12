<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkgroupLeaderDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted user
     *
     * @var User
     */
    protected $user;

    /**
     * Workgroups where the user was a leader
     *
     * @var Collection
     */
    protected $leadWorkgroups;
    
    /**
     * User's workgroup number
     *
     * @var string|null
     */
    protected $workgroupNumber;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     * @param Collection $leadWorkgroups
     * @param string|null $workgroupNumber
     * @return void
     */
    public function __construct(
        User $user,
        Collection $leadWorkgroups,
        ?string $workgroupNumber
    ) {
        $this->user = $user;
        $this->leadWorkgroups = $leadWorkgroups;
        $this->workgroupNumber = $workgroupNumber;
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
            ->subject('Csoportvezető lezárása')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('Értesítjük, hogy ' . $this->user->name . ' (' . ($this->workgroupNumber ? $this->workgroupNumber . '. csoportszám, ' : '') . $this->user->email . ') felhasználó lezárásra került.')
            ->line('A felhasználó az alábbi aktív csoportok vezetőjeként volt beállítva:')
            ->line('');

        // Add workgroups
        foreach ($this->leadWorkgroups as $workgroup) {
            $mailMessage->line("- {$workgroup->workgroup_number}: {$workgroup->name}");
        }

        $mailMessage
            ->line('')
            ->line('Kérjük, hogy a csoport adatkarbantartóban szíveskedjen intézkedni a szükséges módosításokról!')
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'workgroup_number' => $this->workgroupNumber
            ],
            'lead_workgroups' => $this->leadWorkgroups->map(function ($workgroup) {
                return [
                    'id' => $workgroup->id,
                    'number' => $workgroup->workgroup_number,
                    'name' => $workgroup->name
                ];
            })->toArray()
        ];
    }
}