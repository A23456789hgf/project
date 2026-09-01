<?php

namespace Database\Seeders;

use App\Models\InternalEntity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run()
    {
        // Ensure Admin role exists with full access
        $adminRole = Role::updateOrCreate(
            ['name' => 'Admin'],
            [
                'description' => 'مدير النظام (كامل الصلاحيات)',
                'is_active' => true,
                'full_access' => true,
            ]
        );

        // جلب الإدارة العامة لتخصيصها لمستخدم النظام
        $hq = InternalEntity::where('name', 'وزارة الزراعة والثروة السمكية والموارد المائية')->first();

        // إنشاء المستخدم root إذا لم يكن موجود
        User::withoutGlobalScopes()->updateOrCreate(
            ['username' => 'root'], // تحقق من الاسم
            [
                'user_id' => 'root', // رقم المستخدم الفريد
                'name' => 'System Administrator (Root)',
                'email' => 'root@example.com',
                'password' => Hash::make('root@2026'), // كلمة المرور مشفرة
                'phone' => '777777777',
                'role_id' => $adminRole->id,
                'entity_id' => $hq ? $hq->id : null,
                'status' => 'Active',
            ]
        );

        $this->command->info('✅ المستخدم الافتراضي root تم إنشاؤه أو تحديثه بنجاح!');
    }
}
