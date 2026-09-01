<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class DashboardPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'عرض لوحة التحكم', 'slug' => 'home.view', 'description' => 'الوصول إلى لوحة التحكم الرئيسية'],
            ['name' => 'إظهار في القائمة الجانبية', 'slug' => 'home.sidebar', 'description' => 'إظهار زر لوحة التحكم في القائمة الجانبية'],

            // Regular Scopes
            ['name' => 'عرض كافة إحصائيات النظام', 'slug' => 'home.view-all', 'description' => 'القدرة على عرض إحصائيات جميع الإدارات والجهات'],
            ['name' => 'عرض إحصائيات الجهة', 'slug' => 'home.view-own', 'description' => 'القدرة على عرض إحصائيات الجهة التابع لها المستخدم فقط'],
            ['name' => 'عرض إحصائيات الجهات الموازية', 'slug' => 'home.view-parallel', 'description' => 'القدرة على عرض إحصائيات الجهات الموازية والجهة التابع لها'],
            ['name' => 'عرض إحصائياتي فقط', 'slug' => 'home.view-user', 'description' => 'القدرة على عرض الإحصائيات الخاصة بالمستخدم فقط'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'module' => 'home',
                ]
            );
        }

        $this->command->info('✓ Dashboard permissions for "home" module seeded successfully.');
    }
}
