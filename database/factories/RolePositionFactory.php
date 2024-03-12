<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RolePosition;

class RolePositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RolePosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_id' => Role::factory(),
            'position_id' => Position::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}