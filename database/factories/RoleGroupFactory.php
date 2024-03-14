<?php

namespace Database\Factories;

use App\Models\RoleGroup;
use App\Models\User;
use App\Models\WfRole;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoleGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role_id' => WfRole::factory(),
            'group_id' => Group::factory(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}