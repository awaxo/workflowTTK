<?php

namespace Tests\Unit\Models;

use App\Models\LaborAdministrator;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkgroupTest extends TestCase
{
    use RefreshDatabase;

    protected $leader;
    protected $laborAdministrator;
    protected $createdBy;
    protected $updatedBy;

    public function setUp(): void
    {
        parent::setUp();

        // Create users for each role
        $this->leader = User::factory()->create();
        $this->laborAdministrator = LaborAdministrator::factory()->create();
        $this->createdBy = User::factory()->create();
        $this->updatedBy = User::factory()->create();
    }

    /** @test */
    public function a_workgroup_can_be_created()
    {
        $workgroup = Workgroup::factory()->create([
            'leader' => $this->leader->id,
            'labor_administrator' => $this->laborAdministrator->id,
            'created_by' => $this->createdBy->id,
            'updated_by' => $this->updatedBy->id,
        ]);

        $this->assertDatabaseHas('wf_workgroup', [
            'leader' => $this->leader->id,
            'labor_administrator' => $this->laborAdministrator->id,
            'created_by' => $this->createdBy->id,
            'updated_by' => $this->updatedBy->id,
        ]);
    }

    /** @test */
    public function a_workgroup_can_be_updated()
    {
        $workgroup = Workgroup::factory()->create([
            'name' => 'Initial Name',
        ]);

        $newName = 'Updated Name';
        $workgroup->update([
            'name' => $newName,
        ]);

        $this->assertDatabaseHas('wf_workgroup', [
            'id' => $workgroup->id,
            'name' => $newName,
        ]);
    }

    /** @test */
    public function workgroup_relationships_are_accessible()
    {
        $workgroup = Workgroup::factory()->create();

        $this->assertNotNull($workgroup->leader);
        $this->assertNotNull($workgroup->laborAdministrator);
        $this->assertNotNull($workgroup->createdBy);
        $this->assertNotNull($workgroup->updatedBy);
    }
}
