<?php

namespace App\Models\Interfaces;

use App\Models\User;

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
     * Gets the responsible users for the state.
     */
    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array;
    
    /**
     * Checks if all the necessary approvals are given.
     */
    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool;

    /**
     * Gets the next transition for the workflow.
     */
    public function getNextTransition(IGenericWorkflow $workflow): string;

    /**
     * Gets the name of the delegations possible for the user.
     */
    public function getDelegations(User $user): array;
}