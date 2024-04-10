<?php

namespace Modules\EmployeeRecruitment\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RecruitmentRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'titkar_9_fi',
            'titkar_9_gi',
            'titkar_1',
            'titkar_3',
            'titkar_4',
            'titkar_5',
            'titkar_6',
            'titkar_7',
            'titkar_8',
        ];
        
        $permissions = [
            'read_recruitment',
            'create_recruitment',
            'suspend_recruitment',
            'cancel_recruitment'
        ];
        
        foreach ($roles as $roleName) {
            $role = Role::findByName($roleName);
            $role->syncPermissions($permissions);
        }

        $role = Role::findByName('betekinto');
        $permissions = [
            'read_recruitment'            
        ];
        $role->syncPermissions($permissions);
    }
}