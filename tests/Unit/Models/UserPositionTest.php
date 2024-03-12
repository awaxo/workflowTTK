<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\UserPosition;

class UserPositionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_user_position_can_be_created()
    {
        $recruitUserPosition = UserPosition::factory()->create();

        $this->assertDatabaseHas('wf_user_position', [
            'user_id' => $recruitUserPosition->user_id,
            'position_id' => $recruitUserPosition->position_id,
            'created_by' => $recruitUserPosition->created_by,
            'updated_by' => $recruitUserPosition->updated_by,
            'deleted' => false,
        ]);
    }

    /** @test */
    public function recruit_user_position_relationships_are_accessible()
    {
        $recruitUserPosition = UserPosition::factory()->create();

        $this->assertNotNull($recruitUserPosition->user);
        $this->assertNotNull($recruitUserPosition->position);
        $this->assertNotNull($recruitUserPosition->createdBy);
        $this->assertNotNull($recruitUserPosition->updatedBy);
    }
}
