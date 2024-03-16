<?php

namespace Tests\Unit\Models;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstituteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_institution_can_be_created()
    {
        $institution = Institute::factory()->create();

        $this->assertDatabaseHas('wf_institute', [
            'id' => $institution->id,
            'group_level' => $institution->group_level,
            'name' => $institution->name,
            'labor_administrator' => $institution->labor_administrator,
            'created_by' => $institution->created_by,
            'updated_by' => $institution->updated_by
        ]);
    }

    /** @test */
    public function an_institution_can_be_updated()
    {
        $institution = Institute::factory()->create();
        $newGroupLevel = '0';

        $institution->update(['group_level' => $newGroupLevel, 'name' => 'New Name']);

        $this->assertEquals([$newGroupLevel, 'New Name'], [$institution->group_level, $institution->name]);
    }

    /** @test */
    public function institution_relations_are_accessible()
    {
        $institution = Institute::factory()->create();

        $this->assertNotNull($institution->createdBy);
        $this->assertInstanceOf(User::class, $institution->createdBy);

        $this->assertNotNull($institution->updatedBy);
        $this->assertInstanceOf(User::class, $institution->updatedBy);
    }
}
