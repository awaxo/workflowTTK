<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

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