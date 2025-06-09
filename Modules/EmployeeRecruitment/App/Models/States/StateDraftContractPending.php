<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use App\Traits\WorkgroupLeadersTrait;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/* * Class StateDraftContractPending
 * Represents the state of a recruitment workflow when the draft contract is pending.
 * This class implements the IStateResponsibility interface to define the responsibilities
 * and transitions for this state.
 */
class StateDraftContractPending implements IStateResponsibility
{
    use WorkgroupLeadersTrait;

    protected function getWorkgroupNumbers(): array
    {
        return [908];
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
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error(__METHOD__ . ' invalid workflow type');
            return false;
        }

        if ($workflow->workgroup2) {
            $isAdmin = $workflow->workgroup2->labor_administrator == $user->id;
        } else {
            $isAdmin = $workflow->workgroup1->labor_administrator == $user->id;
        }

        $isLeader = $this->isWorkgroupLeader($user);

        return ($isAdmin || $isLeader)
            && ! $workflow->isApprovedBy($user);
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

        $service = new DelegationService();

        $type = $workflow->workgroup2
            ? 'draft_contract_labor_administrator_' . $workflow->workgroup2->workgroup_number
            : 'draft_contract_labor_administrator_' . $workflow->workgroup1->workgroup_number;

        $delegated = $service->isDelegate($user, $type);

        if (! $delegated) {
            $delegated = $service->isDelegate($user, 'grouplead_908');
        }

        return $delegated && ! $workflow->isApprovedBy($user);
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

        $service = new DelegationService();

        $adminId = $workflow->workgroup2
            ? $workflow->workgroup2->labor_administrator
            : $workflow->workgroup1->labor_administrator;
        $admin = User::find($adminId);

        $type = $workflow->workgroup2
            ? 'draft_contract_labor_administrator_' . $workflow->workgroup2->workgroup_number
            : 'draft_contract_labor_administrator_' . $workflow->workgroup1->workgroup_number;
        $delegates = $service->getDelegates($admin, $type);

        $leaders = $this->getWorkgroupLeaderUsers();

        $leaderDelegates = collect();
        foreach ($leaders as $leader) {
            $leaderDelegates = $leaderDelegates->concat(
                $service->getDelegates($leader, 'grouplead_908')
            );
        }

        $responsible = collect([$admin])->concat($delegates)->concat($leaders)->concat($leaderDelegates);

        if ($notApprovedOnly) {
            $responsible = $responsible->filter(function ($item) use ($workflow) {
                $usr = $item instanceof User ? $item : User::find($item['id']);
                return ! $workflow->isApprovedBy($usr);
            });
        }

        return Helpers::arrayUniqueMulti(
            $responsible->toArray(),
            'id'
        );
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
        return true;
    }

    /**
     * Get the next transition for the workflow in the current state.
     *
     * @param IGenericWorkflow $workflow
     * @return string
     */
    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_financial_countersign_approval';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $delegations = [];

        $workgroups = Workgroup::where('deleted', 0)
            ->where('labor_administrator', $user->id)
            ->get();

        if ($workgroups->count() > 0) {
            foreach ($workgroups as $workgroup) {
                $delegations[] = [
                    'type' => 'draft_contract_labor_administrator_' . $workgroup->workgroup_number,
                    'readable_name' => 'Munkaügyi ügyintéző'
                ];
            }
        }

        if ($this->isWorkgroupLeader($user)) {
            $delegations[] = [
                'type' => 'grouplead_908',
                'readable_name' => 'Humánpolitikai osztályvezető'
            ];
        }

        return $delegations;
    }
}