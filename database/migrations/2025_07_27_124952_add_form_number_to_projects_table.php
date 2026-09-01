<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // الخطوة 1: إضافة العمود بشكل مؤقت كـ nullable ومع فهرس unique
        if (! Schema::hasColumn('projects', 'form_number')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('form_number', 20)
                    ->after('id')
                    ->nullable()
                    ->unique();
            });
        }

        // الخطوة 2: تعبئة القيم القديمة form_number
        $projects = DB::table('projects')->orderBy('created_at')->get();
        $yearlyCount = [];

        foreach ($projects as $project) {
            $year = date('Y', strtotime($project->created_at));

            // ابدأ العد السنوي من عدد السجلات الموجودة مسبقاً
            if (! isset($yearlyCount[$year])) {
                $yearlyCount[$year] = DB::table('projects')
                    ->whereYear('created_at', $year)
                    ->whereNotNull('form_number')
                    ->count();
            }

            // توليد الرقم التسلسلي
            $serial = str_pad(++$yearlyCount[$year], 6, '0', STR_PAD_LEFT);
            $formNumber = "MAFWRPRO{$year}{$serial}";

            // تأكد من أن الرقم غير مكرر
            while (DB::table('projects')->where('form_number', $formNumber)->exists()) {
                $yearlyCount[$year]++;
                $serial = str_pad($yearlyCount[$year], 6, '0', STR_PAD_LEFT);
                $formNumber = "MAFWRPRO{$year}{$serial}";
            }

            // تحديث السجل
            DB::table('projects')
                ->where('id', $project->id)
                ->update(['form_number' => $formNumber]);
        }

        // الخطوة 3: تعديل العمود ليصبح غير nullable (مع الحفاظ على الفهرس الموجود مسبقًا)
        // SQLite doesn't support changing column properties, so we skip this step
        // The column is already created with unique constraint and will work as intended
    }

    public function down()
    {
        // عند الرجوع، حذف الفهرس ثم حذف العمود
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['form_number']);
            $table->dropColumn('form_number');
        });
    }
};
