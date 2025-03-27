<?php

namespace App\Listeners;

use App\Events\DelegationRejectedEvent;
use App\Models\User;
use App\Notifications\DelegationRejectedNotification;
use App\Services\Interfaces\IDelegationService;
use Illuminate\Support\Facades\Log;

class DelegationRejectedListener
{
    /**
     * The delegation service instance.
     *
     * @var DelegationServiceInterface
     */
    protected $delegationService;

    /**
     * Status translation mapping
     * 
     * @var array
     */
    protected $statusTranslation = [
        'waiting_to_accept' => 'Elfogadásra vár',
        'valid' => 'Érvényes',
        'invalid' => 'Érvénytelen'
    ];

    /**
     * Create the event listener.
     *
     * @param DelegationServiceInterface $delegationService
     */
    public function __construct(IDelegationService $delegationService)
    {
        $this->delegationService = $delegationService;
    }

    /**
     * Handle the event.
     */
    public function handle(DelegationRejectedEvent $event): void
    {
        // Log the event for debugging
        Log::info('DelegationRejectedEvent triggered', [
            'delegation_id' => $event->delegation->id,
            'status' => $event->delegation->status,
            'deleted' => $event->delegation->deleted
        ]);
        
        // Ellenőrzés: csak akkor folytatjuk, ha a státusz tényleg "invalid" és a deleted = 1
        if ($event->delegation->status !== 'invalid' || $event->delegation->deleted !== 1) {
            Log::warning('DelegationRejectedEvent: Delegation status is not properly set', [
                'delegation_id' => $event->delegation->id,
                'status' => $event->delegation->status,
                'deleted' => $event->delegation->deleted
            ]);
            return;
        }
        
        $delegation = $event->delegation;
        
        // Get the original user and delegate user
        $originalUser = User::find($delegation->original_user_id);
        $delegateUser = User::find($delegation->delegate_user_id);
        
        if (!$originalUser || !$delegateUser) {
            Log::warning('DelegationRejectedEvent: Could not find users', [
                'original_user_id' => $delegation->original_user_id,
                'delegate_user_id' => $delegation->delegate_user_id
            ]);
            return;
        }
        
        // Get readable delegation type
        $readableType = $this->getReadableType($delegation, $originalUser);
        
        try {
            // Send notification to the original user
            $notification = new DelegationRejectedNotification($delegation, $delegateUser, $readableType);
            $originalUser->notify($notification);
            Log::info('DelegationRejectedEvent: Notification sent', [
                'to_user_id' => $originalUser->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send delegation rejected notification: ' . $e->getMessage(), [
                'delegation_id' => $delegation->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get readable delegation type
     * 
     * @param \App\Models\Delegation $delegation
     * @param User $originalUser
     * @return string
     */
    private function getReadableType($delegation, $originalUser): string
    {
        // Get readable delegation type using the injected service
        $allDelegations = $this->delegationService->getAllDelegations($originalUser);
        
        $readableType = $delegation->type;
        foreach ($allDelegations as $delegationItem) {
            if (isset($delegationItem['type']) && $delegationItem['type'] === $delegation->type) {
                $readableType = $delegationItem['readable_name'];
                break;
            } elseif (is_array($delegationItem)) {
                foreach ($delegationItem as $item) {
                    if (isset($item['type']) && $item['type'] === $delegation->type) {
                        $readableType = $item['readable_name'];
                        break 2;
                    }
                }
            }
        }
        
        // Check for labor admin or project coordinator type
        if (preg_match('/^draft_contract_labor_administrator_\d+$/', $delegation->type)) {
            $readableType = 'Munkaügyi ügyintéző';
        } elseif (preg_match('/^project_coordinator_workgroup_\d+$/', $delegation->type)) {
            $readableType = 'Projektkoordinátor';
        }
        
        return $readableType;
    }

    /**
     * Translate database status to display status
     * 
     * @param string $dbStatus
     * @return string
     */
    protected function translateStatus($dbStatus)
    {
        return $this->statusTranslation[$dbStatus] ?? $dbStatus;
    }
}