<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'adminisztrator',
            'penzugyi_osztalyvezeto',
            'projektkoordinacios_osztalyvezeto',
            'gazdasagi_igazgatosag_titkarsagvezeto',
            'foigazgatosag_titkarsagvezeto',
            'informatikai_osztalyvezeto',
            'humanpolitikai_osztalyvezeto',
            'gazdasagi_igazgato',
            'foigazgato',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
