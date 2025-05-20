<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateProjectCoordinationLeadApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();
        return $workgroup911 && $workgroup911->leader_id === $user->id;
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'project_coordination_lead');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();
        if (!$workgroup911) {
            return [];
        }
        $leader = $workgroup911->leader;

        $delegateUsers = $service->getDelegates($leader, 'project_coordination_lead');
        $responsibleUsers = array_merge([$leader], $delegateUsers->toArray());

        if ($notApprovedOnly) {
            $responsibleUsers = array_filter($responsibleUsers, function ($user) use ($workflow) {
                $user = User::find($user['id']);
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers, 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

        $metaData = json_decode($workflow->meta_data, true);
        $postFinancedExists = false;

        if (isset($metaData['additional_fields']) && is_array($metaData['additional_fields'])) {
            foreach ($metaData['additional_fields'] as $field) {
                if (isset($field['post_financed_application']) && $field['post_financed_application']) {
                    $postFinancedExists = true;
                    break;
                }
            }
        }

        if ($postFinancedExists) {
            return 'to_post_financing_approval';
        } else {
            return 'to_financial_counterparty_approval';
        }
    }

    public function getDelegations(User $user): array
    {
        $workgroup911 = Workgroup::where('deleted', 0)->where('workgroup_number', 911)->first();
        if ($workgroup911 && $workgroup911->leader_id === $user->id) {
            return [[
                'type' => 'project_coordination_lead',
                'readable_name' => 'Projektkoordinációs osztályvezető'
            ]];
        }

        return [];
    }
}