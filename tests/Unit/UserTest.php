<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_be_created()
    {
        $creator = User::factory()->create();

        User::create([
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);

        $this->assertDatabaseHas('wf_user', [
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);
    }

    /** @test */
    public function a_user_can_be_updated()
    {
        $creator = User::factory()->create();
        $updater = User::factory()->create();

        $user = User::create([
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);

        $user->update(['first_name' => 'Jane', 'updated_by' => $updater->id]);

        $this->assertDatabaseHas('wf_user', [
            'first_name' => 'Jane',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'updated_by' => $updater->id,
        ]);
    }

    /** @test */
    public function a_user_can_be_read()
    {
        $creator = User::factory()->create();

        $user = User::create([
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);

        $foundUser = User::find($user->id);

        $this->assertEquals($foundUser->first_name, 'John');
        $this->assertEquals($foundUser->middle_name, 'M');
    }

    /** @test */
    public function a_user_can_be_deleted()
    {
        $creator = User::factory()->create();

        $user = User::create([
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'created_by' => $creator->id,
            'updated_by' => $creator->id,
        ]);

        $user->delete();

        $this->assertDatabaseMissing('wf_user', [
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);
    }
}