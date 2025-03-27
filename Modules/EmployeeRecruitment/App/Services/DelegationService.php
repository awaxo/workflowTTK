<?php

namespace Modules\EmployeeRecruitment\App\Services;

use App\Models\User;
use App\Services\AbstractDelegationService;
use Illuminate\Support\Facades\Log;

class DelegationService extends AbstractDelegationService
{
    /**
     * The state classes used in the Employee Recruitment module.
     * 
     * @var array
     */
    protected array $stateClasses = [
        'StateCompleted',
        'StateDirectorApproval',
        'StateDraftContractPending',
        'StateEmployeeSignature',
        'StateFinancialCounterpartyApproval',
        'StateFinancialCountersignApproval',
        'StateGroupLeadApproval',
        'StateHrLeadApproval',
        'StateItHeadApproval',
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

    /**
     * Get all delegations for the given user from all states in the Employee Recruitment module.
     * 
     * @param User $user
     * @return array
     */
    public function getAllDelegations(User $user): array
    {
        $delegations = [];
        $stateNamespace = "Modules\\EmployeeRecruitment\\App\\Models\\States\\";

        foreach ($this->stateClasses as $stateClass) {
            $fullyQualifiedStateClass = $stateNamespace . $stateClass;

            if (class_exists($fullyQualifiedStateClass)) {
                try {
                    $stateInstance = new $fullyQualifiedStateClass();
                    $stateDelegations = $stateInstance->getDelegations($user);

                    if (is_array($stateDelegations) && !empty($stateDelegations)) {
                        $delegations = array_merge($delegations, $stateDelegations);
                    }
                } catch (\Throwable $e) {
                    // Log the error but continue processing other states
                    Log::error(
                        "Error getting delegations from state {$stateClass}: " . $e->getMessage(),
                        ['exception' => $e]
                    );
                }
            }
        }

        return $delegations;
    }
}