<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EntitiesSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'الجهات';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'دور الجهة',
            'نوع الجهة',
            'الجهة',
            'المرجع',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                // Supervising
                foreach ($project->supervisingAuthorities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'supervising',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
                // Implementing
                foreach ($project->implementingEntities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'implementing',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
                // Participating
                foreach ($project->participatingEntities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'participating',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',       // project_name (للربط)
                'supervising',         // entity_role: supervising / implementing / participating
                '',                    // authority_type
                '1',                   // authority_id
                '',                    // parent_id
            ],
            [
                'مشروع تجريبي',
                'implementing',
                '',
                '2',
                '',
            ],
            [
                'مشروع تجريبي',
                'participating',
                '',
                '3',
                '',
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
                    'startColor' => ['argb' => 'FF7030A0'],
                ],
            ],
        ];
    }
}
