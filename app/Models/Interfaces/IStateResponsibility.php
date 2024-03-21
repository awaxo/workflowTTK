<?php

namespace App\Models\Interfaces;

use App\Models\User;

/**
 * Interface for defining state responsibility.
 */
interface IStateResponsibility {
    /**
     * Checks if a user is responsible for the state.
     *
     * @param User $user The user to check.
     * @return bool True if the user is responsible, false otherwise.
     */
    public function isUserResponsible(User $user): bool;
}