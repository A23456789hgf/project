<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            RolePermissionsSeeder::class,
            DefaultUserSeeder::class,
            UserSeeder::class,
            DashboardPermissionsSeeder::class,
            DefaultUserSeeder::class,
            PermissionsTableSeeder::class,
            PermissionTypeSeeder::class,
            ReportsPermissionsSeeder::class,
            AuthorityTemplateSeeder::class,

        ]);
    }
}
