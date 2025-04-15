<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workgroup;
use App\Notifications\CostCenterDeletedNotification;
use App\Notifications\CostCenterUserDeletedNotification;
use App\Notifications\DelegationDeletedNotification;
use App\Notifications\DeletedUsersNotification;
use App\Notifications\LaborAdminDeletedNotification;
use App\Notifications\RoomAssignmentNotification;
use App\Notifications\StoreGasCylinderAssignmentNotification;
use App\Notifications\WorkgroupLeaderDeletedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending notifications related to cascade delete operations
 */
class NotificationService
{
    /**
     * Send all notifications related to workgroup deletion
     *
     * @param Workgroup $workgroup
     * @param Collection $deletedUsers
     * @param Collection $deletedCostCenters
     * @return void
     */
    public function sendWorkgroupDeletionNotifications(
        Workgroup $workgroup, 
        Collection $deletedUsers, 
        Collection $deletedCostCenters
    ): void {
        Log::info("NotificationService: Starting to send notifications for workgroup {$workgroup->workgroup_number} deletion");

        // 1. Notify project coordinators about deleted cost centers
        $this->sendCostCenterDeletionNotification($deletedCostCenters);

        // 2. Notify IT manager about deleted users
        $this->sendDeletedUsersNotification($deletedUsers, $workgroup);

        // 3. Notify operations about room reassignment
        $this->sendRoomsAssignmentNotification($workgroup);

        // 4. Notify store about gas cylinder reassignment
        $this->sendStoreGasCylinderAssignmentNotification($workgroup);

        Log::info("NotificationService: Completed sending notifications for workgroup {$workgroup->workgroup_number} deletion");
    }

    /**
     * Send all notifications related to user deletion
     *
     * @param User $user
     * @param Collection $leadCostCenters
     * @param Collection $coordinatorCostCenters
     * @param Collection $leadWorkgroups
     * @param Collection $laborAdminWorkgroups
     * @param Collection $originalDelegations
     * @param Collection $delegateDelegations
     * @return void
     */
    public function sendUserDeletionNotifications(
        User $user,
        Collection $leadCostCenters,
        Collection $coordinatorCostCenters,
        Collection $leadWorkgroups,
        Collection $laborAdminWorkgroups,
        Collection $originalDelegations,
        Collection $delegateDelegations
    ): void {
        Log::info("NotificationService: Starting to send notifications for user {$user->name} deletion");

        // 1. Notify cost center administrators about cost centers where the user was a lead or coordinator
        if (!$leadCostCenters->isEmpty() || !$coordinatorCostCenters->isEmpty()) {
            $this->sendCostCenterUserNotifications($user, $leadCostCenters, $coordinatorCostCenters);
        }

        // 2. Notify procurement department leader about workgroups where the user was a leader
        if (!$leadWorkgroups->isEmpty()) {
            $this->sendWorkgroupLeaderNotifications($user, $leadWorkgroups);
        }

        // 3. Notify HR department leader about workgroups where the user was a labor administrator
        if (!$laborAdminWorkgroups->isEmpty()) {
            $this->sendLaborAdminNotifications($user, $laborAdminWorkgroups);
        }

        // 4. Notify original users about delegate delegations being deleted
        if (!$delegateDelegations->isEmpty()) {
            $this->sendDelegationNotifications($user, $delegateDelegations);
        }

        Log::info("NotificationService: Completed sending notifications for user {$user->name} deletion");
    }


