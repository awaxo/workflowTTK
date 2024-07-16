<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\SuspendedEvent;
use Modules\EmployeeRecruitment\App\Notifications\SuspendedNotification;

class SuspendedListener
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
    public function handle(SuspendedEvent $event): void
    {
        $event->workflow->createdBy->notify(new SuspendedNotification($event->workflow));
    }
}
