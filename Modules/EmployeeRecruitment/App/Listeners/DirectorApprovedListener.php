<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Notifications\EntryPermissionNotification;

class DirectorApprovedListener
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
        if ($event->previousState == 'director_approval') {
            $itLeadUser = Workgroup::where('workgroup_number', 915)->first()->leader;
            $itLeadUser->notify(new EntryPermissionNotification($event->workflow));

            $operationLeadUser = Workgroup::where('workgroup_number', 914)->first()->leader;
            $operationLeadUser->notify(new EntryPermissionNotification($event->workflow));
        }
    }
}
