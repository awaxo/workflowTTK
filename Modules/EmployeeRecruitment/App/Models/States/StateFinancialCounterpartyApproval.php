<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * Class StateFinancialCounterpartyApproval
 * Represents the state of a workflow when financial counterparty approval is required.
 * This class implements the IStateResponsibility interface to define the responsibilities
 * and transitions for this state.
 */
class StateFinancialCounterpartyApproval implements IStateResponsibility 
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
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        return $workgroup903 && $workgroup903->leader_id === $user->id;
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
        $service = new DelegationService();
        return $service->isDelegate($user, 'financial_counterparty_approver');
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
        $service = new DelegationService();
        $workgroup903 = Workgroup::where('workgroup_number', 903)->first();
        if (!$workgroup903) {
            return [];
        }
        $leader = $workgroup903->leader;

        $delegateUsers = $service->getDelegates($leader, 'financial_counterparty_approver');
        $responsibleUsers = array_merge([$leader], $delegateUsers->toArray());

        if ($notApprovedOnly) {
            $responsibleUsers = array_filter($responsibleUsers, function ($user) use ($workflow) {
                $user = User::find($user['id']);
                return !$workflow->isApprovedBy($user);
            });
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
        return 'to_obligee_approval';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $workgroup903 = Workgroup::where('deleted', 0)->where('workgroup_number', 903)->first();
        if ($workgroup903 && $workgroup903->leader_id === $user->id) {
            return [[
                'type' => 'financial_counterparty_approver',
                'readable_name' => 'Gazdasági igazgató'
            ]];
        }

        return [];
    }
}