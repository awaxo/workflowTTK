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
        $email = 'akititkar@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('titkar_aki');
        }

        // add informatikai_osztalyvezeto test user
        $email = 'it@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('informatikai_osztalyvezeto');
        }

        // add betekinto test user
        $email = 'betekinto@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('betekinto');
        }
    }
}
