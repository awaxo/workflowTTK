<?php

namespace App\Notifications;

use App\Models\Delegation;
use App\Models\User;
use App\Services\Interfaces\IDelegationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/*
 * DelegationNotification is a notification class that sends an email
 * to the user when a delegation is created or updated.
 * It includes the details of the delegation and the original user.
 */
class DelegationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Delegation $delegation;
    public User $originalUser;
    protected IDelegationService $delegationService;

    /**
     * Create a new notification instance.
     *
     * @param Delegation $delegation
     * @param User $originalUser
     */
    public function __construct(Delegation $delegation, User $originalUser)
    {
        $this->delegation = $delegation;
        $this->originalUser = $originalUser;
        
        // Resolve the DelegationServiceInterface from the container
        // since Notifications are not resolved from the container automatically
        $this->delegationService = App::make(IDelegationService::class);
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
        // Get readable delegation type using the service
        $readable_type = $this->getReadableType();
        
        $url = url('https://ugyintezes.ttk.hu/profil');
        $start_date = date('Y.m.d', strtotime($this->delegation->start_date));
        $end_date = $this->delegation->end_date 
            ? date('Y.m.d', strtotime($this->delegation->end_date))
            : 'határozatlan idő';

        return (new MailMessage)
                    ->subject('Helyettesítési megbízás')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line($this->originalUser->name . ' helyettesítési megbízást adott Önnek az alábbiak szerint:')
                    ->line('Helyettesített funkció: ' . $readable_type)
                    ->line('Időszak: ' . $start_date . ' - ' . $end_date)
                    ->line('')
                    ->action('Profil megtekintése', $url)
                    ->line('')
                    ->line('Üdvözlettel,')
                    ->line('Ügyintézési rendszer');
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
            //
        ];
    }
    
    /**
     * Get readable delegation type
     * 
     * @return string
     */
    protected function getReadableType(): string
    {
        // Get readable delegation type using the service
        $allDelegations = $this->delegationService->getAllDelegations($this->originalUser);
        
        $readable_type = $this->delegation->type;
        foreach ($allDelegations as $delegationItem) {
            if (isset($delegationItem['type']) && $delegationItem['type'] === $this->delegation->type) {
                $readable_type = $delegationItem['readable_name'];
                break;
            } elseif (is_array($delegationItem)) {
                foreach ($delegationItem as $item) {
                    if (isset($item['type']) && $item['type'] === $this->delegation->type) {
                        $readable_type = $item['readable_name'];
                        break 2;
                    }
                }
            }
        }
        
        // Check for labor admin or project coordinator type
        if (preg_match('/^draft_contract_labor_administrator_\d+$/', $this->delegation->type)) {
            $readable_type = 'Munkaügyi ügyintéző';
        } elseif (preg_match('/^project_coordinator_workgroup_\d+$/', $this->delegation->type)) {
            $readable_type = 'Projektkoordinátor';
        }
        
        return $readable_type;
    }
}