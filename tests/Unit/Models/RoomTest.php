<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use App\Models\Workgroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        DB::table('wf_room')
            ->where('workgroup_number', $workgroup->workgroup_number)
            ->where('room_number', 'Room 101')
            ->update(['room_number' => 'Room 102']);

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

        $foundRoom = Room::where('workgroup_number', $room->workgroup_number)
                         ->where('room_number', 'Room 101')
                         ->first();

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

        DB::table('wf_room')
            ->where('workgroup_number', $workgroup->workgroup_number)
            ->where('room_number', 'Room 101')
            ->delete();

        $this->assertDatabaseMissing('wf_room', [
            'workgroup_number' => $workgroup->workgroup_number,
            'room_number' => 'Room 101',
        ]);
    }
}
