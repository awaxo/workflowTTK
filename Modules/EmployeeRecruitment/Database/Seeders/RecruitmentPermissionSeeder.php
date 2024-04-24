<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use Database\Seeders\Interfaces\IPermissionSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RecruitmentPermissionSeeder extends Seeder implements IPermissionSeeder
{
    public static function getPermissions(): array
    {
        return [];
    }

    public function run()
    {
        $permissions = self::getPermissions();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}