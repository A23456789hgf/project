<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\InternalEntity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGeographicScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // التأكد من وجود كافة الصلاحيات في قاعدة البيانات ومزامنتها مع المصفوفة
        (new PermissionsSeeder)->run();

        $username = env('SUPER_ADMIN_USERNAME', 'admin');
        $password = env('SUPER_ADMIN_PASSWORD', 'superpassword');

        // إنشاء دور Admin وتحديثه بصلاحيات الوصول الكامل
        $role = Role::updateOrCreate(
            ['name' => 'Admin'],
            [
                'is_active' => true,
                'full_access' => true,
                'module_scopes' => ['all' => 'all'],
                'module_geo_scopes' => ['all' => 'all'],
                'entity_display_scope' => ['all' => 'all'],
                'entity_add_scope' => ['all' => 'all'],
            ]
        );

        // ربط كافة الصلاحيات (بما فيها عناصر القائمة الجانبية كاملة) بالدور بشكل افتراضي
        $allPermissionIds = Permission::pluck('id')->toArray();
        if (! empty($allPermissionIds)) {
            DB::table('role_permission')->where('role_id', $role->id)->delete();
            $now = now();
            $rows = array_map(fn ($id) => [
                'role_id' => $role->id,
                'permission_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $allPermissionIds);
            DB::table('role_permission')->insert($rows);
        }

        // تحديث إصدار التخزين المؤقت لصلاحيات الدور لضمان تطبيق التغييرات فوراً
        User::incrementRolePermissionsVersion($role->id);

        // جلب جهة وزارة الزراعة والثروة السمكية والموارد المائية من جدول InternalEntity
        $ministry = InternalEntity::where('name', 'وزارة الزراعة والثروة السمكية والموارد المائية')->first();

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'user_id' => $username,
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'phone' => '777777779',
                'entity_id' => $ministry?->id,
                'created_by' => null,
            ]
        );

        // تحديث بيانات المستخدم حتى وإن كان موجوداً مسبقاً لضمان الصلاحيات الشاملة
        $user->update([
            'role_id' => $role->id,
            'status' => 'Active',
            'entity_id' => $ministry?->id,
            'module_scopes' => ['all' => 'all'],
            'module_geo_scopes' => ['all' => 'all'],
        ]);

        // منح نطاق جغرافي على مستوى جميع المحافظات ليرى جميع الجهات
        $governorates = Governorate::all();

        // مسح النطاقات الجغرافية السابقة لتجنب التكرار
        UserGeographicScope::where('user_id', $user->id)->delete();

        $geoScopes = [];
        $now = now();
        foreach ($governorates as $gov) {
            $geoScopes[] = [
                'user_id' => $user->id,
                'governorate_id' => $gov->id,
                'directorate_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($geoScopes)) {
            UserGeographicScope::insert($geoScopes);
        }
    }
}
