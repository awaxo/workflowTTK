<?php

namespace Tests\Unit;

use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_workflow_can_be_created()
    {
        $workflow = Workflow::factory()->create([
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('wf_workflow', [
            'status' => 'active',
            'workflow_type_id' => $workflow->workflow_type_id,
            'workflow_deadline' => $workflow->workflow_deadline,
            'meta_key' => $workflow->meta_key,
            'meta_value' => $workflow->meta_value,
            'created_by' => $workflow->created_by,
            'updated_by' => $workflow->updated_by,
        ]);
    }

    /** @test */
    public function a_workflow_can_be_updated()
    {
        $workflow = Workflow::factory()->create();

        $newStatus = 'completed';
        $workflow->update(['status' => $newStatus]);

        $this->assertDatabaseHas('wf_workflow', [
            'id' => $workflow->id,
            'status' => $newStatus,
        ]);
    }

    /** @test */
    public function workflow_relationships_are_accessible()
    {
        $workflow = Workflow::factory()->create();

        $this->assertNotNull($workflow->workflowType);
        $this->assertNotNull($workflow->createdBy);
        $this->assertNotNull($workflow->updatedBy);
    }
}