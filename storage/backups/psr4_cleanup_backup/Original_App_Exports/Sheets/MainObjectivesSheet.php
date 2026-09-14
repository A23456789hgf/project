<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MainObjectivesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'الأهداف الرئيسية';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'الهدف الرئيسي',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->mainObjectives as $objective) {
                    $data[] = [
                        $project->project_name,
                        $objective->objective,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',              // project_name (للربط)
                'الهدف الرئيسي الأول',        // objective
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
