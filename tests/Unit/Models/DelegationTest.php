<?php

namespace Tests\Unit\Models;

use App\Models\Delegation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_substitution_can_be_created()
    {
        $delegation = Delegation::factory()->create();

        $this->assertDatabaseHas('wf_role_substitute', [
            'id' => $delegation->id,
            'original_user_id' => $delegation->original_user_id,
            'delegate_user_id' => $delegation->substitute_user_id,
            'start_date' => $delegation->start_date,
        ]);
    }

    /** @test */
    public function a_role_substitution_can_be_updated()
    {
        $delegation = Delegation::factory()->create();
        $newRoleId = 2;

        $delegation->update([
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31'
        ]);

        $this->assertEquals('2023-01-01', $delegation->start_date->toDateString());
        $this->assertEquals('2023-12-31', $delegation->end_date->toDateString());
    }

    /** @test */
    public function role_substitution_relations_are_accessible()
    {
        $delegation = Delegation::factory()->create();

        $this->assertNotNull($delegation->originalUser);
        $this->assertInstanceOf(User::class, $delegation->originalUser);

        $this->assertNotNull($delegation->delegateUser);
        $this->assertInstanceOf(User::class, $delegation->delegateUser);
    }
}
