<?php

namespace Database\Factories;

use App\Models\UserGroup;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'group_id' => Group::factory(),
            'is_lead' => $this->faker->boolean,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}