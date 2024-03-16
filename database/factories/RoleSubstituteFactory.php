<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\RoleSubstitute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleSubstituteFactory extends Factory
{
    protected $model = RoleSubstitute::class;

    public function definition()
    {
        return [
            'original_user_id' => User::factory(),
            'substitute_user_id' => User::factory(),
            'role_id' => Role::factory(),
            'start_date' => $this->faker->date(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}