<?php

namespace Database\Seeders;

use App\Models\Role;
use Database\Seeders\Interfaces\IPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * A static list of seeder classes to run.
     *
     * @var array
     */
    protected static $seeders = [];

    /**
     * Allow modules to register their seeders.
     *
     * @param string $seederClass The seeder class to add.
     */
    public static function addSeeder(string $seederClass): void
    {
        self::$seeders[] = $seederClass;
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserRoleSeeder::class,
        ]);

        Role::findByName('adminisztrator')->givePermissionTo(PermissionSeeder::getPermissions());

        // Run the generic seeders first
        foreach (self::$seeders as $seederClass) {
            $this->call($seederClass);

            // Assign permissions to admin role
            if (in_array(IPermissionSeeder::class, class_implements($seederClass))) {
                Role::findByName('adminisztrator')->givePermissionTo($seederClass::getPermissions());
            }
        }
    }
}