<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\EmployeeRecruitment\App\Models\RecruitWorkflowStepRights;

class RecruitWorkflowStepRightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitWorkflowStepRights::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workflow_step_id' => 1,
            'role_id' => Role::factory(),
            'custom_approval_rules' => $this->faker->text(500),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}