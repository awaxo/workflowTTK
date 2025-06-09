<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\WorkflowStartedEvent;
use Modules\EmployeeRecruitment\App\Notifications\LaborAdministratorNotification;

/**
 * RecruitmentStartedListener is an event listener that handles the WorkflowStartedEvent.
 * It sends a notification to the labor administrator when a recruitment workflow starts.
 */
class RecruitmentStartedListener
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
     * This method is triggered when the WorkflowStartedEvent is fired.
     * It sends a notification to the labor administrator associated with the workgroup1
     * of the workflow that has just started.
     *
     * @param WorkflowStartedEvent $event
     * @return void
     */
    public function handle(WorkflowStartedEvent $event): void
    {
        $workgroup1 = $event->workflow->workgroup1;
        $workgroup1->laborAdministrator->notify(new LaborAdministratorNotification($event->workflow));
    }
}
