<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateSuspended implements IStateResponsibility {
    private $stateClass = null;

    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workflow_meta = json_decode($workflow->meta_data);

        // get user by id of last entry in 'history' of meta_value
        $lastEntry = end($workflow_meta->history);
        $lastUser = User::find($lastEntry->user_id);

        if (!$lastUser->deleted && $lastUser->id === $user->id) {
            return true;
        } else {
            $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
            $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
            if (class_exists($stateClassName)) {
                $this->stateClass = new $stateClassName();
            }

            return $this->stateClass && $this->stateClass->isUserResponsible($user, $workflow);
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        if (!$this->stateClass) {
            $workflow_meta = json_decode($workflow->meta_data);
            $lastEntry = end($workflow_meta->history);
            $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
            $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
            
            if (class_exists($stateClassName)) {
                $this->stateClass = new $stateClassName();
            }
        }

        return $this->stateClass && $this->stateClass->isUserResponsibleAsDelegate($user, $workflow);
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $workflow_meta = json_decode($workflow->meta_data);

        // get user by id of last entry in 'history' of meta_value
        $lastEntry = end($workflow_meta->history);
        $lastUser = User::find($lastEntry->user_id);

        if (!$lastUser->deleted) {
            $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
            $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
            if (class_exists($stateClassName)) {
                $this->stateClass = new $stateClassName();
            }

            if ($this->stateClass) {
                return $this->stateClass->getResponsibleUsers($workflow, $notApprovedOnly);
            }
        }

        return [];
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        // next transition depends on from where we get suspended
        return '';
    }

    public function getDelegations(User $user): array {
        return [];
    }
}