<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitCostCenterType;

class RecruitCostCenterTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_cost_center_type_can_be_created()
    {
        $recruitCostCenterType = RecruitCostCenterType::factory()->create();

        $this->assertDatabaseHas('recruit_cost_center_type', [
            'name' => $recruitCostCenterType->name,
            'tender' => $recruitCostCenterType->tender,
            'financial_approver_role_id' => $recruitCostCenterType->financial_approver_role_id,
            'clause_template' => $recruitCostCenterType->clause_template,
            'created_by' => $recruitCostCenterType->created_by,
            'updated_by' => $recruitCostCenterType->updated_by,
            'deleted' => false,
        ]);
    }

    /** @test */
    public function a_recruit_cost_center_type_can_be_updated()
    {
        $recruitCostCenterType = RecruitCostCenterType::factory()->create([
            'name' => 'Original Type',
        ]);

        $updatedName = 'Updated Type';
        $recruitCostCenterType->update(['name' => $updatedName]);

        $this->assertDatabaseHas('recruit_cost_center_type', [
            'name' => $updatedName,
        ]);
    }

    /** @test */
    public function recruit_cost_center_type_relationships_are_accessible()
    {
        $recruitCostCenterType = RecruitCostCenterType::factory()->create();

        $this->assertNotNull($recruitCostCenterType->financialApproverRole);
        $this->assertNotNull($recruitCostCenterType->createdBy);
        $this->assertNotNull($recruitCostCenterType->updatedBy);
    }
}
