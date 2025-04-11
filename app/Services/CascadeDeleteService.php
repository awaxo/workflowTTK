<?php

namespace App\Services;

use App\Events\ModelChangedEvent;
use App\Models\CostCenter;
use App\Models\ExternalAccessRight;
use App\Models\User;
use App\Models\Workgroup;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling cascade delete operations
 */
class CascadeDeleteService
{
    /**
     * The notification service
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new service instance.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the deletion of a workgroup and related cascade operations
     *
     * @param Workgroup $workgroup
     * @return void
     */
    public function handleWorkgroupDeletion(Workgroup $workgroup)
    {
        Log::info("CascadeDeleteService: Starting cascade delete for workgroup: {$workgroup->workgroup_number} - {$workgroup->name}");

        // Start a transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // 1. Mark related users as deleted
            $deletedUsers = $this->deleteRelatedUsers($workgroup);

            // 2. Mark related cost centers as deleted
            $deletedCostCenters = $this->deleteRelatedCostCenters($workgroup);

            // 3. Mark related external access rights as deleted
            $deletedAccessRights = $this->deleteRelatedAccessRights($workgroup);

            // 4. Log the completion of deletions
            Log::info("CascadeDeleteService: Completed cascade deletions for workgroup {$workgroup->workgroup_number}", [
                'workgroup_id' => $workgroup->id,
                'deleted_users_count' => $deletedUsers->count(),
                'deleted_cost_centers_count' => $deletedCostCenters->count(),
                'deleted_access_rights_count' => $deletedAccessRights->count()
            ]);

            // Commit the transaction
            DB::commit();

            // 5. Send notifications (outside of transaction to avoid rollback if sending fails)
            $this->notificationService->sendWorkgroupDeletionNotifications(
                $workgroup, 
                $deletedUsers, 
                $deletedCostCenters
            );

            Log::info("CascadeDeleteService: Cascade delete for workgroup {$workgroup->workgroup_number} completed successfully");
        } catch (\Exception $e) {
            // Rollback the transaction in case of failure
            DB::rollBack();
            
            Log::error("CascadeDeleteService: Failed cascade delete for workgroup {$workgroup->workgroup_number}", [
                'workgroup_id' => $workgroup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw the exception to be caught by the listener
            throw $e;
        }
    }

    /**
     * Delete users related to the given workgroup
     *
     * @param Workgroup $workgroup
     * @return Collection
     */
    protected function deleteRelatedUsers(Workgroup $workgroup): Collection
    {
        Log::info("CascadeDeleteService: Finding users to delete for workgroup {$workgroup->workgroup_number}");
        
        // Find active users belonging to the workgroup
        $users = User::where('workgroup_id', $workgroup->id)
            ->where('deleted', 0)
            ->get();
        
        $systemUser = User::withFeatured()->where('featured', 1)->first();
        $systemUserId = $systemUser ? $systemUser->id : null;
        
        if ($users->isEmpty()) {
            Log::info("CascadeDeleteService: No active users found for workgroup {$workgroup->workgroup_number}");
            return new Collection();
        }
        
        Log::info("CascadeDeleteService: Found {$users->count()} active users to delete for workgroup {$workgroup->workgroup_number}");
        
        foreach ($users as $user) {
            $user->deleted = 1;
            $user->updated_by = $systemUserId;
            $user->save();
            
            // Trigger event for each user to ensure any related cascades happen
            event(new ModelChangedEvent($user, 'deleted'));
            
            Log::info("CascadeDeleteService: User {$user->name} (ID: {$user->id}) marked as deleted");
        }
        
        return $users;
    }

    /**
     * Delete cost centers related to the given workgroup
     *
     * @param Workgroup $workgroup
     * @return Collection
     */
    protected function deleteRelatedCostCenters(Workgroup $workgroup): Collection
    {
        Log::info("CascadeDeleteService: Finding cost centers to delete for workgroup {$workgroup->workgroup_number}");
        
        // Find active cost centers with workgroup number in the code
        $costCenters = CostCenter::where('deleted', 0)
            ->where(function ($query) use ($workgroup) {
                // Match cost centers where the last 3 digits match the workgroup number
                $query->whereRaw("SUBSTRING(cost_center_code, -3) = ?", [$workgroup->workgroup_number]);
            })
            ->get();
        
        $systemUser = User::withFeatured()->where('featured', 1)->first();
        $systemUserId = $systemUser ? $systemUser->id : null;
        
        if ($costCenters->isEmpty()) {
            Log::info("CascadeDeleteService: No active cost centers found for workgroup {$workgroup->workgroup_number}");
            return new Collection();
        }
        
        Log::info("CascadeDeleteService: Found {$costCenters->count()} active cost centers to delete for workgroup {$workgroup->workgroup_number}");
        
        foreach ($costCenters as $costCenter) {
            $costCenter->deleted = 1;
            $costCenter->updated_by = $systemUserId;
            $costCenter->save();
            
            // Trigger event for each cost center to ensure any related cascades happen
            event(new ModelChangedEvent($costCenter, 'deleted'));
            
            Log::info("CascadeDeleteService: Cost center {$costCenter->cost_center_code} (ID: {$costCenter->id}) marked as deleted");
        }
        
        return $costCenters;
    }

    /**
     * Delete external access rights related to the given workgroup
     *
     * @param Workgroup $workgroup
     * @return Collection
     */
    protected function deleteRelatedAccessRights(Workgroup $workgroup): Collection
    {
        Log::info("CascadeDeleteService: Finding external access rights to delete for workgroup {$workgroup->workgroup_number}");
        
        // Find active external access rights for the workgroup
        $accessRights = ExternalAccessRight::where('admin_group_number', $workgroup->id)
            ->where('deleted', 0)
            ->get();
        
        $systemUser = User::withFeatured()->where('featured', 1)->first();
        $systemUserId = $systemUser ? $systemUser->id : null;
        
        if ($accessRights->isEmpty()) {
            Log::info("CascadeDeleteService: No active external access rights found for workgroup {$workgroup->workgroup_number}");
            return new Collection();
        }
        
        Log::info("CascadeDeleteService: Found {$accessRights->count()} active external access rights to delete for workgroup {$workgroup->workgroup_number}");
        
        foreach ($accessRights as $accessRight) {
            $accessRight->deleted = 1;
            $accessRight->updated_by = $systemUserId;
            $accessRight->save();
            
            // Trigger event for each access right to ensure any related cascades happen
            event(new ModelChangedEvent($accessRight, 'deleted'));
            
            Log::info("CascadeDeleteService: External access right {$accessRight->external_system} (ID: {$accessRight->id}) marked as deleted");
        }
        
        return $accessRights;
    }
}