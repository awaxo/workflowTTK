<?php

namespace Database\Factories;

use App\Models\LaborAdministrator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaborAdministratorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LaborAdministrator::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_level' => $this->faker->numberBetween(1, 5),
            'user_id' => User::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
