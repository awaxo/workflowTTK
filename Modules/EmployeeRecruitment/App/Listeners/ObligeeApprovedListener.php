<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\ExternalAccessRight;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Notifications\EntryPermissionNotification;
use Modules\EmployeeRecruitment\App\Notifications\ExternalSystemNotification;
use Modules\EmployeeRecruitment\App\Notifications\WorkgroupLeadsNotification;

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
            
            // Notify the leaders of external access right admin groups
            $externalAccessRights = $event->workflow->external_access_rights;
            if (!empty($externalAccessRights)) {
                $leaders = ExternalAccessRight::whereIn('id', explode(',', $externalAccessRights))
                    ->pluck('admin_group_number')
                    ->unique()
                    ->flatMap(function ($groupNumber) { // Fetch workgroups and their leaders in one step
                        return Workgroup::where('workgroup_number', $groupNumber)->pluck('leader');
                    })
                    ->filter(); // Filter out null values

                $leaders->each(function ($leader) use ($event) {
                    $leader->notify(new ExternalSystemNotification($event->workflow));
                });
            }

            // Notify the leaders of workgroups in workgroup1 and workgroup2 of the workflow, if those are not empty
            $leaders2 = collect([$event->workflow->workgroup1, $event->workflow->workgroup2])
                ->filter()
                ->flatMap(function ($workgroup) {
                    return $workgroup->leader;
                });
            
            $leaders2->each(function ($leader) use ($event) {
                $leader->notify(new WorkgroupLeadsNotification($event->workflow));
            });

        }        
    }
}
