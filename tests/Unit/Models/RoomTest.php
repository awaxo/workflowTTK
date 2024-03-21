<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_room_can_be_created()
    {
        $workgroup = Workgroup::factory()->create();

        $room = Room::factory()->create([
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101'
        ]);

        $this->assertDatabaseHas('wf_room', [
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101'
        ]);
    }

    /** @test */
    public function a_room_can_be_updated()
    {
        $workgroup = Workgroup::factory()->create();
        $room = Room::factory()->create([
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101',
        ]);

        $room->update(['room_number' => 'Room 102']);

        $this->assertDatabaseHas('wf_room', [
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 102',
        ]);
    }

    /** @test */
    public function a_room_can_be_read()
    {
        $workgroup = Workgroup::factory()->create();
        $room = Room::factory()->create([
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101',
        ]);

        $foundRoom = Room::find($room->workgroup_number)->first();

        $this->assertEquals($foundRoom->room_number, 'Room 101');
    }

    /** @test */
    public function a_room_can_be_deleted()
    {
        $workgroup = Workgroup::factory()->create();
        $room = Room::factory()->create([
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101',
        ]);

        $room->delete();

        $this->assertDatabaseMissing('wf_room', [
            'room_number' => 'Room 101',
        ]);
    }
}
