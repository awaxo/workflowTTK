<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\SuspendedEvent;
use Modules\EmployeeRecruitment\App\Notifications\SuspendedNotification;

/**
 * SuspendedListener is an event listener that handles the SuspendedEvent.
 * It sends a notification to the user who created the workflow when it is suspended.
 */
class SuspendedListener
{
    /**
     * Create a new listener instance.
     *
     * This constructor does not require any dependencies, but it can be extended in the future
     * if needed for dependency injection.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * This method is triggered when the SuspendedEvent is fired.
     * It sends a notification to the user who created the workflow when it is suspended.
     *
     * @param SuspendedEvent $event
     * @return void
     */
    public function handle(SuspendedEvent $event): void
    {
        $event->workflow->createdBy->notify(new SuspendedNotification($event->workflow));
    }
}
