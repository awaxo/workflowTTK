<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class InstituteController extends Controller
{
    public function index()
    {
        // TODO: itt nem hivatkozhatunk a RecruitmentWorkflow-ra, a service providerben kell a workflowkat regisztrálni
        $institutes = Institute::where('deleted', 0)->get()->map(function ($institute) {
            $workgroupCount = Workgroup::where('workgroup_number', 'like', $institute->group_level . '%')->where('deleted', 0)->count();
            $activeWorkflowCount = RecruitmentWorkflow::whereHas('createdBy', function ($query) use ($institute) {
                $query->whereHas('workgroup', function ($query) use ($institute) {
                    $query->where('workgroup_number', 'like', $institute->group_level . '%')->where('deleted', 0);
                });
            })->count();

            $institute->workgroup_count = $workgroupCount;
            $institute->active_workflow_count = $activeWorkflowCount;

            return $institute;
        });

        return view('content.pages.institutes', compact('institutes'));
    }

    public function manage()
    {
        return view('content.pages.institutes-manage');
    }

    public function getAllInstitutes()
    {
        // get all institutes and updated_by and created_by user's name as updated_by_name and created_by_name
        $institutes = Institute::where('deleted', 0)->get()->map(function ($institute) {
            return [
                'id' => $institute->id,
                'name' => $institute->name,
                'group_level' => $institute->group_level,
                'deleted' => $institute->deleted,
                'created_at' => $institute->created_at,
                'created_by_name' => $institute->createdBy->name,
                'updated_at' => $institute->updated_at,
                'updated_by_name' => $institute->updatedBy->name,
            ];
        });
        return response()->json(['data' => $institutes]);
    }
}
