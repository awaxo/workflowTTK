<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkflowType;
use Illuminate\Database\Eloquent\Factories\Factory;

abstract class AbstractWorkflowFactory extends Factory
{
    public function definition()
    {
        return [
            'workflow_type_id' => WorkflowType::factory(),
            'workflow_deadline' => 24, // 24hrs
            'state' => $this->faker->randomElement(['pending', 'completed', 'in_progress']),
            'meta_key' => $this->faker->word,
            'meta_value' => $this->faker->word,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}