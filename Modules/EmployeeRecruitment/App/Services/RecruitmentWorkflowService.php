<?php

namespace Modules\EmployeeRecruitment\App\Services;

use App\Models\User;
use App\Models\Workgroup;
use App\Models\CostCenter;

/**
 * RecruitmentWorkflowService
 *
 * This service provides methods to check if a user is a project coordinator or financing approver
 * based on their roles, workgroup memberships, and delegations.
 */
class RecruitmentWorkflowService
{
    /**
     * Check if the user is a project coordinator or project coordination leader in general
     *
     * @param User $user The user to check
     * @return bool
     */
    public function isProjectCoordinator(User $user)
    {
        // Ellenőrizzük, hogy a felhasználó bármely költséghely projektkoordinátora-e
        $isCoordinator = CostCenter::where('project_coordinator_user_id', $user->id)
            ->where('deleted', 0)
            ->exists();
        
        if ($isCoordinator) {
            return true;
        }

        // Ellenőrizzük, hogy a felhasználó a 911-es munkacsoport vezetője-e
        $isWorkgroupLeader = Workgroup::where('workgroup_number', 911)
            ->where('leader_id', $user->id)
            ->where('deleted', 0)
            ->exists();
        
        if ($isWorkgroupLeader) {
            return true;
        }

        // Ellenőrizzük, hogy a felhasználó rendelkezik-e helyettesítési joggal
        $delegationService = new DelegationService();
        $delegations = $delegationService->getAllDelegations($user);
        
        foreach ($delegations as $delegation) {
            if (isset($delegation['type'])) {
                // Ellenőrizzük a projekt koordinátor helyettesítést
                if (preg_match('/^project_coordinator_workgroup_\d+$/', $delegation['type'])) {
                    return true;
                }
                
                // Ellenőrizzük a 911-es munkacsoport vezető helyettesítést
                if ($delegation['type'] === 'project_coordination_lead') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if the user is a financing approver or registrator
     *
     * @param User $user The user to check
     * @return bool
     */
    public function isFinancingOrRegistrator(User $user)
    {
        // Ellenőrizzük, hogy a felhasználó rendelkezik-e a szükséges szerepkörökkel
        if ($user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto') || 
            $user->hasRole('utofinanszirozas_fedezetigazolo')) {
            return true;
        }

        // Ellenőrizzük, hogy a felhasználó a 910-es munkacsoport vezetője-e
        $isWorkgroupLeader = Workgroup::where('workgroup_number', 910)
            ->where('leader_id', $user->id)
            ->where('deleted', 0)
            ->exists();
        
        if ($isWorkgroupLeader) {
            return true;
        }

        // Ellenőrizzük, hogy a felhasználó helyettesítési joggal rendelkezik-e
        $delegationService = new DelegationService();
        $delegations = $delegationService->getAllDelegations($user);
        
        foreach ($delegations as $delegation) {
            if (isset($delegation['type'])) {
                // Ellenőrizzük a kötelezettségvállalás nyilvántartó helyettesítést
                if ($delegation['type'] === 'munkaber_kotelezettsegvallalas_nyilvantarto') {
                    return true;
                }
                
                // Ellenőrizzük az utófinanszírozás fedezetigazoló helyettesítést
                if ($delegation['type'] === 'utofinanszirozas_fedezetigazolo') {
                    return true;
                }
                
                // Ellenőrizzük a 910-es munkacsoport vezető helyettesítést
                if ($delegation['type'] === 'grouplead_910') {
                    return true;
                }
            }
        }

        return false;
    }
}