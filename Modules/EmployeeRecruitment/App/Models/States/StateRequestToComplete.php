<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateRequestToComplete implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        return $workgroup908 && $workgroup908->labor_administrator === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'hr_labor_administrator');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_completed';
    }

    public function getDelegations(User $user): array {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        if ($workgroup908 && $workgroup908->labor_administrator === $user->id)
        {
            return ['hr_labor_administrator'];
        }
        return [];
    }
}