<?php

namespace App\Listeners;

use App\Events\DelegationCreatedEvent;
use App\Models\User;
use App\Notifications\DelegationNotification;

class DelegationCreatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
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