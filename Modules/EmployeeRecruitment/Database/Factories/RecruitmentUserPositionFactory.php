<?php

namespace Modules\EmployeeRecruitment\Database\Factories;

use App\Models\User;
use App\Models\Position;
use Modules\EmployeeRecruitment\App\Models\RecruitmentUserPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecruitmentUserPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RecruitmentUserPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'position_id' => Position::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}