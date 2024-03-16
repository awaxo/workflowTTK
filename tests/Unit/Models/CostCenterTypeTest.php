<?php

namespace Tests\Unit\Models;

use App\Models\CostCenterType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CostCenterTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_cost_center_type_can_be_created()
    {
        $recruitCostCenterType = CostCenterType::factory()->create();

        $this->assertDatabaseHas('wf_cost_center_type', [
            'name' => $recruitCostCenterType->name,
            'tender' => $recruitCostCenterType->tender,
            'clause_template' => $recruitCostCenterType->clause_template,
            'created_by' => $recruitCostCenterType->created_by,
            'updated_by' => $recruitCostCenterType->updated_by,
            'deleted' => false,
        ]);
    }

    /** @test */
    public function a_recruit_cost_center_type_can_be_updated()
    {
        $recruitCostCenterType = CostCenterType::factory()->create();

        $recruitCostCenterType->update(['name' => 'Updated Type']);

        $this->assertEquals('Updated Type', $recruitCostCenterType->name);
    }

    /** @test */
    public function recruit_cost_center_type_relationships_are_accessible()
    {
        $recruitCostCenterType = CostCenterType::factory()->create();

        $this->assertNotNull($recruitCostCenterType->createdBy);
        $this->assertNotNull($recruitCostCenterType->updatedBy);
    }
}