    /**
     * Send notifications to project coordinators about deleted cost centers
     *
     * @param Collection $deletedCostCenters
     * @return void
     */
    public function sendCostCenterDeletionNotification(Collection $deletedCostCenters): void
    {
        if ($deletedCostCenters->isEmpty()) {
            Log::info("NotificationService: No cost centers to notify about");
            return;
        }

        // Get users with "Költséghely adatkarbantartó" role for CC
        $costCenterAdmins = User::role('koltseghely_adatkarbantarto')
            ->where('deleted', 0)
            ->get();

        // Group cost centers by project coordinator
        $groupedByCoordinator = $deletedCostCenters->groupBy('project_coordinator_user_id');
        
        Log::info("NotificationService: Sending cost center deletion notifications to " . 
            $groupedByCoordinator->count() . " project coordinators");

        foreach ($groupedByCoordinator as $coordinatorId => $costCenters) {
            $coordinator = User::find($coordinatorId);
            
            if (!$coordinator) {
                Log::warning("NotificationService: Coordinator with ID {$coordinatorId} not found");
                continue;
            }

            try {
                $coordinator->notify(new CostCenterDeletedNotification(
                    $costCenters, 
                    $costCenterAdmins
                ));
                
                Log::info("NotificationService: Sent cost center deletion notification to coordinator {$coordinator->name} (ID: {$coordinator->id})");
            } catch (\Exception $e) {
                Log::error("NotificationService: Failed to send cost center deletion notification to coordinator {$coordinator->name}", [
                    'coordinator_id' => $coordinator->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notification to IT manager about deleted users
     *
     * @param Collection $deletedUsers
     * @param Workgroup $workgroup
     * @return void
     */
    public function sendDeletedUsersNotification(Collection $deletedUsers, Workgroup $workgroup): void
    {
        if ($deletedUsers->isEmpty()) {
            Log::info("NotificationService: No users to notify about");
            return;
        }

        // Find IT manager (assuming it's workgroup 915 leader)
        $itWorkgroup = Workgroup::where('workgroup_number', '915')
            ->where('deleted', 0)
            ->first();
            
        if (!$itWorkgroup || !$itWorkgroup->leader) {
            Log::warning("NotificationService: IT manager (workgroup 915 leader) not found");
            return;
        }
        
        $itManager = $itWorkgroup->leader;

        try {
            $itManager->notify(new DeletedUsersNotification($deletedUsers, $workgroup));
            
            Log::info("NotificationService: Sent deleted users notification to IT manager {$itManager->name} (ID: {$itManager->id})");
        } catch (\Exception $e) {
            Log::error("NotificationService: Failed to send deleted users notification to IT manager {$itManager->name}", [
                'it_manager_id' => $itManager->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification to operations about room reassignment
     *
     * @param Workgroup $workgroup
     * @return void
     */
    public function sendRoomsAssignmentNotification(Workgroup $workgroup): void
    {
        // Get all users with 'uzemeltetesi_ugyintezo' role
        $operationsUsers = User::role('uzemeltetesi_ugyintezo')
            ->where('deleted', 0)
            ->get();
                
        if ($operationsUsers->isEmpty()) {
            Log::warning("NotificationService: No operations users found for notification");
            return;
        }
        
        $ccEmails = ['gazdtitk.all@ttk.hu'];
        $notificationCount = 0;
        
        foreach ($operationsUsers as $user) {
            try {
                $user->notify(new RoomAssignmentNotification(
                    $workgroup,
                    $ccEmails
                ));
                
                $notificationCount++;
                Log::info("NotificationService: Sent room reassignment notification to operations user {$user->name}");
            } catch (\Exception $e) {
                Log::error("NotificationService: Failed to send room reassignment notification", [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info("NotificationService: Total {$notificationCount} room reassignment notifications sent successfully");
    }

    /**
     * Send notification to store about gas cylinder reassignment
     *
     * @param Workgroup $workgroup
     * @return void
     */
    public function sendStoreGasCylinderAssignmentNotification(Workgroup $workgroup): void
    {
        try {
            // Keressünk egy admin felhasználót, aki feladóként szerepelhet
            $adminUser = User::whereHas('roles', function ($query) {
                $query->where('name', 'adminisztrator');
            })->where('deleted', 0)->first();
            
            if (!$adminUser) {
                Log::warning("NotificationService: No admin user found for sending store notification");
                return;
            }

            // Külső email címzett
            $toEmail = 'raktar@ttk.hu';
            $ccEmails = ['gazdtitk.all@ttk.hu'];
            
            $notification = new StoreGasCylinderAssignmentNotification(
                $workgroup,
                $toEmail,
                $ccEmails
            );
            
            // A notification belső logikája fogja kezelni, hogy a címzett a raktar@ttk.hu legyen
            $adminUser->notify($notification);
            
            Log::info("NotificationService: Sent gas cylinder reassignment notification to store ({$toEmail})");
        } catch (\Exception $e) {
            Log::error("NotificationService: Failed to send gas cylinder reassignment notification", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notifications to cost center administrators about user's lead and coordinator roles
     *
     * @param User $user
     * @param Collection $leadCostCenters
     * @param Collection $coordinatorCostCenters
     * @return void
     */
    public function sendCostCenterUserNotifications(
        User $user,
        Collection $leadCostCenters,
        Collection $coordinatorCostCenters
    ): void {
        // Get users with "Költséghely adatkarbantartó" role
        $costCenterAdmins = User::role('koltseghely_adatkarbantarto')
            ->where('deleted', 0)
            ->get();

        if ($costCenterAdmins->isEmpty()) {
            Log::warning("NotificationService: No cost center administrators found to notify about user deletion");
            return;
        }

        Log::info("NotificationService: Sending cost center user role notifications to " . 
            $costCenterAdmins->count() . " cost center administrators");

        foreach ($costCenterAdmins as $admin) {
            try {
                $admin->notify(new CostCenterUserDeletedNotification(
                    $user,
                    $leadCostCenters,
                    $coordinatorCostCenters,
                    $user->workgroup ? $user->workgroup->workgroup_number : null
                ));
                
                Log::info("NotificationService: Sent cost center user role notification to administrator {$admin->name} (ID: {$admin->id})");
            } catch (\Exception $e) {
                Log::error("NotificationService: Failed to send cost center user role notification to administrator {$admin->name}", [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notifications to procurement department leader about workgroups where the user was a leader
     *
     * @param User $user
     * @param Collection $leadWorkgroups
     * @return void
     */
    public function sendWorkgroupLeaderNotifications(
        User $user,
        Collection $leadWorkgroups
    ): void {
        // Find procurement department leader (workgroup 912 leader)
        $procurementWorkgroup = Workgroup::where('workgroup_number', '912')
            ->where('deleted', 0)
            ->first();
            
        if (!$procurementWorkgroup || !$procurementWorkgroup->leader) {
            Log::warning("NotificationService: Procurement department leader (workgroup 912 leader) not found");
            return;
        }
        
        $procurementManager = $procurementWorkgroup->leader;

        try {
            $procurementManager->notify(new WorkgroupLeaderDeletedNotification(
                $user,
                $leadWorkgroups,
                $user->workgroup ? $user->workgroup->workgroup_number : null
            ));
            
            Log::info("NotificationService: Sent workgroup leader notification to procurement manager {$procurementManager->name} (ID: {$procurementManager->id})");
        } catch (\Exception $e) {
            Log::error("NotificationService: Failed to send workgroup leader notification to procurement manager {$procurementManager->name}", [
                'procurement_manager_id' => $procurementManager->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications to HR department leader about workgroups where the user was a labor administrator
     *
     * @param User $user
     * @param Collection $laborAdminWorkgroups
     * @return void
     */
    public function sendLaborAdminNotifications(
        User $user,
        Collection $laborAdminWorkgroups
    ): void {
        // Find HR department leader (workgroup 908 leader)
        $hrWorkgroup = Workgroup::where('workgroup_number', '908')
            ->where('deleted', 0)
            ->first();
            
        if (!$hrWorkgroup || !$hrWorkgroup->leader) {
            Log::warning("NotificationService: HR department leader (workgroup 908 leader) not found");
            return;
        }
        
        $hrManager = $hrWorkgroup->leader;

        try {
            $hrManager->notify(new LaborAdminDeletedNotification(
                $user,
                $laborAdminWorkgroups,
                $user->workgroup ? $user->workgroup->workgroup_number : null
            ));
            
            Log::info("NotificationService: Sent labor administrator notification to HR manager {$hrManager->name} (ID: {$hrManager->id})");
        } catch (\Exception $e) {
            Log::error("NotificationService: Failed to send labor administrator notification to HR manager {$hrManager->name}", [
                'hr_manager_id' => $hrManager->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notifications to original users about delegate delegations being deleted
     *
     * @param User $user
     * @param Collection $delegateDelegations
     * @return void
     */
    public function sendDelegationNotifications(
        User $user,
        Collection $delegateDelegations
    ): void {
        // Group delegations by original user to send one notification per user
        $groupedByOriginalUser = $delegateDelegations->groupBy('original_user_id');
        
        foreach ($groupedByOriginalUser as $originalUserId => $delegations) {
            $originalUser = User::find($originalUserId);
            
            if (!$originalUser) {
                Log::warning("NotificationService: Original user with ID {$originalUserId} not found");
                continue;
            }

            try {
                $originalUser->notify(new DelegationDeletedNotification(
                    $user,
                    $delegations,
                    $user->workgroup ? $user->workgroup->workgroup_number : null
                ));
                
                Log::info("NotificationService: Sent delegation notification to original user {$originalUser->name} (ID: {$originalUser->id})");
            } catch (\Exception $e) {
                Log::error("NotificationService: Failed to send delegation notification to original user {$originalUser->name}", [
                    'original_user_id' => $originalUser->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}