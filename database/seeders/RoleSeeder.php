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
            'titkar_foigazgatosag',
            'titkar_gazdasagi_igazgatosag',
            'titkar_szki',
            'titkar_aki',
            'titkar_ei',
            'titkar_kpi',
            'titkar_akk',
            'titkar_szkk',
            'titkar_gyfl',
            'nyilvantartas_rogzito_penzugyi_osztaly'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
