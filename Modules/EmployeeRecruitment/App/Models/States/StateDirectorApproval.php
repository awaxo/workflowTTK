<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class StateDirectorApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
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
            
            // Check if user has already approved the workflow
            $metaData = json_decode($workflow->meta_data, true);
            $already_approved_by_user = false;
            if (isset($metaData['approvals'][$workflow->state]['approval_user_ids']) && 
                in_array($user->id, $metaData['approvals'][$workflow->state]['approval_user_ids'])) {
                    $already_approved_by_user = true;
            }

            return $director && !$already_approved_by_user;
        } else {
            return false;
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $metaData = json_decode($workflow->meta_data, true);

            $approval_user_ids = $metaData['approvals'][$workflow->state]['approval_user_ids'] ?? [];
            $approval_user_ids[] = Auth::id();

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

            $workflow->updated_by = Auth::id();
            $workflow->save();

            return count(array_diff($director_ids, $approval_user_ids)) === 0;
        } else {
            return false;
        }
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_hr_lead_approval';
    }
}