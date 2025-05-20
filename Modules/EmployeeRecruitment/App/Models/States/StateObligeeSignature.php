<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateObligeeSignature implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        return $user->hasRole('titkar_9_fi');
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'obligee_signer');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $users = User::role('titkar_9_fi')->get();
        $delegateUsers = collect();
        foreach ($users as $user) {
            $delegates = $service->getDelegates($user, 'obligee_signer');
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
    }

    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_employee_signature';
    }

    public function getDelegations(User $user): array
    {
        return $user->hasRole('titkar_9_fi')
            ? [[
                'type' => 'obligee_signer',
                'readable_name' => 'Kötelezettségvállaló'
            ]]
            : [];
    }
}