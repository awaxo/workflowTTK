<?php

namespace App\Services;

use App\Models\AbstractWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Nwidart\Modules\Facades\Module;

class WorkflowService
{
    public function getVisibleWorkflows(User $user): Collection
    {
        $visibleWorkflows = collect();

        foreach (WorkflowRegistry::getAll() as $workflowClass) {
            $activeWorkflows = $workflowClass::fetchActive();
    
            $marked = $activeWorkflows->map(function ($workflow) use ($user) {
                $stateHandler = $this->getStateHandler($workflow);
                $is_user_responsible = $stateHandler && ($stateHandler->isUserResponsible($user, $workflow) || $stateHandler->isUserResponsibleAsDelegate($user, $workflow));
    
                // add is_user_responsible field to the output
                return (object) array_merge($workflow->toArray(), ['is_user_responsible' => $is_user_responsible]);
            });
    
            $visibleWorkflows = $visibleWorkflows->merge($marked);
        }

        return new \Illuminate\Database\Eloquent\Collection($visibleWorkflows->all());
    }

    public function getClosedWorkflows(User $user): Collection
    {
        $visibleWorkflows = collect();

        foreach (WorkflowRegistry::getAll() as $workflowClass) {
            $activeWorkflows = $workflowClass::fetchClosed();
    
            $marked = $activeWorkflows->map(function ($workflow) use ($user) {
                $stateHandler = $this->getStateHandler($workflow);
                $is_user_responsible = $stateHandler && ($stateHandler->isUserResponsible($user, $workflow) || $stateHandler->isUserResponsibleAsDelegate($user, $workflow));
    
                // add is_user_responsible field to the output
                return (object) array_merge($workflow->toArray(), ['is_user_responsible' => $is_user_responsible]);
            });
    
            $visibleWorkflows = $visibleWorkflows->merge($marked);
        }

        return new \Illuminate\Database\Eloquent\Collection($visibleWorkflows->all());
    }

    public function isUserResponsible(User $user, AbstractWorkflow $workflow): bool
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler && ($stateHandler->isUserResponsible($user, $workflow) || $stateHandler->isUserResponsibleAsDelegate($user, $workflow));
    }

    public function getResponsibleUsers(AbstractWorkflow $workflow, $notApprovedOnly = false): array
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler->getResponsibleUsers($workflow, $notApprovedOnly);
    }

    public function isAllApproved(AbstractWorkflow $workflow): bool
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler && $stateHandler->isAllApproved($workflow);
    }

    public function getNextTransition(AbstractWorkflow $workflow): string
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler->getNextTransition($workflow);
    }

    /**
     * Determines and instantiates the appropriate state handler for a given workflow.
     *
     * @param AbstractWorkflow $workflow The workflow instance.
     * @return ?IStateResponsibility The state handler or null if not found.
     */
    public function getStateHandler(AbstractWorkflow $workflow): ?IStateResponsibility
    {
        $currentState = $workflow->getCurrentState();
        $stateClassShortName = 'State' . str_replace(' ', '', ucwords(str_replace('_', ' ', $currentState)));

        $modules = Module::toCollection();
        
        foreach ($modules as $module) {
            $stateClass = "Modules\\{$module->getName()}\\App\\Models\\States\\{$stateClassShortName}";
    
            if (class_exists($stateClass)) {
                return new $stateClass();
            }
        }

        throw new Exception("State handler for {$workflow->getCurrentState()} ({$stateClass}) not found.");
    }

    /**
     * Stores metadata about a workflow decision.
     *
     * @param AbstractWorkflow $workflow The workflow instance.
     * @param string $message The message to store.
     * @param string $decision The decision type (approvals, rejections, suspensions, etc.).
     * @param int $userId The user ID who made the decision.
     */
    public function storeMetadata(AbstractWorkflow $workflow, ?string $message, string $decision, $userId = null) 
    {
        if (!$userId) {
            $userId = Auth::id();
        }
        
        $detail = [
            'user_id' => $userId,
            'datetime' => now()->toDateTimeString(),
            'message' => $message ? $message : '',
        ];
        $history = [
            'decision' => $decision == 'approvals' ? 'approve' : ($decision == 'rejections' ? 'reject' : ($decision == 'suspensions' ? 'suspend' : 'restore')),
            'status' => $workflow->state,
            'user_id' => $userId,
            'datetime' => now()->toDateTimeString(),
            'message' => $message ? $message : '',
        ];

        $metaData = json_decode($workflow->meta_data, true) ?? [];
        if (!isset($metaData[$decision])) {
            $metaData[$decision] = [];
        }

        if (!isset($metaData[$decision][$workflow->state])) {
            $metaData[$decision][$workflow->state] = [
                'approval_user_ids' => [],
                'details' => [],
            ];
        }

        $metaData[$decision][$workflow->state]['details'][] = $detail;
        $metaData['history'][] = $history;

        $workflow->meta_data = json_encode($metaData);
    }
}