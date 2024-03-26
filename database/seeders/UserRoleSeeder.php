<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // add admin test user
        $adminEmail = 'laszlo.tovari@awaxo.com';
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            $admin->assignRole('adminisztrator');
        }

        // add titkar_aki test user
        $akiEmail = 'akititkar@b.com';
        $aki = User::where('email', $akiEmail)->first();

        if ($aki) {
            $aki->assignRole('titkar_aki');
        }
    }
}
