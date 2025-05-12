<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Traits\WorkgroupLeadersTrait;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StatePostFinancingApproval implements IStateResponsibility
{
    use WorkgroupLeadersTrait;

    protected function getWorkgroupNumbers(): array
    {
        return [910];
    }

    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        $isApprover = $user->hasRole('utofinanszirozas_fedezetigazolo');

        $isLeader = $this->isWorkgroupLeader($user);

        return ($isApprover || $isLeader)
            && !$workflow->isApprovedBy($user);
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();

        $delegated = $service->isDelegate($user, 'post_financing_approver');

        if (!$delegated) {
            $delegated = $service->isDelegate($user, 'grouplead_910');
        }

        return $delegated;
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();
        $users = User::role('utofinanszirozas_fedezetigazolo')->get();
        $delegateUsers = collect();
        foreach ($users as $user) {
            $delegates = $service->getDelegates($user, 'post_financing_approver');
            $delegateUsers = $delegateUsers->concat($delegates);
        }

        $leaders = $this->getWorkgroupLeaderUsers();
        $users = $users->concat($leaders);
        foreach ($leaders as $leader) {
            $delegateUsers = $delegateUsers->concat(
                $service->getDelegates($leader, 'grouplead_910')
            );
        }

        $responsible = $users->concat($delegateUsers);

        if ($notApprovedOnly) {
            $responsible = $responsible->filter(function ($item) use ($workflow) {
                $usr = $item instanceof User ? $item : User::find($item['id']);
                return ! $workflow->isApprovedBy($usr);
            });
        }

        return Helpers::arrayUniqueMulti(
            $responsible->toArray(),
            'id'
        );
    }

    public function isAllApproved(IGenericWorkflow $workflow): bool
    {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_financial_counterparty_approval';
    }

    public function getDelegations(User $user): array
    {
        $delegations = [];
        
        if ($user->hasRole('utofinanszirozas_fedezetigazolo')) {
            $delegations[] = [
                'type' => 'post_financing_approver',
                'readable_name' => trans('auth.roles.utofinanszirozas_fedezetigazolo')
            ];
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