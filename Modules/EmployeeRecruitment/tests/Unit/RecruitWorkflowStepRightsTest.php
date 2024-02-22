<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitWorkflowStepRights;

class RecruitWorkflowStepRightsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_workflow_step_right_can_be_created()
    {
        $recruitWorkflowStepRight = RecruitWorkflowStepRights::factory()->create();

        $this->assertDatabaseHas('recruit_workflow_step_rights', [
            'workflow_step_id' => $recruitWorkflowStepRight->workflow_step_id,
            'role_id' => $recruitWorkflowStepRight->role_id,
            'custom_approval_rules' => $recruitWorkflowStepRight->custom_approval_rules,
            'created_by' => $recruitWorkflowStepRight->created_by,
            'updated_by' => $recruitWorkflowStepRight->updated_by,
        ]);
    }

    /** @test */
    public function a_recruit_workflow_step_right_can_be_updated()
    {
        $recruitWorkflowStepRight = RecruitWorkflowStepRights::factory()->create([
            'custom_approval_rules' => 'Original approval rules',
        ]);

        $updatedRules = 'Updated approval rules';
        $recruitWorkflowStepRight->update(['custom_approval_rules' => $updatedRules]);

        $this->assertDatabaseHas('recruit_workflow_step_rights', [
            'id' => $recruitWorkflowStepRight->id,
            'custom_approval_rules' => $updatedRules,
        ]);
    }

    /** @test */
    public function recruit_workflow_step_right_relationships_are_accessible()
    {
        $recruitWorkflowStepRight = RecruitWorkflowStepRights::factory()->create();

        $this->assertNotNull($recruitWorkflowStepRight->role);
        $this->assertNotNull($recruitWorkflowStepRight->createdBy);
        $this->assertNotNull($recruitWorkflowStepRight->updatedBy);
    }
}
