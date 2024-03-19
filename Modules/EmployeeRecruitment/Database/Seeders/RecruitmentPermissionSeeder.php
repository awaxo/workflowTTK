<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RecruitmentPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'read_recruitment',
            'create_recruitment',
            'suspend_recruitment',
            'cancel_recruitment',
            'approve_email'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}