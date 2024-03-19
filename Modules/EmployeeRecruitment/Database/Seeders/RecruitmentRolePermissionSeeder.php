<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RecruitmentRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $role = Role::findByName('adminisztrator');
        $role->givePermissionTo([
            'read_recruitment',
            'create_recruitment',
            'suspend_recruitment',
            'cancel_recruitment',
            'approve_email'
        ]);
    }
}
