<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserPosition;

class UserPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPosition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'position_id' => Position::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}