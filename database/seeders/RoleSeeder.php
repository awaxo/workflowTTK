<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'adminisztrator',
            'betekinto',
            'titkar_9_fi',
            'titkar_9_gi',
            'titkar_1',
            'titkar_3',
            'titkar_4',
            'titkar_5',
            'titkar_6',
            'titkar_7',
            'titkar_8',
            'utofinanszirozas_fedezetigazolo',
            'munkaber_kotelezettsegvallalas_nyilvantarto',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
