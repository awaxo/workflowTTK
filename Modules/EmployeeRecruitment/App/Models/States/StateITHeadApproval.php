<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Delegation;
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
        return $workgroup915 && $workgroup915->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        // TODO: ez általános függvény, kirakni egy közös helyre
        return Delegation::where('delegate_user_id', $user->id)
            ->where('type', 'it_head')
            ->where(function ($query) {
                $query->where(function ($subquery) {
                    $subquery->whereNotNull('end_date')
                        ->whereDate('end_date', '>=', now());
                })->orWhere(function ($subquery) {
                    $subquery->whereNull('end_date')
                        ->whereDate('start_date', '<=', now());
                });
            })
            ->count() > 0;
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_supervisor_approval';
    }

    public function getDelegations(User $user): array {
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        if ($workgroup915 && $workgroup915->leader_id === $user->id)
        {
            return ['it_head'];
        }
        return '';
    }
}