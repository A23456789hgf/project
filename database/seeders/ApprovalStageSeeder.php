<?php

namespace Database\Seeders;

use App\Models\ApprovalStage;
use Illuminate\Database\Seeder;

class ApprovalStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => 'مرحلة التقييم والتوثيق',
                'order' => 1,
                'description' => 'التقييم الأولي والتوثيق للمشروع',
                'type' => 'documentation',
                'is_active' => true,
            ],
            [
                'name' => 'مرحلة رئيس الجمعية',
                'order' => 2,
                'description' => 'موافقة رئيس الجمعية على المشروع',
                'type' => 'association_president',
                'is_active' => true,
            ],
            [
                'name' => 'مرحلة رئيس اللجنة',
                'order' => 3,
                'description' => 'موافقة رئيس اللجنة على المشروع',
                'type' => 'committee_head',
                'is_active' => true,
            ],
            [
                'name' => 'المراجعة التقنية',
                'order' => 4,
                'description' => 'المراجعة التقنية الشاملة للمشروع',
                'type' => 'technical_review',
                'is_active' => true,
            ],
            [
                'name' => 'المراجعة المالية',
                'order' => 5,
                'description' => 'المراجعة المالية والميزانية للمشروع',
                'type' => 'financial_review',
                'is_active' => true,
            ],
            [
                'name' => 'الموافقة النهائية',
                'order' => 6,
                'description' => 'الموافقة النهائية على المشروع',
                'type' => 'final_approval',
                'is_active' => true,
            ],
        ];

        foreach ($stages as $stage) {
            ApprovalStage::firstOrCreate(
                ['type' => $stage['type']],
                $stage
            );
        }
    }
}
