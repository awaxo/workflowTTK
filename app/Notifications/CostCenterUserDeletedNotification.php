<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * CostCenterUserDeletedNotification is a notification class that sends an email
 * to the cost center admin users when a user is deleted from cost centers.
 * It includes the details of the deleted user and their roles in the cost centers.
 */
class CostCenterUserDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted user
     *
     * @var User
     */
    protected $user;

    /**
     * Cost centers where the user was a lead
     *
     * @var Collection
     */
    protected $leadCostCenters;

    /**
     * Cost centers where the user was a project coordinator
     *
     * @var Collection
     */
    protected $coordinatorCostCenters;
    
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
     * @param Collection $leadCostCenters
     * @param Collection $coordinatorCostCenters
     * @param string|null $workgroupNumber
     * @return void
     */
    public function __construct(
        User $user,
        Collection $leadCostCenters,
        Collection $coordinatorCostCenters,
        ?string $workgroupNumber
    ) {
        $this->user = $user;
        $this->leadCostCenters = $leadCostCenters;
        $this->coordinatorCostCenters = $coordinatorCostCenters;
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
            ->subject('Költséghely témavezető/projektkoordinátor lezárása')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('Értesítjük, hogy ' . $this->user->name . ' (' . ($this->workgroupNumber ? $this->workgroupNumber . '. csoportszám, ' : '') . $this->user->email . ') felhasználó lezárásra került.');

        // Add lead cost centers if any
        if (!$this->leadCostCenters->isEmpty()) {
            $mailMessage->line('A felhasználó az alábbi aktív költséghelyek témavezetőjeként volt beállítva:')
                ->line('');
            
            foreach ($this->leadCostCenters as $costCenter) {
                $mailMessage->line("- {$costCenter->cost_center_code}: {$costCenter->name}");
            }
            
            $mailMessage->line('');
        }

        // Add coordinator cost centers if any
        if (!$this->coordinatorCostCenters->isEmpty()) {
            $mailMessage->line('A felhasználó az alábbi aktív költséghelyek projektkoordinátoraként volt beállítva:')
                ->line('');
            
            foreach ($this->coordinatorCostCenters as $costCenter) {
                $mailMessage->line("- {$costCenter->cost_center_code}: {$costCenter->name}");
            }
            
            $mailMessage->line('');
        }

        $mailMessage
            ->line('Kérjük, hogy a költséghely adatkarbantartóban szíveskedjen intézkedni a szükséges módosításokról!')
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
            'lead_cost_centers' => $this->leadCostCenters->map(function ($center) {
                return [
                    'id' => $center->id,
                    'code' => $center->cost_center_code,
                    'name' => $center->name
                ];
            })->toArray(),
            'coordinator_cost_centers' => $this->coordinatorCostCenters->map(function ($center) {
                return [
                    'id' => $center->id,
                    'code' => $center->cost_center_code,
                    'name' => $center->name
                ];
            })->toArray()
        ];
    }
}