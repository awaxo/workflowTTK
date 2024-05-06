<?php

namespace App\Listeners;

use App\Events\StateChangedEvent;
use App\Notifications\StateChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        $notificationReceivers[] = $event->workflow->createdBy;

        // notify all users in notificationReceivers
        foreach ($notificationReceivers as $receiver) {
            $receiver->notify(new StateChangedNotification($event->workflow, $event->previousState, $event->currentState));
        }
    }
}
