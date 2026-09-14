<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpecialObjectivesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'الأهداف الخاصة';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'الهدف الخاص',
            'وزن الهدف',
            'القيمة المستهدفة',
            'وحدة القياس',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->specialObjectives as $objective) {
                    $data[] = [
                        $project->project_name,
                        $objective->objective,
                        $objective->objective_weight,
                        $objective->target_value,
                        $objective->measurement_unit,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',              // project_name (للربط)
                'الهدف الخاص الأول',          // objective
                '50',                         // objective_weight
                '100',                        // target_value
                'نسبة مئوية',                 // measurement_unit
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
                    'startColor' => ['argb' => 'FF2E75B6'],
                ],
            ],
        ];
    }
}
