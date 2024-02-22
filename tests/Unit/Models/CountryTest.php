<?php

namespace Tests\Unit\Models;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_country_can_be_created()
    {
        $country = Country::factory()->create();

        $this->assertDatabaseHas('wf_country', [
            'name' => $country->name,
            'created_by' => $country->created_by,
            'updated_by' => $country->updated_by,
        ]);
    }

    /** @test */
    public function a_country_can_be_updated()
    {
        $country = Country::factory()->create([
            'name' => 'Initial Name',
        ]);

        $newName = 'Updated Name';
        $country->update([
            'name' => $newName,
        ]);

        $this->assertDatabaseHas('wf_country', [
            'id' => $country->id,
            'name' => $newName,
        ]);
    }

    /** @test */
    public function a_country_relationships_are_accessible()
    {
        $country = Country::factory()->create();

        $this->assertNotNull($country->createdBy);
        $this->assertNotNull($country->updatedBy);
    }
}