<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateSuspended implements IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     *
     * @param User $user The user to check.
     * @return bool True if the user is responsible, false otherwise.
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workflow_meta = json_decode($workflow->meta_data);

        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $workflow_meta->source_state)));
        $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
        if (class_exists($stateClassName)) {
            $stateClass = new $stateClassName();
        }

        return $stateClass && $stateClass->isUserResponsible($user, $workflow);
    }
}