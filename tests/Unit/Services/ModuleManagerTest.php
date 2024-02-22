<?php

namespace Tests\Unit\Services;

use App\Services\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleManagerTest extends TestCase
{
    use RefreshDatabase;

    protected $moduleManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->moduleManager = new ModuleManager();
    }

    /** @test */
    public function module_can_be_registered()
    {
        $this->moduleManager->registerModule('test', 'TestProvider');

        $this->assertTrue($this->moduleManager->isRegistered('test'));
    }

    /** @test */
    public function module_is_enabled_by_default()
    {
        $this->moduleManager->registerModule('test', 'TestProvider');

        $this->assertTrue($this->moduleManager->isEnabled('test'));
    }

    /** @test */
    public function module_can_be_disabled()
    {
        $this->moduleManager->registerModule('test', 'TestProvider');
        $this->moduleManager->disableModule('test');

        $this->assertFalse($this->moduleManager->isEnabled('test'));
    }

    /** @test */
    public function module_can_be_enabled()
    {
        $this->moduleManager->registerModule('test', 'TestProvider');
        $this->moduleManager->disableModule('test');
        $this->moduleManager->enableModule('test');

        $this->assertTrue($this->moduleManager->isEnabled('test'));
    }

    /** @test */
    public function get_registered_modules()
    {
        $this->moduleManager->registerModule('test', 'TestProvider');

        $this->assertEquals(['test'], $this->moduleManager->getRegisteredModules());
    }
}