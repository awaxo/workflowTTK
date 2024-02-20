<?php

namespace Database\Factories;

use App\Models\ExternalAccessRight;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalAccessRightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExternalAccessRight::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'external_system' => $this->faker->word,
            'admin_group_number' => Workgroup::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}