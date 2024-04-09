<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class StateSupervisorApproval implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $cc_given_for_user = 
                ($workflow->base_salary_cc1 && $workflow->base_salary_cc1->lead_user_id == $user->id) ||
                ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->lead_user_id == $user->id) ||
                ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->lead_user_id == $user->id) ||
                ($workflow->health_allowance_cc && $workflow->health_allowance_cc->lead_user_id == $user->id) ||
                ($workflow->management_allowance_cc && $workflow->management_allowance_cc->lead_user_id == $user->id) ||
                ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->lead_user_id == $user->id) ||
                ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->lead_user_id == $user->id);

            $metaData = json_decode($workflow->meta_data, true);
            $already_approved_by_user = false;
            if (isset($metaData['supervisor_approval']['approval_user_ids']) && 
                in_array($user->id, $metaData['supervisor_approval']['approval_user_ids'])) {
                    $already_approved_by_user = true;
            }

            return $cc_given_for_user && !$already_approved_by_user;
        } else {
            return false;
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $metaData = json_decode($workflow->meta_data, true);

            $approval_user_ids = $metaData['supervisor_approval']['approval_user_ids'] ?? [];
            $approval_user_ids[] = Auth::id();

            $metaData['supervisor_approval']['approval_user_ids'] = $approval_user_ids;
            $workflow->meta_data = json_encode($metaData);

            $cost_center_user_ids = array_filter([
                optional($workflow->base_salary_cc1)->lead_user_id,
                optional($workflow->base_salary_cc2)->lead_user_id,
                optional($workflow->base_salary_cc3)->lead_user_id,
                optional($workflow->health_allowance_cc)->lead_user_id,
                optional($workflow->management_allowance_cc)->lead_user_id,
                optional($workflow->extra_pay_1_cc)->lead_user_id,
                optional($workflow->extra_pay_2_cc)->lead_user_id,
            ]);

            $workflow->updated_by = Auth::id();
            $workflow->save();

            return count(array_diff($cost_center_user_ids, $approval_user_ids)) === 0;
        } else {
            return false;
        }
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_group_lead_approval';
    }
}