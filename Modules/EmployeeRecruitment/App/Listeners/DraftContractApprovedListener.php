<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Notifications\EntryPermissionNotification;

/**
 * DraftContractApprovedListener is an event listener that handles the StateChangedEvent.
 * It checks if the previous state was 'draft_contract_pending' and sends a notification
 * to the IT lead user when the draft contract is approved.
 */
class DraftContractApprovedListener
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
     * This method is triggered when the StateChangedEvent is fired.
     * It checks if the previous state was 'draft_contract_pending' and sends a notification
     * to the IT lead user when the draft contract is approved.
     *
     * @param StateChangedEvent $event
     * @return void
     */
    public function handle(StateChangedEvent $event): void
    {
        if ($event->previousState == 'draft_contract_pending') {
            $itLeadUser = Workgroup::where('workgroup_number', 915)->first()->leader;
            $itLeadUser->notify(new EntryPermissionNotification($event->workflow), true);
        }
    }
}
