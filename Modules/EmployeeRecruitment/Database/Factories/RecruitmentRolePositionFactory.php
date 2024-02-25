<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\Role;
use App\Models\Position;
use App\Models\User;
use Modules\EmployeeRecruitment\App\Models\RecruitmentRolePosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecruitmentRolePositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitmentRolePosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_id' => Role::factory(),
            'position_id' => Position::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}