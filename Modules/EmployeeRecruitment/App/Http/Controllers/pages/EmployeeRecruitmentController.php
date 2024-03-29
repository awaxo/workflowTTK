<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\WorkflowType;
use App\Models\Workgroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class EmployeeRecruitmentController extends Controller
{
    public function index()
    {
        $workgroups1 = Workgroup::all();
        $workgroups2 = Workgroup::where('workgroup_number', '!=', 800)->get();
        
        return view('employeerecruitment::content.pages.new-employee-recruitment', [
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2
        ]);
    }

    public function store(Request $request)
    {
        $uploads = session('file_uploads', []);

        // Retrieve paths by type
        /*$personalDataSheetPath = $uploads['personal_data_sheet'] ?? null;
        $studentStatusVerificationPath = $uploads['student_status_verification'] ?? null;
        $certificatesPaths = $uploads['certificates'] ?? [];*/

        $validatedData = $request->all();

        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();

        $validatedData['workflow_type_id'] = $workflowType->id;
        $validatedData['created_by'] = auth()->user()->id;
        $validatedData['updated_by'] = auth()->user()->id;
        /*$validatedData['personal_data_sheet'] = $personalDataSheetPath;
        $validatedData['student_status_verification'] = $studentStatusVerificationPath;
        $validatedData['certificates'] = $certificatesPaths;*/
        $recruitment = new RecruitmentWorkflow();
        $recruitment->state = 'it_head_approval';
        $recruitment->workflow_type_id = $workflowType->id;
        $recruitment->initiator_workgroup_id = $validatedData['workgroup_id_1'] != 800 ? $validatedData['workgroup_id_1'] : $validatedData['workgroup_id_2'];
        $recruitment->name = $validatedData['name'];
        $recruitment->created_by = auth()->user()->id;
        $recruitment->updated_by = auth()->user()->id;
        $recruitment->job_ad_exists = $validatedData['job_ad_exists'] == 'true' ? 1 : 0;
        $recruitment->has_prior_employment = $validatedData['has_prior_employment'] == 'true' ? 1 : 0;
        $recruitment->has_current_volunteer_contract = $validatedData['has_current_volunteer_contract'] == 'true' ? 1 : 0;
        $recruitment->applicants_female_count = $validatedData['applicants_female_count'];
        $recruitment->applicants_male_count = $validatedData['applicants_male_count'];
        $recruitment->citizenship = $validatedData['citizenship'];
        $recruitment->workgroup_id_1 = $validatedData['workgroup_id_1'];
        $recruitment->position_id = $validatedData['position_id'];
        $recruitment->employment_type = $validatedData['employment_type'];
        $recruitment->employment_start_date = $validatedData['employment_start_date'];
        $recruitment->employment_end_date = $validatedData['employment_end_date'];
        $recruitment->base_salary_cost_center_1 = $validatedData['base_salary_cost_center_1'];
        $recruitment->base_salary_monthly_gross_1 = $validatedData['base_salary_monthly_gross_1'];
        $recruitment->weekly_working_hours = $validatedData['weekly_working_hours'];
        $recruitment->work_start_monday = $validatedData['work_start_monday'];
        $recruitment->work_end_monday = $validatedData['work_end_monday'];
        $recruitment->work_start_tuesday = $validatedData['work_start_tuesday'];
        $recruitment->work_end_tuesday = $validatedData['work_end_tuesday'];
        $recruitment->work_start_wednesday = $validatedData['work_start_wednesday'];
        $recruitment->work_end_wednesday = $validatedData['work_end_wednesday'];
        $recruitment->work_start_thursday = $validatedData['work_start_thursday'];
        $recruitment->work_end_thursday = $validatedData['work_end_thursday'];
        $recruitment->work_start_friday = $validatedData['work_start_friday'];
        $recruitment->work_end_friday = $validatedData['work_end_friday'];
        $recruitment->email = $validatedData['email'];

        //$recruitment->fill($validatedData);
        $recruitment->save();

        return response()->json($recruitment, 201);
    }
}
