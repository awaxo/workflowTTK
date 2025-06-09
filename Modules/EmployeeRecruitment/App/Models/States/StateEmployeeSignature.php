<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/*
 * Class StateEmployeeSignature
 * Represents the state of a workflow where an employee signature is required.
 * This class implements the IStateResponsibility interface to define
 * the responsibilities and transitions for this state.
 */
class StateEmployeeSignature implements IStateResponsibility
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

        $role_to_check = $workflow->createdBy->roles->first()->name;
        return $user->hasRole($role_to_check);
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

        $service = new DelegationService();
        $role_to_check = $workflow->createdBy->roles->first()->name;

        return $service->isDelegate($user, $role_to_check);
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

        $service = new DelegationService();

        $role_to_check = $workflow->createdBy->roles->first()->name;
        $users = User::role($role_to_check)->get();

        // Get all delegate users
        $delegateUsers = collect();
        foreach ($users as $user) {
            $delegates = $service->getDelegates($user, str_replace('titkar_', 'secretary_', $role_to_check));
            $delegateUsers = $delegateUsers->concat($delegates);
        }

        $responsibleUsers = $users->concat($delegateUsers);
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
        return 'to_registration';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        $roles = $user->roles->pluck('name')->toArray();
        $delegations = [];
        
        for ($i = 1; $i <= 9; $i++) {
            if (in_array('titkar_' . $i, $roles)) {
                $delegations[] = ['type' => 'secretary_' . $i, 'readable_name' => trans('auth.roles.' . 'titkar_' . $i)];
            }
        }

        if (in_array('titkar_9_fi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_fi', 'readable_name' => trans('auth.roles.' . 'titkar_9_fi')];
        }
        if (in_array('titkar_9_gi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_gi', 'readable_name' => trans('auth.roles.' . 'titkar_9_gi')];
        }

        return empty($delegations) ? [] : [$delegations];
    }
}