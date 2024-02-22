<?php

namespace Tests\Unit\Models;

use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_group_can_be_created()
    {
        $userGroup = UserGroup::factory()->create([
            'is_lead' => true,
        ]);

        $this->assertDatabaseHas('wf_user_group', [
            'user_id' => $userGroup->user_id,
            'group_id' => $userGroup->group_id,
            'is_lead' => true,
        ]);

        $this->assertTrue($userGroup->is_lead);
    }

    /** @test */
    public function user_group_can_be_updated()
    {
        $userGroup = UserGroup::factory()->create([
            'is_lead' => false,
        ]);

        UserGroup::where('user_id', $userGroup->user_id)
                  ->where('group_id', $userGroup->group_id)
                  ->update(['is_lead' => true]);

        $updatedUserGroup = UserGroup::where('user_id', $userGroup->user_id)
                                      ->where('group_id', $userGroup->group_id)
                                      ->first();

        $this->assertNotNull($updatedUserGroup);
        $this->assertTrue($updatedUserGroup->is_lead);

        $this->assertDatabaseHas('wf_user_group', [
            'user_id' => $userGroup->user_id,
            'group_id' => $userGroup->group_id,
            'is_lead' => true,
        ]);
    }

    /** @test */
    public function user_group_can_be_found()
    {
        $userGroup = UserGroup::factory()->create();

        $foundUserGroup = UserGroup::where('user_id', $userGroup->user_id)
                                    ->where('group_id', $userGroup->group_id)
                                    ->first();

        $this->assertNotNull($foundUserGroup);
        $this->assertEquals($userGroup->user_id, $foundUserGroup->user_id);
        $this->assertEquals($userGroup->group_id, $foundUserGroup->group_id);
    }
}