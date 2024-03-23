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
        $institutes = Institute::all()->map(function ($institute) {
            $workgroupCount = Workgroup::where('workgroup_number', 'like', $institute->group_level . '%')->count();
            $activeWorkflowCount = RecruitmentWorkflow::whereHas('createdBy', function ($query) use ($institute) {
                $query->whereHas('workgroup', function ($query) use ($institute) {
                    $query->where('workgroup_number', 'like', $institute->group_level . '%');
                });
            })->count();

            $institute->workgroup_count = $workgroupCount;
            $institute->active_workflow_count = $activeWorkflowCount;

            return $institute;
        });

        return view('content.pages.institutes', compact('institutes'));
    }
}
