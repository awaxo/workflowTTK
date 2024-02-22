<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\User;
use App\Models\Workgroup;
use Modules\EmployeeRecruitment\App\Models\RecruitCostCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecruitCostCenterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitCostCenter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cost_center_code' => $this->faker->unique()->word,
            'workgroup_id' => Workgroup::factory(),
            'name' => $this->faker->company,
            'type' => $this->faker->word,
            'lead_user_id' => User::factory(),
            'project_coordinator_user_id' => User::factory(),
            'due_date' => $this->faker->date(),
            'minimal_order_limit' => $this->faker->randomFloat(2, 0, 10000),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}