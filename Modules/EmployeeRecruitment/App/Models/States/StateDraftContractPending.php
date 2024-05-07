<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateDraftContractPending implements IStateResponsibility {
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            if ($workflow->workgroup2) {
                return $workflow->workgroup2->labor_administrator == Auth::id();
            } else {
                return $workflow->workgroup1->labor_administrator == Auth::id();
            }
        } else {
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $service = new DelegationService();
    
            if ($workflow->workgroup2) {
                return $service->isDelegate($user, 'draft_contract_labor_administrator_' . $workflow->workgroup2->id);
            } else if ($workflow->workgroup1) {
                return $service->isDelegate($user, 'draft_contract_labor_administrator_' . $workflow->workgroup1->id);
            }    
        } else {
            return false;
        }
    }
    

    public function isAllApproved(IGenericWorkflow $workflow): bool {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string {
        return 'to_financial_countersign_approval';
    }

    public function getDelegations(User $user): array {
        $workgroups = Workgroup::where('labor_administrator', $user->id)->get();
        if ($workgroups->count() > 0) {
            return $workgroups->map(function ($workgroup) {
                return [
                    'type' => 'draft_contract_labor_administrator_' . $workgroup->workgroup_number,
                    'readable_name' => 'Munkaügyi ügyintéző (intézet: ' . substr($workgroup->workgroup_number, 0, 1) . ')'
                ];
            })->toArray();
        }

        return [];
    }
}