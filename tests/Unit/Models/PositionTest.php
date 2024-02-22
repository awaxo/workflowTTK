<?php

namespace Tests\Unit\Models;

use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_position_can_be_created()
    {
        $user = User::factory()->create();
        $position = Position::factory()->create([
            'name' => 'Test Position',
            'type' => 'Test Type',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $this->assertDatabaseHas('wf_position', [
            'name' => 'Test Position',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted' => false
        ]);
    }

    /** @test */
    public function a_position_can_be_updated()
    {
        $user = User::factory()->create();
        $position = Position::factory()->create([
            'name' => 'Test Position',
            'type' => 'Test Type',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $position->update(['name' => 'Updated Position']);

        $this->assertDatabaseHas('wf_position', [
            'name' => 'Updated Position',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted' => false
        ]);
    }

    /** @test */
    public function a_position_can_be_read()
    {
        $user = User::factory()->create();
        $position = Position::factory()->create([
            'name' => 'Test Position',
            'type' => 'Test Type',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $foundPosition = Position::find($position->id);

        $this->assertEquals($position->name, $foundPosition->name);
        $this->assertEquals($position->created_by, $foundPosition->created_by);
        $this->assertEquals($position->updated_by, $foundPosition->updated_by);
        $this->assertEquals(false, $foundPosition->deleted);
    }
}