<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Models\Workgroup;
use App\Traits\WorkgroupLeadersTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateRequestToComplete implements IStateResponsibility
{
    use WorkgroupLeadersTrait;

    protected function getWorkgroupNumbers(): array
    {
        return [910];
    }

    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error('StateRequestToComplete::isUserResponsible invalid workflow type');
            return false;
        }

        if ($workflow->workgroup2) {
            $isAdmin = $workflow->workgroup2->labor_administrator == $user->id;
        } else {
            $isAdmin = $workflow->workgroup1->labor_administrator == $user->id;
        }

        $isLeader = $this->isWorkgroupLeader($user);

        return ($isAdmin || $isLeader)
            && ! $workflow->isApprovedBy($user);
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error('StateRequestToComplete::isUserResponsibleAsDelegate invalid workflow type');
            return false;
        }

        $service = new DelegationService();

        $type = $workflow->workgroup2
            ? 'draft_contract_labor_administrator_' . $workflow->workgroup2->workgroup_number
            : 'draft_contract_labor_administrator_' . $workflow->workgroup1->workgroup_number;

        $delegated = $service->isDelegate($user, $type);

        if (! $delegated) {
            $delegated = $service->isDelegate($user, 'grouplead_910');
        }

        return $delegated && ! $workflow->isApprovedBy($user);
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        if (! $workflow instanceof RecruitmentWorkflow) {
            Log::error('StateRequestToComplete::getResponsibleUsers invalid workflow type');
            return [];
        }

        $service = new DelegationService();

        if ($workflow->workgroup2) {
            $adminId = $workflow->workgroup2->labor_administrator;
            $type = 'draft_contract_labor_administrator_' . $workflow->workgroup2->workgroup_number;
        } else {
            $adminId = $workflow->workgroup1->labor_administrator;
            $type = 'draft_contract_labor_administrator_' . $workflow->workgroup1->workgroup_number;
        }
        $admin = User::find($adminId);
        $responsible = collect([$admin]);

        $responsible = $responsible->concat($service->getDelegates($admin, $type));

        $leaders = $this->getWorkgroupLeaderUsers();
        $responsible = $responsible->concat($leaders);

        foreach ($leaders as $leader) {
            $responsible = $responsible->concat(
                $service->getDelegates($leader, 'grouplead_910')
            );
        }

        if ($notApprovedOnly) {
            $responsible = $responsible->filter(function ($item) use ($workflow) {
                $u = $item instanceof User ? $item : User::find($item['id']);
                return ! $workflow->isApprovedBy($u);
            });
        }

        return Helpers::arrayUniqueMulti($responsible->toArray(), 'id');
    }

    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_completed';
    }

    public function getDelegations(User $user): array
    {
        $delegations = [];

        $workgroups = Workgroup::where('deleted', 0)
            ->where('labor_administrator', $user->id)
            ->get();
        if ($workgroups->count() > 0) {
            foreach ($workgroups as $wg) {
                $delegations[] = [
                    'type' => 'draft_contract_labor_administrator_' . $wg->workgroup_number,
                    'readable_name' => 'Munkaügyi ügyintéző'
                ];
            }
        }

        if ($this->isWorkgroupLeader($user)) {
            $delegations[] = [
                'type' => 'grouplead_910',
                'readable_name' => 'Pénzügyi osztályvezető'
            ];
        }

        return $delegations;
    }
}