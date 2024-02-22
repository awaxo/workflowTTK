<?php

namespace Tests\Unit\Models;

use App\Models\ExternalAccessRight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalAccessRightTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function external_access_rights_can_be_created()
    {
        $externalAccessRight = ExternalAccessRight::factory()->create();

        $this->assertDatabaseHas('wf_external_access_rights', [
            'external_system' => $externalAccessRight->external_system,
            'admin_group_number' => $externalAccessRight->admin_group_number,
            'deleted' => false,
            'created_by' => $externalAccessRight->created_by,
            'updated_by' => $externalAccessRight->updated_by,
        ]);
    }

    /** @test */
    public function external_access_rights_can_be_updated()
    {
        $externalAccessRight = ExternalAccessRight::factory()->create([
            'external_system' => 'System A',
        ]);

        $externalAccessRight->update([
            'external_system' => 'System B',
        ]);

        $this->assertDatabaseHas('wf_external_access_rights', [
            'id' => $externalAccessRight->id,
            'external_system' => 'System B',
        ]);
    }

    /** @test */
    public function external_access_rights_relationships_are_accessible()
    {
        $externalAccessRight = ExternalAccessRight::factory()->create();

        $this->assertNotNull($externalAccessRight->createdBy);
        $this->assertNotNull($externalAccessRight->updatedBy);
        $this->assertNotNull($externalAccessRight->workgroup);
    }
}
