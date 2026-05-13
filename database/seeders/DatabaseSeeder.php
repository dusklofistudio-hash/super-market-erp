<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            BranchSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
