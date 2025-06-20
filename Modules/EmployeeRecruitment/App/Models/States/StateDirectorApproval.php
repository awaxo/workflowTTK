<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Delegation;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * StateDirectorApproval is a class that implements the IStateResponsibility interface.
 * It defines the responsibilities and transitions for the director approval state in a recruitment workflow.
 * This class checks if a user is responsible for approving a recruitment workflow based on their role as a director
 * and their associated workgroup.
 */
class StateDirectorApproval implements IStateResponsibility
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

        $cost_center_codes = array_filter([
            optional($workflow->base_salary_cc1)->cost_center_code,
            optional($workflow->base_salary_cc2)->cost_center_code,
            optional($workflow->base_salary_cc3)->cost_center_code,
            optional($workflow->health_allowance_cc)->cost_center_code,
            optional($workflow->management_allowance_cc)->cost_center_code,
            optional($workflow->extra_pay_1_cc)->cost_center_code,
            optional($workflow->extra_pay_2_cc)->cost_center_code,
        ]);

        // Check if user is a director
        $director = false;
        foreach ($cost_center_codes as $cost_center_code) {
            $workgroup_number = substr($cost_center_code, -3);

            // Determine the director based on the workgroup number
            if (in_array(substr($workgroup_number, 0, 1), ['1', '3', '4', '5', '6', '7', '8'])) {
                $workgroup = Workgroup::where('workgroup_number', substr($workgroup_number, 0, 1) . '00')->first();
                if ($workgroup && $workgroup->leader_id == $user->id) {
                    $director = true;
                    break;
                }
            } elseif (in_array($workgroup_number, ['900', '901', '905', '908'])) {
                $workgroup = Workgroup::where('workgroup_number', '901')->first();
                if ($workgroup && $workgroup->leader_id == $user->id) {
                    $director = true;
                    break;
                }
            } elseif (in_array($workgroup_number, ['903', '907', '910', '911', '912', '914', '915'])) {
                $workgroup = Workgroup::where('workgroup_number', '903')->first();
                if ($workgroup && $workgroup->leader_id == $user->id) {
                    $director = true;
                    break;
                }
            }
        }

        return $director && !$workflow->isApprovedBy($user);
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

        $cost_center_codes = array_filter([
            optional($workflow->base_salary_cc1)->cost_center_code,
            optional($workflow->base_salary_cc2)->cost_center_code,
            optional($workflow->base_salary_cc3)->cost_center_code,
            optional($workflow->health_allowance_cc)->cost_center_code,
            optional($workflow->management_allowance_cc)->cost_center_code,
            optional($workflow->extra_pay_1_cc)->cost_center_code,
            optional($workflow->extra_pay_2_cc)->cost_center_code,
        ]);

        $delegated = false;
        $service = new DelegationService();

        foreach ($cost_center_codes as $cost_center_code) {
            $workgroup_number = substr($cost_center_code, -3);

            if (in_array(substr($workgroup_number, 0, 1), ['1', '3', '4', '5', '6', '7', '8'])) {
                $workgroup_id = substr($workgroup_number, 0, 1) . '00';
                if ($service->isDelegate($user, 'director_' . $workgroup_id)) {
                    $delegated = true;
                    break;
                }
            } elseif (in_array($workgroup_number, ['900', '901', '905', '908'])) {
                if ($service->isDelegate($user, 'director_901')) {
                    $delegated = true;
                    break;
                }
            } elseif (in_array($workgroup_number, ['903', '907', '910', '911', '912', '914', '915'])) {
                if ($service->isDelegate($user, 'director_903')) {
                    $delegated = true;
                    break;
                }
            }
        }

        return $delegated && !$workflow->isApprovedBy($user);
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

        $cost_center_codes = array_filter([
            optional($workflow->base_salary_cc1)->cost_center_code,
            optional($workflow->base_salary_cc2)->cost_center_code,
            optional($workflow->base_salary_cc3)->cost_center_code,
            optional($workflow->health_allowance_cc)->cost_center_code,
            optional($workflow->management_allowance_cc)->cost_center_code,
            optional($workflow->extra_pay_1_cc)->cost_center_code,
            optional($workflow->extra_pay_2_cc)->cost_center_code,
        ]);

        // Get the workgroup numbers
        $workgroup_numbers = [];
        foreach ($cost_center_codes as $cost_center_code) {
            $workgroup_number = substr($cost_center_code, -3);
            if (in_array(substr($workgroup_number, 0, 1), ['1', '3', '4', '5', '6', '7', '8'])) {
                $workgroup_numbers[] = substr($workgroup_number, 0, 1) . '00';
            } elseif (in_array($workgroup_number, ['900', '901', '905', '908'])) {
                $workgroup_numbers[] = '901';
            } elseif (in_array($workgroup_number, ['903', '907', '910', '911', '912', '914', '915'])) {
                $workgroup_numbers[] = '903';
            }
        }

        // Get the leaders of the workgroups
        $workgroups = Workgroup::whereIn('workgroup_number', $workgroup_numbers)->get();
        $responsibleUsers = [];
        $service = new DelegationService();
        foreach ($workgroups as $workgroup) {
            $user = $workgroup->leader;
            if ($user && (!$notApprovedOnly || !$workflow->isApprovedBy($user))) {
                $responsibleUsers[] = $user;
            }

            // Check for delegates
            $delegates = $service->getDelegates($user, 'director_' . $workgroup->workgroup_number);
            foreach ($delegates as $delegate) {
                if (!$notApprovedOnly || !$workflow->isApprovedBy($delegate)) {
                    $responsibleUsers[] = $delegate;
                }
            }
        }

        return Helpers::arrayUniqueMulti($responsibleUsers, 'id');
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

        $cost_center_codes = array_filter([
            optional($workflow->base_salary_cc1)->cost_center_code,
            optional($workflow->base_salary_cc2)->cost_center_code,
            optional($workflow->base_salary_cc3)->cost_center_code,
            optional($workflow->health_allowance_cc)->cost_center_code,
            optional($workflow->management_allowance_cc)->cost_center_code,
            optional($workflow->extra_pay_1_cc)->cost_center_code,
            optional($workflow->extra_pay_2_cc)->cost_center_code,
        ]);
        $director_ids = [];
        foreach ($cost_center_codes as $cost_center_code) {
            $workgroup_number = substr($cost_center_code, -3);

            if (in_array($workgroup_number, ['100', '300', '400', '500', '600', '700', '800'])) {
                $workgroup = Workgroup::where('workgroup_number', substr($workgroup_number, 0, 1) . '00')->first();
                if ($workgroup) {
                    $director_ids[] = $workgroup->leader_id;
                }
            } elseif (in_array($workgroup_number, ['900', '901', '905', '908'])) {
                $workgroup = Workgroup::where('workgroup_number', '901')->first();
                if ($workgroup) {
                    $director_ids[] = $workgroup->leader_id;
                }
            } elseif (in_array($workgroup_number, ['903', '907', '910', '911', '912', '914', '915'])) {
                $workgroup = Workgroup::where('workgroup_number', '903')->first();
                if ($workgroup) {
                    $director_ids[] = $workgroup->leader_id;
                }
            }
        }
        $director_ids = array_filter($director_ids);

        foreach ($director_ids as $key => $userId) {
            $delegation = Delegation::where('original_user_id', $userId)
                ->where('delegate_user_id', $userId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('deleted', 0)
                ->where('type', 'like', 'director_%') // Check for any supervisor delegations
                ->first();

            if ($delegation) {
                $director_ids[$key] = $userId;
            }
        }

        $director_ids = array_unique($director_ids);

        $workflow->updated_by = $userId;
        $workflow->save();

        return count(array_diff($director_ids, $approval_user_ids)) === 0;
    }

    /**
     * Get the next transition for the workflow in the current state.
     *
     * @param IGenericWorkflow $workflow
     * @return string
     */
    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_hr_lead_approval';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $workgroups = Workgroup::where('deleted', 0)
            ->whereIn('workgroup_number', ['100', '300', '400', '500', '600', '700', '800', '901', '903'])->where('leader_id', $user->id)->get();
        if ($workgroups->count() > 0) {
            return $workgroups->map(function ($workgroup) {
                return [
                    'type' => 'director_' . $workgroup->workgroup_number,
                    'readable_name' => 'Intézeti igazgató (csoport: ' . $workgroup->workgroup_number . ')'
                ];
            })->toArray();
        }

        return [];
    }
}