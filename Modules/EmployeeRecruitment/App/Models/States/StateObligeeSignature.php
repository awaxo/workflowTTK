<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateObligeeSignature implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        return $user->hasRole('titkar_9_fi');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_employee_signature';
    }
}