<?php

namespace Tests\Unit;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_can_be_created()
    {
        Role::create([
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
        $role = Role::create([
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
        $role = Role::create([
            'name' => 'Admin',
            'description' => 'Administrator role',
        ]);

        $foundRole = Role::find($role->id);

        $this->assertEquals($foundRole->name, 'Admin');
    }

    /** @test */
    public function a_role_can_be_deleted()
    {
        $role = Role::create([
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