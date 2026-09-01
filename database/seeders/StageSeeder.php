<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\StageFlow;
use App\Models\StageStatus;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStages();
        $this->seedStageStatuses();
        $this->seedStageFlows();
    }

    private function seedStages(): void
    {
        $stages = [
            [
                'code' => 'assembly',
                'name_ar' => 'موافقة الجمعية',
                'name_en' => 'Assembly Approval',
                'description_ar' => 'المرحلة الأولى من عملية الموافقة على المشروع من قبل الجمعية',
                'description_en' => 'First stage of project approval process by assembly',
                'order' => 1,
                'type' => 'assessment',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'union',
                'name_ar' => 'موافقة الاتحاد',
                'name_en' => 'Union Approval',
                'description_ar' => 'المرحلة الثانية من عملية الموافقة على المشروع من قبل الاتحاد',
                'description_en' => 'Second stage of project approval process by union',
                'order' => 2,
                'type' => 'approval',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'committee',
                'name_ar' => 'موافقة اللجنة',
                'name_en' => 'Committee Approval',
                'description_ar' => 'المرحلة الثالثة من عملية الموافقة على المشروع من قبل اللجنة',
                'description_en' => 'Third stage of project approval process by committee',
                'order' => 3,
                'type' => 'approval',
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'implementation',
                'name_ar' => 'مرحلة التنفيذ',
                'name_en' => 'Implementation Phase',
                'description_ar' => 'المرحلة الرابعة - مرحلة التنفيذ الفعلي للمشروع',
                'description_en' => 'Fourth stage - actual implementation phase of the project',
                'order' => 4,
                'type' => 'implementation',
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($stages as $stage) {
            Stage::firstOrCreate(['code' => $stage['code']], $stage);
        }
    }

    private function seedStageStatuses(): void
    {
        $statuses = [
            [
                'code' => 'draft',
                'name_ar' => 'مسودة',
                'name_en' => 'Draft',
                'description_ar' => 'المرحلة في حالة مسودة قيد الإعداد',
                'description_en' => 'Stage is in draft status',
                'category' => 'draft',
                'color' => '#6c757d',
                'icon' => 'fa-file-alt',
                'order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'pending',
                'name_ar' => 'قيد الانتظار',
                'name_en' => 'Pending',
                'description_ar' => 'المرحلة في انتظار المراجعة والموافقة',
                'description_en' => 'Stage is pending review and approval',
                'category' => 'pending',
                'color' => '#ffc107',
                'icon' => 'fa-hourglass-half',
                'order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'under_review',
                'name_ar' => 'قيد المراجعة',
                'name_en' => 'Under Review',
                'description_ar' => 'المرحلة قيد المراجعة من قبل الجهات المختصة',
                'description_en' => 'Stage is under review by competent authorities',
                'category' => 'in_progress',
                'color' => '#17a2b8',
                'icon' => 'fa-eye',
                'order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'approved',
                'name_ar' => 'موافق',
                'name_en' => 'Approved',
                'description_ar' => 'تمت الموافقة على المرحلة',
                'description_en' => 'Stage has been approved',
                'category' => 'completed',
                'color' => '#28a745',
                'icon' => 'fa-check-circle',
                'order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'rejected',
                'name_ar' => 'مرفوض',
                'name_en' => 'Rejected',
                'description_ar' => 'تم رفض المرحلة',
                'description_en' => 'Stage has been rejected',
                'category' => 'rejected',
                'color' => '#dc3545',
                'icon' => 'fa-times-circle',
                'order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'needs_revision',
                'name_ar' => 'بحاجة إلى تعديل',
                'name_en' => 'Needs Revision',
                'description_ar' => 'المرحلة بحاجة إلى إجراءات إضافية وتعديلات',
                'description_en' => 'Stage requires revision and additional actions',
                'category' => 'revision',
                'color' => '#ff6b6b',
                'icon' => 'fa-exclamation-circle',
                'order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'on_hold',
                'name_ar' => 'معلق',
                'name_en' => 'On Hold',
                'description_ar' => 'المرحلة معلقة مؤقتاً',
                'description_en' => 'Stage is temporarily on hold',
                'category' => 'in_progress',
                'color' => '#fd7e14',
                'icon' => 'fa-pause-circle',
                'order' => 7,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'code' => 'completed',
                'name_ar' => 'مكتمل',
                'name_en' => 'Completed',
                'description_ar' => 'المرحلة مكتملة',
                'description_en' => 'Stage is completed',
                'category' => 'completed',
                'color' => '#20c997',
                'icon' => 'fa-check-double',
                'order' => 8,
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            StageStatus::firstOrCreate(['code' => $status['code']], $status);
        }
    }

    private function seedStageFlows(): void
    {
        $stages = Stage::all()->keyBy('code');
        $statuses = StageStatus::all();

        $flows = [
            [
                'from_stage_id' => $stages['assembly']->id,
                'to_stage_id' => $stages['union']->id,
                'trigger_status' => 'approved',
                'auto_create_next' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'from_stage_id' => $stages['union']->id,
                'to_stage_id' => $stages['committee']->id,
                'trigger_status' => 'approved',
                'auto_create_next' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'from_stage_id' => $stages['committee']->id,
                'to_stage_id' => $stages['implementation']->id,
                'trigger_status' => 'approved',
                'auto_create_next' => true,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'from_stage_id' => $stages['assembly']->id,
                'to_stage_id' => $stages['assembly']->id,
                'trigger_status' => 'needs_revision',
                'auto_create_next' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'from_stage_id' => $stages['union']->id,
                'to_stage_id' => $stages['assembly']->id,
                'trigger_status' => 'needs_revision',
                'auto_create_next' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'from_stage_id' => $stages['committee']->id,
                'to_stage_id' => $stages['union']->id,
                'trigger_status' => 'needs_revision',
                'auto_create_next' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'from_stage_id' => $stages['implementation']->id,
                'to_stage_id' => $stages['committee']->id,
                'trigger_status' => 'needs_revision',
                'auto_create_next' => false,
                'is_active' => true,
                'order' => 2,
            ],
        ];

        foreach ($flows as $flow) {
            StageFlow::firstOrCreate(
                [
                    'from_stage_id' => $flow['from_stage_id'],
                    'to_stage_id' => $flow['to_stage_id'],
                    'trigger_status' => $flow['trigger_status'],
                ],
                $flow
            );
        }
    }
}
