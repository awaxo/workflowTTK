<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Events\ApproverAssignedEvent;
use App\Events\StateChangedEvent;
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
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Models\States\StateGroupLeadApproval;

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
                $workgroupNumber = $workgroupNumber == 'i' ? 9 : $workgroupNumber;
                $workgroupsForRole = Workgroup::where('workgroup_number', 'LIKE', $workgroupNumber.'%')->where('deleted', 0)->get();
                $workgroups = $workgroups->concat($workgroupsForRole);
            }
        }
        $workgroup800 = Workgroup::where('workgroup_number', 800)->where('deleted', 0)->get();

        $workgroups2 = $workgroups->unique('id')->map(function ($workgroup) {
            $workgroup->leader_name = $workgroup->leader()->first()?->name;
            return $workgroup;
        });
        $workgroups1 = $workgroups->concat($workgroup800)->unique('id')->map(function ($workgroup) {
            $workgroup->leader_name = $workgroup->leader()->first()?->name;
            return $workgroup;
        });
        $positions = Position::where('deleted', 0)->get();
        $costCenters = CostCenter::where('deleted', 0)
            ->where('valid_employee_recruitment', 1)
            ->where('due_date', '>', Carbon::today())
            ->get();
        $rooms = Room::orderBy('room_number')->get();
        $externalAccessRights = ExternalAccessRight::where('deleted', 0)->get();

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
        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();
        $workgroup = User::find(Auth::id())->workgroup;
        $recruitment = new RecruitmentWorkflow();

        $validatedData = $request->all();
        
        // generic data
        $recruitment->state = 'it_head_approval';
        $recruitment->workflow_type_id = $workflowType->id;
        $firstLetter = substr($workgroup->workgroup_number, 0, 1);
        $recruitment->initiator_institute_id = $firstLetter;
        $recruitment->created_by = Auth::id();
        $recruitment->updated_by = Auth::id();

        // data section 1
        $recruitment->name = $validatedData['name'];
        $recruitment->job_ad_exists = $validatedData['job_ad_exists'] == 'true' ? 1 : 0;
        $recruitment->has_prior_employment = $validatedData['has_prior_employment'] == 'true' ? 1 : 0;
        $recruitment->has_current_volunteer_contract = $validatedData['has_current_volunteer_contract'] == 'true' ? 1 : 0;
        $recruitment->applicants_female_count = str_replace(' ', '', $validatedData['applicants_female_count']);
        $recruitment->applicants_male_count = str_replace(' ', '', $validatedData['applicants_male_count']);
        $recruitment->citizenship = $validatedData['citizenship'];
        $recruitment->workgroup_id_1 = $validatedData['workgroup_id_1'];
        $recruitment->workgroup_id_2 = $validatedData['workgroup_id_2'] == -1 ? null : $validatedData['workgroup_id_2'];

        // data section 2
        $recruitment->position_id = $validatedData['position_id'];
        $recruitment->job_description = $this->getNewFileName($validatedData['name'], 'MunkaköriLeírás', $validatedData['job_description_file']);
        $recruitment->employment_type = $validatedData['employment_type'];
        $recruitment->task = isset($validatedData['task']) ? $validatedData['task'] : '';
        $validatedData['employment_start_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['employment_start_date'])));
        if (!empty($validatedData['employment_end_date'])) {
            $validatedData['employment_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['employment_end_date'])));
        } else {
            $validatedData['employment_end_date'] = null;
        }
        $recruitment->employment_start_date = $validatedData['employment_start_date'];
        $recruitment->employment_end_date = $validatedData['employment_end_date'];

        // data section 3
        $recruitment->base_salary_cost_center_1 = $validatedData['base_salary_cost_center_1'];
        $recruitment->base_salary_monthly_gross_1 = floatval(str_replace(' ', '', $validatedData['base_salary_monthly_gross_1']));
        $recruitment->base_salary_cost_center_2 = $validatedData['base_salary_cost_center_2'];
        $recruitment->base_salary_monthly_gross_2 = !empty($validatedData['base_salary_monthly_gross_2']) ? floatval(str_replace(' ', '', $validatedData['base_salary_monthly_gross_2'])) : null;
        $recruitment->base_salary_cost_center_3 = $validatedData['base_salary_cost_center_3'];
        $recruitment->base_salary_monthly_gross_3 = !empty($validatedData['base_salary_monthly_gross_3']) ? floatval(str_replace(' ', '', $validatedData['base_salary_monthly_gross_3'])) : null;
        $recruitment->health_allowance_cost_center_4 = $validatedData['health_allowance_cost_center_4'];
        $recruitment->health_allowance_monthly_gross_4 = !empty($validatedData['health_allowance_monthly_gross_4']) ? floatval(str_replace(' ', '', $validatedData['health_allowance_monthly_gross_4'])) : null;
        $recruitment->management_allowance_cost_center_5 = $validatedData['management_allowance_cost_center_5'];
        $recruitment->management_allowance_monthly_gross_5 = !empty($validatedData['management_allowance_monthly_gross_5']) ? floatval(str_replace(' ', '', $validatedData['management_allowance_monthly_gross_5'])) : null;
        if (!empty($validatedData['management_allowance_end_date'])) {
            $validatedData['management_allowance_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['management_allowance_end_date'])));
        } else {
            $validatedData['management_allowance_end_date'] = null;
        }
        $recruitment->management_allowance_end_date = $validatedData['management_allowance_end_date'];
        $recruitment->extra_pay_1_cost_center_6 = $validatedData['extra_pay_1_cost_center_6'];
        $recruitment->extra_pay_1_monthly_gross_6 = !empty($validatedData['management_allowance_monthly_gross_5']) ? floatval(str_replace(' ', '', $validatedData['extra_pay_1_monthly_gross_6'])) : null;
        if (!empty($validatedData['extra_pay_1_end_date'])) {
            $validatedData['extra_pay_1_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['extra_pay_1_end_date'])));
        } else {
            $validatedData['extra_pay_1_end_date'] = null;
        }
        $recruitment->extra_pay_1_end_date = $validatedData['extra_pay_1_end_date'];
        $recruitment->extra_pay_2_cost_center_7 = $validatedData['extra_pay_2_cost_center_7'];
        $recruitment->extra_pay_2_monthly_gross_7 = !empty($validatedData['management_allowance_monthly_gross_5']) ? floatval(str_replace(' ', '', $validatedData['extra_pay_2_monthly_gross_7'])) : null;
        if (!empty($validatedData['extra_pay_2_end_date'])) {
            $validatedData['extra_pay_2_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['extra_pay_2_end_date'])));
        } else {
            $validatedData['extra_pay_2_end_date'] = null;
        }
        $recruitment->extra_pay_2_end_date = $validatedData['extra_pay_2_end_date'];

        // data section 4
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

        // data section 5
        $recruitment->email = $validatedData['email'];
        $recruitment->entry_permissions = is_array($validatedData['entry_permissions']) ? implode(',', $validatedData['entry_permissions']) : $validatedData['entry_permissions'];
        $recruitment->license_plate = $validatedData['license_plate'];
        $recruitment->employee_room = is_array($validatedData['employee_room']) ? implode(',', $validatedData['employee_room']) : null;
        $recruitment->phone_extension = $validatedData['phone_extension'];
        $recruitment->external_access_rights = isset($validatedData['external_access_rights']) && is_array($validatedData['external_access_rights']) ? implode(',', $validatedData['external_access_rights']) : null;
        $recruitment->required_tools = isset($validatedData['required_tools']) && is_array($validatedData['required_tools']) ? implode(',', $validatedData['required_tools']) : null;
        $recruitment->available_tools = isset($validatedData['available_tools']) && is_array($validatedData['available_tools']) ? implode(',', $validatedData['available_tools']) : null;
        $inventoryNumbers = [];
        foreach ($validatedData as $key => $value) {
            if (strpos($key, 'inventory_numbers_of_available_tools_') === 0) {
                $toolName = substr($key, strlen('inventory_numbers_of_available_tools_'));
                $inventoryNumbers[] = [$toolName => $value];
            }
        }
        $recruitment->inventory_numbers_of_available_tools = json_encode($inventoryNumbers);
        $recruitment->work_with_radioactive_isotopes = $validatedData['work_with_radioactive_isotopes'];
        $recruitment->work_with_carcinogenic_materials = $validatedData['work_with_carcinogenic_materials'];
        $recruitment->planned_carcinogenic_materials_use = isset($validatedData['planned_carcinogenic_materials_use']) ? $validatedData['planned_carcinogenic_materials_use'] : null;

        // data section 6
        $recruitment->personal_data_sheet = $this->getNewFileName($validatedData['name'], 'SzemélyiAdatlap', $validatedData['personal_data_sheet_file']);
        $recruitment->student_status_verification = $this->getNewFileName($validatedData['name'], 'HallgatóiJogviszony', $validatedData['student_status_verification_file']);
        $recruitment->certificates = $this->getNewFileName($validatedData['name'], 'Bizonyítványok', $validatedData['certificates_file']);
        $recruitment->requires_commute_support = $validatedData['requires_commute_support'] == 'true' ? 1 : 0;
        $recruitment->commute_support_form = isset($validatedData['commute_support_form_file']) ? $this->getNewFileName($validatedData['name'], 'MunkábaJárásiAdatlap', $validatedData['commute_support_form_file']) : null;
        $recruitment->commute_support_form = isset($validatedData['commute_support_form_file']) ? $validatedData['commute_support_form_file'] : null;

        try {
            $recruitment->save();
            return response()->json(['url' => route('workflows-employee-recruitment-opened')]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while saving the recruitment. Please try again later.'], 500);
        }
    }

    public function opened()
    {
        return view('employeerecruitment::content.pages.recruitment-opened');
    }

    public function closed()
    {
        return view('employeerecruitment::content.pages.recruitment-closed');
    }
    
    public function getAllOpened()
    {
        $service = new WorkflowService();

        $recruitments = RecruitmentWorkflow::whereNotIn('state', ['completed', 'rejected'])->get()->map(function ($recruitment) use ($service) {
            return [
                'id' => $recruitment->id,
                'name' => $recruitment->name,
                'state' => __('states.' . $recruitment->state),
                'workgroup1' => $recruitment->workgroup1->name,
                'workgroup1_number' => $recruitment->workgroup1->workgroup_number,
                'workgroup2' => $recruitment->workgroup2?->name,
                'workgroup2_number' => $recruitment->workgroup2?->workgroup_number,
                'position_type' => $recruitment->position->type,
                'position_name' => $recruitment->position->name,
                'base_salary_cost_center_1' => $recruitment->base_salary_cc1->name,
                'base_salary_cost_center_1_code' => $recruitment->base_salary_cc1->cost_center_code,
                'employment_type' => $recruitment->employment_type,
                'employment_start_date' => $recruitment->employment_start_date,
                'created_at' => $recruitment->created_at,
                'created_by_name' => $recruitment->createdBy->name,
                'updated_at' => $recruitment->updated_at,
                'updated_by_name' => $recruitment->updatedBy->name,
                'is_user_responsible' => $service->isUserResponsible(Auth::user(), $recruitment),
                'is_initiator_role' => User::find(Auth::id())->hasRole('titkar_' . $recruitment->initiator_institute_id),
                'is_manager_user' => WorkflowType::find($recruitment->workflow_type_id)->first()->workgroup->leader_id == Auth::id()
            ];
        });

        return response()->json(['data' => $recruitments]);
    }

    public function getAllClosed()
    {
        $recruitments = RecruitmentWorkflow::where(function ($query) {
            $query->whereIn('state', ['completed', 'rejected'])
            ->orWhere('deleted', 1);
        })->get()->map(function ($recruitment) {
                return [
                    'id' => $recruitment->id,
                    'name' => $recruitment->name,
                    'state' => __('states.' . $recruitment->state),
                    'workgroup1' => $recruitment->workgroup1->name,
                    'workgroup1_number' => $recruitment->workgroup1->workgroup_number,
                    'workgroup2' => $recruitment->workgroup2?->name,
                    'workgroup2_number' => $recruitment->workgroup2?->workgroup_number,
                    'position_type' => $recruitment->position->type,
                    'position_name' => $recruitment->position->name,
                    'base_salary_cost_center_1' => $recruitment->base_salary_cc1->name,
                    'base_salary_cost_center_1_code' => $recruitment->base_salary_cc1->cost_center_code,
                    'employment_type' => $recruitment->employment_type,
                    'employment_start_date' => $recruitment->employment_start_date,
                    'created_at' => $recruitment->created_at,
                    'created_by_name' => $recruitment->createdBy->name,
                    'updated_at' => $recruitment->updated_at,
                    'updated_by_name' => $recruitment->updatedBy->name,
                ];
        });

        return response()->json(['data' => $recruitments]);
    }

    public function view($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            return view('content.pages.misc-error');
        }

        // administrators to see, who else need to approve
        $service = new WorkflowService();
        $usersToApprove = $service->getResponsibleUsers($recruitment, true);
        $usersToApproveName = [];
        foreach ($usersToApprove as $user) {
            $usersToApproveName[] = User::find($user['id'])->name;
        }

        // IT workgroup
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        
        return view('employeerecruitment::content.pages.recruitment-view', [
            'recruitment' => $recruitment,
            'history' => $this->getHistory($recruitment),
            'isITHead' => $workgroup915 && $workgroup915->leader_id === Auth::id(),
            'nonBaseWorkgroupLead' => ($recruitment->state == 'group_lead_approval' && (new StateGroupLeadApproval)->isUserResponsibleNonBaseWorkgroup(Auth::user(), $recruitment)),
            'usersToApprove' => implode(', ', $usersToApproveName)
        ]);
    }

    public function beforeApprove($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            return view('content.pages.misc-error');
        }

        $service = new WorkflowService();
        
        if ($recruitment->state != 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            // IT workgroup
            $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

            return view('employeerecruitment::content.pages.recruitment-approval', [
                'recruitment' => $recruitment,
                'id' => $id,
                'history' => $this->getHistory($recruitment),
                'isITHead' => $workgroup915 && $workgroup915->leader_id === Auth::id(),
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
                $previous_state = __('states.' . $recruitment->state);
                
                if ($transition) {
                    $this->validateFields($recruitment, $request);
                    $service->storeMetadata($recruitment, $request->input('message'), 'approvals');
                    $recruitment->workflow_apply($transition);   
                    $recruitment->updated_by = Auth::id();

                    $recruitment->save();
                    event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));
                    event(new ApproverAssignedEvent($recruitment));
                    
                    return response()->json(['redirectUrl' => route('workflows-all-open')]);
                } else {            
                    Log::error('Nincs vagy nem pontosan 1 valós transition van az adott státuszból');
                    throw new \Exception('No valid transition found');
                }                    
            }
            $service->storeMetadata($recruitment, $request->input('message'), 'approvals');
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
                $previous_state = __('states.' . $recruitment->state);
                $service->storeMetadata($recruitment, $request->input('message'), 'rejections');
                $recruitment->workflow_apply('to_request_review');
                $recruitment->updated_by = Auth::id();

                $recruitment->save();
                event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));

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
        
        if ($service->isUserResponsible(Auth::user(), $recruitment) || $request->input('is_cancel')) {
            if (strlen($request->input('message')) > 0) {
                $previous_state = __('states.' . $recruitment->state);
                $service->storeMetadata($recruitment, $request->input('message'), 'suspensions');
                $recruitment->workflow_apply('to_suspended');
                $recruitment->updated_by = Auth::id();
                if ($request->input('is_cancel')) {
                    $recruitment->deleted = 1;
                }
                
                $recruitment->save();
                event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));

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
        if (!$recruitment) {
            return view('content.pages.misc-error');
        }

        $service = new WorkflowService();

        if ($recruitment->state == 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            // IT workgroup
            $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

            return view('employeerecruitment::content.pages.recruitment-restore', [
                'recruitment' => $recruitment,
                'history' => $this->getHistory($recruitment),
                'isITHead' => $workgroup915 && $workgroup915->leader_id === Auth::id(),
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
                $service->storeMetadata($recruitment, $request->input('message'), 'restorations');
                $this->setPreviousStateToRestore($recruitment);
                $recruitment->updated_by = Auth::id();

                $recruitment->save();
                
                return response()->json(['redirectUrl' => route('workflows-all-open')]);
            } else {            
                Log::error('Nincs definiált transition az adott státuszból');
                throw new \Exception('No valid transition found');
            }
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

    private function getNewFileName($name, $prefix, $originalFileName): string {
        if (!$originalFileName) {
            return '';
        }

        $newFileName = str_replace(' ', '', $name) . '_' . $prefix . '_' . $originalFileName;
        Storage::move('public/uploads/' . $originalFileName, 'public/uploads/' . $newFileName);

        return $newFileName;
    }
}
