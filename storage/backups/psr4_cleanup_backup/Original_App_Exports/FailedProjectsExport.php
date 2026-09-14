<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedProjectsExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    protected array $failures;

    protected array $headings = [];

    protected bool $isFullData = false;

    public function __construct(array $failures)
    {
        if (isset($failures['headings']) && isset($failures['rows'])) {
            $this->headings = $failures['headings'];
            $this->failures = $failures['rows'];
            $this->isFullData = true;
        } else {
            $this->failures = $failures;
            $this->headings = [
                '#',
                'اسم المشروع',
                'سبب الفشل',
            ];
            $this->isFullData = false;
        }
    }

    public function array(): array
    {
        if ($this->isFullData) {
            $rows = [];
            foreach ($this->failures as $row) {
                $formattedRow = [];
                foreach ($this->headings as $heading) {
                    $formattedRow[] = $row[$heading] ?? '';
                }
                $rows[] = $formattedRow;
            }

            return $rows;
        }

        $rows = [];
        $counter = 1;

        foreach ($this->failures as $failure) {
            $projectName = $failure['row'] ?? $failure['project_name'] ?? '—';
            $errors = is_array($failure['errors'] ?? null)
                ? implode(' | ', $failure['errors'])
                : ($failure['reason'] ?? '—');

            $rows[] = [
                $counter++,
                $projectName,
                $errors,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'المشاريع غير المستوردة';
    }

    public function styles(Worksheet $sheet): array
    {
        $colCount = count($this->headings);
        $lastColLetter = Coordinate::stringFromColumnIndex($colCount ?: 1);

        // Header row styling
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC2626'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data rows – RTL alignment for Arabic text
        $lastRow = count($this->failures) + 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:{$lastColLetter}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Right-to-left sheet direction
        $sheet->setRightToLeft(true);

        // Row height
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [];
    }
}
