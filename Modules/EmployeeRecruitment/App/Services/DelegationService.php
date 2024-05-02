<?php

namespace Modules\EmployeeRecruitment\App\Services;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DelegationService
{
    /**
     * Get all delegations for the given user.
     */
    public function getAllDelegations(User $user)
    {
        $states = [
            'StateCompleted',
            'StateDirectorApproval',
            'StateDraftContractPending',
            'StateEmployeeSignature',
            'StateFinancialCounterpartyApproval',
            'StateFinancialCountersignApproval',
            'StateGroupLeadApproval',
            'StateHRLeadApproval',
            'StateITHeadApproval',
            'StateObligeeApproval',
            'StateObligeeSignature',
            'StatePostFinancingApproval',
            'StateProjectCoordinationLeadApproval',
            'StateProofOfCoverage',
            'StateRegistration',
            'StateRequestReview',
            'StateRequestToComplete',
            'StateSupervisorApproval',
            'StateSuspended'
        ];

        $delegations = [];
        foreach ($states as $state) {
            $stateClass = "Modules\\EmployeeRecruitment\\App\\Models\\States\\" . $state;

            if (class_exists($stateClass)) {
                $stateInstance = new $stateClass();
                $stateDelegations = $stateInstance->getDelegations($user);

                $delegations = array_merge($delegations, $stateDelegations);
            }
        }

        return $delegations;
    }

    /**
     * Check if the user is a delegate for the given delegation type.
     */
    public function isDelegate(User $user, string $delegationType)
    {
        return Delegation::where('delegate_user_id', $user->id)
            ->where('type', $delegationType)
            ->where(function ($query) {
                $query->where(function ($subquery) {
                    $subquery->whereNotNull('end_date')
                        ->whereDate('end_date', '>=', now());
                })->orWhere(function ($subquery) {
                    $subquery->whereNull('end_date')
                        ->whereDate('start_date', '<=', now());
                });
            })
            ->count() > 0;
    }
}