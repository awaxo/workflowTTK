<?php

namespace Modules\EmployeeRecruitment\App\Models;

use App\Models\AbstractWorkflow;
use App\Models\Position;
use App\Models\Workgroup;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\Database\Factories\RecruitmentWorkflowFactory;
use ZeroDaHero\LaravelWorkflow\Traits\WorkflowTrait;

class RecruitmentWorkflow extends AbstractWorkflow
{
    use WorkflowTrait;

    protected static function newFactory()
    {
        return RecruitmentWorkflowFactory::new();
    }

    protected $table = 'recruitment_workflow';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, [
            'name',
            'job_ad_exists',
            'applicants_female_count',
            'applicants_male_count',
            'has_prior_employment',
            'has_current_volunteer_contract',
            'citizenship',
            'workgroup_id_1',
            'workgroup_id_2',
            'position_id',
            'job_description',
            'employment_type',
            'task',
            'employment_start_date',
            'employment_end_date',
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
            'work_with_radioactive_isotopes',
            'work_with_carcinogenic_materials',
            'planned_carcinogenic_materials_use',
            'personal_data_sheet',
            'student_status_verification',
            'certificates',
            'requires_commute_support',
            'commute_support_form',
            'probation_period',
            'post_financed_application',
            'contract'
        ]);

        $this->casts = array_merge($this->casts, [
            'job_ad_exists' => 'boolean',
            'has_prior_employment' => 'boolean',
            'has_current_volunteer_contract' => 'boolean',
            'work_with_radioactive_isotopes' => 'boolean',
            'work_with_carcinogenic_materials' => 'boolean',
            'requires_commute_support' => 'boolean',
            'post_financed_application' => 'boolean',
        ]);

        $this->attributes = array_merge($this->attributes, [
            'state' => 'new_request',
            'job_ad_exists' => true,
            'work_with_radioactive_isotopes' => false,
            'work_with_carcinogenic_materials' => false,
        ]);
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
