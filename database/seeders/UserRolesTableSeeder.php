<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRolesTableSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = 'laszlo.tovari@awaxo.com';
        $admin = User::where('email', $adminEmail)->first();

        if ($admin) {
            $admin->assignRole('adminisztrator');
        }
    }
}
