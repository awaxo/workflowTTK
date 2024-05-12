<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateItHeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        return $workgroup915 && $workgroup915->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'it_head');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        if (!$workgroup915) {
            return [];
        }
        $leader = $workgroup915->leader;

        $delegateUsers = $service->getDelegates($leader, 'it_head');
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
        return 'to_supervisor_approval';
    }

    public function getDelegations(User $user): array {
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        if ($workgroup915 && $workgroup915->leader_id === $user->id) {
            return [[
                'type' => 'it_head',
                'readable_name' => 'Informatikai osztályvezető'
            ]];
        }

        return [];
    }
}