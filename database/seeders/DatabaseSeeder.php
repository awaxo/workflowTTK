<?php

namespace Database\Seeders;

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
        // Run the generic seeders first
        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            UserRolesTableSeeder::class,
        ]);

        foreach (self::$seeders as $seederClass) {
            $this->call($seederClass);
        }
    }
}
