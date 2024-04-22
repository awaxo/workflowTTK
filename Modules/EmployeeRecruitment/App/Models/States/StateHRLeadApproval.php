<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateHRLeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        return $workgroup908 && $workgroup908->leader_id === $user->id;
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_proof_of_coverage';
    }
}