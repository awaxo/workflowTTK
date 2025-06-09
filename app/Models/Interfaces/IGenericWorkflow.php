<?php

namespace App\Models\Interfaces;

use App\Models\User;

/*
 * Interface IGenericWorkflow
 * This interface defines the methods that any generic workflow model should implement.
 * It includes methods to get the current state of the workflow and to check if a user has approved it.
 */
interface IGenericWorkflow {
    public function getCurrentState();
    public function isApprovedBy(User $user): bool;
}