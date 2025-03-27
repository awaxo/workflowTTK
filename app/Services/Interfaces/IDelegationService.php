<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface IDelegationService
{
    /**
     * Get all delegations for the given user.
     * 
     * @param User $user
     * @return array
     */
    public function getAllDelegations(User $user): array;

    /**
     * Check if the user is a delegate for the given delegation type.
     * 
     * @param User $user
     * @param string $delegationType
     * @return bool
     */
    public function isDelegate(User $user, string $delegationType): bool;

    /**
     * Get delegate users for the given user and delegation type.
     * 
     * @param User $user
     * @param string $delegationType
     * @return Collection|null
     */
    public function getDelegates(User $user, string $delegationType): ?Collection;
}