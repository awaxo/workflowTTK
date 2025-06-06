<?php

namespace Modules\EmployeeRecruitment\App\Http\Controllers\pages;

use App\Enums\LegalRelationship;
use App\Events\ApproverAssignedEvent;
use App\Events\CancelledEvent;
use App\Events\RejectedEvent;
use App\Events\StateChangedEvent;
use App\Events\SuspendedEvent;
use App\Events\WorkflowStartedEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\pages\UserController;
use App\Models\AbstractWorkflow;
use App\Models\ChemicalPathogenicFactor;
use App\Models\CostCenter;
use App\Models\ExternalAccessRight;
use App\Models\Institute;
use App\Models\Option;
use App\Models\Position;
use App\Models\Room;
use App\Models\User;
use App\Models\WorkflowType;
use App\Models\Workgroup;
use App\Services\PdfService;
use App\Services\RoleService;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflowDraft;
use Modules\EmployeeRecruitment\App\Models\States\StateDirectorApproval;
use Modules\EmployeeRecruitment\App\Models\States\StateGroupLeadApproval;
use Modules\EmployeeRecruitment\App\Models\States\StateSupervisorApproval;
use Modules\EmployeeRecruitment\App\Services\DelegationService;
use Modules\EmployeeRecruitment\App\Services\RecruitmentWorkflowService;

class EmployeeRecruitmentController extends Controller
{
    /**
     * The PDF service instance
     *
     * @var PdfService
     */
    protected $pdfService;

    /**
     * Create a new controller instance
     *
     * @param PdfService $pdfService
     */
    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $roles = RoleService::getAllSecretaryRoles();
        $user = User::find(Auth::id());

