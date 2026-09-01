<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancingsSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'التمويلات';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'مصدر التمويل',
            'الجهة',
            'نوع التمويل',
            'شكل التمويل',
            'شكل التمويل الفرعي',
            'مبلغ التمويل',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->financings as $financing) {
                    $data[] = [
                        $project->project_name,
                        $financing->funding_source_id,
                        $financing->authority_id,
                        $financing->financing_type_id,
                        $financing->financing_form_id,
                        $financing->sub_financing_form_id,
                        $financing->financing_amount,
                    ];
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',       // project_name (للربط)
                '1',                   // funding_source_id
                '1',                   // authority_id
                '1',                   // financing_type_id
                '1',                   // financing_form_id
                '',                    // sub_financing_form_id
                '50000',               // financing_amount
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
                    'startColor' => ['argb' => 'FFBF8F00'],
                ],
            ],
        ];
    }
}
