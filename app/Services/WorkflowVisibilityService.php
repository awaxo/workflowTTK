<?php

namespace App\Services;

use App\Models\AbstractWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class WorkflowVisibilityService
{
    public function getVisibleWorkflows(User $user): Collection
    {
        $visibleWorkflows = collect();

        foreach (WorkflowRegistry::getAll() as $workflowClass) {
            $activeWorkflows = $workflowClass::fetchActive();

            $filtered = $activeWorkflows->filter(function ($workflow) use ($user) {
                $stateHandler = $this->getStateHandler($workflow);
                return $stateHandler && $stateHandler->isUserResponsible($user, $workflow);
            });

            $visibleWorkflows = $visibleWorkflows->merge($filtered);
        }

        return $visibleWorkflows;
    }

    /**
     * Determines and instantiates the appropriate state handler for a given workflow.
     *
     * @param AbstractWorkflow $workflow The workflow instance.
     * @return ?IStateResponsibility The state handler or null if not found.
     */
    protected function getStateHandler(AbstractWorkflow $workflow): ?IStateResponsibility
    {
        $currentState = $workflow->getCurrentState();
        $stateClass = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $currentState)));
        
        if (class_exists($stateClass)) {
            return new $stateClass();
        }

        throw new Exception("State handler for {$workflow->getCurrentState()} not found.");
    }
}