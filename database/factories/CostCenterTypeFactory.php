<?php

namespace Database\Factories;

use App\Models\WfRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CostCenterType;

class CostCenterTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CostCenterType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'tender' => $this->faker->boolean,
            'clause_template' => $this->faker->paragraph,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}