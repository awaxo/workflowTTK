<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserWorkgroup;
use App\Models\Workgroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserWorkgroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserWorkgroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'workgroup_id' => Workgroup::factory(),
            'is_lead' => $this->faker->boolean,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}