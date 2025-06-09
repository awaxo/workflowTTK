<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\CostCenter;
use App\Models\Delegation;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use App\Traits\WorkgroupLeadersTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * Class StateProofOfCoverage
 * Represents the state of a recruitment workflow where proof of coverage is required.
 * This class implements the IStateResponsibility interface to define the responsibilities
 * and transitions for this state.
 */
class StateProofOfCoverage implements IStateResponsibility
{
    use WorkgroupLeadersTrait;

    protected function getWorkgroupNumbers(): array
    {
        // 910: pénzügyi osztályvezető, 911: projektkoordinációs osztályvezető
        return [910, 911];
    }

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

        $is_project_coordinator = 
            ($workflow->base_salary_cc1 && $workflow->base_salary_cc1->project_coordinator_user_id == $user->id) ||
            ($workflow->base_salary_cc2 && $workflow->base_salary_cc2->project_coordinator_user_id == $user->id) ||
            ($workflow->base_salary_cc3 && $workflow->base_salary_cc3->project_coordinator_user_id == $user->id) ||
            ($workflow->health_allowance_cc && $workflow->health_allowance_cc->project_coordinator_user_id == $user->id) ||
            ($workflow->management_allowance_cc && $workflow->management_allowance_cc->project_coordinator_user_id == $user->id) ||
            ($workflow->extra_pay_1_cc && $workflow->extra_pay_1_cc->project_coordinator_user_id == $user->id) ||
            ($workflow->extra_pay_2_cc && $workflow->extra_pay_2_cc->project_coordinator_user_id == $user->id);

        $isLeader = $this->isWorkgroupLeader($user);

        return ($is_project_coordinator || $isLeader) && !$workflow->isApprovedBy($user);
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
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

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

        // Osztályvezetői delegációk ellenőrzése
        if (! $delegated) {
            if ($service->isDelegate($user, 'project_coordination_lead')
                || $service->isDelegate($user, 'grouplead_910')
            ) {
                $delegated = true;
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
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return [];
        }

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

        $leaders = $this->getWorkgroupLeaderUsers();
        $responsibleUsers = $responsibleUsers->concat($leaders);

        foreach ($leaders as $leader) {
            $type = null;

            // pénzügyi osztályvezető
            if (in_array(910, $this->getWorkgroupNumbers())
                && Workgroup::where('workgroup_number', 910)->value('leader_id') === $leader->id
            ) {
                $type = 'grouplead_910';
            }

            // projektkoordinációs osztályvezető
            if (in_array(911, $this->getWorkgroupNumbers())
                && Workgroup::where('workgroup_number', 911)->value('leader_id') === $leader->id
            ) {
                $type = 'project_coordination_lead';
            }
            if ($type) {
                $delegates = $service->getDelegates($leader, $type);
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
                ->where('delegate_user_id', $userId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('deleted', 0)
                ->where('type', 'like', 'project_coordinator_workgroup_%') // Check for any supervisor delegations
                ->first();

            if ($delegation) {
                $cost_center_project_coordinator_ids[$key] = $userId;
            }
        }

        $cost_center_project_coordinator_ids = array_unique($cost_center_project_coordinator_ids);

        $workflow->updated_by = $userId;
        $workflow->save();

        if (count(array_diff($cost_center_project_coordinator_ids, $approval_user_ids)) === 0) {
            return true;
        }

        // Ha a workgroup vezetők bármelyike jóváhagyta, az is elegendő
        $leaders = $this->getWorkgroupLeaderUsers();
        foreach ($leaders as $leader) {
            if ($workflow->isApprovedBy($leader)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the next transition for the workflow in the current state.
     *
     * @param IGenericWorkflow $workflow
     * @return string
     */
    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        if (!$workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

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
                return 'to_financial_counterparty_approval';
            }
        }
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $cost_center_codes = CostCenter::where('deleted', 0)
            ->where('project_coordinator_user_id', $user->id)
            ->pluck('cost_center_code')
            ->toArray();

        $workgroup_numbers = array_map(function ($code) {
            return substr($code, -3);
        }, $cost_center_codes);

        $distinct_workgroup_numbers = array_unique($workgroup_numbers);

        $delegations = [];
        foreach ($distinct_workgroup_numbers as $number) {
            $delegations[] = [
                'type' => 'project_coordinator_workgroup_' . $number,
                'readable_name' => 'Projektkoordinátor'
            ];
        }

        // Projektkoordinációs osztályvezető (911)
        $wg911 = Workgroup::where('deleted', 0)
            ->where('workgroup_number', 911)
            ->first();
        if ($wg911 && $wg911->leader_id === $user->id) {
            $delegations[] = [
                'type' => 'project_coordination_lead',
                'readable_name' => 'Projektkoordinációs osztályvezető'
            ];
        }

        // Pénzügyi osztályvezető (910)
        $wg910 = Workgroup::where('deleted', 0)
            ->where('workgroup_number', 910)
            ->first();
        if ($wg910 && $wg910->leader_id === $user->id) {
            $delegations[] = [
                'type' => 'grouplead_910',
                'readable_name' => 'Pénzügyi osztályvezető'
            ];
        }

        return $delegations;
    }
}