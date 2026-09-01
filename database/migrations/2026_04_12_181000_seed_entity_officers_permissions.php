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
        $module = 'entity_officers';

        $permissions = [
            [
                'slug' => 'entity_officers.sidebar',
                'name' => 'عرض في القائمة الجانبية',
                'type' => Permission::TYPE_SIDEBAR,
                'module' => $module,
                'description' => 'السماح برؤية وحدة مسؤولي الجهات في القائمة الجانبية',
            ],
            [
                'slug' => 'entity_officers.view',
                'name' => 'عرض المسؤولين',
                'type' => Permission::TYPE_PAGE,
                'module' => $module,
                'description' => 'السماح بعرض قائمة مسؤولي الجهات',
            ],
            [
                'slug' => 'entity_officers.create',
                'name' => 'إضافة مسؤول',
                'type' => Permission::TYPE_ACTION,
                'module' => $module,
                'description' => 'السماح بإضافة مسؤولي جهات جدد',
            ],
            [
                'slug' => 'entity_officers.edit',
                'name' => 'تعديل مسؤول',
                'type' => Permission::TYPE_ACTION,
                'module' => $module,
                'description' => 'السماح بتعديل بيانات مسؤولي الجهات',
            ],
            [
                'slug' => 'entity_officers.delete',
                'name' => 'حذف مسؤول',
                'type' => Permission::TYPE_ACTION,
                'module' => $module,
                'description' => 'السماح بحذف مسؤولي الجهات',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('module', 'entity_officers')->delete();
    }
};
