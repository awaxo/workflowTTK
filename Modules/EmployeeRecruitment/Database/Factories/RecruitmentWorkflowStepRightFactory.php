<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\WfRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflowStepRights;

class RecruitmentWorkflowStepRightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitmentWorkflowStepRights::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workflow_step_id' => 1,
            'role_id' => WfRole::factory(),
            'custom_approval_rules' => $this->faker->text(500),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}