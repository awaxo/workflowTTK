<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Notifications\OperationsCoordinatorNotification;

class HrLeadApprovedListener
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
        if ($event->previousState == 'hr_lead_approval') {
            $operationsCoordinators = User::whereHas('roles', function($query) {
                $query->where('name', 'uzemeltetesi_ugyintezo');
            })->get();
            
            foreach ($operationsCoordinators as $user) {
                $user->notify(new OperationsCoordinatorNotification($event->workflow));
            }
        }
    }
}
