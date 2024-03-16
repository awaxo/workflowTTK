<?php

namespace Database\Factories;

use App\Models\Workgroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkgroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Workgroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workgroup_number' => $this->faker->unique()->randomNumber(3),
            'name' => $this->faker->company,
            'leader' => User::factory(),
            'deleted' => $this->faker->boolean,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}