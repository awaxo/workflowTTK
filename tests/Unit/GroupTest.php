<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_group_can_be_created()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Test Group',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $this->assertDatabaseHas('wf_group', [
            'name' => 'Test Group',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted' => false
        ]);
    }

    /** @test */
    public function a_group_can_be_updated()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Test Group',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $group->update(['name' => 'Updated Group']);

        $this->assertDatabaseHas('wf_group', [
            'name' => 'Updated Group',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'deleted' => false
        ]);
    }

    /** @test */
    public function a_group_can_be_read()
    {
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Test Group',
            'created_by' => $user->id,
            'updated_by' => $user->id
        ]);

        $foundGroup = Group::find($group->id);

        $this->assertEquals($group->name, $foundGroup->name);
        $this->assertEquals($group->created_by, $foundGroup->created_by);
        $this->assertEquals($group->updated_by, $foundGroup->updated_by);
        $this->assertEquals(false, $foundGroup->deleted);
    }
}