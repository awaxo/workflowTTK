<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\CostCenter;
use App\Models\Delegation;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * Class StateSupervisorApproval
 * Represents the state of a recruitment workflow where supervisor approval is required.
 * This class implements the IStateResponsibility interface to define the responsibilities
 * and transitions for this state.
 */
class StateSupervisorApproval implements IStateResponsibility
{
    /**
     * Check if the user is responsible for approving the workflow.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

        $is_supervisor = 
            ($workflow->base_salary_cc1 && $workflow->base_salary_cc1->lead_user_id == $user->id) ||
            ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->lead_user_id == $user->id) ||
            ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->lead_user_id == $user->id) ||
            ($workflow->health_allowance_cc && $workflow->health_allowance_cc->lead_user_id == $user->id) ||
            ($workflow->management_allowance_cc && $workflow->management_allowance_cc->lead_user_id == $user->id) ||
            ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->lead_user_id == $user->id) ||
            ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->lead_user_id == $user->id);

        return $is_supervisor && !$workflow->isApprovedBy($user);
    }

    /**
     * Check if the user is responsible for approving the workflow as a delegate.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

        $costCenters = [
            $workflow->base_salary_cc1,
            $workflow->base_salary_cc2,
            $workflow->base_salary_cc3,
            $workflow->health_allowance_cc,
            $workflow->management_allowance_cc,
            $workflow->extra_pay_1_cc,
            $workflow->extra_pay_2_cc
        ];
        
        $workgroups = [];
        foreach ($costCenters as $costCenter) {
            if ($costCenter) {
                $code = substr($costCenter->cost_center_code, -3);
                $workgroups[] = 'supervisor_workgroup_' . $code;
            }
        }
        $workgroups = array_unique($workgroups);

        $service = new DelegationService();
        $isDelegate = false;
        foreach ($workgroups as $workgroup) {
            if ($service->isDelegate($user, $workgroup)) {
                $isDelegate = true;
                break;
            }
        }

        return $isDelegate && !$workflow->isApprovedBy($user);
    }

    /**
     * Get the responsible users for the workflow's current state.
     *
     * @param IGenericWorkflow $workflow
     * @param bool $notApprovedOnly
     * @return array
     */
    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return [];
        }

        $costCenters = [
            $workflow->base_salary_cc1,
            $workflow->base_salary_cc2,
            $workflow->base_salary_cc3,
            $workflow->health_allowance_cc,
            $workflow->management_allowance_cc,
            $workflow->extra_pay_1_cc,
            $workflow->extra_pay_2_cc
        ];

        $responsibleUsers = collect();
        $service = new DelegationService();

        foreach ($costCenters as $costCenter) {
            if ($costCenter && $costCenter->lead_user_id) {
                $user = User::find($costCenter->lead_user_id);
                if ($user) {
                    $responsibleUsers->push($user);
                }

                // Get delegate users
                $workgroup = 'supervisor_workgroup_' . substr($costCenter->cost_center_code, -3);
                $delegates = $service->getDelegates($user, $workgroup);
                $responsibleUsers = $responsibleUsers->concat($delegates);
            }
        }

        if ($notApprovedOnly) {
            $responsibleUsers = $responsibleUsers->filter(function ($user) use ($workflow) {
                $user = User::find($user['id']);
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers->toArray(), 'id');
    }

    /**
     * Check if all required approvals have been obtained for the workflow in the given state.
     *
     * @param IGenericWorkflow $workflow
     * @param int|null $userId
     * @return bool
     */
    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

        $userId = $userId ?: Auth::id();

        $metaData = json_decode($workflow->meta_data, true);

        $approval_user_ids = $metaData['approvals'][$workflow->state]['approval_user_ids'] ?? [];
        if (!in_array($userId, $approval_user_ids)) {
            $approval_user_ids[] = $userId;
        }

        $metaData['approvals'][$workflow->state]['approval_user_ids'] = $approval_user_ids;
        $workflow->meta_data = json_encode($metaData);

        $cost_center_lead_user_ids = array_filter([
            optional($workflow->base_salary_cc1)->lead_user_id,
            optional($workflow->base_salary_cc2)->lead_user_id,
            optional($workflow->base_salary_cc3)->lead_user_id,
            optional($workflow->health_allowance_cc)->lead_user_id,
            optional($workflow->management_allowance_cc)->lead_user_id,
            optional($workflow->extra_pay_1_cc)->lead_user_id,
            optional($workflow->extra_pay_2_cc)->lead_user_id,
        ]);

        foreach ($cost_center_lead_user_ids as $key => $userId) {
            $delegation = Delegation::where('original_user_id', $userId)
                ->where('delegate_user_id', $userId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('deleted', 0)
                ->where('type', 'like', 'supervisor_workgroup_%') // Check for any supervisor delegations
                ->first();

            if ($delegation) {
                $cost_center_lead_user_ids[$key] = $userId;
            }
        }

        $cost_center_lead_user_ids = array_unique($cost_center_lead_user_ids);

        $workflow->updated_by = $userId;
        $workflow->save();

        return count(array_diff($cost_center_lead_user_ids, $approval_user_ids)) === 0;
    }

    /**
     * Get the next transition for the workflow in the current state.
     *
     * @param IGenericWorkflow $workflow
     * @return string
     */
    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_group_lead_approval';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $cost_center_codes = CostCenter::where('deleted', 0)->where('lead_user_id', $user->id)->pluck('cost_center_code')->toArray();
        $workgroup_numbers = array_map(function($code) {
            return substr($code, -3);
        }, $cost_center_codes);
        $distinct_workgroup_numbers = array_unique($workgroup_numbers);
        
        return array_map(function($number) {
            return [[
                'type' => 'supervisor_workgroup_' . $number,
                'readable_name' => 'Témavezető (csoport: ' . $number . ')'
            ]];
        }, $distinct_workgroup_numbers);
    }
}