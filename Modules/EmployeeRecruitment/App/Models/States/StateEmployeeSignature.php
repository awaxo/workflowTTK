<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
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

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_request_to_complete';
    }
}