<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;

class StateRequestReview implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        $role = null;

        switch ($workflow->initiatorInstitute->group_level) {
            case 1:
                $role = 'titkar_szki';
                break;
            case 3:
                $role = 'titkar_aki';
                break;
            case 4:
                $role = 'titkar_ei';
                break;
            case 5:
                $role = 'titkar_kpi';
                break;
            case 6:
                $role = 'titkar_akk';
                break;
            case 7:
                $role = 'titkar_szkk';
                break;
            case 8:
                $role = 'titkar_gyfl';
                break;
            case 9:
                $role = 'titkar_foigazgatosag';
                break;
        }

        if ($role) {
            if ($user->hasRole($role)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }
}