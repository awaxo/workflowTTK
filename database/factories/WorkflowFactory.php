<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Workflow::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workflow_type_id' => WorkflowType::factory(),
            'workflow_deadline' => 24, // 24hrs
            'status' => $this->faker->randomElement(['pending', 'completed', 'in_progress']),
            'meta_key' => $this->faker->word,
            'meta_value' => $this->faker->word,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}