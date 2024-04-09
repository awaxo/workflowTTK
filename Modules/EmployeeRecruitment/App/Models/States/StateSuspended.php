<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateSuspended implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workflow_meta = json_decode($workflow->meta_data);

        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $workflow_meta->suspend->source_state)));
        $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
        if (class_exists($stateClassName)) {
            $stateClass = new $stateClassName();
        }

        return $stateClass && $stateClass->isUserResponsible($user, $workflow);
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        // next transition depends on from where we get suspended
        return '';
    }
}