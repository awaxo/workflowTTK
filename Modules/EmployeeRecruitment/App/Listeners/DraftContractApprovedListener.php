<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Notifications\EntryPermissionNotification;

class DraftContractApprovedListener
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
        if ($event->previousState == 'draft_contract_pending') {
            $itLeadUser = Workgroup::where('workgroup_number', 915)->first()->leader;
            $itLeadUser->notify(new EntryPermissionNotification($event->workflow), true);
        }
    }
}
