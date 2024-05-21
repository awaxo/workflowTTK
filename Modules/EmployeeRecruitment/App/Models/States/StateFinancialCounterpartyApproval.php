<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateFinancialCounterpartyApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        return $workgroup903 && $workgroup903->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'financial_counterparty_approver');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        if (!$workgroup903) {
            return [];
        }
        $leader = $workgroup903->leader;

        $delegateUsers = $service->getDelegates($leader, 'financial_counterparty_approver');
        $responsibleUsers = array_merge([$leader], $delegateUsers->toArray());

        if ($notApprovedOnly) {
            $responsibleUsers = array_filter($responsibleUsers, function ($user) use ($workflow) {
                $user = User::find($user['id']);
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers, 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_obligee_approval';
    }

    public function getDelegations(User $user): array {
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        if ($workgroup903 && $workgroup903->leader_id === $user->id) {
            return [[
                'type' => 'financial_counterparty_approver',
                'readable_name' => 'Gazdasági igazgató'
            ]];
        }

        return [];
    }
}