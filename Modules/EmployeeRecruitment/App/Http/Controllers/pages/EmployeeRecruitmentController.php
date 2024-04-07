<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\ExternalAccessRight;
use App\Models\Position;
use App\Models\Room;
use App\Models\User;
use App\Models\WorkflowType;
use App\Models\Workgroup;
use App\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class EmployeeRecruitmentController extends Controller
{
    public function index()
    {
        $workgroups1 = Workgroup::all();
        $workgroups2 = Workgroup::where('workgroup_number', '!=', 800)->get();
        $positions = Position::all();
        $costCenters = CostCenter::all();
        $rooms = Room::all();
        $externalAccessRights = ExternalAccessRight::all();
        
        return view('employeerecruitment::content.pages.new-employee-recruitment', [
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2,
            'positions' => $positions,
            'costcenters' => $costCenters,
            'rooms' => $rooms,
            'externalAccessRights' => $externalAccessRights
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

        $workgroup = User::find(auth()->user()->id)->workgroup;
        $firstLetter = substr($workgroup->workgroup_number, 0, 1);
        $recruitment->initiator_institute_id = $firstLetter;

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

    public function beforeApprove($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            return view('employeerecruitment::content.pages.recruitment-approval', [
                'recruitment' => $recruitment
            ]);
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function approve(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            if ($service->isAllApproved($recruitment)) {
                $transition = $this->getNextTransition($recruitment->workflow_transitions());
                if ($transition) {
                    $recruitment->workflow_apply($transition);
    
                    $recruitment->save();
                    
                    return response()->json(['redirectUrl' => route('pages-workflows')]);
                } else {            
                    Log::error('Nincs vagy nem pontosan 1 valós transition van az adott státuszból');
                    throw new \Exception('No valid transition found');
                }                    
            }

            return response()->json(['redirectUrl' => route('pages-workflows')]);
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function reject(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            if (strlen($request->input('decision_message')) > 0) {
                $reject = [
                    'user_id' => Auth::id(),
                    'datetime' => now(),
                    'decision_message' => $request->input('decision_message'),
                ];
                $metaData = json_decode($recruitment->meta_data, true);
                if ($metaData === null) {
                    $metaData = [
                        'reject' => $reject,
                    ];
                } else {
                    $metaData['reject'] = $reject;
                }
                $recruitment->meta_data = json_encode($metaData);
                $recruitment->workflow_apply('to_request_review');

                $recruitment->save();

                return response()->json(['redirectUrl' => route('pages-workflows')]);
            } else {
                Log::error('Nincs indoklás az elutasításhoz');
                throw new \Exception('No reason given for rejection');
            }
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function suspend(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            if (strlen($request->input('decision_message')) > 0) {
                $suspend = [
                    'user_id' => Auth::id(),
                    'source_state' => $recruitment->state,
                    'datetime' => now(),
                    'decision_message' => $request->input('decision_message'),
                ];
                $metaData = json_decode($recruitment->meta_data, true);
                if ($metaData === null) {
                    $metaData = [
                        'suspend' => $suspend,
                    ];
                } else {
                    $metaData['suspend'] = $suspend;
                }
                $recruitment->meta_data = json_encode($metaData);
                $recruitment->workflow_apply('to_suspended');

                $recruitment->save();

                return response()->json(['redirectUrl' => route('pages-workflows')]);
            } else {
                Log::error('Nincs indoklás az elutasításhoz');
                throw new \Exception('No reason given for rejection');
            }
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function beforeRestore($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            return view('employeerecruitment::content.pages.recruitment-restore', [
                'recruitment' => $recruitment
            ]);
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function restore(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            $previous_state = json_decode($recruitment->meta_data)->suspend->source_state;
            
            if ($recruitment->workflow_can('restore_from_suspended')) {
                $metaData = json_decode($recruitment->meta_data, true);
                $metaData['suspend'] = null;
                $recruitment->meta_data = json_encode($metaData);
                $recruitment->state = $previous_state;

                $recruitment->save();
                
                return response()->json(['redirectUrl' => route('pages-workflows')]);
            } else {            
                Log::error('Nincs definiált transition az adott státuszból');
                throw new \Exception('No valid transition found');
            }

            return redirect()->route('content.pages.workflows');
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    /**
     * Returns the next transition that can be made from the current state. It has to be one transition which is not suspended or request_review.
     */
    private function getNextTransition($transitions) {
        $filteredTransitions = array_filter($transitions, function($transition) {
            $tos = $transition->getTos();
            return !in_array("suspended", $tos) && !in_array("request_review", $tos);
        });
    
        if (count($filteredTransitions) === 1) {
            $uniqueTransition = reset($filteredTransitions);
            return $uniqueTransition->getName();
        }
    
        return null;
    }
}
