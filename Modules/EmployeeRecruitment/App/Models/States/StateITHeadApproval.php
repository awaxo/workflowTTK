<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateITHeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        return $workgroup915 && $workgroup915->leader === $user->id;
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_supervisor_approval';
    }
}