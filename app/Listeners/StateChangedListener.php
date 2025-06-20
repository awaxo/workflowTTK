<?php

namespace App\Listeners;

use App\Events\StateChangedEvent;
use App\Notifications\StateChangedNotification;

/**
 * Listener for handling state change events.
 * This listener sends a notification to the user when a workflow state changes.
 */
class StateChangedListener
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
     * @param StateChangedEvent $event
     * @return void
     */
    public function handle(StateChangedEvent $event): void
    {
        $notificationReceivers[] = $event->message == '' ? '' : $event->workflow->createdBy;

        // notify all users in notificationReceivers
        foreach ($notificationReceivers as $receiver) {
            if(method_exists($receiver, 'notify')) {
                $receiver->notify(new StateChangedNotification($event->workflow, __('states.' . $event->previousState), __('states.' . $event->currentState)));
            }
        }
    }
}
