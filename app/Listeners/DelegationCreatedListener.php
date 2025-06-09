<?php

namespace App\Listeners;

use App\Events\DelegationCreatedEvent;
use App\Models\User;
use App\Notifications\DelegationNotification;

/**
 * Listener for handling delegation created events.
 * This listener sends a notification to the delegate user when a delegation is created.
 */
class DelegationCreatedListener
{
    /**
     * Create a new listener instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param DelegationCreatedEvent $event
     * @return void
     */
    public function handle(DelegationCreatedEvent $event): void
    {
        // Get the delegation and delegate user
        $delegation = $event->delegation;
        $delegateUser = User::find($delegation->delegate_user_id);
        $originalUser = User::find($delegation->original_user_id);
        
        if ($delegateUser) {
            // Send notification to the delegate user
            $delegateUser->notify(new DelegationNotification($delegation, $originalUser));
        }
    }
}