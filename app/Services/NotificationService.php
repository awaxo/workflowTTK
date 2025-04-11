<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workgroup;
use App\Notifications\CostCenterDeletedNotification;
use App\Notifications\DeletedUsersNotification;
use App\Notifications\RoomAssignmentNotification;
use App\Notifications\StoreGasCylinderAssignmentNotification;
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
     * Send notifications to project coordinators about deleted cost centers
     *
     * @param Collection $deletedCostCenters
     * @return void
     */
    protected function sendCostCenterDeletionNotification(Collection $deletedCostCenters): void
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
    protected function sendDeletedUsersNotification(Collection $deletedUsers, Workgroup $workgroup): void
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
    protected function sendRoomsAssignmentNotification(Workgroup $workgroup): void
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
    protected function sendStoreGasCylinderAssignmentNotification(Workgroup $workgroup): void
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
}