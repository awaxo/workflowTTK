<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateRegistration implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        return $user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto');
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();
        return $service->isDelegate($user, 'registrator');
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $users = User::role('munkaber_kotelezettsegvallalas_nyilvantarto')->get();
        $delegateUsers = collect();
        foreach ($users as $user) {
            $delegates = $service->getDelegates($user, 'registrator');
            $delegateUsers = $delegateUsers->concat($delegates);
        }

        $responsibleUsers = $users->concat($delegateUsers);

        if ($notApprovedOnly) {
            $responsibleUsers = $responsibleUsers->filter(function ($user) use ($workflow) {
                return !$workflow->isApprovedBy($user);
            });
        }

        return Helpers::arrayUniqueMulti($responsibleUsers->toArray(), 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_financial_counterparty_approval';
    }

    public function getDelegations(User $user): array {
        return $user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto') 
            ? [[
                'type' => 'registrator',
                'readable_name' => 'Kötelezettségvállalás nyilvántartás rögzítő'
            ]]
            : [];
    }
}