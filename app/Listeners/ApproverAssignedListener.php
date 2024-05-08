<?php

namespace App\Listeners;

use App\Events\ApproverAssignedEvent;

class StateChangedListener
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
    public function handle(ApproverAssignedEvent $event): void
    {
                
    }
}
