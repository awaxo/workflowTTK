<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class StateDraftContractPending implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            if ($workflow->workgroup2) {
                return $workflow->workgroup2->labor_administrator == Auth::id();
            } else {
                return $workflow->workgroup1->labor_administrator == Auth::id();
            }
        } else {
            return false;
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_financial_countersign_approval';
    }
}