<?php

namespace Tests\Unit\Models;

use App\Models\LaborAdministrator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborAdministratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_labor_administrator_can_be_created()
    {
        $laborAdministrator = LaborAdministrator::factory()->create();

        $this->assertDatabaseHas('wf_labor_administrator', [
            'id' => $laborAdministrator->id,
            'group_level' => $laborAdministrator->group_level,
            'name' => $laborAdministrator->name,
            'created_by' => $laborAdministrator->created_by,
            'updated_by' => $laborAdministrator->updated_by
        ]);
    }

    /** @test */
    public function a_labor_administrator_can_be_updated()
    {
        $laborAdministrator = LaborAdministrator::factory()->create();
        $newName = 'Updated Name';

        $laborAdministrator->update(['name' => $newName]);

        $this->assertEquals($newName, $laborAdministrator->fresh()->name);
    }

    /** @test */
    public function labor_administrator_relations_are_accessible()
    {
        $laborAdministrator = LaborAdministrator::factory()->create();

        $this->assertNotNull($laborAdministrator->createdBy);
        $this->assertInstanceOf(User::class, $laborAdministrator->createdBy);

        $this->assertNotNull($laborAdministrator->updatedBy);
        $this->assertInstanceOf(User::class, $laborAdministrator->updatedBy);
    }
}
