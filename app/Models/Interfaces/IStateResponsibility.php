<?php

namespace App\Models\Interfaces;

use App\Models\User;

/**
 * Interface IStateResponsibility
 *
 * This interface defines the methods required for managing state responsibilities in a workflow.
 * It includes methods to check user responsibilities, approvals, and transitions.
 */
interface IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool;

    /**
     * Checks if a user is responsible for the state as a delegate.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool;

    /**
     * Gets the responsible users for the state.
     *
     * @param IGenericWorkflow $workflow
     * @param bool $notApprovedOnly If true, only returns users who have not approved the workflow.
     * @return array
     */
    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array;
    
    /**
     * Checks if all required approvals are given
     *
     * @param IGenericWorkflow $workflow
     * @param User $user
     * @return bool
     */
    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool;

    /**
     * Gets the next transition for the workflow
     *
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function getNextTransition(IGenericWorkflow $workflow): string;

    /**
     * Gets the name of the delegations possible for the user
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array;
}