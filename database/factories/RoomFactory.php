<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Workgroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'workgroup_id' => Workgroup::factory(),
            'room_number' => $this->faker->numerify('Room ###'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
