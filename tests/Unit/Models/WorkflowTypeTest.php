<?php

namespace Tests\Unit\Models;

use App\Models\WorkflowType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_workflow_type_can_be_created()
    {
        $workflowType = WorkflowType::factory()->create();

        $this->assertDatabaseHas('wf_workflow_type', [
            'name' => $workflowType->name,
            'description' => $workflowType->description,
            'meta_key' => $workflowType->meta_key,
            'meta_value' => $workflowType->meta_value,
        ]);
    }

    /** @test */
    public function a_workflow_type_can_be_updated()
    {
        $workflowType = WorkflowType::factory()->create();

        $newName = 'Updated Workflow';
        $workflowType->update(['name' => $newName]);

        $this->assertDatabaseHas('wf_workflow_type', [
            'id' => $workflowType->id,
            'name' => $newName,
        ]);
    }

    /** @test */
    public function workflow_type_relationships_are_accessible()
    {
        $workflowType = WorkflowType::factory()->create();

        $this->assertNotNull($workflowType->createdBy);
        $this->assertNotNull($workflowType->updatedBy);
    }
}