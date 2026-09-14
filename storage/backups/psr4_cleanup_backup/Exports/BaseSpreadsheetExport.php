<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BaseSpreadsheetExport
{
    /**
     * Build a styled spreadsheet with given headers and rows
     *
     * @param  string  $sheetTitle  Visible sheet tab title
     * @param  string  $reportTitle  Title text shown in A1
     * @param  array  $headers  Array of header labels (1D)
     * @param  array  $rows  Array of rows (2D), each row is array of cell values
     */
    public static function build(string $sheetTitle, string $reportTitle, array $headers, array $rows): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);

        // Title row A1..last
        $lastColLetter = self::columnLetter(count($headers));

        $sheet->setCellValue('A1', $reportTitle);
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getRowDimension(1)->setRowHeight(30);

        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FF000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray($titleStyle);

        // Export date row A2..last
        $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getStyle("A2:{$lastColLetter}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row at A4
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getRowDimension(4)->setRowHeight(25);
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF70AD47'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle("A4:{$lastColLetter}4")->applyFromArray($headerStyle);

        // Data rows starting at row 5
        $startRow = 5;
        foreach ($rows as $i => $row) {
            $sheet->fromArray(array_values($row), null, 'A'.($startRow + $i));
            $sheet->getStyle('A'.($startRow + $i).':'.$lastColLetter.($startRow + $i))
                ->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
        }

        // Auto-size columns and center alignment
        foreach (range('A', $lastColLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $dataEndRow = $startRow + max(count($rows) - 1, 0);
        if ($dataEndRow >= 4) {
            $sheet->getStyle('A4:'.$lastColLetter.$dataEndRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A4:'.$lastColLetter.$dataEndRow)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        return $spreadsheet;
    }

    private static function columnLetter(int $index): string
    {
        // 1 -> A, 2 -> B ... 26 -> Z, 27 -> AA, etc.
        $result = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $result = chr(65 + $mod).$result;
            $index = intdiv($index - 1, 26);
        }

        return $result;
    }
}
