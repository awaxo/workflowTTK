<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateRejected implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        return false;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        return false;
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        return [];
    }

    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return false;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return '';
    }

    public function getDelegations(User $user): array
    {
        return [];
    }
}