        $workgroups = collect();
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                if ($role === 'titkar_9_fi' || $role === 'titkar_9_gi') {
                    $workgroupsForRole = Workgroup::where('deleted', 0)->get();
                } else {
                    $workgroupNumber = substr($role, strrpos($role, '_') + 1);
                    $workgroupsForRole = Workgroup::where('workgroup_number', 'LIKE', $workgroupNumber.'%')->where('deleted', 0)->get();
                }
                $workgroups = $workgroups->concat($workgroupsForRole);
            }
        }

        $workgroups2 = $workgroups
            ->unique('id')
            ->reject(fn($wg) => $wg->workgroup_number == 800)
            ->map(function ($workgroup) {
                $workgroup->leader_name = $workgroup->leader()->first()?->name;
                return $workgroup;
            });
        $workgroups1 = $workgroups
            ->unique('id')
            ->map(function ($workgroup) {
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
        $isWorkgroupLeader = Workgroup::where('leader_id', $user->id)->exists();
        $isSecretary = $user->getRoleNames()->filter(fn($role) => str_starts_with($role, 'titkar_'))->isNotEmpty();

        return view('employeerecruitment::content.pages.new-employee-recruitment', [
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2,
            'positions' => $positions,
            'costcenters' => $costCenters,
            'rooms' => $rooms,
            'externalAccessRights' => $externalAccessRights,
            'employerContributionRate' => $recruitment->employer_contribution ?? Option::where('option_name', 'employer_contribution')->first()?->option_value,
            'isWorkgroupLeader' => $isWorkgroupLeader,
            'isSecretary' => $isSecretary,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateRequest();

        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();
        $workgroup = User::find(Auth::id())->workgroup;

        if ($request->has('recruitment_id') && !empty($request->input('recruitment_id'))) {
            $recruitment = RecruitmentWorkflow::find($request->input('recruitment_id'));
            if (!$recruitment) {
                return response()->json(['error' => 'Recruitment not found.'], 404);
            }
        } else {
            $recruitment = new RecruitmentWorkflow();

            $maxPseudoId = RecruitmentWorkflow::whereYear('created_at', date('Y'))->max('pseudo_id');
            $recruitment->pseudo_id = $maxPseudoId ? $maxPseudoId + 1 : 1;
        }
        $old_state = $recruitment->state;

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
        $recruitment->is_retired = request('is_retired') == 'true' ? 1 : 0;
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
        
        // For retired employees, employer contribution is 0
        // Otherwise, use the value from options
        $recruitment->employer_contribution = request('is_retired') == 'true' ? 0.0 : 
            (Option::where('option_name', 'employer_contribution')->first() ? 
            (float)Option::where('option_name', 'employer_contribution')->first()->option_value : null);

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
        $recruitment->initiator_comment = $validatedData['initiator_comment'] ?? null;

        $service = new WorkflowService();
        if ($old_state == 'request_review') {
            $service->storeMetadata($recruitment, '-- Felvételi kérelem újra leadva --', 'restart');
        } else {
            $service->storeMetadata($recruitment, '-- Felvételi kérelem létrehozva --', 'start');
        }

        try {
            $recruitment->save();

            if ($request->has('draft_id') && !empty($request->input('draft_id'))) {
                $draft = RecruitmentWorkflowDraft::find($request->input('draft_id'));
                if ($draft && $draft->created_by === Auth::id()) {
                    $draft->deleted = 1;
                    $draft->updated_by = Auth::id();
                    $draft->save();
                }
            }

            event(new WorkflowStartedEvent($recruitment));
            return response()->json(['url' => route('workflows-employee-recruitment-opened')]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while saving the recruitment. Please try again later.'], 500);
        }
    }

    public function storeDraft(Request $request)
    {
        $workflowType = WorkflowType::where('name', 'Felvételi kérelem folyamata')->first();
        $user = User::find(Auth::id());
        $workgroup = $user->workgroup;

        if ($request->has('draft_id') && !empty($request->input('draft_id'))) {
            $draft = RecruitmentWorkflowDraft::find($request->input('draft_id'));
            if (!$draft) {
                return response()->json(['error' => 'Draft not found.'], 404);
            }
        } else {
            $draft = new RecruitmentWorkflowDraft();

            $maxDraftPseudoId = RecruitmentWorkflowDraft::whereYear('created_at', date('Y'))->max('pseudo_id');
            $draft->pseudo_id = $maxDraftPseudoId ? $maxDraftPseudoId + 1 : 1;
        }

        // generic data
        $draft->workflow_type_id = $workflowType->id;
        if ($workgroup) {
            $firstLetter = substr($workgroup->workgroup_number, 0, 1);
            $institute = Institute::where('group_level', $firstLetter)->first();
            if ($institute) {
                $draft->initiator_institute_id = $institute->id;
            }
        }
        
        $draft->created_by = Auth::id();
        $draft->updated_by = Auth::id();

        $draft->fill($request->all());

        // data section 1
        $draft->name = $request->input('name');
        $draft->job_ad_exists = $request->input('job_ad_exists') == 'true' ? 1 : 0;
        $draft->has_prior_employment = $request->input('has_prior_employment') == 'true' ? 1 : 0;
        $draft->has_current_volunteer_contract = $request->input('has_current_volunteer_contract') == 'true' ? 1 : 0;
        $draft->is_retired = $request->input('is_retired') == 'true' ? 1 : 0;
        $draft->applicants_female_count = str_replace(' ', '', $request->input('applicants_female_count')) == '' ? null : str_replace(' ', '', $request->input('applicants_female_count'));
        $draft->applicants_male_count = str_replace(' ', '', $request->input('applicants_male_count')) == '' ? null : str_replace(' ', '', $request->input('applicants_male_count'));
        $draft->citizenship = $request->input('citizenship');
        $draft->workgroup_id_1 = $request->input('workgroup_id_1');
        $draft->workgroup_id_2 = $request->input('workgroup_id_2') == -1 ? null : $request->input('workgroup_id_2');

        // data section 2
        $draft->position_id = $request->input('position_id');
        $draft->job_description = $request->input('job_description_file'); // Piszkozatnál egyszerűen mentjük a fájlnevet
        $draft->employment_type = $request->input('employment_type');
        $draft->task = $request->input('task') ?? '';
        
        // Dátumok kezelése
        if (!empty($request->input('employment_start_date'))) {
            $employment_start_date = date('Y-m-d', strtotime(str_replace('.', '-', $request->input('employment_start_date'))));
            $draft->employment_start_date = $employment_start_date;
        } else {
            $draft->employment_start_date = null;
        }
        
        if (!empty($request->input('employment_end_date'))) {
            $employment_end_date = date('Y-m-d', strtotime(str_replace('.', '-', $request->input('employment_end_date'))));
            $draft->employment_end_date = $employment_end_date;
        } else {
            $draft->employment_end_date = null;
        }
        
        // For retired employees, employer contribution is 0
        // Otherwise, use the value from options
        $draft->employer_contribution = $request->input('is_retired') == 'true' ? 0.0 : 
            (Option::where('option_name', 'employer_contribution')->first() ? 
            (float)Option::where('option_name', 'employer_contribution')->first()->option_value : null);

        // data section 3
        $draft->base_salary_cost_center_1 = $request->input('base_salary_cost_center_1');
        $draft->base_salary_monthly_gross_1 = !empty($request->input('base_salary_monthly_gross_1')) ? 
            floatval(str_replace(' ', '', $request->input('base_salary_monthly_gross_1'))) : 0;
        
        $draft->base_salary_cost_center_2 = $request->input('base_salary_cost_center_2');
        $draft->base_salary_monthly_gross_2 = !empty($request->input('base_salary_monthly_gross_2')) ? 
            floatval(str_replace(' ', '', $request->input('base_salary_monthly_gross_2'))) : 0;
        
        $draft->base_salary_cost_center_3 = $request->input('base_salary_cost_center_3');
        $draft->base_salary_monthly_gross_3 = !empty($request->input('base_salary_monthly_gross_3')) ? 
            floatval(str_replace(' ', '', $request->input('base_salary_monthly_gross_3'))) : 0;
        
        $draft->health_allowance_cost_center_4 = $request->input('health_allowance_cost_center_4');
        $draft->health_allowance_monthly_gross_4 = !empty($request->input('health_allowance_monthly_gross_4')) ? 
            floatval(str_replace(' ', '', $request->input('health_allowance_monthly_gross_4'))) : 0;
        
        $draft->management_allowance_cost_center_5 = $request->input('management_allowance_cost_center_5');
        $draft->management_allowance_monthly_gross_5 = !empty($request->input('management_allowance_monthly_gross_5')) ? 
            floatval(str_replace(' ', '', $request->input('management_allowance_monthly_gross_5'))) : 0;
        
        if (!empty($request->input('management_allowance_end_date'))) {
            $management_allowance_end_date = date('Y-m-d', strtotime(str_replace('.', '-', $request->input('management_allowance_end_date'))));
            $draft->management_allowance_end_date = $management_allowance_end_date;
        } else {
            $draft->management_allowance_end_date = null;
        }

        $draft->extra_pay_1_cost_center_6 = $request->input('extra_pay_1_cost_center_6');
        $draft->extra_pay_1_monthly_gross_6 = !empty($request->input('extra_pay_1_monthly_gross_6')) ? 
            floatval(str_replace(' ', '', $request->input('extra_pay_1_monthly_gross_6'))) : 0;
        
        if (!empty($request->input('extra_pay_1_end_date'))) {
            $extra_pay_1_end_date = date('Y-m-d', strtotime(str_replace('.', '-', $request->input('extra_pay_1_end_date'))));
            $draft->extra_pay_1_end_date = $extra_pay_1_end_date;
        } else {
            $draft->extra_pay_1_end_date = null;
        }

        $draft->extra_pay_2_cost_center_7 = $request->input('extra_pay_2_cost_center_7');
        $draft->extra_pay_2_monthly_gross_7 = !empty($request->input('extra_pay_2_monthly_gross_7')) ? 
            floatval(str_replace(' ', '', $request->input('extra_pay_2_monthly_gross_7'))) : 0;
        
        if (!empty($request->input('extra_pay_2_end_date'))) {
            $extra_pay_2_end_date = date('Y-m-d', strtotime(str_replace('.', '-', $request->input('extra_pay_2_end_date'))));
            $draft->extra_pay_2_end_date = $extra_pay_2_end_date;
        } else {
            $draft->extra_pay_2_end_date = null;
        }

        // data section 4
        $draft->weekly_working_hours = $request->input('weekly_working_hours');
        $draft->work_start_monday = $request->input('work_start_monday');
        $draft->work_end_monday = $request->input('work_end_monday');
        $draft->work_start_tuesday = $request->input('work_start_tuesday');
        $draft->work_end_tuesday = $request->input('work_end_tuesday');
        $draft->work_start_wednesday = $request->input('work_start_wednesday');
        $draft->work_end_wednesday = $request->input('work_end_wednesday');
        $draft->work_start_thursday = $request->input('work_start_thursday');
        $draft->work_end_thursday = $request->input('work_end_thursday');
        $draft->work_start_friday = $request->input('work_start_friday');
        $draft->work_end_friday = $request->input('work_end_friday');

        // data section 5
        $draft->email = $request->input('email');
        $draft->entry_permissions = is_array($request->input('entry_permissions')) ? 
            implode(',', $request->input('entry_permissions')) : $request->input('entry_permissions');
        $draft->license_plate = $request->input('license_plate');
        $draft->employee_room = $request->input('employee_room');
        $draft->phone_extension = $request->input('phone_extension');
        $draft->external_access_rights = $request->input('external_access_rights') && is_array($request->input('external_access_rights')) ? 
            implode(',', $request->input('external_access_rights')) : null;
        $draft->required_tools = $request->input('required_tools') && is_array($request->input('required_tools')) ? 
            implode(',', $request->input('required_tools')) : null;
        $draft->available_tools = $request->input('available_tools') && is_array($request->input('available_tools')) ? 
            implode(',', $request->input('available_tools')) : null;
        
        // Leltári számok kezelése
        $inventoryNumbers = [];
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'inventory_numbers_of_available_tools_') === 0) {
                $toolName = substr($key, strlen('inventory_numbers_of_available_tools_'));
                $inventoryNumbers[] = [$toolName => $value];
            }
        }
        $draft->inventory_numbers_of_available_tools = !empty($inventoryNumbers) ? json_encode($inventoryNumbers) : null;

        // data section 6
        $draft->personal_data_sheet = $request->input('personal_data_sheet_file');
        $draft->student_status_verification = $request->input('student_status_verification_file');
        $draft->certificates = $request->input('certificates_file');
        $draft->requires_commute_support = $request->input('requires_commute_support') == 'true' ? 1 : 0;
        $draft->commute_support_form = $request->input('commute_support_form_file');
        $draft->initiator_comment = $request->input('initiator_comment');

        $service = new WorkflowService();
        if ($request->has('draft_id') && !empty($request->input('draft_id'))) {
            $service->storeMetadata($draft, 'Felvételi kérelem piszkozat módosítva', 'update');
        } else {
            $service->storeMetadata($draft, 'Felvételi kérelem piszkozat létrehozva', 'start');
        }

        try {
            $draft->save();
            return response()->json(['url' => route('workflows-employee-recruitment-drafts')]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Hiba történt a piszkozat mentése közben: ' . $e->getMessage()], 500);
        }
    }

    public function opened()
    {
        return view('employeerecruitment::content.pages.recruitment-opened');
    }

    public function drafts()
    {
        return view('employeerecruitment::content.pages.recruitment-drafts-opened');
    }

    public function closed()
    {
        return view('employeerecruitment::content.pages.recruitment-closed');
    }
    
    public function getAll()
    {
        $service = new WorkflowService();

        $recruitments = RecruitmentWorkflow::baseQuery()->get()->map(function ($recruitment) use ($service) {
            $recruitment_workflow = RecruitmentWorkflow::find($recruitment->id);

            return [
                'id' => $recruitment->id,
                'pseudo_id' => $recruitment->pseudo_id,
                'name' => $recruitment->name,
                'state' => __('states.' . $recruitment->state),
                'state_name' => $recruitment->state,
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
                'created_by_name' => $recruitment->createdBy ? $recruitment->createdBy->name : null,
                'updated_at' => $recruitment->updated_at,
                'updated_by_name' => $recruitment->updatedBy ? $recruitment->updatedBy->name : null,
                'is_user_responsible' => $service->isUserResponsible(Auth::user(), $recruitment_workflow),
                'is_closed' => $recruitment->state == 'completed' || $recruitment->state == 'rejected' || $recruitment->state == 'cancelled',
                'is_initiator_role' => User::find(Auth::id())->hasRole('titkar_' . $recruitment->initiator_institute_id),
                'is_manager_user' => WorkflowType::find($recruitment->workflow_type_id)->first()->workgroup->leader_id == Auth::id()
            ];
        });

        return response()->json(['data' => $recruitments]);
    }

    public function getAllDrafts()
    {
        $drafts = RecruitmentWorkflowDraft::baseQuery()->where('deleted', 0)->get()->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'pseudo_id' => 'P' . $draft->pseudo_id,
                    'name' => $draft->name,
                    'workgroup1' => $draft->workgroup1?->name,
                    'workgroup2' => $draft->workgroup2?->name,
                    'position_name' => $draft->position?->name,
                    'created_at' => $draft->created_at,
                    'updated_at' => $draft->updated_at,
                ];
            });

        return response()->json(['data' => $drafts]);
    }

    public function getAllClosed()
    {
        $recruitments = RecruitmentWorkflow::baseQuery()->where(function ($query) {
            $query->whereIn('state', ['completed', 'rejected'])
            ->orWhere('deleted', 1);
        })->get()->map(function ($recruitment) {
                return [
                    'id' => $recruitment->id,
                    'pseudo_id' => $recruitment->pseudo_id,
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

        // check, if user has read permission for the given recruitment
        if(!RecruitmentWorkflow::baseQuery()->where('id', $id)->exists()) {
            return view('content.pages.misc-not-authorized');
        }

        $service = new WorkflowService();
        $usersToApprove = $service->getResponsibleUsers($recruitment, true);
        $usersToApproveName = [];
        foreach ($usersToApprove as $user) {
            $usersToApproveName[] = User::find($user['id'])->name;
        }

        // IT workgroup
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
        
        // HR workgroup
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();

        // External access rights
        $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
        $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
        // Extract the external_system fields
        $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
        $externalSystemsList = implode(', ', $externalSystems);

        $delegationService = new DelegationService();
        $recruitmentService = new RecruitmentWorkflowService();

        return view('employeerecruitment::content.pages.recruitment-view', [
            'recruitment' => $recruitment,
            'history' => $this->getHistory($recruitment),
            'isITHead' => $workgroup915 && ($workgroup915->leader_id === Auth::id() || $delegationService->isDelegate(Auth::user(), 'it_head')),
            'isHRHead' => $workgroup908 && ($workgroup908->leader_id === Auth::id() || $delegationService->isDelegate(Auth::user(), 'hr_head')),
            'hasNonITHeadPermission' => RecruitmentWorkflow::baseQuery([
                'it_head' => true, 
                'it_head_delegate' => true])->where('id', $id)->exists(),
            'isProjectCoordinator' => $recruitmentService->isProjectCoordinator(Auth::user()),
            'hasNonProjectCoordinatorPermission' => RecruitmentWorkflow::baseQuery([
                'project_coordinator' => true, 
                'project_coordination_lead' => true])->where('id', $id)->exists(),
            'isFinancingOrRegistrator' => $recruitmentService->isFinancingOrRegistrator(Auth::user()),
            'hasNonFinancingOrRegistratorPermission' => RecruitmentWorkflow::baseQuery([
                'registrator' => true, 
                'post_financing_approver' => true, 
                'excluded_workgroups' => [910]])->where('id', $id)->exists(),
            'usersToApprove' => implode(', ', $usersToApproveName),
            'monthlyGrossSalariesSum' => $this->getSumOfSallariesFormatted($recruitment),
            'amountToCover' => $this->getAmountToCover($recruitment),
            'totalAmountToCover' => $this->getTotalAmountToCover($recruitment),
            'externalSystemsList' => $externalSystemsList
        ]);
    }

    public function beforeApprove($id)
    {
        $service = new WorkflowService();

        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            Log::error('Nem található a felvételi kérelem (id: ' . $id . ')');
            return view('content.pages.misc-not-authorized');
        }

        if (!$service->isUserResponsible(Auth::user(), $recruitment)) {
            Log::error('A kérelmet jóváhagyására a felhasználó nem jogosult (id: ' . $id . ')');
            return view('content.pages.misc-not-authorized');
        }

        // check, if user has read permission for the given recruitment
        if(!RecruitmentWorkflow::baseQuery()->where('id', $id)->exists()) {
            return view('content.pages.misc-not-authorized');
        }

        if ($recruitment->state != 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment)) {
            if ($recruitment->state == 'request_review') {
                return $this->review($id);
            }
            
            $usersToApprove = $service->getResponsibleUsers($recruitment, true);
            $usersToApproveName = [];
            foreach ($usersToApprove as $user) {
                $usersToApproveName[] = User::find($user['id'])->name;
            }

            // IT workgroup
            $workgroup915 = Workgroup::where('workgroup_number', 915)->first();
            $chemicalFactors = ChemicalPathogenicFactor::where('deleted', 0)->get();

            // External access rights
            $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
            $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
            // Extract the external_system fields
            $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
            $externalSystemsList = implode(', ', $externalSystems);

            $delegationService = new DelegationService();
            $recruitmentService = new RecruitmentWorkflowService();

            $medicalData = null;
            if (!is_null($recruitment->medical_eligibility_data)) {
                try {
                    $medicalData = json_decode($recruitment->medical_eligibility_data, true);
                } catch (\Exception $e) {
                    Log::error('Hiba a medical eligibility data feldolgozása során: ' . $e->getMessage());
                }
            }

            return view('employeerecruitment::content.pages.recruitment-approval', [
                'recruitment' => $recruitment,
                'id' => $id,
                'history' => $this->getHistory($recruitment),
                'isITHead' => $workgroup915 && ($workgroup915->leader_id === Auth::id() || $delegationService->isDelegate(Auth::user(), 'it_head')),
                'hasNonITHeadPermission' => RecruitmentWorkflow::baseQuery([
                    'it_head' => true, 
                    'it_head_delegate' => true])->where('id', $id)->exists(),
                'isProjectCoordinator' => $recruitmentService->isProjectCoordinator(Auth::user()),
                'hasNonProjectCoordinatorPermission' => RecruitmentWorkflow::baseQuery([
                    'project_coordinator' => true, 
                    'project_coordination_lead' => true])->where('id', $id)->exists(),
                'isFinancingOrRegistrator' => $recruitmentService->isFinancingOrRegistrator(Auth::user()),
                'hasNonFinancingOrRegistratorPermission' => RecruitmentWorkflow::baseQuery([
                    'registrator' => true, 
                    'post_financing_approver' => true, 
                    'excluded_workgroups' => [910]])->where('id', $id)->exists(),
                'usersToApprove' => implode(', ', $usersToApproveName),
                'monthlyGrossSalariesSum' => $this->getSumOfSallariesFormatted($recruitment),
                'amountToCover' => $this->getAmountToCover($recruitment),
                'totalAmountToCover' => $this->getTotalAmountToCover($recruitment),
                'chemicalFactors' => $chemicalFactors,
                'externalSystemsList' => $externalSystemsList,
                'medical' => $medicalData
            ]);
        } else {
            return view('content.pages.misc-not-authorized');
        }
    }

    public function review($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            Log::error('Nem található a felvételi kérelem (id: ' . $id . ')');
            return view('content.pages.misc-error');
        }

        $roles = RoleService::getAllSecretaryRoles();
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

        // External access rights
        $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
        $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
        // Extract the external_system fields
        $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
        $externalSystemsList = implode(', ', $externalSystems);

        return view('employeerecruitment::content.pages.recruitment-review', [
            'recruitment' => $recruitment,
            'history' => $this->getHistory($recruitment),
            'id' => $id,
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2,
            'positions' => $positions,
            'costcenters' => $costCenters,
            'rooms' => $rooms,
            'externalAccessRights' => $externalAccessRights,
            'externalSystemsList' => $externalSystemsList,
            'employerContributionRate' => $recruitment->employer_contribution ?? Option::where('option_name', 'employer_contribution')->first()?->option_value,
        ]);
    }

    public function reviewDraft($id)
    {
        $draft = RecruitmentWorkflowDraft::find($id);
        if (!$draft) {
            Log::error('Nem található a felvételi kérelem piszkozat (id: ' . $id . ')');
            return view('content.pages.misc-error');
        }

        $user = User::find(Auth::id());
        $isWorkgroupLeader = Workgroup::where('leader_id', $user->id)->exists();
        $secretaryRoles = $user->getRoleNames()->filter(fn($role) => str_starts_with($role, 'titkar_'));
        $hasSecretaryRole = $secretaryRoles->isNotEmpty();

        $hasPermission = false;

        // 1. Ha saját piszkozat, akkor van jogosultság
        if ($draft->created_by === Auth::id()) {
            $hasPermission = true;
        }
        // 2. Ha munkacsoport vezető, csak a saját csoportja piszkozatait nézheti
        else if ($isWorkgroupLeader) {
            $leaderWorkgroups = Workgroup::where('leader_id', $user->id)->pluck('id')->toArray();
            $hasPermission = in_array($draft->workgroup_id_1, $leaderWorkgroups) || 
                            (isset($draft->workgroup_id_2) && in_array($draft->workgroup_id_2, $leaderWorkgroups));
        }
        // 3. Ha titkár, csak a saját intézetéhez tartozó piszkozatokat nézheti
        else if ($hasSecretaryRole) {
            // Intézet csoport szintjének lekérdezése a draft-ból
            $institute = Institute::find($draft->initiator_institute_id);
            if ($institute) {
                $instituteGroupLevel = $institute->group_level;
                
                // Ellenőrizzük, hogy a felhasználónak van-e megfelelő titkári jogosultsága
                foreach ($secretaryRoles as $role) {
                    $roleGroupLevel = Str::substr($role, 6, 1); // titkar_X_ formátumból az X kinyerése
                    if ($roleGroupLevel == $instituteGroupLevel) {
                        $hasPermission = true;
                        break;
                    }
                }
            }
        }

        // Ha nincs jogosultsága, nem tekintheti meg a piszkozatot
        if (!$hasPermission) {
            Log::error('Felhasználó (' . Auth::id() . ') nem jogosult megtekinteni a felvételi kérelem piszkozatot (id: ' . $id . ')');
            return view('content.pages.misc-not-authorized');
        }

        $roles = RoleService::getAllSecretaryRoles();
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

        // External access rights
        $externalAccessRightsIds = !empty($draft->external_access_rights) ? explode(',', $draft->external_access_rights) : [];
        $externalAccessRightsData = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
        $externalSystems = $externalAccessRightsData->pluck('external_system')->toArray();
        $externalSystemsList = implode(', ', $externalSystems);
        
        $isWorkgroupLeader = Workgroup::where('leader_id', $user->id)->exists();
        $isSecretary = $user->getRoleNames()->filter(fn($role) => str_starts_with($role, 'titkar_'))->isNotEmpty();

        return view('employeerecruitment::content.pages.recruitment-review-draft', [
            'draft' => $draft,
            'history' => $this->getHistory($draft),
            'id' => $id,
            'workgroups1' => $workgroups1,
            'workgroups2' => $workgroups2,
            'positions' => $positions,
            'costcenters' => $costCenters,
            'rooms' => $rooms,
            'externalAccessRights' => $externalAccessRights,
            'externalSystemsList' => $externalSystemsList,
            'employerContributionRate' => $draft->employer_contribution ?? Option::where('option_name', 'employer_contribution')->first()?->option_value,
            'isDraft' => true,
            'isWorkgroupLeader' => $isWorkgroupLeader,
            'isSecretary' => $isSecretary,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        $isRequestToComplete = $recruitment->state === 'request_to_complete';
        $technicalUser = User::withFeatured()->where('featured', 1)->first();

        if (!$service->isUserResponsible(Auth::user(), $recruitment)) {
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem jóváhagyására');
            return view('content.pages.misc-not-authorized');
        }

        // ────────────────────────────────────────────
        // Ha most fejeződik be az IT_HEAD_APPROVAL:
        //    → lépjünk supervisor_approval-be,
        //    → automatikus „technikai” supervisor jóváhagyások
        //      azoknak, akik mind supervisor, mind group_lead approver-ek.
        // ────────────────────────────────────────────
        if ($recruitment->state === 'it_head_approval') {
            $transition = $service->getNextTransition($recruitment);
            $prevState  = $recruitment->state;
            
            $this->validateFields($recruitment, $request);
            $service->storeMetadata($recruitment, $request->input('message'), 'approvals', null, 'it_head_approval');
            $recruitment->workflow_apply($transition);
            $recruitment->updated_by = Auth::id();
            $recruitment->save();

            // --- technikai jóváhagyás supervisor állapotra ---
            $supervisorIds = collect((new StateSupervisorApproval())
                ->getResponsibleUsers($recruitment))
                ->pluck('id')
                ->all();
            $groupLeadIds  = collect((new StateGroupLeadApproval())
                ->getResponsibleUsers($recruitment))
                ->pluck('id')
                ->all();

            $common = array_intersect($supervisorIds, $groupLeadIds);
            if (!empty($common)) {
                $recruitment->updated_by = $technicalUser->id;
            }
            foreach ($common as $userId) {
                if (!$service->isApprovedBy($recruitment, 'supervisor_approval', $userId)) {
                    $service->storeMetadata($recruitment, 'technikai jóváhagyás', 'approvals', $userId, 'supervisor_approval');

                    if ($service->isAllApprovedForState($recruitment, new StateSupervisorApproval(), $userId)) {
                        $trans = $service->getNextTransition($recruitment);
                        $recruitment->workflow_apply($trans);
                        $recruitment->updated_by = $technicalUser->id;
                    }
                }
            }

            $recruitment->save();
            $message = $request->input('message') ? $request->input('message') : '';
            event(new StateChangedEvent($recruitment, $prevState, $recruitment->state, $message));
            event(new ApproverAssignedEvent($recruitment));
        }
        // ────────────────────────────────────────────
        // Ha group_lead_approval állapotban vagyunk:
        //    a) először a manuális jóváhagyásodat mentjük,
        //    b) aztán **mindig** generálunk director_approval
        //       technikai jóváhagyást, ha ugyanaz a user director is,
        //    c) és ha utána (illetve már eleve korábban is)
        //       mindenki director_ként jóváhagyott,
        //       akkor azonnal ugorjunk hr_lead_approval-re.
        // ────────────────────────────────────────────
        elseif ($recruitment->state === 'group_lead_approval') {
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
            // Encode as JSON and store in `medical_eligibility_data`
            $recruitment->medical_eligibility_data = json_encode($medicalEligibilityData);

            // a) manuális meta
            $service->storeMetadata($recruitment, $request->input('message'), 'approvals');

            // b) technikai director jóváhagyás
            $directorIds = collect((new StateDirectorApproval())
                ->getResponsibleUsers($recruitment))
                ->pluck('id')
                ->all();

            if (in_array(Auth::id(), $directorIds, true) && !$service->isApprovedBy($recruitment, 'director_approval', Auth::id())) {
                $service->storeMetadata($recruitment, 'technikai jóváhagyás', 'approvals', Auth::id(), 'director_approval');
            }

            // c) ha a group_lead kör lezárult, lépjünk director-ba
            if ($service->isAllApproved($recruitment)) {
                $trans1    = $service->getNextTransition($recruitment);
                $prev1     = $recruitment->state;
                $recruitment->workflow_apply($trans1);
                $recruitment->updated_by = Auth::id();

                $recruitment->save();
                $message = $request->input('message') ? $request->input('message') : '';
                event(new StateChangedEvent($recruitment, $prev1, $recruitment->state, $message));
                event(new ApproverAssignedEvent($recruitment));

                // d) ha már director_approval is kész, ugorjunk hr_lead_approval-re
                if ($service->isAllApprovedForState($recruitment, new StateDirectorApproval())) {
                    $trans2 = $service->getNextTransition($recruitment);
                    $prev2  = $recruitment->state;
                    $recruitment->workflow_apply($trans2);
                    $recruitment->updated_by = $technicalUser->id;

                    $recruitment->save();
                    event(new StateChangedEvent($recruitment, $prev2, $recruitment->state, ''));
                    event(new ApproverAssignedEvent($recruitment));
                }
            }

            $recruitment->save();
            return response()->json(['redirectUrl' => route('workflows-all-open')]);
        }
        // ────────────────────────────────────────────
        // A fenti esetektől eltérő összes többi approval esete
        // ────────────────────────────────────────────
        elseif ($service->isAllApproved($recruitment)) {
            $transition = $service->getNextTransition($recruitment);
            $previous_state = $recruitment->state;

            $this->validateFields($recruitment, $request);
            $service->storeMetadata($recruitment, $request->input('message'), 'approvals');
            $recruitment->workflow_apply($transition);
            $recruitment->updated_by = Auth::id();

            // Create user if the previous state was request_to_complete
            if ($isRequestToComplete) {
                try {
                    $userData = [
                        'name' => $recruitment->name,
                        'email' => $recruitment->email,
                        'workgroup_id' => $recruitment->workgroup_id_1,
                        'workflow_id' => $recruitment->id,
                        'social_security_number' => $recruitment->social_security_number,
                        'contract_expiration' => $recruitment->employment_end_date,
                        'legal_relationship' => LegalRelationship::EMPLOYEE,
                    ];

                    $userController = new UserController();
                    $user = $userController->createUserFromData($userData);
                } catch (\Exception $e) {
                    Log::error('Failed to create user from workflow: ' . $e->getMessage());
                    throw new \Exception('Failed to create user from workflow: ' . $e->getMessage());
                }
            }

            $recruitment->save();
            $message = $request->input('message') ? $request->input('message') : '';
            event(new StateChangedEvent($recruitment, $previous_state, $recruitment->state, $message));
            event(new ApproverAssignedEvent($recruitment));
            
            return response()->json(['redirectUrl' => route('workflows-all-open')]);
        }
        else {
            $service->storeMetadata($recruitment, $request->input('message'), 'approvals');
            $recruitment->save();

            return response()->json(['redirectUrl' => route('workflows-all-open')]);
        }
    }

    public function reject(Request $request, $id)
    {
        $service = new WorkflowService();
        $delegationService = new DelegationService();

        $recruitment = RecruitmentWorkflow::find($id);
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        $isHRHead = $workgroup908 && ($workgroup908->leader_id === Auth::id() || $delegationService->isDelegate(Auth::user(), 'hr_head'));
        
        if ($service->isUserResponsible(Auth::user(), $recruitment) || $isHRHead) {
            if (strlen($request->input('message')) > 0) {
                $previous_state = __('states.' . $recruitment->state);
                $service->resetApprovals($recruitment);
                $service->storeMetadata($recruitment, $request->input('message'), 'rejections');

                $recruitment->workflow_apply('to_request_review');
                $recruitment->updated_by = Auth::id();

                $recruitment->save();
                event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));
                event(new RejectedEvent($recruitment));

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
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            if (strlen($request->input('message')) > 0) {
                $previous_state = __('states.' . $recruitment->state);
                $service->storeMetadata($recruitment, $request->input('message'), 'suspensions');
                $recruitment->workflow_apply('to_suspended');
                $recruitment->updated_by = Auth::id();
                
                $recruitment->save();
                event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));
                event(new SuspendedEvent($recruitment));

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

    public function cancel(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();

        if (WorkflowType::find($recruitment->workflow_type_id)->first()->workgroup->leader_id == Auth::id()) {
            if (strlen($request->input('message')) > 0) {
                $previous_state = __('states.' . $recruitment->state);
                $service->storeMetadata($recruitment, $request->input('message'), 'cancellations');
                $recruitment->workflow_apply('to_cancelled');
                $recruitment->updated_by = Auth::id();
                event(new CancelledEvent($recruitment));

                $recruitment->save();
                event(new StateChangedEvent($recruitment, $previous_state, __('states.' . $recruitment->state)));

                return response()->json(['redirectUrl' => route('workflows-all-open')]);
            } else {
                Log::error('Nincs indoklás a törléshez');
                throw new \Exception('No reason given for cancellation');
            }
        } else {
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem törlésére');
            return view('content.pages.misc-not-authorized');
        }
    }

    public function beforeRestore($id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        if (!$recruitment) {
            return view('content.pages.misc-error');
        }
        // check, if user has read permission for the given recruitment
        if(!RecruitmentWorkflow::baseQuery()->where('id', $id)->exists()) {
            return view('content.pages.misc-not-authorized');
        }
        
        $service = new WorkflowService();
        $delegationService = new DelegationService();
        
        // Ellenőrizzük, hogy a felhasználó a felfüggesztő-e
        $isSuspender = $recruitment->state == 'suspended' && $service->isUserResponsible(Auth::user(), $recruitment);
        
        // Ellenőrizzük, hogy a felhasználó az indító intézet titkárnője-e
        $initiatorInstituteCode = $recruitment->initiator_institute;
        $isTitkarForInitiatorInstitute = false;
        
        if ($initiatorInstituteCode) {
            $institute = Institute::find($recruitment->initiator_institute_id);
            if ($institute) {
                $groupLevel = $institute->group_level;
                $isTitkarForInitiatorInstitute = User::find(Auth::id())->hasRole('titkar_' . $groupLevel . '_fi') || 
                                                User::find(Auth::id())->hasRole('titkar_' . $groupLevel . '_gi') ||
                                                $delegationService->isDelegate(Auth::user(), 'secretary_' . $groupLevel . '_fi') ||
                                                $delegationService->isDelegate(Auth::user(), 'secretary_' . $groupLevel . '_gi');
            }
        }

        // Ha a felhasználó a felfüggesztő vagy az indító intézet titkárnője
        if ($recruitment->state == 'suspended' && ($isSuspender || $isTitkarForInitiatorInstitute)) {
            // Munkacsoportok lekérdezése a review view számára
            $roles = RoleService::getAllSecretaryRoles();
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
            
            // External access rights
            $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
            $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
            // Extract the external_system fields
            $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
            $externalSystemsList = implode(', ', $externalSystems);
            
            // Munkáltató járulék mértéke
            $employerContributionRate = $recruitment->employer_contribution ?? Option::where('option_name', 'employer_contribution')->first()?->option_value;

            return view('employeerecruitment::content.pages.recruitment-review', [
                'recruitment' => $recruitment,
                'history' => $this->getHistory($recruitment),
                'id' => $id,
                'workgroups1' => $workgroups1,
                'workgroups2' => $workgroups2,
                'positions' => $positions,
                'costcenters' => $costCenters,
                'rooms' => $rooms,
                'externalAccessRights' => $externalAccessRights,
                'externalSystemsList' => $externalSystemsList,
                'employerContributionRate' => $employerContributionRate,
                'isSuspendedReview' => true
            ]);
        } else {
            $service = new WorkflowService();
            $usersToApprove = $service->getResponsibleUsers($recruitment, true);
            $usersToApproveName = [];
            foreach ($usersToApprove as $user) {
                $usersToApproveName[] = User::find($user['id'])->name;
            }

            // IT workgroup
            $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

            // External access rights
            $externalAccessRightsIds = explode(',', $recruitment->external_access_rights);
            $externalAccessRights = ExternalAccessRight::whereIn('id', $externalAccessRightsIds)->get();
            // Extract the external_system fields
            $externalSystems = $externalAccessRights->pluck('external_system')->toArray();
            $externalSystemsList = implode(', ', $externalSystems);

            $delegationService = new DelegationService();
            $recruitmentService = new RecruitmentWorkflowService();

            return view('employeerecruitment::content.pages.recruitment-restore', [
                'recruitment' => $recruitment,
                'history' => $this->getHistory($recruitment),
                'isITHead' => $workgroup915 && ($workgroup915->leader_id === Auth::id() || $delegationService->isDelegate(Auth::user(), 'it_head')),
                'hasNonITHeadPermission' => RecruitmentWorkflow::baseQuery([
                    'it_head' => true, 
                    'it_head_delegate' => true])->where('id', $id)->exists(),
                'isProjectCoordinator' => $recruitmentService->isProjectCoordinator(Auth::user()),
                'hasNonProjectCoordinatorPermission' => RecruitmentWorkflow::baseQuery([
                    'project_coordinator' => true, 
                    'project_coordination_lead' => true])->where('id', $id)->exists(),
                'isFinancingOrRegistrator' => $recruitmentService->isFinancingOrRegistrator(Auth::user()),
                'hasNonFinancingOrRegistratorPermission' => RecruitmentWorkflow::baseQuery([
                    'registrator' => true, 
                    'post_financing_approver' => true, 
                    'excluded_workgroups' => [910]])->where('id', $id)->exists(),
                'usersToApprove' => implode(', ', $usersToApproveName),
                'monthlyGrossSalariesSum' => $this->getSumOfSallariesFormatted($recruitment),
                'amountToCover' => $this->getAmountToCover($recruitment),
                'totalAmountToCover' => $this->getTotalAmountToCover($recruitment),
                'externalSystemsList' => $externalSystemsList
            ]);
        }
    }

    public function restore(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::findOrFail($id);

        // Csak a fájlok és a kezdő dátum frissítése
        $recruitment->employment_start_date             = $request->input('employment_start_date');
        $recruitment->job_description                    = $request->input('job_description_file');
        $recruitment->personal_data_sheet                = $request->input('personal_data_sheet_file');
        $recruitment->student_status_verification        = $request->input('student_status_verification_file');
        $recruitment->certificates                       = $request->input('certificates_file');
        $recruitment->commute_support_form               = $request->input('commute_support_form_file');

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

    public function delete(Request $request, $id)
    {
        $recruitment = RecruitmentWorkflow::find($id);
        $service = new WorkflowService();
        
        if ($service->isUserResponsible(Auth::user(), $recruitment)) {
            $service->storeMetadata($recruitment, '', 'deletion');
            $recruitment->updated_by = Auth::id();
            $recruitment->deleted = 1;
            
            $recruitment->save();

            return response()->json(['url' => route('workflows-all-open')]);
        } else {
            Log::warning('Felhasználó (' . User::find(Auth::id())->name . ') nem jogosult a felvételi kérelem törlésére');
            return view('content.pages.misc-not-authorized');
        }
    }

    public function deleteDraft($id)
    {
        $draft = RecruitmentWorkflowDraft::find($id);
        
        if (!$draft) {
            return response()->json(['error' => 'A piszkozat nem található.'], 404);
        }
        
        if ($draft->created_by !== Auth::id()) {
            return response()->json(['error' => 'Nincs jogosultságod törölni ezt a piszkozatot.'], 403);
        }
        
        try {
            // Ellenőrizzük, hogy vannak-e hozzá tartozó fájlok, és ha igen, töröljük őket
            $filesToDelete = [
                $draft->job_description,
                $draft->personal_data_sheet,
                $draft->student_status_verification,
                $draft->certificates,
                $draft->commute_support_form
            ];
            
            foreach ($filesToDelete as $file) {
                if (!empty($file) && Storage::exists('public/uploads/' . $file)) {
                    Storage::delete('public/uploads/' . $file);
                }
            }
            
            $draft->updated_by = Auth::id();
            $draft->deleted = 1;
            
            $draft->save();

            return response()->json(['url' => route('workflows-all-open')]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Hiba történt a piszkozat törlése közben: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF for recruitment workflow
     *
     * @param int $id The recruitment workflow ID
     * @return Response
     */
    public function generatePDF($id)
    {
        $recruitment = RecruitmentWorkflow::findOrFail($id);
        $history = $this->getHistory($recruitment);
        $monthlyGrossSalariesSum = $this->getSumOfSallariesFormatted($recruitment);
        $yearFromCreatedAt = date('Y', strtotime($recruitment->created_at));
        
        $mpdf = $this->pdfService->generatePdf(
            'employeerecruitment::content.pdf.recruitment',
            [
                'recruitment' => $recruitment,
                'history' => $history,
                'monthlyGrossSalariesSum' => $monthlyGrossSalariesSum,
            ],
            [],
            [
                'title' => 'Felvételi kérelem - ' . $recruitment->name,
                'author' => 'TTK',
                'creator' => 'Ügyintézés alkalmaás',
            ],
            true,
            [
                'title' => 'Felvételi kérelem',
                'id' => $recruitment->pseudo_id,
                'year' => $yearFromCreatedAt,
            ]
        );
        
        return $this->pdfService->downloadPdf($mpdf, 'FelveteliKerelem_' . $id . '.pdf');
    }

    /**
     * Generate medical eligibility PDF for recruitment workflow
     *
     * @param int $id The recruitment workflow ID
     * @return Response
     */
    public function generateMedicalPDF($id)
    {
        $recruitment = RecruitmentWorkflow::with('position')->findOrFail($id);
        $yearFromCreatedAt = date('Y', strtotime($recruitment->created_at));
        
        $mpdf = $this->pdfService->generatePdf(
            'employeerecruitment::content.pdf.medicalEligibility',
            [
                'recruitment' => $recruitment,
                'medical' => json_decode($recruitment->medical_eligibility_data, true) ?? [],
            ],
            [],
            [
                'title' => 'Orvosi Alkalmasság Beutaló - ' . $recruitment->name,
                'author' => 'TTK',
                'creator' => 'Ügyintézés alkalmazás',
            ],
            true,
            [
                'title' => 'Orvosi Alkalmasság Beutaló',
                'id' => $recruitment->pseudo_id,
                'year' => $yearFromCreatedAt,
            ]
        );
        
        return $this->pdfService->downloadPdf($mpdf, 'OrvosiAlkalmassagBeutalo_' . $id . '.pdf');
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
        
        return $monthlyGrossSalariesSum;
    }

    private function getSumOfSallariesFormatted($recruitment)
    {
        return number_format($this->getSumOfSallaries($recruitment), 0, '', ' ');
    }

    private function getAmountToCover($recruitment)
    {
        // Determine employer contribution rate: use record-specific value if exists, otherwise use default
        if ($recruitment->is_retired) {
            $employerContribution = 0; // No contribution for retired employees
        } else if ($recruitment->employer_contribution !== null) {
            $employerContribution = $recruitment->employer_contribution; // Use record-specific value
        } else {
            $employerContribution = Option::where('option_name', 'employer_contribution')->first()->option_value; // Use default
        }
        
        // Get original dates
        $employmentStartDate = Carbon::createFromFormat('Y-m-d', $recruitment->employment_start_date);
        
        // Fix for handling '0000-00-00' end date
        $employmentEndDate = null;
        if ($recruitment->employment_end_date && $recruitment->employment_end_date !== '0000-00-00') {
            $employmentEndDate = Carbon::createFromFormat('Y-m-d', $recruitment->employment_end_date);
        }
    
        $totalMonthlyGrossSalary = $this->getSumOfSallaries($recruitment);
        
        // Calculate one month value with contributions (for the payment shift adjustment)
        $oneMonthValue = $totalMonthlyGrossSalary * (1 + $employerContribution / 100);
    
        $amountsByYear = [];
        $currentYear = $employmentStartDate->year;
    
        // Both employment types use the same calculation logic
        for ($i = 0; $i < 4; $i++) {
            $startOfYear = Carbon::create($currentYear + $i, 1, 1);
            $endOfYear = Carbon::create($currentYear + $i, 12, 31);
    
            if ($i == 0) {
                $startOfYear = $employmentStartDate;
            }
    
            if ($employmentEndDate && $employmentEndDate->year == $currentYear + $i) {
                $endOfYear = $employmentEndDate;
            }
    
            // Only check if there's a valid end date that's before the current year
            if ($employmentEndDate && $employmentEndDate->year < $currentYear + $i) {
                $amountForYear = 0;
            } else {
                // Calculate months in this year with precise calculation
                $monthsInYear = $this->calculateMonthsInPeriod($startOfYear, $endOfYear);
                
                // Calculate amount before adjustment
                $amountBeforeAdjustment = $totalMonthlyGrossSalary * $monthsInYear * (1 + $employerContribution / 100);
                
                // Apply payment shift adjustment: subtract one month value
                $amountForYear = max(0, $amountBeforeAdjustment - $oneMonthValue);
            }
    
            $amountsByYear[] = [$currentYear + $i, number_format($amountForYear, 0, '', ' ')];
        }
    
        return $amountsByYear;
    }
    
    private function getTotalAmountToCover($recruitment)
    {
        // Determine employer contribution rate
        if ($recruitment->is_retired) {
            $employerContributionRate = 0; // No contribution for retired employees
        } else if ($recruitment->employer_contribution !== null) {
            $employerContributionRate = $recruitment->employer_contribution; // Use record-specific value
        } else {
            $employerContributionRate = Option::where('option_name', 'employer_contribution')->first()->option_value; // Use default
        }
        
        // Get original dates
        $employmentStartDate = Carbon::createFromFormat('Y-m-d', $recruitment->employment_start_date);
        
        // Fix for handling '0000-00-00' end date
        $employmentEndDate = null;
        if ($recruitment->employment_end_date && $recruitment->employment_end_date !== '0000-00-00') {
            $employmentEndDate = Carbon::createFromFormat('Y-m-d', $recruitment->employment_end_date);
        }
    
        $totalMonthlyGrossSalary = $this->getSumOfSallaries($recruitment);
        
        // Calculate one month value with contributions (for the payment shift adjustment)
        $oneMonthValue = $totalMonthlyGrossSalary * (1 + $employerContributionRate / 100);
    
        if ($recruitment->employment_type === 'Határozott' && $employmentEndDate) {
            // For fixed-term employment with valid end date
            // Calculate months and amount before adjustment
            $months = $this->calculateMonthsInPeriod($employmentStartDate, $employmentEndDate);
            $amountBeforeAdjustment = $totalMonthlyGrossSalary * $months * (1 + $employerContributionRate / 100);
            
            // Apply payment shift adjustment: subtract one month value
            $totalAmountToCover = max(0, $amountBeforeAdjustment - $oneMonthValue);
        } else {
            // For indefinite term or fixed-term without valid end date, calculate for 4 years
            $totalAmountToCover = 0;
            
            for ($i = 0; $i < 4; $i++) {
                $startOfYear = Carbon::create($employmentStartDate->year + $i, 1, 1);
                $endOfYear = Carbon::create($employmentStartDate->year + $i, 12, 31);
    
                if ($i == 0) {
                    $startOfYear = $employmentStartDate;
                }
    
                if ($employmentEndDate && $employmentEndDate->year == $employmentStartDate->year + $i) {
                    $endOfYear = $employmentEndDate;
                }
    
                // Only check if there's a valid end date that's before the current year
                if ($employmentEndDate && $employmentEndDate->year < $employmentStartDate->year + $i) {
                    $amountForYear = 0;
                } else {
                    // Calculate months and amount before adjustment
                    $monthsInYear = $this->calculateMonthsInPeriod($startOfYear, $endOfYear);
                    $amountBeforeAdjustment = $totalMonthlyGrossSalary * $monthsInYear * (1 + $employerContributionRate / 100);
                    
                    // In the first year, apply payment shift adjustment
                    if ($i == 0) {
                        $amountForYear = max(0, $amountBeforeAdjustment - $oneMonthValue);
                    } else {
                        $amountForYear = $amountBeforeAdjustment;
                    }
                }
    
                $totalAmountToCover += $amountForYear;
            }
        }
    
        return number_format($totalAmountToCover, 0, '', ' ');
    }
    
    /**
     * Calculate the number of months between two dates more precisely
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateMonthsInPeriod(Carbon $startDate, Carbon $endDate)
    {
        // If dates are the same, return 0
        if ($startDate->eq($endDate)) {
            return 0;
        }
        
        // If end date is before start date, return 0
        if ($endDate->lt($startDate)) {
            return 0;
        }
        
        // If dates span an entire year (Jan 1 to Dec 31)
        if ($startDate->day == 1 && $startDate->month == 1 && 
            $endDate->day == 31 && $endDate->month == 12 && 
            $startDate->year == $endDate->year) {
            return 12;
        }
        
        // Calculate whole months between
        $startClone = clone $startDate;
        $startOfMonth = $startClone->startOfMonth();
        
        $endClone = clone $endDate;
        $endOfMonth = $endClone->startOfMonth();
        
        $wholeMonths = $endOfMonth->diffInMonths($startOfMonth);
        
        // Calculate start month partial
        $daysInStartMonth = $startDate->daysInMonth;
        $remainingDaysInStartMonth = $daysInStartMonth - $startDate->day + 1;
        $startMonthFraction = $remainingDaysInStartMonth / $daysInStartMonth;
        
        // Calculate end month partial
        $daysInEndMonth = $endDate->daysInMonth;
        $endMonthFraction = $endDate->day / $daysInEndMonth;
        
        // If in the same month
        if ($startDate->year == $endDate->year && $startDate->month == $endDate->month) {
            $dayCount = $endDate->day - $startDate->day + 1;
            return $dayCount / $daysInStartMonth;
        }
        
        // Add fractions to whole months
        return ($wholeMonths - 1) + $startMonthFraction + $endMonthFraction;
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
        } elseif ($recruitment->state === 'registration') {
            $obligee_number = $request->input('obligee_number');
            
            // Formátum ellenőrzése: SZ/YYYY/XXXXXXX
            if (!$obligee_number || !preg_match('/^SZ\/\d{4}\/\d{7}$/', $obligee_number)) {
                Log::error('A kötelezettségvállalási szám formátuma nem megfelelő: ' . $obligee_number);
                throw new \Exception('Obligee number format is not valid. Required format: SZ/YYYY/XXXXXXX');
            }
            
            // Év ellenőrzése (aktuális év és az elmúlt 5 év)
            $year = substr($obligee_number, 3, 4);
            $currentYear = date('Y');
            if ($year > $currentYear || $year < ($currentYear - 5)) {
                Log::error('A kötelezettségvállalási szám évszáma nem megfelelő: ' . $year);
                throw new \Exception('Obligee number year is not valid');
            }
            
            $recruitment->obligee_number = $obligee_number;
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
            
            // Validate and save contract_registration_number
            $contract_registration_number = $request->input('contract_registration_number');
            
            // Check if the field is provided and has valid length
            if (!$contract_registration_number || strlen($contract_registration_number) < 6 || strlen($contract_registration_number) > 12) {
                Log::error('A szerződés nyilvántartási száma nem megfelelő hosszúságú: ' . ($contract_registration_number ?? 'nincs megadva'));
                throw new \Exception('Contract registration number must be between 6 and 12 characters');
            }
            
            // Save the validated value
            $recruitment->contract_registration_number = $contract_registration_number;
        } elseif ($recruitment->state === 'draft_contract_pending' && $request->has('social_security_number')) {
            $recruitment->social_security_number = $request->input('social_security_number');
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

    private function getHistory(AbstractWorkflow $recruitment) {
        $metaData = json_decode($recruitment->meta_data, true);
        $history = $metaData['history'] ?? [];
    
        // Add user_name to the details array
        foreach ($history as $key => $history_entry) {
            $user = User::find($history_entry['user_id']);
            $history[$key]['user_name'] = $user ? $user->name : 'Ismeretlen felhasználó';
        }
    
        $history = collect($history)
            ->sortByDesc('datetime')
            ->values()
            ->all();
    
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
            'social_security_number' => [
                function ($attribute, $value, $fail) {
                    if (request('citizenship') != 'Harmadik országbeli' && empty($value)) {
                        $fail('A TAJ szám megadása kötelező.');
                    }
                },
            ],
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
            'workgroup_id_2' => ['nullable','different:workgroup_id_1'],
            'position_id' => 'required',
            'job_description_file' => 'required|string',
            'task' => 'nullable|string|min:25|max:1000',
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
            'base_salary_cost_center_2' => ['nullable','different:base_salary_cost_center_1'],
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
            'base_salary_cost_center_3' => ['nullable','different:base_salary_cost_center_1','different:base_salary_cost_center_2'],
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
            'email' => 'required|email|max:255|regex:/^[a-zA-Z0-9._%+-]+@ttk\.hu$/',
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
            'obligee_number_year' => [
                function ($attribute, $value, $fail) {
                    if (request()->has('obligee_number_year') && request('obligee_number_year') !== null && request('obligee_number_year') !== '') {
                        if (!is_numeric($value) || !is_int((int)$value)) {
                            $fail('Kérjük, csak egész számot adj meg');
                        }
                    }
                },
            ],
            'obligee_number_sequence' => [
                function ($attribute, $value, $fail) {
                    // If year is provided, sequence is required
                    if (request()->has('obligee_number_year') && request('obligee_number_year') !== null && request('obligee_number_year') !== '') {
                        if (empty($value)) {
                            $fail('Kérjük, add meg a kötelezettségvállalási szám sorszámát');
                        }
                    }
                },
            ],
            'initiator_comment' => 'nullable|string|max:2000',
        ], [
            'name.required' => 'A név megadása kötelező',
            'name.string' => 'A név érvénytelen',
            'name.max' => 'A név nem lehet hosszabb 100 karakternél',
            'applicants_female_count.required' => 'Kérjük, add meg a női jelentkezők számát',
            'applicants_male_count.required' => 'Kérjük, add meg a férfi jelentkezők számát',
            'workgroup_id_1.required' => 'Kérjük, add meg a csoportot',
            'workgroup_id_2.different' => 'A Csoport 1 és Csoport 2 nem lehet ugyanaz.',
            'position_id.required' => 'Kérjük, válaszd ki a munkakört',
            'job_description_file.required' => 'Kérjük, töltsd fel a munkaköri leírást',
            'task.string' => 'A feladat leírása érvénytelen',
            'task.min' => 'A feladat leírásának 25 és 1000 karakter között kell lennie',
            'task.max' => 'A feladat leírásának 25 és 1000 karakter között kell lennie',
            'employment_start_date.required' => 'Kérjük, add meg a jogviszony kezdetét',
            'employment_start_date.date_format' => 'Kérjük, valós dátumot adj meg',
            'employment_end_date.date_format' => 'Kérjük, valós dátumot adj meg',
            'base_salary_cost_center_1.required' => 'Kérjük, add meg a költséghelyet',
            'base_salary_monthly_gross_1.required' => 'Kérjük, add meg havi bruttó bér összegét',
            'base_salary_cost_center_2.different' => 'A Költséghely 2 nem egyezhet a Költséghely 1-gyel.',
            'base_salary_cost_center_3.different' => 'A Költséghely 3 nem egyezhet a Költséghely 1-gyel vagy a 2-vel.',
            'management_allowance_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'extra_pay_1_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'extra_pay_2_end_date.callback' => 'A dátum nem lehet későbbi, mint a mai dátum + 4 év',
            'email.required' => 'Kérjük, add meg az email címet',
            'email.email' => 'Kérjük, valós email címet adj meg',
            'email.max' => 'Az email nem lehet hosszabb 255 karakternél',
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
            'commute_support_form_file.required' => 'Kérjük, töltsd fel a munkába járási adatlapot',
            'initiator_comment.max' => 'A megjegyzés nem lehet hosszabb 2000 karakternél',
        ]);
    }    
}
