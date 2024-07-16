<?php

namespace App\Listeners;

use App\Events\StateChangedEvent;
use App\Notifications\StateChangedNotification;

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
    public function handle(StateChangedEvent $event): void
    {
        $notificationReceivers[] = $event->message ? $event->workflow->createdBy : [];

        // notify all users in notificationReceivers
        foreach ($notificationReceivers as $receiver) {
            if(method_exists($receiver, 'notify')) {
                $receiver->notify(new StateChangedNotification($event->workflow, $event->previousState, $event->currentState));
            }
        }
    }
}
