<?php

namespace Tests\Unit;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_option_can_be_created()
    {
        $option = Option::create([
            'option_name' => 'site_title',
            'option_value' => 'My Amazing Website',
        ]);

        $this->assertDatabaseHas('wf_options', [
            'option_name' => 'site_title',
            'option_value' => 'My Amazing Website',
        ]);
    }

    /** @test */
    public function an_option_can_be_updated()
    {
        $option = Option::create([
            'option_name' => 'site_title',
            'option_value' => 'My Amazing Website',
        ]);

        $option->update([
            'option_value' => 'My Even More Amazing Website',
        ]);

        $this->assertDatabaseHas('wf_options', [
            'option_name' => 'site_title',
            'option_value' => 'My Even More Amazing Website',
        ]);
    }

    /** @test */
    public function an_option_can_be_retrieved_by_name()
    {
        Option::create([
            'option_name' => 'site_title',
            'option_value' => 'My Amazing Website',
        ]);

        $option = Option::where('option_name', 'site_title')->first();

        $this->assertNotNull($option);
        $this->assertEquals('My Amazing Website', $option->option_value);
    }
}
