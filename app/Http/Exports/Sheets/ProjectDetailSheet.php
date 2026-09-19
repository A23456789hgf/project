<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectDetailSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'التفاصيل';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'ضمن الخطة',
            'ملخص المشروع',
            'مقدمة المشروع',
            'المبرر والمشكلة',
            'مكونات المشروع',
            'الأثر المتوقع',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                if ($project->detail) {
                    $data[] = [
                        $project->project_name,
                        $project->detail->is_part_of_plan,
                        $project->detail->project_summary,
                        $project->detail->project_introduction,
                        $project->detail->problem_and_justification,
                        $project->detail->project_components,
                        $project->detail->expected_impact,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',       // project_name (للربط)
                '1',                   // is_part_of_plan
                'ملخص المشروع',       // project_summary
                'مقدمة المشروع',       // project_introduction
                'المشكلة والمبررات',   // problem_and_justification
                'مكونات المشروع',      // project_components
                'الأثر المتوقع',       // expected_impact
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
                    'startColor' => ['argb' => 'FF1F4E79'],
                ],
            ],
        ];
    }
}
