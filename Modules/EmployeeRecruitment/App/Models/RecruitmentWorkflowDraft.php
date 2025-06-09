<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\AbstractWorkflow;
use App\Models\CostCenter;
use App\Models\User;
use App\Models\Workgroup;
use App\Models\Institute;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Class RecruitmentWorkflowDraft
 * Represents a draft recruitment workflow.
 * This class extends the AbstractWorkflow and defines the base query for fetching drafts
 * based on user roles and permissions.
 */
class RecruitmentWorkflowDraft extends AbstractWorkflow
{
    protected $table = 'recruitment_workflow_draft';

    /**
     * Base query for fetching recruitment workflow drafts.
     * This method checks the user's roles and permissions to determine which drafts they can access.
     * 
     * @return Builder
     */
    public static function baseQuery(): Builder
    {
        $user = User::find(Auth::id());

        // Csoportvezető: ha bármely workgroup leader-e
        $isWorkgroupLeader = Workgroup::where('leader_id', $user->id)->exists();
        if ($isWorkgroupLeader) {
            return self::query()->where('created_by', $user->id);
        }

        $secretaryRoles = $user->getRoleNames()->filter(fn($role) => str_starts_with($role, 'titkar_'));

        if ($secretaryRoles->isNotEmpty()) {
            $allowedPrefixes = $secretaryRoles->map(function ($role) {
                preg_match('/^titkar_(\d+)/', $role, $matches);
                return $matches[1] ?? null;
            })->filter()->unique();

            return self::query()->whereHas('creator.workgroup', function ($query) use ($allowedPrefixes) {
                $query->where(function ($subQuery) use ($allowedPrefixes) {
                    foreach ($allowedPrefixes as $prefix) {
                        $subQuery->orWhere('workgroup_number', 'like', $prefix . '%');
                    }
                });
            });
        }

        return self::query()->whereRaw('1 = 0');
    }

    /**
     * Constructor for RecruitmentWorkflowDraft.
     * Initializes the model with default attributes and casts.
     * 
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, [
            'name',
            'birth_date',
            'social_security_number',
            'address',
            'job_ad_exists',
            'applicants_female_count',
            'applicants_male_count',
            'has_prior_employment',
            'has_current_volunteer_contract',
            'is_retired',
            'citizenship',
            'workgroup_id_1',
            'workgroup_id_2',
            'position_id',
            'job_description',
            'employment_type',
            'task',
            'employment_start_date',
            'employment_end_date',
            'employer_contribution',
            'base_salary_cost_center_1',
            'base_salary_monthly_gross_1',
            'base_salary_cost_center_2',
            'base_salary_monthly_gross_2',
            'base_salary_cost_center_3',
            'base_salary_monthly_gross_3',
            'health_allowance_cost_center_4',
            'health_allowance_monthly_gross_4',
            'management_allowance_cost_center_5',
            'management_allowance_monthly_gross_5',
            'management_allowance_end_date',
            'extra_pay_1_cost_center_6',
            'extra_pay_1_monthly_gross_6',
            'extra_pay_1_end_date',
            'extra_pay_2_cost_center_7',
            'extra_pay_2_monthly_gross_7',
            'extra_pay_2_end_date',
            'weekly_working_hours',
            'work_start_monday',
            'work_end_monday',
            'work_start_tuesday',
            'work_end_tuesday',
            'work_start_wednesday',
            'work_end_wednesday',
            'work_start_thursday',
            'work_end_thursday',
            'work_start_friday',
            'work_end_friday',
            'email',
            'entry_permissions',
            'license_plate',
            'employee_room',
            'phone_extension',
            'external_access_rights',
            'required_tools',
            'available_tools',
            'inventory_numbers_of_available_tools',
            'personal_data_sheet',
            'student_status_verification',
            'certificates',
            'requires_commute_support',
            'commute_support_form',
            'probation_period',
            'contract',
            'contract_registration_number',
            'obligee_number',
            'comment',
            'external_privileges',
            'medical_eligibility_data'
        ]);

        $this->casts = array_merge($this->casts, [
            'job_ad_exists' => 'boolean',
            'has_prior_employment' => 'boolean',
            'has_current_volunteer_contract' => 'boolean',
            'is_retired' => 'boolean',
            'requires_commute_support' => 'boolean',
            'employer_contribution' => 'decimal:1',
        ]);

        $this->attributes = array_merge($this->attributes, [
            'state' => 'new_request',
            'job_ad_exists' => true,
        ]);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workgroup1()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id_1');
    }

    public function workgroup2()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id_2');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function initiatorInstitute()
    {
        return $this->belongsTo(Institute::class, 'initiator_institute_id');
    }

    public function base_salary_cc1()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_1');
    }

    public function base_salary_cc2()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_2');
    }

    public function base_salary_cc3()
    {
        return $this->belongsTo(CostCenter::class, 'base_salary_cost_center_3');
    }

    public function health_allowance_cc()
    {
        return $this->belongsTo(CostCenter::class, 'health_allowance_cost_center_4');
    }

    public function management_allowance_cc()
    {
        return $this->belongsTo(CostCenter::class, 'management_allowance_cost_center_5');
    }

    public function extra_pay_1_cc()
    {
        return $this->belongsTo(CostCenter::class, 'extra_pay_1_cost_center_6');
    }

    public function extra_pay_2_cc()
    {
        return $this->belongsTo(CostCenter::class, 'extra_pay_2_cost_center_7');
    }
}
