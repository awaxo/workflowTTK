<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateRequestReview implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $role = null;

        $role = 'titkar_' . $workflow->initiatorInstitute->group_level;
        if ($workflow->initiatorInstitute->group_level == 9) {
            $createdBy = User::find($workflow->created_by);
            $role .= $createdBy->hasRole('titkar_foigazgatosag') ? '_fi' : '_gi';
        }

        if ($role) {
            if ($user->hasRole($role)) {
                return true;
            } else {
                return false;
            }
        } else {
            Log::error('StateRequestReview::isUserResponsible role is missing');
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        $role = 'secretary_' . $workflow->initiatorInstitute->group_level;
    
        if ($workflow->initiatorInstitute->group_level == 9) {
            $createdBy = User::find($workflow->created_by);
            $role .= $createdBy->hasRole('titkar_foigazgatosag') ? '_fi' : '_gi';
        }
    
        if ($role) {
            $service = new DelegationService();
            return $service->isDelegate($user, $role);
        } else {
            Log::error('StateRequestReview::isUserResponsibleAsDelegate role is missing');
            return false;
        }
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $role = 'titkar_' . $workflow->initiatorInstitute->group_level;

        if ($workflow->initiatorInstitute->group_level == 9) {
            $createdBy = User::find($workflow->created_by);
            $role .= $createdBy->hasRole('titkar_foigazgatosag') ? '_fi' : '_gi';
        }

        $usersWithRole = User::role($role)->get();
        $service = new DelegationService();
        $delegateUsers = collect();

        foreach ($usersWithRole as $user) {
            $delegates = $service->getDelegates($user, str_replace('titkar_', 'secretary_', $role));
            $delegateUsers = $delegateUsers->concat($delegates);
        }

        $responsibleUsers = $usersWithRole->concat($delegateUsers);

        if ($notApprovedOnly) {
            $responsibleUsers = $responsibleUsers->filter(function ($user) use ($workflow) {
                $user = User::find($user['id']);
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers->toArray(), 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_it_head_approval';
    }

    public function getDelegations(User $user): array {
        // returns empty because secretary_X delegations are already added in StateEmployeeSignature
        return [];
    }
}