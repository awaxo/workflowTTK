<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitmentRolePosition;

class RecruitmentRolePositionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_role_position_can_be_created()
    {
        $recruitRolePosition = RecruitmentRolePosition::factory()->create();

        $this->assertDatabaseHas('recruitment_role_position', [
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
        $recruitRolePosition = RecruitmentRolePosition::factory()->create();

        $this->assertNotNull($recruitRolePosition->role);
        $this->assertNotNull($recruitRolePosition->position);
        $this->assertNotNull($recruitRolePosition->createdBy);
        $this->assertNotNull($recruitRolePosition->updatedBy);
    }
}
