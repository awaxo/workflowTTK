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
        $email = 'titkar3@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('titkar_3');
        }

        // add titkar_9_gi test user
        $email = 'titkargi@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('titkar_9_gi');
        }

        // add titkar_9_gi test user
        $email = 'titkarfi@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('titkar_9_fi');
        }

        // add utofinanszirozas_fedezetigazolo test user
        $email = 'utofin@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('utofinanszirozas_fedezetigazolo');
        }

        // add munkaber_kotelezettsegvallalas_nyilvantarto test user
        $email = 'munkaber@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('munkaber_kotelezettsegvallalas_nyilvantarto');
        }

        // add betekinto test user
        /*$email = 'betekinto@b.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->assignRole('betekinto');
        }*/
    }
}
