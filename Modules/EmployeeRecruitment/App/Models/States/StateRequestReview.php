<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

/**
 * Class StateRequestReview
 * Represents the state of a workflow when a request review is required.
 * This class implements the IStateResponsibility interface to define the responsibilities
 * and transitions for this state.
 */
class StateRequestReview implements IStateResponsibility
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
        $role = 'titkar_' . $workflow->initiatorInstitute->group_level;
        
        if ($workflow->initiatorInstitute->group_level == 9) {
            $role .= '_' . strtolower($workflow->initiatorInstitute->abbreviation);
        }

        if ($role) {
            if ($user->hasRole($role)) {
                return true;
            } else {
                return false;
            }
        } else {
            Log::error('StateRequestReview::isUserResponsible role is missing');
            return false;
        }
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
        $role = 'secretary_' . $workflow->initiatorInstitute->group_level;

        if ($workflow->initiatorInstitute->group_level == 9) {
            $role .= '_' . strtolower($workflow->initiatorInstitute->abbreviation);
        }
    
        if ($role) {
            $service = new DelegationService();
            return $service->isDelegate($user, $role);
        } else {
            Log::error('StateRequestReview::isUserResponsibleAsDelegate role is missing');
            return false;
        }
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
        $role = 'titkar_' . $workflow->initiatorInstitute->group_level;

        if ($workflow->initiatorInstitute->group_level == 9) {
            $role .= '_' . strtolower($workflow->initiatorInstitute->abbreviation);
        }

        $usersWithRole = User::role($role)->get();
        $service = new DelegationService();
        $delegateUsers = collect();

        foreach ($usersWithRole as $user) {
            $delegates = $service->getDelegates($user, str_replace('titkar_', 'secretary_', $role));
            $delegateUsers = $delegateUsers->concat($delegates);
        }

        $responsibleUsers = $usersWithRole->concat($delegateUsers);

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
        return 'to_it_head_approval';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        // returns empty because secretary_X delegations are already added in StateEmployeeSignature
        return [];
    }
}