<?php

namespace Database\Factories;

use App\Models\CostCenter;
use App\Models\CostCenterType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CostCenterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CostCenter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cost_center_code' => $this->faker->word,
            'name' => $this->faker->company,
            'type_id' => CostCenterType::factory(),
            'lead_user_id' => User::factory(),
            'project_coordinator_user_id' => User::factory(),
            'due_date' => $this->faker->date(),
            'minimal_order_limit' => $this->faker->randomFloat(2, 0, 10000),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}