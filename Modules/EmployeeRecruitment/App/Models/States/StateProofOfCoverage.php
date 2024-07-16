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

            return $is_project_coordinator && !$workflow->isApprovedBy($user);
        } else {
            Log::error('StateProofOfCoverage::isUserResponsible called with invalid workflow type');
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $cost_center_keys = [
                'base_salary_cc1', 'base_salary_cc2', 'base_salary_cc3',
                'health_allowance_cc', 'management_allowance_cc', 
                'extra_pay_1_cc', 'extra_pay_2_cc'
            ];
            
            $delegated = false;
            $service = new DelegationService();

            foreach ($cost_center_keys as $key) {
                $cc = $workflow->$key;
                if ($cc && $service->isDelegate($user, 'project_coordinator_workgroup_' . substr($workflow->$key->cost_center_code, -3))) {
                    $delegated = true;
                    break;
                }
            }
    
            return $delegated && !$workflow->isApprovedBy($user);
        } else {
            Log::error('StateProofOfCoverage::isUserResponsibleAsDelegate called with invalid workflow type');
            return false;
        }
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if ($workflow instanceof RecruitmentWorkflow) {
            $cost_center_keys = [
                'base_salary_cc1', 'base_salary_cc2', 'base_salary_cc3',
                'health_allowance_cc', 'management_allowance_cc', 
                'extra_pay_1_cc', 'extra_pay_2_cc'
            ];
    
            $service = new DelegationService();
            $responsibleUsers = collect();
    
            foreach ($cost_center_keys as $key) {
                $cc = $workflow->$key;
                if ($cc && $cc->project_coordinator_user_id) {
                    $user = User::find($cc->project_coordinator_user_id);
                    if ($user) {
                        $responsibleUsers->push($user);
                    }
    
                    // Get delegate users
                    $delegates = $service->getDelegates($user, 'project_coordinator_workgroup_' . substr($cc->cost_center_code, -3));
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
        } else {
            Log::error('StateProofOfCoverage::getResponsibleUsers called with invalid workflow type');
            return [];
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

            foreach ($cost_center_project_coordinator_ids as $key => $userId) {
                $delegation = Delegation::where('original_user_id', $userId)
                    ->where('delegate_user_id', Auth::id())
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('deleted', 0)
                    ->where('type', 'like', 'project_coordinator_workgroup_%') // Check for any supervisor delegations
                    ->first();

                if ($delegation) {
                    $cost_center_project_coordinator_ids[$key] = Auth::id();
                }
            }

            $cost_center_project_coordinator_ids = array_unique($cost_center_project_coordinator_ids);

            $workflow->updated_by = Auth::id();
            $workflow->save();

            return count(array_diff($cost_center_project_coordinator_ids, $approval_user_ids)) === 0;
        } else {
            Log::error('StateProofOfCoverage::isAllApproved called with invalid workflow type');
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
        else {
            Log::error('StateProofOfCoverage::getNextTransition called with invalid workflow type');
            return '';
        }
    }

    public function getDelegations(User $user): array {
        $cost_center_codes = CostCenter::where('deleted', 0)->where('project_coordinator_user_id', $user->id)->pluck('cost_center_code')->toArray();
        $workgroup_numbers = array_map(function($code) {
            return substr($code, -3);
        }, $cost_center_codes);
        $distinct_workgroup_numbers = array_unique($workgroup_numbers);
        
        return array_map(function($number) {
            return [[
                'type' => 'project_coordinator_workgroup_' . $number,
                'readable_name' => 'Projektkoordinátor (csoport: ' . $number . ')'
            ]];
        }, $distinct_workgroup_numbers);
    }
}