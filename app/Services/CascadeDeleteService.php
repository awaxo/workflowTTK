<?php

namespace App\Services;

use App\Events\ModelChangedEvent;
use App\Models\CostCenter;
use App\Models\Delegation;
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
     * Handle the deletion of a user and related cascade operations
     *
     * @param User $user
     * @return void
     */
    public function handleUserDeletion(User $user)
    {
        Log::info("CascadeDeleteService: Starting cascade delete for user: {$user->name} (ID: {$user->id})");

        // Start a transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // 1. Check if user is a lead user in any active cost centers
            $leadCostCenters = CostCenter::where('lead_user_id', $user->id)
                ->where('deleted', 0)
                ->get();

            // 2. Check if user is a project coordinator in any active cost centers
            $coordinatorCostCenters = CostCenter::where('project_coordinator_user_id', $user->id)
                ->where('deleted', 0)
                ->get();

            // 3. Check if user is a leader of any active workgroups
            $leadWorkgroups = Workgroup::where('leader_id', $user->id)
                ->where('deleted', 0)
                ->get();

            // 4. Check if user is a labor administrator of any active workgroups
            $laborAdminWorkgroups = Workgroup::where('labor_administrator', $user->id)
                ->where('deleted', 0)
                ->get();

            // 5. Check if user is an original user in any active delegations
            $originalDelegations = Delegation::where('original_user_id', $user->id)
                ->where('deleted', 0)
                ->get();

            // 6. Check if user is a delegate user in any active delegations
            $delegateDelegations = Delegation::where('delegate_user_id', $user->id)
                ->where('deleted', 0)
                ->get();

            // 7. Set delegations as deleted
            $this->deleteDelegations($originalDelegations);
            $this->deleteDelegations($delegateDelegations);

            Log::info("CascadeDeleteService: Found relationships for deleted user {$user->name}:", [
                'lead_cost_centers_count' => $leadCostCenters->count(),
                'coordinator_cost_centers_count' => $coordinatorCostCenters->count(),
                'lead_workgroups_count' => $leadWorkgroups->count(),
                'labor_admin_workgroups_count' => $laborAdminWorkgroups->count(),
                'original_delegations_count' => $originalDelegations->count(),
                'delegate_delegations_count' => $delegateDelegations->count()
            ]);

            // Commit the transaction
            DB::commit();

            // 8. Send notifications (outside of transaction to avoid rollback if sending fails)
            $this->notificationService->sendUserDeletionNotifications(
                $user,
                $leadCostCenters,
                $coordinatorCostCenters,
                $leadWorkgroups,
                $laborAdminWorkgroups,
                $originalDelegations,
                $delegateDelegations
            );

            Log::info("CascadeDeleteService: Cascade delete for user {$user->name} completed successfully");
        } catch (\Exception $e) {
            // Rollback the transaction in case of failure
            DB::rollBack();
            
            Log::error("CascadeDeleteService: Failed cascade delete for user {$user->name}", [
                'user_id' => $user->id,
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

    /**
     * Delete delegations
     *
     * @param Collection $delegations
     * @return void
     */
    protected function deleteDelegations(Collection $delegations): void
    {
        if ($delegations->isEmpty()) {
            return;
        }
        
        $systemUser = User::withFeatured()->where('featured', 1)->first();
        $systemUserId = $systemUser ? $systemUser->id : null;
        
        foreach ($delegations as $delegation) {
            $delegation->deleted = 1;
            $delegation->updated_by = $systemUserId;
            $delegation->save();
            
            // Trigger event for each delegation to ensure any related cascades happen
            event(new ModelChangedEvent($delegation, 'deleted'));
            
            Log::info("CascadeDeleteService: Delegation (ID: {$delegation->id}) marked as deleted");
        }
    }
}