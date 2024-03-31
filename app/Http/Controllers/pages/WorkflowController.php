<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Services\WorkflowVisibilityService;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('content.pages.workflows');
    }

    public function getAllWorkflows()
    {
        $wfService = new WorkflowVisibilityService();

        $workflows = $wfService->getVisibleWorkflows(Auth::user())
            ->map(function ($workflow) {
                $workflow->workflow_type_name = $workflow->workflow_type["name"];
                $workflow->initiator_workgroup_number = $workflow->initiator_workgroup["workgroup_number"];
                $workflow->updated_by_name = $workflow->updated_by["name"];
                $workflow->created_by_name = $workflow->created_by["name"];
                $workflow->state = __('states.' . $workflow->state);

                return $workflow;
            });

        return response()->json(['data' => $workflows]);
    }
}
