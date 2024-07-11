<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            Log::error('StateDraftContractPending::isUserResponsible called with invalid workflow type');
            return false;
        }
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool {
        if ($workflow instanceof RecruitmentWorkflow) {
            $service = new DelegationService();
    
            if ($workflow->workgroup2) {
                return $service->isDelegate($user, 'draft_contract_labor_administrator_' . $workflow->workgroup2->workgroup_number);
            } else if ($workflow->workgroup1) {
                return $service->isDelegate($user, 'draft_contract_labor_administrator_' . $workflow->workgroup1->workgroup_number);
            }    
        } else {
            Log::error('StateDraftContractPending::isUserResponsibleAsDelegate called with invalid workflow type');
            return false;
        }
    }
    
    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if ($workflow instanceof RecruitmentWorkflow) {
            $service = new DelegationService();
    
            // Get the labor administrator for the workflow
            $laborAdminId = $workflow->workgroup2 ? $workflow->workgroup2->labor_administrator : $workflow->workgroup1->labor_administrator;
            $laborAdmin = User::find($laborAdminId);
    
            // Get the delegate users
            $delegateType = 'draft_contract_labor_administrator_' . ($workflow->workgroup2 ? $workflow->workgroup2->workgroup_number : $workflow->workgroup1->workgroup_number);
            $delegateUsers = $service->getDelegates($laborAdmin, $delegateType);
    
            $responsibleUsers = array_merge([$laborAdmin], $delegateUsers->toArray());
    
            if ($notApprovedOnly) {
                $responsibleUsers = array_filter($responsibleUsers, function ($user) use ($workflow) {
                    $user = User::find($user['id']);
                    return !$workflow->isApprovedBy($user);
                });
            }
    
            return Helpers::arrayUniqueMulti($responsibleUsers, 'id');
        } else {
            Log::error('StateDraftContractPending::getResponsibleUsers called with invalid workflow type');
            return [];
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
            $instituteAbbreviations = [
                1 => 'SZKI',
                3 => 'AKI',
                4 => 'MÉI',
                5 => 'KPI',
                6 => 'AKK',
                7 => 'SZKK',
                8 => 'GYIK',
            ];

            return $workgroups->map(function ($workgroup) {
                $abbreviation = isset($instituteAbbreviations[substr($workgroup->workgroup_number, 0, 1)]) ? $instituteAbbreviations[substr($workgroup->workgroup_number, 0, 1)] : substr($workgroup->workgroup_number, 0, 1);

                return [
                    'type' => 'draft_contract_labor_administrator_' . $workgroup->workgroup_number,
                    'readable_name' => 'Munkaügyi ügyintéző (intézet: ' . $abbreviation . ', csoport: ' . $workgroup->workgroup_number . ')'
                ];
            })->toArray();
        }

        return [];
    }
}