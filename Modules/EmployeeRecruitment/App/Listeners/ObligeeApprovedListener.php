<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Events\SuspendedEvent;
use App\Models\Workgroup;
use App\Notifications\ITLeadNotification;
use Modules\EmployeeRecruitment\App\Notifications\SuspendedNotification;

class ObligeeApprovedListener
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
        if ($event->previousState == 'obligee_approval') {
            $itLeadUser = Workgroup::where('workgroup_number', 915)->first()->leader;
            $itLeadUser->notify(new ITLeadNotification($event->workflow, $event->previousState, $event->currentState));
        }
    }
}
