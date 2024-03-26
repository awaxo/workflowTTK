<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateITHeadApproval implements IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     *
     * @param User $user The user to check.
     * @return bool True if the user is responsible, false otherwise.
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        return $user->can('approve_email_address');
    }
}