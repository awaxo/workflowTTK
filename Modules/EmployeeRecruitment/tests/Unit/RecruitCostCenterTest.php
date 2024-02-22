<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitCostCenter;

class RecruitCostCenterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_cost_center_can_be_created()
    {
        $recruitCostCenter = RecruitCostCenter::factory()->create();

        $this->assertDatabaseHas('recruit_cost_center', [
            'cost_center_code' => $recruitCostCenter->cost_center_code,
            'name' => $recruitCostCenter->name,
            'minimal_order_limit' => $recruitCostCenter->minimal_order_limit,
            'workgroup_id' => $recruitCostCenter->workgroup_id,
            'lead_user_id' => $recruitCostCenter->lead_user_id,
            'project_coordinator_user_id' => $recruitCostCenter->project_coordinator_user_id,
            'due_date' => $recruitCostCenter->due_date,
            'minimal_order_limit' => $recruitCostCenter->minimal_order_limit,
            'deleted' => false,
            'created_by' => $recruitCostCenter->created_by,
            'updated_by' => $recruitCostCenter->updated_by,
        ]);
    }

    /** @test */
    public function a_recruit_cost_center_can_be_updated()
    {
        $recruitCostCenter = RecruitCostCenter::factory()->create([
            'name' => 'Original Name',
        ]);

        $recruitCostCenter->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('recruit_cost_center', [
            'id' => $recruitCostCenter->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function recruit_cost_center_relationships_are_accessible()
    {
        $recruitCostCenter = RecruitCostCenter::factory()->create();

        $this->assertNotNull($recruitCostCenter->workgroup);
        $this->assertNotNull($recruitCostCenter->leadUser);
        $this->assertNotNull($recruitCostCenter->projectCoordinatorUser);
        $this->assertNotNull($recruitCostCenter->createdBy);
        $this->assertNotNull($recruitCostCenter->updatedBy);
    }
}