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
     * Checks if all the necessary approvals are given.
     */
    public function isAllApproved(IGenericWorkflow $workflow): bool;
}