<?php

namespace App\Services;

use App\Models\Delegation;
use App\Models\User;
use App\Services\Interfaces\IDelegationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractDelegationService implements IDelegationService
{
    /**
     * Get all delegations for the given user.
     * 
     * @param User $user
     * @return array
     */
    abstract public function getAllDelegations(User $user): array;

    /**
     * Check if the user is a delegate for the given delegation type.
     * 
     * @param User $user
     * @param string $delegationType
     * @return bool
     */
    public function isDelegate(User $user, string $delegationType): bool
    {
        if (!$user || empty($delegationType)) {
            Log::error('DelegationService::isDelegate called with invalid parameters');
            return false;
        }

        return Delegation::where('delegate_user_id', $user->id)
            ->where('type', $delegationType)
            ->where('deleted', 0)
            ->where(function ($query) {
                $query->where(function ($subquery) {
                    $subquery->whereNotNull('end_date')
                        ->whereDate('end_date', '>=', now());
                })->orWhere(function ($subquery) {
                    $subquery->whereNull('end_date')
                        ->whereDate('start_date', '<=', now());
                });
            })
            ->exists();
    }

    /**
     * Get delegate users for the given user and delegation type.
     * 
     * @param User $user
     * @param string $delegationType
     * @return Collection|null
     */
    public function getDelegates(User $user, string $delegationType): ?Collection
    {
        if (!$user || empty($delegationType)) {
            Log::error('DelegationService::getDelegates called with invalid parameters');
            return null;
        }

        $delegateUserIds = Delegation::where('original_user_id', $user->id)
            ->where('type', $delegationType)
            ->where('deleted', 0)
            ->where($this->getActiveDelegationsCondition())
            ->pluck('delegate_user_id');
        
        return empty($delegateUserIds) 
            ? new Collection() 
            : User::whereIn('id', $delegateUserIds)->get();
    }

    /**
     * Get the active delegations query condition.
     * 
     * @return callable
     */
    protected function getActiveDelegationsCondition(): callable
    {
        return function ($query) {
            $query->where(function ($subquery) {
                $subquery->whereNotNull('end_date')
                    ->whereDate('end_date', '>=', now());
            })->orWhere(function ($subquery) {
                $subquery->whereNull('end_date')
                    ->whereDate('start_date', '<=', now());
            });
        };
    }
}