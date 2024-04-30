<?php

namespace App\Models\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Interface for defining state responsibility.
 */
interface IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool;

    /**
     * Checks if a user is responsible for the state as a delegate.
     */
    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool;
    
    /**
     * Checks if all the necessary approvals are given.
     */
    public function isAllApproved(IGenericWorkflow $workflow): bool;

    /**
     * Gets the next transition for the workflow.
     */
    public function getNextTransition(IGenericWorkflow $workflow): string;

    /**
     * Gets the name of the delegations possible for the user.
     */
    public function getDelegations(User $user): array;
}