<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelegationDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted delegate user
     *
     * @var User
     */
    protected $delegateUser;

    /**
     * Delegations where the user was a delegate
     *
     * @var Collection
     */
    protected $delegations;
    
    /**
     * User's workgroup number
     *
     * @var string|null
     */
    protected $workgroupNumber;

    /**
     * Create a new notification instance.
     *
     * @param User $delegateUser
     * @param Collection $delegations
     * @param string|null $workgroupNumber
     * @return void
     */
    public function __construct(
        User $delegateUser,
        Collection $delegations,
        ?string $workgroupNumber
    ) {
        $this->delegateUser = $delegateUser;
        $this->delegations = $delegations;
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
            ->subject('Helyettesítési megbízás megszűnés')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('Értesítjük, hogy ' . $this->delegateUser->name . ' (' . ($this->workgroupNumber ? $this->workgroupNumber . '. csoportszám, ' : '') . $this->delegateUser->email . ') felhasználó lezárásra került.')
            ->line('Ezért az alábbi helyettesítési megbízások automatikusan megszűntek:')
            ->line('');

        // Add delegations
        foreach ($this->delegations as $delegation) {
            // A delegáció részleteinek megjelenítése
            $delegationType = $delegation->delegation_type ?? 'Általános helyettesítés';
            $validUntil = $delegation->valid_until ? date('Y.m.d', strtotime($delegation->valid_until)) : 'Határozatlan';
            
            $mailMessage->line("- {$delegationType} ({$validUntil}-ig)");
        }

        $mailMessage
            ->line('')
            ->line('Amennyiben szükséges, kérjük, gondoskodjon új helyettesítő kijelöléséről.')
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
            'delegate_user' => [
                'id' => $this->delegateUser->id,
                'name' => $this->delegateUser->name,
                'email' => $this->delegateUser->email,
                'workgroup_number' => $this->workgroupNumber
            ],
            'delegations' => $this->delegations->map(function ($delegation) {
                return [
                    'id' => $delegation->id,
                    'delegation_type' => $delegation->delegation_type,
                    'valid_until' => $delegation->valid_until
                ];
            })->toArray()
        ];
    }
}