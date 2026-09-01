<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            [
                'name' => 'عرض الملفات الشخصية',
                'slug' => 'users.view-profiles',
                'description' => 'عرض الملفات الشخصية للمستخدمين',
                'module' => 'users',
                'type' => Permission::TYPE_PAGE,
            ],
            [
                'name' => 'تعديل كلمات المرور',
                'slug' => 'users.edit-passwords',
                'description' => 'تعديل كلمات المرور للمستخدمين',
                'module' => 'users',
                'type' => Permission::TYPE_ACTION,
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                $perm
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('slug', ['users.view-profiles', 'users.edit-passwords'])->delete();
    }
};
