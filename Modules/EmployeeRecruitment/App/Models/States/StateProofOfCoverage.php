<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class StateProofOfCoverage implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $is_project_coordinator = 
                ($workflow->base_salary_cc1 && $workflow->base_salary_cc1->project_coordinator_user_id == $user->id) ||
                ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->project_coordinator_user_id == $user->id) ||
                ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->project_coordinator_user_id == $user->id) ||
                ($workflow->health_allowance_cc && $workflow->health_allowance_cc->project_coordinator_user_id == $user->id) ||
                ($workflow->management_allowance_cc && $workflow->management_allowance_cc->project_coordinator_user_id == $user->id) ||
                ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->project_coordinator_user_id == $user->id) ||
                ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->project_coordinator_user_id == $user->id);

            $metaData = json_decode($workflow->meta_data, true);
            $already_approved_by_user = false;
            if (isset($metaData['approvals'][$workflow->state]['approval_user_ids']) && 
                in_array($user->id, $metaData['approvals'][$workflow->state]['approval_user_ids'])) {
                    $already_approved_by_user = true;
            }

            return $is_project_coordinator && !$already_approved_by_user;
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

            $cost_center_project_coordinator_ids = array_filter([
                optional($workflow->base_salary_cc1)->project_coordinator_user_id,
                optional($workflow->base_salary_cc2)->project_coordinator_user_id,
                optional($workflow->base_salary_cc3)->project_coordinator_user_id,
                optional($workflow->health_allowance_cc)->project_coordinator_user_id,
                optional($workflow->management_allowance_cc)->project_coordinator_user_id,
                optional($workflow->extra_pay_1_cc)->project_coordinator_user_id,
                optional($workflow->extra_pay_2_cc)->project_coordinator_user_id,
            ]);

            $workflow->updated_by = Auth::id();
            $workflow->save();

            return count(array_diff($cost_center_project_coordinator_ids, $approval_user_ids)) === 0;
        } else {
            return false;
        }
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        if ($workflow instanceof RecruitmentWorkflow) {
            if (($workflow->base_salary_cc1 && $workflow->base_salary_cc1->type && $workflow->base_salary_cc1->type->tender) ||
                ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->type && $workflow->base_salary_cc2->type->tender) ||
                ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->type && $workflow->base_salary_cc3->type->tender) ||
                ($workflow->health_allowance_cc && $workflow->health_allowance_cc->type && $workflow->health_allowance_cc->type->tender) ||
                ($workflow->management_allowance_cc && $workflow->management_allowance_cc->type && $workflow->management_allowance_cc->type->tender) ||
                ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->type && $workflow->extra_pay_1_cc->type->tender) ||
                ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->type && $workflow->extra_pay_2_cc->type->tender)) {
                return 'to_project_coordination_lead_approval';
            } else {
                $metaData = json_decode($workflow->meta_data, true);
                $postFinancedExists = false;

                if (isset($metaData['additional_fields']) && is_array($metaData['additional_fields'])) {
                    foreach ($metaData['additional_fields'] as $field) {
                        if (isset($field['post_financed_application']) && $field['post_financed_application'] === true) {
                            $postFinancedExists = true;
                            break;
                        }
                    }
                }

                if ($postFinancedExists) {
                    return 'to_post_financing_approval';
                } else {
                    return 'to_registration';
                }
            }
        }
    }
}