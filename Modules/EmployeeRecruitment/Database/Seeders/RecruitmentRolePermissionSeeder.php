<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RecruitmentRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $role = Role::findByName('informatikai_osztalyvezeto');
        $permissions = [
            'read_recruitment',
            'suspend_recruitment',
            'approve_email_address'
        ];
        $role->syncPermissions($permissions);

        $role = Role::findByName('titkar_aki');
        $permissions = [
            'read_recruitment',
            'create_recruitment',
            'suspend_recruitment',
            'cancel_recruitment'
        ];
        $role->syncPermissions($permissions);

        $role = Role::findByName('betekinto');
        $permissions = [
            'read_recruitment'            
        ];
        $role->syncPermissions($permissions);
    }
}