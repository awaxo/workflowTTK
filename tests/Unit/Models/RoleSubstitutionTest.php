<?php

namespace Tests\Unit\Models;

use App\Models\RoleSubstitute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSubstitutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_substitution_can_be_created()
    {
        $roleSubstitution = RoleSubstitute::factory()->create();

        $this->assertDatabaseHas('wf_role_substitute', [
            'id' => $roleSubstitution->id,
            'original_user_id' => $roleSubstitution->original_user_id,
            'substitute_user_id' => $roleSubstitution->substitute_user_id,
            'start_date' => $roleSubstitution->start_date,
        ]);
    }

    /** @test */
    public function a_role_substitution_can_be_updated()
    {
        $roleSubstitution = RoleSubstitute::factory()->create();
        $newRoleId = 2;

        $roleSubstitution->update([
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31'
        ]);

        $this->assertEquals('2023-01-01', $roleSubstitution->start_date->toDateString());
        $this->assertEquals('2023-12-31', $roleSubstitution->end_date->toDateString());
    }

    /** @test */
    public function role_substitution_relations_are_accessible()
    {
        $roleSubstitution = RoleSubstitute::factory()->create();

        $this->assertNotNull($roleSubstitution->originalUser);
        $this->assertInstanceOf(User::class, $roleSubstitution->originalUser);

        $this->assertNotNull($roleSubstitution->substituteUser);
        $this->assertInstanceOf(User::class, $roleSubstitution->substituteUser);
    }
}
