<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\RolePosition;

class RolePositionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_role_position_can_be_created()
    {
        $recruitRolePosition = RolePosition::factory()->create();

        $this->assertDatabaseHas('wf_role_position', [
            'role_id' => $recruitRolePosition->role_id,
            'position_id' => $recruitRolePosition->position_id,
            'created_by' => $recruitRolePosition->created_by,
            'updated_by' => $recruitRolePosition->updated_by,
            'deleted' => false,
        ]);
    }

    /** @test */
    public function recruit_role_position_relationships_are_accessible()
    {
        $recruitRolePosition = RolePosition::factory()->create();

        $this->assertNotNull($recruitRolePosition->role);
        $this->assertNotNull($recruitRolePosition->position);
        $this->assertNotNull($recruitRolePosition->createdBy);
        $this->assertNotNull($recruitRolePosition->updatedBy);
    }
}
