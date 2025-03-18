<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\WorkflowStartedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\LaborAdministratorNotification;

class RecruitmentStartedListener
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
    public function handle(WorkflowStartedEvent $event): void
    {
        $workgroup1 = $event->workflow->workgroup1;
        $workgroup1->laborAdministrator->notify(new LaborAdministratorNotification($event->workflow));
    }
}
