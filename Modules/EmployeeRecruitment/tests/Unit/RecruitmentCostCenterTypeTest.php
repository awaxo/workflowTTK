<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitmentCostCenterType;

class RecruitmentCostCenterTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_cost_center_type_can_be_created()
    {
        $recruitCostCenterType = RecruitmentCostCenterType::factory()->create();

        $this->assertDatabaseHas('recruitment_cost_center_type', [
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
        $recruitCostCenterType = RecruitmentCostCenterType::factory()->create([
            'name' => 'Original Type',
        ]);

        $updatedName = 'Updated Type';
        $recruitCostCenterType->update(['name' => $updatedName]);

        $this->assertDatabaseHas('recruitment_cost_center_type', [
            'name' => $updatedName,
        ]);
    }

    /** @test */
    public function recruit_cost_center_type_relationships_are_accessible()
    {
        $recruitCostCenterType = RecruitmentCostCenterType::factory()->create();

        $this->assertNotNull($recruitCostCenterType->financialApproverRole);
        $this->assertNotNull($recruitCostCenterType->createdBy);
        $this->assertNotNull($recruitCostCenterType->updatedBy);
    }
}
