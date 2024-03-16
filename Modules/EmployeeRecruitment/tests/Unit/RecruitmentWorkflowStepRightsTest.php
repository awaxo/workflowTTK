<?php

namespace Modules\EmployeeRecruitment\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflowStepRights;

class RecruitmentWorkflowStepRightsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recruit_workflow_step_right_can_be_created()
    {
        $recruitWorkflowStepRight = RecruitmentWorkflowStepRights::factory()->create();

        $this->assertDatabaseHas('recruitment_workflow_step_rights', [
            'workflow_step_id' => $recruitWorkflowStepRight->workflow_step_id,
            'custom_approval_rules' => $recruitWorkflowStepRight->custom_approval_rules,
            'created_by' => $recruitWorkflowStepRight->created_by,
            'updated_by' => $recruitWorkflowStepRight->updated_by,
        ]);
    }

    /** @test */
    public function a_recruit_workflow_step_right_can_be_updated()
    {
        $recruitWorkflowStepRight = RecruitmentWorkflowStepRights::factory()->create([
            'custom_approval_rules' => 'Original approval rules',
        ]);

        $updatedRules = 'Updated approval rules';
        $recruitWorkflowStepRight->update(['custom_approval_rules' => $updatedRules]);

        $this->assertDatabaseHas('recruitment_workflow_step_rights', [
            'id' => $recruitWorkflowStepRight->id,
            'custom_approval_rules' => $updatedRules,
        ]);
    }

    /** @test */
    public function recruit_workflow_step_right_relationships_are_accessible()
    {
        $recruitWorkflowStepRight = RecruitmentWorkflowStepRights::factory()->create();

        $this->assertNotNull($recruitWorkflowStepRight->createdBy);
        $this->assertNotNull($recruitWorkflowStepRight->updatedBy);
    }
}
