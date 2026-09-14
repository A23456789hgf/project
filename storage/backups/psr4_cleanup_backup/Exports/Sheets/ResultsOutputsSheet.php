<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultsOutputsSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    public function title(): string
    {
        return 'النتائج والمخرجات';
    }

    public function headings(): array
    {
        return [
            'اسم المشروع',
            'النتيجة',
            'القيمة المستهدفة',
            'نوع المؤشر',
            'وحدة المؤشر',
            'المخرج',
        ];
    }

    public function array(): array
    {
        if ($this->projects && $this->projects->count() > 0) {
            $data = [];
            foreach ($this->projects as $project) {
                foreach ($project->objectiveResults as $result) {
                    foreach ($result->outputs as $output) {
                        $data[] = [
                            $project->project_name,
                            $result->result_name,
                            $result->target_value,
                            $result->indicator_type,
                            $result->indicator_unit,
                            $output->output,
                        ];
                    }
                }
            }

            return $data;
        }

        return [
            [
                'مشروع تجريبي',          // project_name (للربط)
                'نتيجة 1',               // result_name
                '100',                    // target_value
                'كمي',                    // indicator_type
                'عدد',                    // indicator_unit
                'مخرج 1',                // output
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
