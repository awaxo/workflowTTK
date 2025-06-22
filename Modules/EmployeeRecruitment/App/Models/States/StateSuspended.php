<?php

namespace Modules\EmployeeRecruitment\App\Models\States;

use App\Models\Interfaces\IGenericWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Class StateSuspended
 * Represents the state of a workflow when it is suspended.
 * This class implements the IStateResponsibility interface to define
 * the responsibilities and transitions for this state.
 */
class StateSuspended implements IStateResponsibility
{
    private $stateClass = null;

    /**
     * Check if the user is responsible for approving the workflow.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsible(User $user, IGenericWorkflow $workflow): bool
    {
        $workflow_meta = json_decode($workflow->meta_data);

        if (!$workflow_meta || !isset($workflow_meta->history) || empty($workflow_meta->history)) {
            return false;
        }        

        $lastEntry = end($workflow_meta->history);
        $lastUser = User::find($lastEntry->user_id);

        if (!$lastUser->deleted && $lastUser->id === $user->id) {
            return true;
        } 
        
        if ($workflow->initiator_institute) {
            $level = $workflow->initiator_institute->group_level;
            if ($user->hasRole('titkar_' . $level)) {
                return true;
            }
        }
        
        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
        $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
        if (class_exists($stateClassName)) {
            $this->stateClass = new $stateClassName();
        }

        return $this->stateClass && $this->stateClass->isUserResponsible($user, $workflow);
    }

    /**
     * Check if the user is responsible for approving the workflow as a delegate.
     *
     * @param User $user
     * @param IGenericWorkflow $workflow
     * @return bool
     */
    public function isUserResponsibleAsDelegate(User $user, IGenericWorkflow $workflow): bool
    {
        if ($workflow->initiator_institute) {
            $level = $workflow->initiator_institute->group_level;
            if ($user->hasRole('titkar_' . $level)) {
                return true;
            }
        }

        if (!$this->stateClass) {
            $workflow_meta = json_decode($workflow->meta_data);
            
            if (!$workflow_meta || !isset($workflow_meta->history) || empty($workflow_meta->history)) {
                return false;
            }

            $lastEntry = end($workflow_meta->history);
            $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
            $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
            
            if (class_exists($stateClassName)) {
                $this->stateClass = new $stateClassName();
            }
        }

        return $this->stateClass && $this->stateClass->isUserResponsibleAsDelegate($user, $workflow);
    }

    /**
     * Get the responsible users for the workflow's current state.
     *
     * @param IGenericWorkflow $workflow
     * @param bool $notApprovedOnly
     * @return array
     */
    public function getResponsibleUsers(IGenericWorkflow $workflow, bool $notApprovedOnly = false): array
    {
        $users = [];

        // 1. Titkár-ellenőrzés: ha van initiátor intézet és a hozzá tartozó group_level-hez tartozó titkár szerep
        if ($workflow->initiator_institute) {
            $level = $workflow->initiator_institute->group_level;
            // lekérjük az összes usert, akiknek megvan ez a szerep
            $sekretars = User::role('titkar_' . $level)->get();
            foreach ($sekretars as $sek) {
                $users[$sek->id] = $sek;
            }
        }

        // 2. Ha nincs history, nincs más felelős
        $workflow_meta = json_decode($workflow->meta_data);
        if (!$workflow_meta || empty($workflow_meta->history)) {
            return array_values($users);
        }

        // 3. Alapból továbbítjuk a felelőst a korábbi állapotnak
        $lastEntry = end($workflow_meta->history);
        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $lastEntry->status)));
        $stateClassName = "Modules\\EmployeeRecruitment\\App\\Models\\States\\{$stateClassShortName}";
        if (class_exists($stateClassName)) {
            $this->stateClass = new $stateClassName();
            $delegated = $this->stateClass->getResponsibleUsers($workflow, $notApprovedOnly);
            foreach ($delegated as $u) {
                // Check if $u is an object and has an id property
                if (is_object($u) && property_exists($u, 'id')) {
                    $users[$u->id] = $u;
                } elseif (is_array($u) && isset($u['id'])) {
                    // If it's an array, convert it or handle accordingly
                    $users[$u['id']] = $u;
                } else {
                    Log::warning('Invalid user object in delegated users:', [
                        'type' => gettype($u),
                        'content' => $u
                    ]);
                }
            }
        } else {
            Log::error("State class not found: {$stateClassName}");
        }

        // 4. Visszaadjuk egyedi user-ek tömbjét
        return array_values($users);
    }

    /**
     * Check if all required approvals have been obtained for the workflow in the given state.
     *
     * @param IGenericWorkflow $workflow
     * @param int|null $userId
     * @return bool
     */
    public function isAllApproved(IGenericWorkflow $workflow, ?int $userId = null): bool
    {
        return true;
    }

    /**
     * Get the next transition for the workflow in the current state.
     *
     * @param IGenericWorkflow $workflow
     * @return string
     */
    public function getNextTransition(IGenericWorkflow $workflow): string
    {
        // next transition depends on from where we get suspended
        return '';
    }

    /**
     * Get the delegations for the user in the current state.
     *
     * @param User $user
     * @return array
     */
    public function getDelegations(User $user): array
    {
        return [];
    }
}