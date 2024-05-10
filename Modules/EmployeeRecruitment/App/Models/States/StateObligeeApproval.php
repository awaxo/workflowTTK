<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateObligeeApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup901 = Workgroup::where('workgroup_number', 901)->first();
        return $workgroup901 && $workgroup901->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'obligee_approver');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $workgroup901 = Workgroup::where('workgroup_number', 901)->first();
        if (!$workgroup901) {
            return [];
        }
        $leader = $workgroup901->leader;

        $delegateUsers = $service->getDelegates($leader, 'obligee_approver');
        $responsibleUsers = array_merge([$leader], $delegateUsers->toArray());

        if ($notApprovedOnly) {
            $responsibleUsers = array_filter($responsibleUsers, function ($user) use ($workflow) {
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers, 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_draft_contract_pending';
    }

    public function getDelegations(User $user): array {
        $workgroup901 = Workgroup::where('workgroup_number', 901)->first();
        if ($workgroup901 && $workgroup901->leader_id === $user->id) {
            return [[
                'type' => 'obligee_approver',
                'readable_name' => 'Főigazgató'
            ]];
        }

        return [];
    }
}