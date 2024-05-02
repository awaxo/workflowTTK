<?php

namespace Modules\EmployeeRecruitment\App\Services;

use App\Models\Delegation;
use App\Models\User;

class DelegationService
{
    /**
     * Get all delegations for the given user.
     */
    public function getAllDelegations(User $user)
    {
        return;
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