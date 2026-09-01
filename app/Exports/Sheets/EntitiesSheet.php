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
        // القيم المقبولة لعمود "دور الجهة":
        //   مشرفة   = جهة إشرافية
        //   منفذة   = جهة منفذة
        //   مشاركة  = جهة مشاركة
        //   مستفيدة = جهة مستفيدة (للمشاريع القديمة فقط)
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                // مشرفة
                foreach ($project->supervisingAuthorities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'مشرفة',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
                // منفذة
                foreach ($project->implementingEntities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'منفذة',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
                // مشاركة
                foreach ($project->participatingEntities as $entity) {
                    $data[] = [
                        $project->project_name,
                        'مشاركة',
                        $entity->authority_type,
                        $entity->authority_id,
                        $entity->parent_id,
                    ];
                }
                // مستفيدة (للمشاريع القديمة فقط)
                if ($project->project_type === 'old') {
                    foreach ($project->beneficiaryEntities as $entity) {
                        $data[] = [
                            $project->project_name,
                            'مستفيدة',
                            $entity->authority_type,
                            $entity->authority_id,
                            $entity->parent_id,
                        ];
                    }
                }
            }

            return $data;
        }

        // بيانات نموذجية
        return [
            [
                'مشروع تجريبي',   // اسم المشروع (للربط)
                'مشرفة',           // دور الجهة: مشرفة / منفذة / مشاركة / مستفيدة
                'internal',        // نوع الجهة: internal أو external
                '1',               // معرّف الجهة
                '',                // المرجع (اختياري)
            ],
            [
                'مشروع تجريبي',
                'منفذة',
                'external',
                '2',
                '',
            ],
            [
                'مشروع تجريبي',
                'مشاركة',
                'internal',
                '3',
                '',
            ],
            [
                'مشروع قديم تجريبي',
                'مستفيدة',
                'external',
                '4',
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
