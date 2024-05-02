<?php

namespace App\Models\Interfaces;

use App\Models\User;

interface IGenericWorkflow {
    public function getCurrentState();
    public function isApprovedBy(User $user): bool;
}