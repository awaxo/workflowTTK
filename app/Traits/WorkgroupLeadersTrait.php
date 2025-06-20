<?php
namespace App\Traits;

use App\Models\Workgroup;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Trait WorkgroupLeadersTrait provides methods to manage and check workgroup leaders.
 * It includes functionality to retrieve all leader users based on workgroup numbers
 * and check if a user is a leader in the specified workgroups.
 */
trait WorkgroupLeadersTrait
{
    /**
     * Get all workgroup leader users based on the defined workgroup numbers.
     *
     * @return Collection
     */
    protected function getWorkgroupLeaderUsers(): Collection
    {
        return Workgroup::whereIn('workgroup_number', $this->getWorkgroupNumbers())
                        ->with('leader')
                        ->get()
                        ->pluck('leader')
                        ->filter(); // csak nem-null értékek
    }

    /**
     * Check if the given user is a leader in the specified workgroups.
     *
     * @param User $user
     * @return bool
     */
    protected function isWorkgroupLeader(User $user): bool
    {
        $leaderIds = Workgroup::whereIn('workgroup_number', $this->getWorkgroupNumbers())
                              ->pluck('leader_id')
                              ->toArray();

        return in_array($user->id, $leaderIds, true);
    }

    /**
     * Get the workgroup numbers that this trait will use to filter leaders.
     * This method should be implemented in the class using this trait.
     *
     * @return array
     */
    abstract protected function getWorkgroupNumbers(): array;
}
