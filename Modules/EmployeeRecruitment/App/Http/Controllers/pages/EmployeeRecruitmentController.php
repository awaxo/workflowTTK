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
use Barryvdh\DomPDF\Facade\PDF;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Models\States\StateGroupLeadApproval;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class EmployeeRecruitmentController extends Controller
{
    public function index()
    {
        // if not 'titkar*' role, return not authorized
        $roles = ['titkar_9_fi','titkar_9_gi','titkar_1','titkar_3','titkar_4','titkar_5','titkar_6','titkar_7','titkar_8'];
        $user = User::find(Auth::id());
        if (!$user->hasAnyRole($roles)) {
            return view('content.pages.misc-not-authorized');
        }

        $workgroups = collect();
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $workgroupNumber = substr($role, -1);
                $workgroupsForRole = Workgroup::where('workgroup_number', 'LIKE', $workgroupNumber.'%')->get();
                $workgroups = $workgroups->concat($workgroupsForRole);
            }
        }
        $workgroup800 = Workgroup::where('workgroup_number', 800)->get();

        $workgroups2 = $workgroups->unique('id')->map(function ($workgroup) {
            $workgroup->leader_name = $workgroup->leader()->first()?->name;
            return $workgroup;
        });
        $workgroups1 = $workgroups->concat($workgroup800)->unique('id')->map(function ($workgroup) {
            $workgroup->leader_name = $workgroup->leader()->first()?->name;
            return $workgroup;
        });
        $positions = Position::all();
        $costCenters = CostCenter::all();
        $rooms = Room::orderBy('room_number')->get();
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
        $validatedData = $request->all();

        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();

        $validatedData['workflow_type_id'] = $workflowType->id;
        $validatedData['created_by'] = auth()->user()->id;
        $validatedData['updated_by'] = auth()->user()->id;
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
        $recruitment->base_salary_cost_center_2 = $validatedData['base_salary_cost_center_2'];
        $recruitment->base_salary_monthly_gross_2 = $validatedData['base_salary_monthly_gross_2'];
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
        $recruitment->updated_by = Auth::id();
        $recruitment->save();

        return response()->json($recruitment, 201);
    }

    public function view($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        
        return view('employeerecruitment::content.pages.recruitment-view', [
            'recruitment' => $recruitment,
            'history' => $this->getHistory($recruitment),
            'nonBaseWorkgroupLead' => ($recruitment->state == 'group_lead_approval' && (new StateGroupLeadApproval)->isUserResponsibleNonBaseWorkgroup(Auth::user(), $recruitment)),
        ]);
    }

    public function beforeApprove($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($recruitment->status != 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            return view('employeerecruitment::content.pages.recruitment-approval', [
                'recruitment' => $recruitment,
                'id' => $id,
                'history' => $this->getHistory($recruitment),
                'nonBaseWorkgroupLead' => ($recruitment->state == 'group_lead_approval' && (new StateGroupLeadApproval)->isUserResponsibleNonBaseWorkgroup(Auth::user(), $recruitment)),
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
                $transition = $service->getNextTransition($recruitment);
                if ($transition) {
                    $this->validateFields($recruitment, $request);
                    $this->storeMetadata($recruitment, $request, 'approvals');
                    $recruitment->workflow_apply($transition);   
                    $recruitment->updated_by = Auth::id();

                    $recruitment->save();
                    
                    return response()->json(['redirectUrl' => route('workflows-all-open')]);
                } else {            
                    Log::error('Nincs vagy nem pontosan 1 valós transition van az adott státuszból');
                    throw new \Exception('No valid transition found');
                }                    
            }
            $this->storeMetadata($recruitment, $request, 'approvals');
            $recruitment->save();

            return response()->json(['redirectUrl' => route('workflows-all-open')]);
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function reject(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            if (strlen($request->input('message')) > 0) {
                $this->storeMetadata($recruitment, $request, 'rejections');
                $recruitment->workflow_apply('to_request_review');
                $recruitment->updated_by = Auth::id();

                $recruitment->save();

                return response()->json(['redirectUrl' => route('workflows-all-open')]);
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
            if (strlen($request->input('message')) > 0) {
                $this->storeMetadata($recruitment, $request, 'suspensions');
                $recruitment->workflow_apply('to_suspended');
                $recruitment->updated_by = Auth::id();
                
                $recruitment->save();

                return response()->json(['redirectUrl' => route('workflows-all-open')]);
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
        
        if ($recruitment->status == 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            return view('employeerecruitment::content.pages.recruitment-restore', [
                'recruitment' => $recruitment,
                'history' => $this->getHistory($recruitment),
                'nonBaseWorkgroupLead' => ($recruitment->state == 'group_lead_approval' && (new StateGroupLeadApproval)->isUserResponsibleNonBaseWorkgroup(Auth::user(), $recruitment)),
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
            if ($recruitment->workflow_can('restore_from_suspended')) {
                $this->storeMetadata($recruitment, $request, 'restorations');
                $this->setPreviousStateToRestore($recruitment);
                $recruitment->updated_by = Auth::id();

                $recruitment->save();
                
                return response()->json(['redirectUrl' => route('workflows-all-open')]);
            } else {            
                Log::error('Nincs definiált transition az adott státuszból');
                throw new \Exception('No valid transition found');
            }

            //return redirect()->route('content.pages.workflows');
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function generatePDF($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $pdf = PDF::loadView('employeerecruitment::content.pdf.recruitment', [
            'recruitment' => $recruitment
        ]);

        return $pdf->download('FelveteliKerelem_' . $id . '.pdf');
    }

    private function validateFields(RecruitmentWorkflow $recruitment, Request $request)
    {
        if ($recruitment->state === 'hr_lead_approval') {
            $probation_period = $request->input('probation_period');
            if ($probation_period === null || $probation_period < 7 || $probation_period > 90) {
                Log::error('A próbaidő hossza nem megfelelő');
                throw new \Exception('Probationary period length is not valid');
            }
            $recruitment->probation_period = $probation_period;
        } elseif ($recruitment->state === 'proof_of_coverage') {
            $post_financed_application = $request->input('post_financed_application');
            if ($post_financed_application === null) {
                return;
            }

            $additional_fields = [
                'user_id' => Auth::id(),
                'datetime' => now()->toDateTimeString(),
                'post_financed_application' => $post_financed_application,
            ];
            $metaData = json_decode($recruitment->meta_data, true) ?? [];
            $metaData['additional_fields'][] = $additional_fields;
            $recruitment->meta_data = json_encode($metaData);
        } elseif ($recruitment->state === 'employee_signature') {
            $recruitment->contract = $request->input('contract_file');
        }
    }

    private function storeMetadata(RecruitmentWorkflow $recruitment, Request $request, string $decision) 
    {
        $detail = [
            'user_id' => Auth::id(),
            'datetime' => now()->toDateTimeString(),
            'message' => $request->input('message'),
        ];
        $history = [
            'decision' => $decision == 'approvals' ? 'approve' : ($decision == 'rejections' ? 'reject' : ($decision == 'suspensions' ? 'suspend' : 'restore')),
            'status' => $recruitment->state,
            'user_id' => Auth::id(),
            'datetime' => now()->toDateTimeString(),
            'message' => $request->input('message'),
        ];

        $metaData = json_decode($recruitment->meta_data, true) ?? [];
        if (!isset($metaData[$decision])) {
            $metaData[$decision] = [];
        }

        if (!isset($metaData[$decision][$recruitment->state])) {
            $metaData[$decision][$recruitment->state] = [
                'approval_user_ids' => [],
                'details' => [],
            ];
        }

        $metaData[$decision][$recruitment->state]['details'][] = $detail;
        $metaData['history'][] = $history;

        $recruitment->meta_data = json_encode($metaData);
    }

    private function setPreviousStateToRestore(RecruitmentWorkflow $recruitment) {
        $metaData = json_decode($recruitment->meta_data, true);
        $latestDateTime = null;
        $previousState = 'suspended';
    
        if (isset($metaData['suspensions']) && is_array($metaData['suspensions'])) {
            foreach ($metaData['suspensions'] as $state => $approvalData) {
                if (isset($approvalData['details']) && is_array($approvalData['details'])) {
                    foreach ($approvalData['details'] as $detail) {
                        // Compare datetimes to find the latest
                        $currentDateTime = isset($detail['datetime']) ? strtotime($detail['datetime']) : null;
                        if ($currentDateTime && (!$latestDateTime || $currentDateTime > $latestDateTime)) {
                            $latestDateTime = $currentDateTime;
                            $previousState = $state;
                        }
                    }
                }
            }
        }
    
        $recruitment->state = $previousState;
    }

    private function getHistory(RecruitmentWorkflow $recruitment) {
        $metaData = json_decode($recruitment->meta_data, true);
        $history = $metaData['history'] ?? [];
    
        // Add user_name to the details array
        foreach ($history as $key => $history_entry) {
            $user = User::find($history_entry['user_id']);
            $history[$key]['user_name'] = $user->name;
        }
    
        // order history by datetime descreasing
        usort($history, function($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });
    
        return $history;
    }
}
