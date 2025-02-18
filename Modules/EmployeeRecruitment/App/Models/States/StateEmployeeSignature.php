<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateEmployeeSignature implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $role_to_check = $workflow->createdBy->roles->first()->name;
            return $user->hasRole($role_to_check);
        } else {
            Log::error('StateEmployeeSignature::isUserResponsible called with invalid workflow type');
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $service = new DelegationService();
            $role_to_check = $workflow->createdBy->roles->first()->name;
    
            return $service->isDelegate($user, $role_to_check);
        } else {
            Log::error('StateEmployeeSignature::isUserResponsibleAsDelegate called with invalid workflow type');
            return false;
        }
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if ($workflow instanceof RecruitmentWorkflow) {
            $service = new DelegationService();

            $role_to_check = $workflow->createdBy->roles->first()->name;
            $users = User::role($role_to_check)->get();

            // Get all delegate users
            $delegateUsers = collect();
            foreach ($users as $user) {
                $delegates = $service->getDelegates($user, str_replace('titkar_', 'secretary_', $role_to_check));
                $delegateUsers = $delegateUsers->concat($delegates);
            }

            $responsibleUsers = $users->concat($delegateUsers);
            if ($notApprovedOnly) {
                $responsibleUsers = $responsibleUsers->filter(function ($user) use ($workflow) {
                    $user = User::find($user['id']);

                    return !$workflow->isApprovedBy($user);
                });
            }
    
            return Helpers::arrayUniqueMulti($responsibleUsers->toArray(), 'id');
        } else {
            Log::error('StateEmployeeSignature::getResponsibleUsers called with invalid workflow type');
            return [];
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_registration';
    }

    public function getDelegations(User $user): array {
        $roles = $user->roles->pluck('name')->toArray();
        $delegations = [];
        
        for ($i = 1; $i <= 9; $i++) {
            if (in_array('titkar_' . $i, $roles)) {
                $delegations[] = ['type' => 'secretary_' . $i, 'readable_name' => trans('auth.roles.' . 'titkar_' . $i)];
            }
        }

        if (in_array('titkar_9_fi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_fi', 'readable_name' => trans('auth.roles.' . 'titkar_9_fi')];
        }
        if (in_array('titkar_9_gi', $roles)) {
            $delegations[] = ['type' => 'secretary_9_gi', 'readable_name' => trans('auth.roles.' . 'titkar_9_gi')];
        }

        return empty($delegations) ? [] : [$delegations];
    }
}