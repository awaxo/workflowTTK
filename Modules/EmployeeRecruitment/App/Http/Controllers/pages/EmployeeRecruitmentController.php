<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Events\ApproverAssignedEvent;
use App\Events\StateChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\ChemicalPathogenicFactor;
use App\Models\CostCenter;
use App\Models\ExternalAccessRight;
use App\Models\Institute;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class EmployeeRecruitmentController extends Controller
{
    public function index()
    {
        $roles = ['titkar_9_fi','titkar_9_gi','titkar_1','titkar_3','titkar_4','titkar_5','titkar_6','titkar_7','titkar_8'];
        $user = User::find(Auth::id());

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
            ->get()
            ->map(function ($costCenter) {
                $costCenter->leader_name = $costCenter->leadUser()->first()?->name;
                return $costCenter;
            });
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
        $validatedData = $this->validateRequest();

        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();
        $workgroup = User::find(Auth::id())->workgroup;
        $recruitment = new RecruitmentWorkflow();

        $recruitment->fill($validatedData);
        
        // generic data
        $recruitment->state = 'it_head_approval';
        $recruitment->workflow_type_id = $workflowType->id;
        $firstLetter = substr($workgroup->workgroup_number, 0, 1);
        $recruitment->initiator_institute_id = Institute::where('group_level', $firstLetter)->first()->id;
        
        $recruitment->created_by = Auth::id();
        $recruitment->updated_by = Auth::id();

        // data section 1
        $recruitment->name = $validatedData['name'];
        $recruitment->job_ad_exists = request('job_ad_exists') == 'true' ? 1 : 0;
        $recruitment->has_prior_employment = request('has_prior_employment') == 'true' ? 1 : 0;
        $recruitment->has_current_volunteer_contract = request('has_current_volunteer_contract') == 'true' ? 1 : 0;
        $recruitment->applicants_female_count = str_replace(' ', '', $validatedData['applicants_female_count']) == '' ? null : str_replace(' ', '', $validatedData['applicants_female_count']);
        $recruitment->applicants_male_count = str_replace(' ', '', $validatedData['applicants_male_count']) == '' ? null : str_replace(' ', '', $validatedData['applicants_male_count']);
        $recruitment->citizenship = request('citizenship');
        $recruitment->workgroup_id_1 = $validatedData['workgroup_id_1'];
        $recruitment->workgroup_id_2 = request('workgroup_id_2') == -1 ? null : request('workgroup_id_2');

        // data section 2
        $recruitment->position_id = $validatedData['position_id'];
        $recruitment->job_description = $this->getNewFileName($validatedData['name'], 'MunkaköriLeírás', $validatedData['job_description_file']);
        $recruitment->employment_type = request('employment_type');
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
        $recruitment->base_salary_cost_center_2 = request('base_salary_cost_center_2');
        $recruitment->base_salary_monthly_gross_2 = !empty($validatedData['base_salary_monthly_gross_2']) ? floatval(str_replace(' ', '', $validatedData['base_salary_monthly_gross_2'])) : null;
        $recruitment->base_salary_cost_center_3 = request('base_salary_cost_center_3');
        $recruitment->base_salary_monthly_gross_3 = !empty($validatedData['base_salary_monthly_gross_3']) ? floatval(str_replace(' ', '', $validatedData['base_salary_monthly_gross_3'])) : null;
        $recruitment->health_allowance_cost_center_4 = request('health_allowance_cost_center_4');
        $recruitment->health_allowance_monthly_gross_4 = !empty($validatedData['health_allowance_monthly_gross_4']) ? floatval(str_replace(' ', '', $validatedData['health_allowance_monthly_gross_4'])) : null;
        $recruitment->management_allowance_cost_center_5 = request('management_allowance_cost_center_5');
        $recruitment->management_allowance_monthly_gross_5 = !empty($validatedData['management_allowance_monthly_gross_5']) ? floatval(str_replace(' ', '', $validatedData['management_allowance_monthly_gross_5'])) : null;
        if (!empty($validatedData['management_allowance_end_date'])) {
            $validatedData['management_allowance_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['management_allowance_end_date'])));
        } else {
            $validatedData['management_allowance_end_date'] = null;
        }
        $recruitment->management_allowance_end_date = $validatedData['management_allowance_end_date'];

        $recruitment->extra_pay_1_cost_center_6 = request('extra_pay_1_cost_center_6');
        $recruitment->extra_pay_1_monthly_gross_6 = !empty($validatedData['extra_pay_1_monthly_gross_6']) ? floatval(str_replace(' ', '', $validatedData['extra_pay_1_monthly_gross_6'])) : null;
        if (!empty($validatedData['extra_pay_1_end_date'])) {
            $validatedData['extra_pay_1_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['extra_pay_1_end_date'])));
        } else {
            $validatedData['extra_pay_1_end_date'] = null;
        }
        $recruitment->extra_pay_1_end_date = $validatedData['extra_pay_1_end_date'];

        $recruitment->extra_pay_2_cost_center_7 = request('extra_pay_2_cost_center_7');
        $recruitment->extra_pay_2_monthly_gross_7 = !empty($validatedData['extra_pay_2_monthly_gross_7']) ? floatval(str_replace(' ', '', $validatedData['extra_pay_2_monthly_gross_7'])) : null;
        if (!empty($validatedData['extra_pay_2_end_date'])) {
            $validatedData['extra_pay_2_end_date'] = date('Y-m-d', strtotime(str_replace('.', '-', $validatedData['extra_pay_2_end_date'])));
        } else {
            $validatedData['extra_pay_2_end_date'] = null;
        }
        $recruitment->extra_pay_2_end_date = $validatedData['extra_pay_2_end_date'];

        // data section 4
        $recruitment->weekly_working_hours = request('weekly_working_hours');
        $recruitment->work_start_monday = request('work_start_monday');
        $recruitment->work_end_monday = request('work_end_monday');
        $recruitment->work_start_tuesday = request('work_start_tuesday');
        $recruitment->work_end_tuesday = request('work_end_tuesday');
        $recruitment->work_start_wednesday = request('work_start_wednesday');
        $recruitment->work_end_wednesday = request('work_end_wednesday');
        $recruitment->work_start_thursday = request('work_start_thursday');
        $recruitment->work_end_thursday = request('work_end_thursday');
        $recruitment->work_start_friday = request('work_start_friday');
        $recruitment->work_end_friday = request('work_end_friday');

        // data section 5
        $recruitment->email = $validatedData['email'];
        $recruitment->entry_permissions = is_array($validatedData['entry_permissions']) ? implode(',', $validatedData['entry_permissions']) : $validatedData['entry_permissions'];
        $recruitment->license_plate = $validatedData['license_plate'];
        $recruitment->employee_room = $validatedData['employee_room'];
        $recruitment->phone_extension = $validatedData['phone_extension'];
        $recruitment->external_access_rights = request('external_access_rights') && is_array(request('external_access_rights')) ? implode(',', request('external_access_rights')) : null;
        $recruitment->required_tools = request('required_tools') && is_array(request('required_tools')) ? implode(',', request('required_tools')) : null;
        $recruitment->available_tools = request('available_tools') && is_array(request('available_tools')) ? implode(',', request('available_tools')) : null;
        $inventoryNumbers = [];
        foreach ($validatedData as $key => $value) {
            if (strpos($key, 'inventory_numbers_of_available_tools_') === 0) {
                $toolName = substr($key, strlen('inventory_numbers_of_available_tools_'));
                $inventoryNumbers[] = [$toolName => $value];
            }
        }
        $recruitment->inventory_numbers_of_available_tools = json_encode($inventoryNumbers);

        // data section 6
        $recruitment->personal_data_sheet = $this->getNewFileName($validatedData['name'], 'SzemélyiAdatlap', $validatedData['personal_data_sheet_file']);
        $recruitment->student_status_verification = $this->getNewFileName($validatedData['name'], 'HallgatóiJogviszony', $validatedData['student_status_verification_file']);
        $recruitment->certificates = $this->getNewFileName($validatedData['name'], 'Bizonyítványok', $validatedData['certificates_file']);
        $recruitment->requires_commute_support = request('requires_commute_support') == 'true' ? 1 : 0;
        $recruitment->commute_support_form = isset($validatedData['commute_support_form_file']) ? $this->getNewFileName($validatedData['name'], 'MunkábaJárásiAdatlap', $validatedData['commute_support_form_file']) : null;

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
            Log::error('Nem található a felvételi kérelem (id: ' . $id . ')');
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
            'usersToApprove' => implode(', ', $usersToApproveName),
            'monthlyGrossSalariesSum' => $this->getSumOfSallaries($recruitment)
        ]);
    }

    public function beforeApprove($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            Log::error('Nem található a felvételi kérelem (id: ' . $id . ')');
            return view('content.pages.misc-error');
        }

        $service = new WorkflowService();
        
        if ($recruitment->state != 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            // IT workgroup
            $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
            $chemicalFactors = ChemicalPathogenicFactor::where('deleted', 0)->get();
            
            return view('employeerecruitment::content.pages.recruitment-approval', [
                'recruitment' => $recruitment,
                'id' => $id,
                'history' => $this->getHistory($recruitment),
                'isITHead' => $workgroup915 && $workgroup915->leader_id === Auth::id(),
                'monthlyGrossSalariesSum' => $this->getSumOfSallaries($recruitment),
                'chemicalFactors' => $chemicalFactors
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
            // Collect client fields for medical eligibility
            $medicalEligibilityData = $request->only([
                'manual_handling',
                'manual_handling_weight_5_20',
                'manual_handling_weight_20_50',
                'manual_handling_weight_over_50',
                'increased_accident_risk',
                'fire_and_explosion_risk',
                'live_electrical_work',
                'high_altitude_work',
                'other_risks_description',
                'other_risks',
                'forced_body_position',
                'sitting',
                'standing',
                'walking',
                'stressful_workplace_climate',
                'heat_exposure',
                'cold_exposure',
                'noise_exposure',
                'ionizing_radiation_exposure',
                'non_ionizing_radiation_exposure',
                'local_vibration_exposure',
                'whole_body_vibration_exposure',
                'ergonomic_factors_exposure',
                'dust_exposure_description',
                'dust_exposure',
                'chemicals_exposure',
                'chemical_hazards_exposure',
                'other_chemicals_description',
                'carcinogenic_substances_exposure',
                'planned_carcinogenic_substances_list',
                'epidemiological_interest_position',
                'infection_risk',
                'psychological_stress',
                'screen_time',
                'night_shift_work',
                'psychosocial_factors',
                'personal_protective_equipment_stress',
                'work_away_from_family',
                'working_alongside_pension',
                'others',
                'planned_other_health_risk_factors'
            ]);

            Log::info($medicalEligibilityData);
            Log::info(json_encode($medicalEligibilityData));
            
            // Encode as JSON and store in `medical_eligibility_data`
            $recruitment->medical_eligibility_data = json_encode($medicalEligibilityData);

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

            // Encode as JSON and store in `medical_eligibility_data`
            $recruitment->medical_eligibility_data = json_encode($medicalEligibilityData);
            $recruitment->save();

            return response()->json(['redirectUrl' => route('workflows-all-open')]);
        } else {
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem jóváhagyására');
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
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem elutasítására');
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
                if ($request->input('is_cancel') && WorkflowType::find($recruitment->workflow_type_id)->first()->workgroup->leader_id == Auth::id()) {
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
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem felfüggesztésére');
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
                'monthlyGrossSalariesSum' => $this->getSumOfSallaries($recruitment)
            ]);
        } else {
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem felfüggesztésének visszaállítására');
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
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem felfüggesztésének visszaállítására');
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

    public function generateMedicalPDF($id)
    {
        $recruitment = RecruitmentWorkflow::with('position')->find($id);
        $pdf = PDF::loadView('employeerecruitment::content.pdf.medicalEligibility', [
            'recruitment' => $recruitment,
            'medical' => json_decode($recruitment->medical_eligibility_data, true) ?? [],
        ]);

        return $pdf->download('OrvosiAlkalmassagBeutalo_' . $id . '.pdf');
    }

    private function getSumOfSallaries($recruitment)
    {
        $monthlyGrossSalaries = [
            $recruitment->base_salary_monthly_gross_1,
            $recruitment->base_salary_monthly_gross_2,
            $recruitment->base_salary_monthly_gross_3,
            $recruitment->health_allowance_monthly_gross_4,
            $recruitment->management_allowance_monthly_gross_5,
            $recruitment->extra_pay_1_monthly_gross_6,
            $recruitment->extra_pay_2_monthly_gross_7
        ];
        $monthlyGrossSalariesSum = array_sum(array_filter($monthlyGrossSalaries, function ($value) {
            return !is_null($value);
        }));
        
        return number_format($monthlyGrossSalariesSum, 0, '', ' ');
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
            $recruitment->contract = $this->getNewFileName($recruitment->name, 'AláírtSzerződés', $request->input('contract_file'));
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
            Log::warning('A fájl név üres');
            return '';
        }

        $newFileName = str_replace(' ', '', $name) . '_' . $prefix . '_' . $originalFileName;
        Storage::move('public/uploads/' . $originalFileName, 'public/uploads/' . $newFileName);

        return $newFileName;
    }

    private function validateRequest() {
        return request()->validate([
            'name' => 'required|string|max:100',
            'birth_date' => 'required|date_format:Y.m.d',
            'social_security_number' => 'required|string|regex:/^[0-9]{3}-[0-9]{3}-[0-9]{3}$/',
            'address' => 'required|string|max:1000',
            'applicants_female_count' => [
                'required_if:job_ad_exists,true',
                function ($attribute, $value, $fail) {
                    if (request('job_ad_exists') == 'true') {
                        $value = str_replace(' ', '', $value);
                        if (!is_numeric($value) || intval($value) < 0 || intval($value) > 1000) {
                            $fail('Az érték 0 és 1000 között lehet, és egész számot kell megadni');
                        }
                    }
                },
            ],
            'applicants_male_count' => [
                'required_if:job_ad_exists,true',
                function ($attribute, $value, $fail) {
                    if (request('job_ad_exists') == 'true') {
                        $value = str_replace(' ', '', $value);
                        if (!is_numeric($value) || intval($value) < 0 || intval($value) > 1000) {
                            $fail('Az érték 0 és 1000 között lehet, és egész számot kell megadni');
                        }
                    }
                },
            ],
            'workgroup_id_1' => 'required',
            'position_id' => 'required',
            'job_description_file' => 'required|string',
            'task' => 'nullable|string|min:50|max:1000',
            'employment_start_date' => 'required|date_format:Y.m.d',
            'employment_end_date' => 'nullable|date_format:Y.m.d',
            'base_salary_cost_center_1' => 'required',
            'base_salary_monthly_gross_1' => [
                'required',
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (!is_numeric($value) || intval($value) < 1000 || intval($value) > 3000000) {
                        $fail('Az érték 1000 és 3000000 között lehet, és egész számot kell megadni');
                    }
                },
            ],
            'base_salary_monthly_gross_2' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('base_salary_cost_center_2')) {
                        if (!is_numeric($value) || $value < 1000 || $value > 3000000) {
                            $fail('Az érték 1000 és 3 000 000 között lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 vagy null lehet');
                        }
                    }
                },
            ],
            'base_salary_monthly_gross_3' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('base_salary_cost_center_3')) {
                        if (!is_numeric($value) || $value < 1000 || $value > 3000000) {
                            $fail('Az érték 1000 és 3 000 000 között lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 lehet');
                        }
                    }
                },
            ],
            'health_allowance_monthly_gross_4' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('health_allowance_cost_center_4')) {
                        $weeklyWorkingHours = request('weekly_working_hours');
                        if (!is_numeric($value) || $value != $weeklyWorkingHours * 500) {
                            $fail('Az érték csak ' . $weeklyWorkingHours . ' lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 lehet');
                        }
                    }
                },
            ],
            'management_allowance_monthly_gross_5' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('management_allowance_cost_center_5')) {
                        if (!is_numeric($value) || $value < 1000 || $value > 300000) {
                            $fail('Az érték 1000 és 300 000 között lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 lehet');
                        }
                    }
                },
            ],
            'management_allowance_end_date' => [
                function ($attribute, $value, $fail) {
                    if ($value) {
                        try {
                            $endDate = Carbon::createFromFormat('Y.m.d', $value);
                            $maxDate = now()->addYears(4);
                            if ($endDate->isAfter($maxDate)) {
                                $fail('A dátum nem lehet későbbi, mint a mai dátum + 4 év');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid date format');
                        }
                    }
                },
            ],
            'extra_pay_1_monthly_gross_6' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('extra_pay_1_cost_center_6')) {
                        if (!is_numeric($value) || $value < 1000 || $value > 300000) {
                            $fail('Az érték 1000 és 300 000 között lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 lehet');
                        }
                    }
                },
            ],
            'extra_pay_1_end_date' => [
                function ($attribute, $value, $fail) {
                    if ($value) {
                        try {
                            $endDate = Carbon::createFromFormat('Y.m.d', $value);
                            $maxDate = now()->addYears(4);
                            if ($endDate->isAfter($maxDate)) {
                                $fail('A dátum nem lehet későbbi, mint a mai dátum + 4 év');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid date format');
                        }
                    }
                },
            ],
            'extra_pay_2_monthly_gross_7' => [
                function ($attribute, $value, $fail) {
                    $value = str_replace(' ', '', $value);
                    if (request('extra_pay_2_cost_center_7')) {
                        if (!is_numeric($value) || $value < 1000 || $value > 300000) {
                            $fail('Az érték 1000 és 300 000 között lehet');
                        }
                    } else {
                        if ($value && $value != 0) {
                            $fail('Az érték 0 lehet');
                        }
                    }
                },
            ],
            'extra_pay_2_end_date' => [
                function ($attribute, $value, $fail) {
                    if ($value) {
                        try {
                            $endDate = Carbon::createFromFormat('Y.m.d', $value);
                            $maxDate = now()->addYears(4);
                            if ($endDate->isAfter($maxDate)) {
                                $fail('A dátum nem lehet későbbi, mint a mai dátum + 4 év');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid date format');
                        }
                    }
                },
            ],
            'email' => 'required|email|max:100|regex:/^[a-zA-Z0-9._%+-]+@ttk\.hu$/',
            'entry_permissions' => 'required',
            'license_plate' => 'nullable|string|max:9',
            'employee_room' => 'required',
            'phone_extension' => 'required|integer|between:400,999',
            'inventory_numbers_of_available_tools_asztal' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_szek' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_asztali_szamitogep' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_laptop' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_laptop_taska' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_monitor' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_billentyuzet' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_eger' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_dokkolo' => 'string|max:30|regex:/^[0-9 -]+$/',
            'inventory_numbers_of_available_tools_mobiltelefon' => 'string|max:30|regex:/^[0-9 -]+$/',
            'personal_data_sheet_file' => 'required|string',
            'student_status_verification_file' => 'nullable|string',
            'certificates_file' => 'required|string',
            'commute_support_form_file' => 'nullable|string',
        ], [
            'name.required' => 'A név megadása kötelező',
            'name.string' => 'A név érvénytelen',
            'name.max' => 'A név nem lehet hosszabb 100 karakternél',
            'applicants_female_count.required' => 'Kérjük, add meg a női jelentkezők számát',
            'applicants_male_count.required' => 'Kérjük, add meg a férfi jelentkezők számát',
            'workgroup_id_1.required' => 'Kérjük, add meg a csoportot',
            'position_id.required' => 'Kérjük, válaszd ki a munkakört',
            'job_description_file.required' => 'Kérjük, töltsd fel a munkaköri leírást',
            'task.string' => 'A feladat leírása érvénytelen',
            'task.min' => 'A feladat leírásának 50 és 1000 karakter között kell lennie',
            'task.max' => 'A feladat leírásának 50 és 1000 karakter között kell lennie',
            'employment_start_date.required' => 'Kérjük, add meg a jogviszony kezdetét',
            'employment_start_date.date_format' => 'Kérjük, valós dátumot adj meg',
            'employment_end_date.date_format' => 'Kérjük, valós dátumot adj meg',
            'base_salary_cost_center_1.required' => 'Kérjük, add meg a költséghelyet',
            'base_salary_monthly_gross_1.required' => 'Kérjük, add meg havi bruttó bér összegét',
            'management_allowance_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'extra_pay_1_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'extra_pay_2_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'email.required' => 'Kérjük, add meg az email címet',
            'email.email' => 'Kérjük, valós email címet adj meg',
            'email.max' => 'Az email nem lehet hosszabb 100 karakternél',
            'email.regex' => 'Csak @ttk.hu-ra végződő email cím adható meg',
            'entry_permissions.required' => 'Kérjük, válaszd ki a szükséges belépési jogosultságokat',
            'license_plate.string' => 'A rendszám érvénytelen',
            'license_plate.max' => 'A rendszám nem lehet hosszabb 9 karakternél',
            'employee_room.required' => 'Kérjük, válaszd ki a dolgozószobát',
            'phone_extension.required' => 'Kérjük, add meg a telefonszámot',
            'phone_extension.integer' => 'Kérjük, csak egész számot adj meg',
            'phone_extension.between' => 'Az érték 400 és 999 között kell legyen',
            'inventory_numbers_of_available_tools_asztal.string' => 'Az asztal leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_asztal.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_asztal.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_szek.string' => 'A szék leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_szek.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_szek.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_asztali_szamitogep.string' => 'Az asztali számítógép leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_asztali_szamitogep.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_asztali_szamitogep.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_laptop.string' => 'A laptop leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_laptop.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_laptop.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_laptop_taska.string' => 'A laptop táska leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_laptop_taska.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_laptop_taska.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_monitor.string' => 'A monitor leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_monitor.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_monitor.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_billentyuzet.string' => 'A billentyűzet leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_billentyuzet.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_billentyuzet.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_eger.string' => 'Az egér leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_eger.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_eger.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_dokkolo.string' => 'A dokkoló leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_dokkolo.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_dokkolo.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'inventory_numbers_of_available_tools_mobiltelefon.string' => 'A mobiltelefon leltári száma érvénytelen',
            'inventory_numbers_of_available_tools_mobiltelefon.max' => 'A leltári szám nem lehet hosszabb 30 karakternél',
            'inventory_numbers_of_available_tools_mobiltelefon.regex' => 'A leltári szám csak számokat, szóközöket és kötőjeleket tartalmazhat',
            'personal_data_sheet_file.required' => 'Kérjük, töltsd fel a személyi adatlapot',
            'student_status_verification_file.required' => 'Kérjük, töltsd fel a hallgatói jogviszony igazolást',
            'certificates_file.required' => 'Kérjük, töltsd fel a bizonyítványokat',
            'commute_support_form_file.required' => 'Kérjük, töltsd fel a munkába járási adatlapot'
        ]);
    }    
}
