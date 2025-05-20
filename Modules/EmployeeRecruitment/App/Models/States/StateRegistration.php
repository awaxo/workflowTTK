<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Helpers\Helpers;
use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use App\Traits\WorkgroupLeadersTrait;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class StateRegistration implements IStateResponsibility
{
    use WorkgroupLeadersTrait;

    protected function getWorkgroupNumbers(): array
    {
        return [910];
    }

    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        $isRegistrar = $user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto');
        $isLeader    = $this->isWorkgroupLeader($user);

        return ($isRegistrar || $isLeader)
            && ! $workflow->isApprovedBy($user);
    }

    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        $service = new DelegationService();

        $delegated = $service->isDelegate($user, 'registrator');

        if (! $delegated) {
            $delegated = $service->isDelegate($user, 'grouplead_910');
        }

        return $delegated && ! $workflow->isApprovedBy($user);
    }

    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $service = new DelegationService();

        $users = User::role('munkaber_kotelezettsegvallalas_nyilvantarto')->get();

        $delegateUsers = collect();
        foreach ($users as $registrar) {
            $delegateUsers = $delegateUsers->concat(
                $service->getDelegates($registrar, 'registrator')
            );
        }

        $leaders = $this->getWorkgroupLeaderUsers();

        foreach ($leaders as $leader) {
            $delegateUsers = $delegateUsers->concat(
                $service->getDelegates($leader, 'grouplead_910')
            );
        }

        $responsible = $users->concat($leaders)->concat($delegateUsers);

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

    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return true;
    }

    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        return 'to_request_to_complete';
    }

    public function getDelegations(User $user): array
    {
        $delegations = [];

        if ($user->hasRole('munkaber_kotelezettsegvallalas_nyilvantarto')) {
            $delegations[] = [
                'type' => 'registrator',
                'readable_name' => trans('auth.roles.munkaber_kotelezettsegvallalas_nyilvantarto')
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