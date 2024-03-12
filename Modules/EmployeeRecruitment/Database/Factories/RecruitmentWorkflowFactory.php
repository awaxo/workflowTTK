<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\Country;
use App\Models\Workgroup;
use App\Models\Position;
use App\Models\CostCenter;
use Database\Factories\AbstractWorkflowFactory;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class RecruitmentWorkflowFactory extends AbstractWorkflowFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitmentWorkflow::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return array_merge(parent::definition(), [
            'state' => $this->faker->randomElement(['new_request', 'it_head_approval', 'supervisor_approval', 'completed']),
            'applicants_female_count' => $this->faker->numberBetween(0, 100),
            'applicants_male_count' => $this->faker->numberBetween(0, 100),
            'has_prior_employment' => $this->faker->boolean,
            'has_current_volunteer_contract' => $this->faker->boolean,
            'citizenship_id' => Country::factory(),
            'workgroup_id_1' => Workgroup::factory(),
            'workgroup_id_2' => Workgroup::factory(),
            'position_id' => Position::factory(),
            'job_description' => $this->faker->text(500),
            'employment_type' => $this->faker->randomElement(['full-time', 'part-time', 'contract']),
            'task' => $this->faker->text(1000),
            'employment_start_date' => $this->faker->date(),
            'employment_end_date' => $this->faker->date(),
            'base_salary_cost_center_1' => CostCenter::factory(),
            'base_salary_monthly_gross_1' => $this->faker->randomFloat(2, 0, 10000),
            'base_salary_cost_center_2' => CostCenter::factory(),
            'base_salary_monthly_gross_2' => $this->faker->randomFloat(2, 0, 10000),
            'base_salary_cost_center_3' => CostCenter::factory(),
            'base_salary_monthly_gross_3' => $this->faker->randomFloat(2, 0, 10000),
            'health_allowance_cost_center_4' => CostCenter::factory(),
            'health_allowance_monthly_gross_4' => $this->faker->randomFloat(2, 0, 10000),
            'management_allowance_cost_center_5' => CostCenter::factory(),
            'management_allowance_monthly_gross_5' => $this->faker->randomFloat(2, 0, 10000),
            'management_allowance_end_date' => $this->faker->date(),
            'extra_pay_1_cost_center_6' => CostCenter::factory(),
            'extra_pay_1_monthly_gross_6' => $this->faker->randomFloat(2, 0, 10000),
            'extra_pay_1_end_date' => $this->faker->date(),
            'extra_pay_2_cost_center_7' => CostCenter::factory(),
            'extra_pay_2_monthly_gross_7' => $this->faker->randomFloat(2, 0, 10000),
            'extra_pay_2_end_date' => $this->faker->date(),
            'weekly_working_hours' => $this->faker->numberBetween(1, 40),
            'work_start_monday' => $this->faker->time(),
            'work_end_monday' => $this->faker->time(),
            'work_start_tuesday' => $this->faker->time(),
            'work_end_tuesday' => $this->faker->time(),
            'work_start_wednesday' => $this->faker->time(),
            'work_end_wednesday' => $this->faker->time(),
            'work_start_thursday' => $this->faker->time(),
            'work_end_thursday' => $this->faker->time(),
            'work_start_friday' => $this->faker->time(),
            'work_end_friday' => $this->faker->time(),
            'email' => $this->faker->unique()->safeEmail,
            'entry_permissions' => $this->faker->text(),
            'license_plate' => $this->faker->bothify('??###'),
            'employee_room' => $this->faker->bothify('Room ##'),
            'phone_extension' => $this->faker->numberBetween(100, 999),
            'required_tools' => $this->faker->word,
            'available_tools' => $this->faker->word,
            'inventory_numbers_of_available_tools' => $this->faker->text(1000),
            'planned_carcinogenic_materials_use' => $this->faker->text(),
            'personal_data_sheet' => $this->faker->word,
            'student_status_verification' => $this->faker->word,
            'certificates' => $this->faker->word,
            'requires_commute_support' => $this->faker->numberBetween(0, 1),
            'commute_support_form' => $this->faker->word,
        ]);
    }
}