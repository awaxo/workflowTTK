<?php

namespace Tests\Unit\Models;

use App\Models\WfRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WfRoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_can_be_created()
    {
        WfRole::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $this->assertDatabaseHas('wf_role', [
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);
    }

    /** @test */
    public function a_role_can_be_updated()
    {
        $role = WfRole::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $role->update(['name' => 'Super Admin']);

        $this->assertDatabaseHas('wf_role', [
            'name' => 'Super Admin',
            'description' => 'Administrator role',
        ]);
    }

    /** @test */
    public function a_role_can_be_read()
    {
        $role = WfRole::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $foundRole = WfRole::find($role->id);

        $this->assertEquals($foundRole->name, 'Admin');
    }

    /** @test */
    public function a_role_can_be_deleted()
    {
        $role = WfRole::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $role->delete();

        $this->assertDatabaseMissing('wf_role', [
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);
    }
}