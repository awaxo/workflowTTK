<?php

namespace Database\Seeders;

use App\Models\Permission;
use Database\Seeders\Interfaces\IPermissionSeeder;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder implements IPermissionSeeder
{
    public static function getPermissions(): array
    {
        return [
            'update_global_settings',
        ];
    }

    public function run()
    {
        $permissions = self::getPermissions();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
