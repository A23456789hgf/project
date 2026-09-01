<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'مدير النظام - صلاحيات كاملة',
            ],
            [
                'name' => 'Manager',
                'description' => 'مدير مشروع - صلاحيات إدارية',
            ],
            [
                'name' => 'Financer',
                'description' => 'مسؤول مالي - صلاحيات مالية',
            ],
            [
                'name' => 'Creator',
                'description' => 'منشئ مشاريع - صلاحيات محدودة',
            ],
            [
                'name' => 'Funder',
                'description' => 'جهة مانحة - صلاحيات عرض وتصدير',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
