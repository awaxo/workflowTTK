<?php

namespace App\Services;

use App\Models\AbstractWorkflow;
use App\Models\Interfaces\IStateResponsibility;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;

/*
 * WorkflowService provides methods to manage workflows, including fetching workflows,
 * checking user responsibilities, and handling workflow metadata.
 */
class WorkflowService
{
    /**
     * Retrieves all workflows that are not deleted, marking them with user responsibility and closed state.
     *
     * @param User $user The user for whom the workflows are being fetched.
     * @return Collection A collection of workflows with additional metadata.
     */
    public function getAllButDeletedWorkflows(User $user): Collection
    {
        $allWorkflows = collect();

        foreach (WorkflowRegistry::getAll() as $workflowClass) {
            $workflows = $workflowClass::fetchAllButDeleted();
    
            $marked = $workflows->map(function ($workflow) use ($user) {
                $stateHandler = $this->getStateHandler($workflow);
                $is_user_responsible = $stateHandler && ($stateHandler->isUserResponsible($user, $workflow) || $stateHandler->isUserResponsibleAsDelegate($user, $workflow));
                $is_closed = $workflow->state == 'completed' || $workflow->state == 'rejected' || $workflow->state == 'cancelled';
    
                // add is_user_responsible and is_closed fields to the output
                return (object) array_merge($workflow->toArray(), ['is_user_responsible' => $is_user_responsible, 'is_closed' => $is_closed]);
            });
    
            $allWorkflows = $allWorkflows->merge($marked);
        }

        return new \Illuminate\Database\Eloquent\Collection($allWorkflows->all());
    }

    /**
     * Retrieves all workflows that are currently active for the given user.
     *
     * @param User $user The user for whom the workflows are being fetched.
     * @return Collection A collection of active workflows with additional metadata.
     */
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

    /**
     * Retrieves all closed workflows for the given user.
     *
     * @param User $user The user for whom the workflows are being fetched.
     * @return Collection A collection of closed workflows with additional metadata.
     */
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

    /**
     * Checks if the given user is responsible for the specified workflow.
     *
     * @param User $user The user to check.
     * @param AbstractWorkflow $workflow The workflow to check against.
     * @return bool True if the user is responsible, false otherwise.
     */
    public function isUserResponsible(User $user, AbstractWorkflow $workflow): bool
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler && ($stateHandler->isUserResponsible($user, $workflow) || $stateHandler->isUserResponsibleAsDelegate($user, $workflow));
    }

    /**
     * Retrieves the users responsible for the given workflow.
     *
     * @param AbstractWorkflow $workflow The workflow to check.
     * @param bool $notApprovedOnly If true, only returns users who have not approved the workflow.
     * @return array An array of user IDs who are responsible for the workflow.
     */
    public function getResponsibleUsers(AbstractWorkflow $workflow, $notApprovedOnly = false): array
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler->getResponsibleUsers($workflow, $notApprovedOnly);
    }

    /**
     * Checks if the given user has approved the specified workflow state.
     *
     * @param AbstractWorkflow $workflow The workflow to check.
     * @param string $workflowState The state of the workflow to check against.
     * @param int $userId The ID of the user to check.
     * @return bool True if the user has approved, false otherwise.
     */
    public function isApprovedBy(AbstractWorkflow $workflow, string $workflowState, int $userId): bool
    {
        $metaData = json_decode($workflow->meta_data, true);
        if (isset($metaData['approvals'][$workflowState]['approval_user_ids']) && 
            in_array($userId, $metaData['approvals'][$workflowState]['approval_user_ids'])) {
                return true;
        }
        return false;
    }

    /**
     * Checks if all approvals for the given workflow have been completed.
     *
     * @param AbstractWorkflow $workflow The workflow to check.
     * @return bool True if all approvals are completed, false otherwise.
     */
    public function isAllApproved(AbstractWorkflow $workflow): bool
    {
        $stateHandler = $this->getStateHandler($workflow);
        return $stateHandler && $stateHandler->isAllApproved($workflow);
    }

    /**
     * Checks if all approvals for the given workflow state have been completed by a specific user.
     *
     * @param AbstractWorkflow $workflow The workflow to check.
     * @param IStateResponsibility $stateHandler The state handler responsible for the workflow.
     * @param int|null $userId The ID of the user to check, or null for the current authenticated user.
     * @return bool True if all approvals are completed by the user, false otherwise.
     */
    public function isAllApprovedForState(AbstractWorkflow $workflow, IStateResponsibility $stateHandler, ?int $userId = null): bool {
        return $stateHandler->isAllApproved($workflow, $userId);
    }

    /**
     * Retrieves the next transition for the given workflow.
     *
     * @param AbstractWorkflow $workflow The workflow to check.
     * @return string The next transition for the workflow.
     */
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

        Log::error("State handler for {$workflow->getCurrentState()} ({$stateClass}) not found.");
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
    public function storeMetadata(AbstractWorkflow $workflow, ?string $message, string $decision, $userId = null, ?string $workflowState = null) 
    {
        if (!$userId) {
            $userId = Auth::id();
        }

        $state = $workflowState ?? $workflow->state;
        
        $detail = [
            'user_id' => $userId,
            'datetime' => now()->format('Y-m-d H:i:s.u'),
            'message' => $message ? $message : '',
        ];
        $history = [
            'decision' => match($decision) {
                'approvals'     => 'approve',
                'rejections'    => 'reject',
                'suspensions'   => 'suspend',
                'start'         => 'start',
                'restart'       => 'restart',
                'cancellations' => 'cancel',
                'deletion'      => 'delete',
                'update'        => 'update',
                default         => 'restore',
            },
            'status'   => match($decision) {
                'rejections'    => 'rejected',
                'start'         => 'new_request',
                'restart'       => 'request_review',
                'cancellations' => 'cancelled',
                'deletion'      => 'rejected',
                default         => $state,
            },
            'user_id' => $userId,
            'datetime' => now()->format('Y-m-d H:i:s.u'),
            'message' => $message ? $message : '',
        ];

        $metaData = json_decode($workflow->meta_data, true) ?? [];
        if (!isset($metaData[$decision])) {
            $metaData[$decision] = [];
        }

        if (!isset($metaData[$decision][$state])) {
            $metaData[$decision][$state] = [
                'approval_user_ids' => [],
                'details' => [],
            ];
        }

        $metaData[$decision][$state]['details'][] = $detail;
        $metaData['history'][] = $history;

        $workflow->meta_data = json_encode($metaData);
    }

    /**
     * Resets the approvals for a workflow.
     * 
     * @param AbstractWorkflow $workflow The workflow instance.
     */
    public function resetApprovals(AbstractWorkflow $workflow)
    {
        $metaData = json_decode($workflow->meta_data, true) ?? [];
        $metaData['approvals'] = [];
        $workflow->meta_data = json_encode($metaData);
    }
}