<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/* Még kérdés ennek az osztálynak a szerepe, az nwidart/laravel-modules miatt másképp kell majd megoldani a modul engedélyezést/tiltást */
class ModuleManager
{
    protected $modules = [];

    public function registerModule($name, $provider)
    {
        $this->modules[$name] = $provider;
        
        // Module is disabled by default
        $this->enableModule($name);
    }

    public function isEnabled($name)
    {
        $module = DB::table('wf_modules')->where('name', $name)->first();
        return $module ? (bool)$module->is_enabled : false;
    }

    public function enableModule($name)
    {
        DB::table('wf_modules')->updateOrInsert(['name' => $name], ['is_enabled' => 1]);
    }

    public function disableModule($name)
    {
        DB::table('wf_modules')->updateOrInsert(['name' => $name], ['is_enabled' => 0]);
    }

    public function getRegisteredModules()
    {
        return array_keys($this->modules);
    }

    public function isRegistered($name)
    {
        return isset($this->modules[$name]);
    }
}
