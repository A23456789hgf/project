<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExecutiveActivitiesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'الأنشطة التنفيذية';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'النشاط التنفيذي',
            'وزن النشاط',
            'الإجراء التنفيذي',
            'وزن الإجراء',
            'تاريخ البداية',
            'تاريخ النهاية',
            'وسيلة التحقق',
            'الجهة المسؤولة',
            'البند المالي',
            'التكلفة',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->executiveActivities as $activity) {
                    foreach ($activity->actions as $action) {
                        // Handle assigned entities
                        $assignedEntityIds = $action->assignedEntities->pluck('id')->toArray();
                        $entityId = count($assignedEntityIds) > 0 ? $assignedEntityIds[0] : '';

                        foreach ($action->costs as $cost) {
                            $data[] = [
                                $project->project_name,
                                $activity->name,
                                $activity->weight,
                                $action->action,
                                $action->weight,
                                $action->start_date,
                                $action->end_date,
                                $action->verification_means,
                                $entityId,
                                $cost->financial_item_id,
                                $cost->amount,
                            ];
                        }

                        // If no costs, at least add one row for the action
                        if ($action->costs->isEmpty()) {
                            $data[] = [
                                $project->project_name,
                                $activity->name,
                                $activity->weight,
                                $action->action,
                                $action->weight,
                                $action->start_date,
                                $action->end_date,
                                $action->verification_means,
                                $entityId,
                                '',
                                '',
                            ];
                        }
                    }
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',           // project_name (للربط)
                'نشاط تنفيذي 1',          // activity_name
                '40',                      // activity_weight
                'إجراء تنفيذي 1',          // action
                '20',                      // action_weight
                '2026-01-15',              // start_date
                '2026-06-30',              // end_date
                'وسيلة التحقق',            // verification_means
                '1',                       // assigned_entity_id
                '1',                       // financial_item_id
                '10000',                   // cost_amount
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF548235'],
                ],
            ],
        ];
    }
}
