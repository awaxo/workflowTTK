<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\ExternalAccessRight;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Log;
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
                $externalAccessRightsIds = explode(',', $externalAccessRights);
                $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
                // Extract the admin_group_number fields
                $adminGroupNumbers = $externalAccessRights->pluck('admin_group_number')->toArray();

                // Notify the leaders of the admin groups
                $adminGroups = Workgroup::whereIn('id', $adminGroupNumbers)->get();

                // get leader_id fields from adminGroups and get the Users with those ids
                $leaderIds = $adminGroups->pluck('leader_id')->toArray();
                $leaders = User::whereIn('id', $leaderIds)->get();

                $leaders->each(function ($leader) use ($event) {
                    $leader->notify(new ExternalSystemNotification($event->workflow));
                });
            }

            // get leader_id fields from workgroup1 and workgroup2 and get the Users with those ids
            $leaderIds2 = collect([$event->workflow->workgroup1, $event->workflow->workgroup2])
                ->filter()
                ->pluck('leader_id')
                ->toArray();
            $leaders2 = User::whereIn('id', $leaderIds2)->get();
            
            $leaders2->each(function ($leader) use ($event) {
                $leader->notify(new WorkgroupLeadsNotification($event->workflow));
            });
        }        
    }
}
