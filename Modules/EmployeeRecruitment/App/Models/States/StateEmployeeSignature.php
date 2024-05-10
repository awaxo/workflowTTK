<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateEmployeeSignature implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $initiator_group_level = $workflow->initiatorInstitute?->group_level;
            if ($initiator_group_level < 9) {
                return $user->hasRole('titkar_' . $initiator_group_level);
            } else {
                return $workflow->initiatorInstitute?->name === 'Főigazgatóság' ? $user->hasRole('titkar_9_fi') : $user->hasRole('titkar_9_gi');
            }
        } else {
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $initiator_group_level = $workflow->initiatorInstitute?->group_level;
            $service = new DelegationService();
            $role_to_check = '';
    
            if ($initiator_group_level < 9) {
                $role_to_check = 'secretary_' . $initiator_group_level;
            } else {
                $role_to_check = $workflow->initiatorInstitute?->name === 'Főigazgatóság' ? 'secretary_9_fi' : 'secretary_9_gi';
            }
    
            return $service->isDelegate($user, $role_to_check);
        } else {
            return false;
        }
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if ($workflow instanceof RecruitmentWorkflow) {
            $initiator_group_level = $workflow->initiatorInstitute?->group_level;
            $role_to_check = '';
            $service = new DelegationService();
    
            if ($initiator_group_level < 9) {
                $role_to_check = 'titkar_' . $initiator_group_level;
            } else {
                $role_to_check = $workflow->initiatorInstitute?->name === 'Főigazgatóság' ? 'titkar_9_fi' : 'titkar_9_gi';
            }
    
            // Get all users with the role
            $users = User::role($role_to_check)->get();
    
            // Get all delegate users
            $delegateUsers = collect();
            foreach ($users as $user) {
                $delegates = $service->getDelegates($user, str_replace('titkar_', 'secretary_', $role_to_check));
                $delegateUsers = $delegateUsers->concat($delegates);
            }
    
            $responsibleUsers = $users->concat($delegateUsers);
    
            if ($notApprovedOnly) {
                $responsibleUsers = $responsibleUsers->filter(function ($user) use ($workflow) {
                    return !$workflow->isApprovedBy($user);
                });
            }
    
            return Helpers::arrayUniqueMulti($responsibleUsers->toArray(), 'id');
        } else {
            return [];
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_request_to_complete';
    }

    public function getDelegations(User $user): array {
        $roles = $user->roles->pluck('name')->toArray();
        $delegations = [];
        
        for ($i = 1; $i <= 9; $i++) {
            if (in_array('titkar_' . $i, $roles)) {
                $delegations[] = ['type' => 'secretary_' . $i, 'readable_name' => 'Titkár (intézet: ' . $i . ')'];
            }
        }

        if (in_array('titkar_9_fi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_fi', 'readable_name' => 'Főigazgatósági titkárságvezető'];
        }
        if (in_array('titkar_9_gi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_gi', 'readable_name' => 'Gazdasági titkárságvezető'];
        }

        return empty($delegations) ? [] : [$delegations];
    }
}