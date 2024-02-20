<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkflowType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkflowType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'meta_key' => $this->faker->word,
            'meta_value' => $this->faker->word,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}