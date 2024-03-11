<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_be_created()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('wf_user', [
            'name' => $user->name,
            'email' => $user->email,
            'workgroup_id' => $user->workgroup_id,
            'created_by' => null,
            'updated_by' => null,
        ]);
    }

    /** @test */
    public function a_user_can_be_updated()
    {
        $updater = User::factory()->create();

        $user = User::factory()->create();

        $user->update(['name' => 'Jane Doe', 'updated_by' => $updater->id]);

        $this->assertDatabaseHas('wf_user', [
            'name' => 'Jane Doe',
            'updated_by' => $updater->id,
        ]);
    }

    /** @test */
    public function a_user_can_be_read()
    {
        $user = User::factory()->create();

        $foundUser = User::find($user->id);

        $this->assertEquals($foundUser->first_name, $user->first_name);
        $this->assertEquals($foundUser->middle_name, $user->middle_name);
    }
}