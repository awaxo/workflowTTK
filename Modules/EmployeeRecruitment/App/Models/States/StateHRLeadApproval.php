<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateHRLeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        return $workgroup908 && $workgroup908->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'hr_head');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        if (!$workgroup908) {
            return [];
        }
        $leader = $workgroup908->leader;

        $delegateUsers = $service->getDelegates($leader, 'hr_head');
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
        return 'to_proof_of_coverage';
    }

    public function getDelegations(User $user): array {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        if ($workgroup908 && $workgroup908->leader_id === $user->id) {
            return [[
                'type' => 'hr_head',
                'readable_name' => 'Humámpolitikai osztályvezető'
            ]];
        }
        
        return [];
    }
}