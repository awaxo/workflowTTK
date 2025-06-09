<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkflowType;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Auth;

/*
 * WorkflowController handles the management of workflows,
 * including displaying workflows, fetching data, and managing states.
 *
 * This controller is responsible for rendering the workflows page,
 * fetching all workflows, closed workflows, and workflow states by configuration name.
 */
class WorkflowController extends Controller
{
    /**
     * Display the workflows management page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('content.pages.workflows');
    }

    /**
     * Display the closed workflows management page.
     *
     * @return \Illuminate\View\View
     */
    public function closed()
    {
        return view('content.pages.workflows-closed');
    }

    /**
     * Get all workflows for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllWorkflows()
    {
        $service = new WorkflowService();

        $workflows = $service->getAllButDeletedWorkflows(Auth::user())
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'pseudo_id' => $workflow->pseudo_id,
                    'workflow_type_name' => $workflow->workflow_type["name"],
                    'state' => __('states.' . $workflow->state),
                    'state_name' => $workflow->state,
                    'initiator_institute_abbreviation' => $workflow->initiator_institute["abbreviation"],
                    'updated_by_name' => $workflow->updated_by["name"] ?? 'Technikai felhasználó',
                    'updated_at' => $workflow->updated_at,
                    'created_by_name' => $workflow->created_by["name"] ?? 'Technikai felhasználó',
                    'created_at' => $workflow->created_at,
                    'is_user_responsible' => $workflow->is_user_responsible,
                    'is_closed' => $workflow->is_closed,
                    'is_initiator_role' => User::find(Auth::id())->hasRole('titkar_' . $workflow->initiator_institute_id),
                    'is_manager_user' => WorkflowType::find($workflow->workflow_type_id)->first()->workgroup->leader_id == Auth::id()
                ];
            });

        return response()->json(['data' => $workflows]);
    }

    /**
     * Get closed workflows for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClosedWorkflows()
    {
        $service = new WorkflowService();

        $workflows = $service->getClosedWorkflows(Auth::user())
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'pseudo_id' => $workflow->pseudo_id,
                    'workflow_type_name' => $workflow->workflow_type["name"],
                    'state' => __('states.' . $workflow->state),
                    'state_name' => $workflow->state,
                    'initiator_institute_abbreviation' => $workflow->initiator_institute["abbreviation"],
                    'updated_by_name' => $workflow->updated_by["name"] ?? 'Technikai felhasználó',
                    'updated_at' => $workflow->updated_at,
                    'created_by_name' => $workflow->created_by["name"] ?? 'Technikai felhasználó',
                    'created_at' => $workflow->created_at,
                    'is_user_responsible' => $workflow->is_user_responsible
                ];
            });

        return response()->json(['data' => $workflows]);
    }

    /**
     * Get workflow states by configuration name.
     *
     * @param string $configName
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWorkflowStatesByConfigName($configName)
    {
        $workflow_configs = config('workflow');
        $places = [];
        foreach ($workflow_configs[$configName]['places'] as $place) {
            $places[$place] = __('states.' . $place);
        }

        return response()->json(['data' => $places]);
    }
}
