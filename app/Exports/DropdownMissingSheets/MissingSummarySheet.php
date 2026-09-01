<?php

namespace App\Exports\DropdownMissingSheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet 1 — One row per missing value × project combination.
 */
class MissingSummarySheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    private array $rows = [];

    public function __construct(array $dropdownSkipped)
    {
        $counter = 1;
        foreach ($dropdownSkipped as $skipped) {
            foreach ($skipped['missing_values'] as $mv) {
                $this->rows[] = [
                    $counter++,
                    $skipped['project_name'],
                    'صف '.$skipped['row_number'],
                    $mv['field_label'],
                    $mv['value'],
                ];
            }
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['#', 'اسم المشروع', 'رقم الصف', 'الحقل', 'القيمة غير الموجودة'];
    }

    public function title(): string
    {
        return 'ملخص القيم الناقصة';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'E';
        $lastRow = max(count($this->rows) + 1, 2);

        // Header
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC6803']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Data rows
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
        }

        // Alternating row colors
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF7ED']],
                ]);
            }
        }

        $sheet->setRightToLeft(true);
        $sheet->getRowDimension(1)->setRowHeight(24);

        return [];
    }
}
