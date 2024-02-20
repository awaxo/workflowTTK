<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\RoleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_group_can_be_created()
    {
        $roleGroup = RoleGroup::factory()->create();

        $this->assertDatabaseHas('wf_role_group', [
            'id' => $roleGroup->id,
            'role_id' => $roleGroup->role_id,
            'group_id' => $roleGroup->group_id,
            'created_by' => $roleGroup->created_by,
            'updated_by' => $roleGroup->updated_by
        ]);
    }

    /** @test */
    public function a_role_group_can_be_updated()
    {
        $roleGroup = RoleGroup::factory()->create();
        $newRole = Role::factory()->create();

        $roleGroup->update(['role_id' => $newRole->id]);

        $this->assertEquals($newRole->id, $roleGroup->fresh()->role_id);
    }

    /** @test */
    public function user_group_can_be_found()
    {
        $roleGroup = RoleGroup::factory()->create();

        $foundRoleGroup = RoleGroup::find($roleGroup->id);

        $this->assertNotNull($foundRoleGroup);
        $this->assertEquals($roleGroup->id, $foundRoleGroup->id);
    }
}
