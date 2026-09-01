<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class SystemStagesSeeder extends Seeder
{
    public function run()
    {
        $systemStages = [
            ['code' => 'association', 'name_ar' => 'الجمعية', 'name_en' => 'Association', 'order' => 1],
            ['code' => 'union', 'name_ar' => 'الاتحاد', 'name_en' => 'Union', 'order' => 2],
            ['code' => 'committee', 'name_ar' => 'اللجنة', 'name_en' => 'Committee', 'order' => 3],
            ['code' => 'implementation', 'name_ar' => 'التنفيذ', 'name_en' => 'Implementation', 'order' => 4],
        ];

        foreach ($systemStages as $stage) {
            Stage::updateOrCreate(
                ['code' => $stage['code']],  // إذا موجودة يحدثها
                array_merge($stage, ['is_system' => true, 'is_active' => true])
            );
        }

        $this->command->info('✅ المراحل النظامية الأساسية تم إنشاؤها أو تحديثها بنجاح!');
    }
}
