<?php

namespace Database\Seeders;

use App\Models\Authority;
use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\TypeEntity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeder لإدراج الجهات من ملف القالب JSON
 * (database/authorities_template_2026-07-17 (1).json)
 * إلى جدول authorities المستخدم في صفحة الجهات (AuthorityController).
 *
 * - يربط المحافظة/المديرية/نوع الجهة بالجداول المرجعية (يُنشئها إن لم تكن موجودة).
 * - يعتمد نهجاً من مرحلتين: إنشاء الجهات أولاً ثم ربط الجهة الأب.
 * - قابل للتكرار (idempotent) عبر firstOrCreate.
 */
class AuthorityTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('authorities_template_2026-07-17 (1).json');

        if (! File::exists($path)) {
            $this->command->error("ملف القالب غير موجود: {$path}");

            return;
        }

        $rows = json_decode(File::get($path), true, 512, JSON_UNESCAPED_UNICODE);

        if (! is_array($rows)) {
            $this->command->error('تعذّر تحليل ملف JSON. تأكد من صحة التنسيق.');

            return;
        }

        // -------------------------------------------------------------------
        // 1) أنواع الجهات (type_entities) من القيم الفريدة في الملف
        // -------------------------------------------------------------------
        $typeEntityCache = [];
        $typeNames = array_filter(array_unique(array_map(
            fn ($r) => trim((string) ($r['نوع الجهة (نوع التمويل)'] ?? '')),
            $rows
        )), fn ($v) => $v !== '' && $v !== '-');

        foreach ($typeNames as $typeName) {
            $typeEntity = TypeEntity::firstOrCreate(['name' => $typeName], ['is_active' => true]);
            $typeEntityCache[$typeName] = $typeEntity->id;
        }

        // -------------------------------------------------------------------
        // 2) المرحلة الأولى: إنشاء الجهات مع ربط المحافظة/المديرية/النوع
        //    (دون ربط الجهة الأب في هذه المرحلة)
        // -------------------------------------------------------------------
        $authorityIds = [];   // اسم الجهة (مُنظّف) => id
        $governorateCache = []; // اسم المحافظة => id

        foreach ($rows as $row) {
            $name = trim((string) ($row['اسم الجهة'] ?? ''));
            if ($name === '') {
                continue;
            }

            // المحافظة
            $govName = trim((string) ($row['المحافظة'] ?? ''));
            $govName = ($govName === '' || $govName === '-') ? null : $govName;

            // المديرية
            $dirName = trim((string) ($row['المديرية'] ?? ''));
            $dirName = ($dirName === '' || $dirName === '-') ? null : $dirName;

            // نوع الجهة (تُعامل "-" والقيمة الفارغة كعدم وجود نوع)
            $typeName = trim((string) ($row['نوع الجهة (نوع التمويل)'] ?? ''));
            $typeId = ($typeName !== '' && $typeName !== '-' && isset($typeEntityCache[$typeName]))
                ? $typeEntityCache[$typeName]
                : null;

            // حلّ المحافظة (إن وُجدت) — أنشئها إن لم تكن موجودة
            $governorateId = null;
            if ($govName !== null) {
                if (! isset($governorateCache[$govName])) {
                    $governorate = Governorate::withInactive()
                        ->firstOrCreate(['name' => $govName], ['is_active' => true]);
                    $governorateCache[$govName] = $governorate->id;
                }
                $governorateId = $governorateCache[$govName];
            }

            // حلّ المديرية (تتطلب محافظة) — أنشئها إن لم تكن موجودة
            $directorateId = null;
            if ($dirName !== null && $governorateId !== null) {
                $directorate = Directorate::withInactive()
                    ->firstOrCreate(
                        ['name' => $dirName, 'governorate_id' => $governorateId],
                        ['is_active' => true]
                    );
                $directorateId = $directorate->id;
            }

            // إنشاء/تحديث الجهة
            $authority = Authority::withInactive()
                ->withoutGlobalScope('valid_names')
                ->firstOrCreate(['agency_name' => $name]);

            $authority->agency_name = $name;
            $authority->is_active = true;
            $authority->governorate_id = $governorateId;
            $authority->directorate_id = $directorateId;
            $authority->type_entity_id = $typeId;
            $authority->save();

            $authorityIds[$name] = $authority->id;
        }

        // -------------------------------------------------------------------
        // 3) المرحلة الثانية: ربط الجهة الأب
        // -------------------------------------------------------------------
        $missingParents = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['اسم الجهة'] ?? ''));
            $parentName = trim((string) ($row['الجهة الأب'] ?? ''));

            if ($name === '' || $parentName === '') {
                continue;
            }
            if (! isset($authorityIds[$name])) {
                continue;
            }

            // تفضيل الأب من داخل ملف القالب، وإلا من قاعدة البيانات إن وُجد
            $parentId = $authorityIds[$parentName]
                ?? Authority::withInactive()
                    ->withoutGlobalScope('valid_names')
                    ->where('agency_name', $parentName)
                    ->value('id');

            $authority = Authority::withInactive()
                ->withoutGlobalScope('valid_names')
                ->find($authorityIds[$name]);

            if (! $authority) {
                continue;
            }

            if ($parentId) {
                if ($parentId !== $authority->parent_id) {
                    $authority->parent_id = $parentId;
                    $authority->save();
                }
            } else {
                $missingParents[$name] = $parentName;
            }
        }

        // -------------------------------------------------------------------
        // 4) تنظيف نوع الجهة الوهمي "-" (إن وُجد وغير مستخدم)
        // -------------------------------------------------------------------
        $dashType = TypeEntity::where('name', '-')->first();
        if ($dashType && Authority::where('type_entity_id', $dashType->id)->doesntExist()) {
            $dashType->delete();
        }

        // -------------------------------------------------------------------
        // 5) تقرير النتائج
        // -------------------------------------------------------------------
        $this->command->info('تم إدراج/تحديث '.count($authorityIds).' جهة بنجاح.');

        if (! empty($missingParents)) {
            $this->command->warn('الجهات الآتية أُدرجت بدون أب لأن الجهة الأب غير موجودة في القالب ولا في قاعدة البيانات:');
            foreach ($missingParents as $child => $parent) {
                $this->command->warn("  • {$child}  « الأب: {$parent}");
            }
        }
    }
}
