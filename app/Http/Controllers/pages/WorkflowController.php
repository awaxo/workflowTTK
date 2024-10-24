<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkflowType;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('content.pages.workflows');
    }

    public function closed()
    {
        return view('content.pages.workflows-closed');
    }

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
                    'updated_by_name' => $workflow->updated_by["name"],
                    'updated_at' => $workflow->updated_at,
                    'created_by_name' => $workflow->created_by["name"],
                    'created_at' => $workflow->created_at,
                    'is_user_responsible' => $workflow->is_user_responsible,
                    'is_closed' => $workflow->is_closed,
                    'is_initiator_role' => User::find(Auth::id())->hasRole('titkar_' . $workflow->initiator_institute_id),
                    'is_manager_user' => WorkflowType::find($workflow->workflow_type_id)->first()->workgroup->leader_id == Auth::id()
                ];
            });

        return response()->json(['data' => $workflows]);
    }

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
                    'updated_by_name' => $workflow->updated_by["name"],
                    'updated_at' => $workflow->updated_at,
                    'created_by_name' => $workflow->created_by["name"],
                    'created_at' => $workflow->created_at,
                    'is_user_responsible' => $workflow->is_user_responsible
                ];
            });

        return response()->json(['data' => $workflows]);
    }

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
