<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateRequestReview implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $role = null;

        $role = 'titkar_' . $workflow->initiatorInstitute->group_level;
        if ($workflow->initiatorInstitute->group_level == 9) {
            $createdBy = User::find($workflow->created_by);
            $role .= $createdBy->hasRole('titkar_foigazgatosag') ? '_fi' : '_gi';
        }

        if ($role) {
            if ($user->hasRole($role)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        $role = 'secretary_' . $workflow->initiatorInstitute->group_level;
    
        if ($workflow->initiatorInstitute->group_level == 9) {
            $createdBy = User::find($workflow->created_by);
            $role .= $createdBy->hasRole('titkar_foigazgatosag') ? '_fi' : '_gi';
        }
    
        if ($role) {
            $service = new DelegationService();
            return $service->isDelegate($user, $role);
        } else {
            return false;
        }
    }    

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_it_head_approval';
    }

    public function getDelegations(User $user): array {
        // returns empty because secretary_X delegations are already added in StateEmployeeSignature
        return [];
    }
}