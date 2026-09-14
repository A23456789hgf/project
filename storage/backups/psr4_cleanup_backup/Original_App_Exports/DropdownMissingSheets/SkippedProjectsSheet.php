<?php

namespace App\Exports\DropdownMissingSheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet 2 — Full original rows of skipped projects, exactly as they came from Excel.
 * Includes an extra "القيم الناقصة" column at the end for reference.
 * This sheet can be corrected and re-uploaded after fixes.
 */
class SkippedProjectsSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    private array $rows = [];

    private array $headings = [];

    public function __construct(array $dropdownSkipped, array $originalHeadings = [])
    {
        // Build headings — use original Excel headings + extra column
        $this->headings = ! empty($originalHeadings) ? $originalHeadings : [];
        if (! in_array('القيم الناقصة', $this->headings)) {
            $this->headings[] = 'القيم الناقصة';
        }

        foreach ($dropdownSkipped as $skipped) {
            $row = $skipped['original_row'];

            // Append missing values summary
            $missingText = implode(' | ', array_map(
                fn ($mv) => $mv['field_label'].': '.$mv['value'],
                $skipped['missing_values']
            ));

            // Build row in same order as headings
            $orderedRow = [];
            foreach ($this->headings as $heading) {
                if ($heading === 'القيم الناقصة') {
                    $orderedRow[] = $missingText;
                } else {
                    $orderedRow[] = $row[$heading] ?? '';
                }
            }

            $this->rows[] = $orderedRow;
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'بيانات المشاريع غير المستوردة';
    }

    public function styles(Worksheet $sheet): array
    {
        $colCount = max(count($this->headings), 1);
        $lastColLtr = Coordinate::stringFromColumnIndex($colCount);
        $lastRow = max(count($this->rows) + 1, 2);

        // Header row
        $sheet->getStyle("A1:{$lastColLtr}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Highlight the last column (القيم الناقصة) header in orange
        $missingColLtr = $lastColLtr;
        $sheet->getStyle("{$missingColLtr}1")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC6803']],
        ]);

        // Data rows
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastColLtr}{$lastRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);

            // Highlight missing values column in light orange for all data rows
            $sheet->getStyle("{$missingColLtr}2:{$missingColLtr}{$lastRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                'font' => ['color' => ['rgb' => '92400E']],
            ]);
        }

        $sheet->setRightToLeft(true);
        $sheet->getRowDimension(1)->setRowHeight(24);

        return [];
    }
}
