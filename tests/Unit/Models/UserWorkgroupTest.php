<?php

namespace Tests\Unit\Models;

use App\Models\UserWorkgroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserWorkgroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_workgroup_can_be_created()
    {
        $userWorkgroup = UserWorkgroup::factory()->create([
            'is_lead' => true,
        ]);

        $this->assertDatabaseHas('wf_user_workgroup', [
            'user_id' => $userWorkgroup->user_id,
            'workgroup_id' => $userWorkgroup->workgroup_id,
            'is_lead' => true,
        ]);

        $this->assertTrue($userWorkgroup->is_lead);
    }

    /** @test */
    public function user_workgroup_can_be_updated()
    {
        $userWorkgroup = UserWorkgroup::factory()->create([
            'is_lead' => false,
        ]);

        UserWorkgroup::where('user_id', $userWorkgroup->user_id)
                  ->where('workgroup_id', $userWorkgroup->workgroup_id)
                  ->update(['is_lead' => true]);

        $updatedUserWorkgroup = UserWorkgroup::where('user_id', $userWorkgroup->user_id)
                                      ->where('workgroup_id', $userWorkgroup->workgroup_id)
                                      ->first();

        $this->assertNotNull($updatedUserWorkgroup);
        $this->assertTrue($updatedUserWorkgroup->is_lead);

        $this->assertDatabaseHas('wf_user_workgroup', [
            'user_id' => $userWorkgroup->user_id,
            'workgroup_id' => $userWorkgroup->workgroup_id,
            'is_lead' => true,
        ]);
    }

    /** @test */
    public function user_workgroup_can_be_found()
    {
        $userWorkgroup = UserWorkgroup::factory()->create();

        $foundUserWorkgroup = UserWorkgroup::where('user_id', $userWorkgroup->user_id)
                                    ->where('workgroup_id', $userWorkgroup->workgroup_id)
                                    ->first();

        $this->assertNotNull($foundUserWorkgroup);
        $this->assertEquals($userWorkgroup->user_id, $foundUserWorkgroup->user_id);
        $this->assertEquals($userWorkgroup->workgroup_id, $foundUserWorkgroup->workgroup_id);
    }
}