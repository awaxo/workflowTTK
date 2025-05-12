<?php
namespace App\Traits;

use App\Models\Workgroup;
use App\Models\User;
use Illuminate\Support\Collection;

trait WorkgroupLeadersTrait
{
    /**
     * Visszaadja az összes leader User modellt a megadott workgroup_number tömb alapján.
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
     * Megnézi, hogy az adott $user benne van-e a workgroup_number listából jövő vezetők között.
     */
    protected function isWorkgroupLeader(User $user): bool
    {
        $leaderIds = Workgroup::whereIn('workgroup_number', $this->getWorkgroupNumbers())
                              ->pluck('leader_id')
                              ->toArray();

        return in_array($user->id, $leaderIds, true);
    }

    abstract protected function getWorkgroupNumbers(): array;
}
