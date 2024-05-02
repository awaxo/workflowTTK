<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateFinancialCountersignApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        return $user->hasRole('titkar_9_gi');
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'financial_countersign_approver');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_obligee_signature';
    }

    public function getDelegations(User $user): array {
        return $user->hasRole('titkar_9_gi') ? ['financial_countersign_approver'] : [];
    }
}