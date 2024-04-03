<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/**
 * The state of the recruitment process when the IT head has to approve the recruitment.
 */
class StateSupervisorApproval implements IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     *
     * @param User $user The user to check.
     * @return bool True if the user is responsible, false otherwise.
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            return
                ($workflow->base_salary_cc1 && $workflow->base_salary_cc1->lead_user_id == $user->id) ||
                ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->lead_user_id == $user->id) ||
                ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->lead_user_id == $user->id) ||
                ($workflow->health_allowance_cc && $workflow->health_allowance_cc->lead_user_id == $user->id) ||
                ($workflow->management_allowance_cc && $workflow->management_allowance_cc->lead_user_id == $user->id) ||
                ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->lead_user_id == $user->id) ||
                ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->lead_user_id == $user->id);
        } else {
            return false;
        }
    }
}