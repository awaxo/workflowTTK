<?php

namespace Tests\Unit\Models;

use App\Models\ApproverSubstitute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproverSubstituteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_approver_substitute_can_be_created()
    {
        $approverSubstitute = ApproverSubstitute::factory()->create([
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
        ]);

        $this->assertDatabaseHas('wf_approver_substitute', [
            'id' => $approverSubstitute->id,
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
        ]);
    }

    /** @test */
    public function an_approver_substitute_can_be_updated()
    {
        $approverSubstitute = ApproverSubstitute::factory()->create([
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
        ]);

        $approverSubstitute->update([
            'start_date' => '2021-01-01',
            'end_date' => '2021-12-31',
        ]);

        $this->assertDatabaseHas('wf_approver_substitute', [
            'id' => $approverSubstitute->id,
            'start_date' => '2021-01-01',
            'end_date' => '2021-12-31',
        ]);
    }

    /** @test */
    public function an_approver_substitute_relationships_can_be_accessed()
    {
        $approverSubstitute = ApproverSubstitute::factory()->create();

        $this->assertNotNull($approverSubstitute->originalApproverRoleGroup);
        $this->assertNotNull($approverSubstitute->substituteRoleGroup);
        $this->assertNotNull($approverSubstitute->createdBy);
        $this->assertNotNull($approverSubstitute->updatedBy);
    }
}