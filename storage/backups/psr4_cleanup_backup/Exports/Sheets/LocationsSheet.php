<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LocationsSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'المواقع';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'المحافظة',
            'المديرية',
            'العزلة',
            'القرية',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->locations as $location) {
                    $data[] = [
                        $project->project_name,
                        $location->governorate_id,
                        $location->directorate_id,
                        $location->sub_area_id,
                        $location->village_id,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',   // project_name (للربط)
                '1',               // governorate_id
                '1',               // directorate_id
                '',                // sub_area_id
                '',                // village_id
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
