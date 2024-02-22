<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\Role;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Models\RecruitCostCenterType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecruitCostCenterTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitCostCenterType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'tender' => $this->faker->boolean,
            'financial_approver_role_id' => Role::factory(),
            'clause_template' => $this->faker->paragraph,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}