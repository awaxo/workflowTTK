<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class WorkflowController extends Controller
{
    public function index()
    {
        return view('content.pages.workflows');
    }

    // TODO: ideiglenes megoldás, amíg nincs általánosítva a workflow kezelés, mert itt nem hivatkozhatunk specifikus workflow osztályokra
    public function getAllWorkflows()
    {
        $workflows = RecruitmentWorkflow::query()
            ->select([
                'recruitment_workflow.*',
                'wf_workflow_type.name as workflow_type', 
                'initiator_workgroup.workgroup_number as initiator_workgroup', 
                'updatedBy.name as updated_by_name', 
                'createdBy.name as created_by_name'
            ])
            ->join('wf_workflow_type', 'recruitment_workflow.workflow_type_id', '=', 'wf_workflow_type.id')
            ->join('wf_workgroup as initiator_workgroup', 'recruitment_workflow.initiator_workgroup_id', '=', 'initiator_workgroup.id')
            ->join('wf_user as updatedBy', 'recruitment_workflow.updated_by', '=', 'updatedBy.id')
            ->join('wf_user as createdBy', 'recruitment_workflow.created_by', '=', 'createdBy.id')
            ->where('recruitment_workflow.deleted', '=', false)
            ->get();

        // TODO: map függvény, mint a RoleController-nél
        foreach ($workflows as $workflow) {
            $workflow->state = __('states.' . $workflow->state);
        }

        return response()->json(['data' => $workflows]);
    }
}
