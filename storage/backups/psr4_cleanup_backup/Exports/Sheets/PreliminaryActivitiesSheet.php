<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PreliminaryActivitiesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'الأنشطة الأولية';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'النشاط',
            'وزن النشاط',
            'الإجراء',
            'البند المالي',
            'المبلغ',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->preliminaryActivities as $activity) {
                    foreach ($activity->procedures as $procedure) {
                        foreach ($procedure->costs as $cost) {
                            $data[] = [
                                $project->project_name,
                                $activity->name,
                                $activity->weight,
                                $procedure->procedure_name,
                                $cost->financial_item_id,
                                $cost->amount,
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
                'نشاط أولي 1',            // activity_name
                '30',                      // activity_weight
                'إجراء 1',                 // procedure_name
                '1',                       // financial_item_id
                '5000',                    // cost_amount
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
