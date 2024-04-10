<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateObligeeApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup901 = Workgroup::where('workgroup_number', 901)->first();
        return $workgroup901 && $workgroup901->leader === $user->id;
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_draft_contract_pending';
    }
}