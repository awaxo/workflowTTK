<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('content.pages.workflows');
    }

    public function getAllWorkflows()
    {
        $service = new WorkflowService();

        $workflows = $service->getVisibleWorkflows(Auth::user())
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'workflow_type_name' => $workflow->workflow_type["name"],
                    'state' => __('states.' . $workflow->state),
                    'state_name' => $workflow->state,
                    'initiator_institute_group_level' => $workflow->initiator_institute["group_level"],
                    'updated_by_name' => $workflow->updated_by["name"],
                    'updated_at' => $workflow->updated_at,
                    'created_by_name' => $workflow->created_by["name"],
                    'created_at' => $workflow->created_at,
                    'is_user_responsible' => $workflow->is_user_responsible
                ];
            });

        return response()->json(['data' => $workflows]);
    }
}
