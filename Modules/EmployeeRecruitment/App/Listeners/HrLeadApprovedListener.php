<?php

namespace Modules\EmployeeRecruitment\App\Listeners;

use App\Events\StateChangedEvent;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Notifications\EntryPermissionNotification;
use Modules\EmployeeRecruitment\App\Notifications\OperationsCoordinatorNotification;
use Modules\EmployeeRecruitment\App\Notifications\RadiationProtectionServiceNotification;
use Modules\EmployeeRecruitment\App\Notifications\RecruitmentCreatorNotification;

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
            // Notify Operations Coordinators
            $operationsCoordinators = User::whereHas('roles', function($query) {
                $query->where('name', 'uzemeltetesi_ugyintezo');
            })->get();
            
            foreach ($operationsCoordinators as $user) {
                $user->notify(new OperationsCoordinatorNotification($event->workflow));
            }

            // Notify Creator
            $event->workflow->createdBy->notify(new RecruitmentCreatorNotification($event->workflow));

            // Notify Operations Lead
            $operationLeadUser = Workgroup::where('workgroup_number', 914)->first()->leader;
            $operationLeadUser->notify(new EntryPermissionNotification($event->workflow));
            
            // Check if we need to notify Radiation Protection Service
            $this->notifyRadiationProtectionServiceIfNeeded($event);
        }
    }
    
    /**
     * Notify the Radiation Protection Service if the recruitment involves ionizing radiation risk.
     *
     * @param StateChangedEvent $event
     * @return void
     */
    private function notifyRadiationProtectionServiceIfNeeded(StateChangedEvent $event): void
    {
        $medicalData = json_decode($event->workflow->medical_eligibility_data, true);
        
        // Check if ionizing radiation exposure is present and is either "resz" (part-time) or "egesz" (full-time)
        if (
            isset($medicalData['ionizing_radiation_exposure']) && 
            in_array($medicalData['ionizing_radiation_exposure'], ['resz', 'egesz'])
        ) {
            // Get all users with the radiation_protection_service role
            $radiationProtectionUsers = User::whereHas('roles', function($query) {
                $query->where('name', 'sugarvedelmi_szolgalat');
            })->get();
            
            // Notify each radiation protection service user
            foreach ($radiationProtectionUsers as $user) {
                $user->notify(new RadiationProtectionServiceNotification($event->workflow));
            }
        }
    }
}