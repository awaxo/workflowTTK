<?php

namespace Database\Factories;

use App\Models\ApproverSubstitute;
use App\Models\RoleGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApproverSubstituteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApproverSubstitute::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'original_approver_role_group_id' => RoleGroup::factory(),
            'substitute_role_group_id' => RoleGroup::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->dateTimeBetween('start_date', '+1 month')->format('Y-m-d'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}