<?php

namespace App\Exports;

use App\Models\SubArea;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SubAreasExport
{
    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('SubAreasExport: Starting data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('المناطق الفرعية');

            // Add title row
            $sheet->setCellValue('A1', 'تقرير المناطق الفرعية');
            $sheet->mergeCells('A1:G1');

            // Style title
            $titleStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['argb' => 'FF000000'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF4472C4',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Add export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2:G2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Set headers
            $headers = [
                'المعرف',
                'اسم المنطقة الفرعية',
                'المحافظة',
                'المديرية',
                'الحالة',
                'تاريخ الإنشاء',
                'تاريخ التحديث',
            ];

            // Add headers to the fourth row
            $sheet->fromArray($headers, null, 'A4');

            // Style the header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FF70AD47',
                    ],
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

            $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(25);

            // Get sub areas data with relationships
            $subAreas = SubArea::with(['governorate', 'directorate'])
                ->orderBy('name')
                ->get();

            Log::info('SubAreasExport: Data retrieved', [
                'total_records' => $subAreas->count(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $row = 5;

            foreach ($subAreas as $index => $subArea) {
                $sheet->setCellValue('A'.$row, $subArea->id);
                $sheet->setCellValue('B'.$row, $subArea->name);
                $sheet->setCellValue('C'.$row, $subArea->governorate ? $subArea->governorate->name : '');
                $sheet->setCellValue('D'.$row, $subArea->directorate ? $subArea->directorate->name : '');
                $sheet->setCellValue('E'.$row, $subArea->is_active ? 'نشط' : 'غير نشط');
                $sheet->setCellValue('F'.$row, $subArea->created_at ? $subArea->created_at->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue('G'.$row, $subArea->updated_at ? $subArea->updated_at->format('Y-m-d H:i:s') : '');

                // Add borders to data rows
                $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'G') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Center align all data
            $dataRange = 'A4:G'.($row - 1);
            $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Add summary at the bottom
            $summaryRow = $row + 1;
            $sheet->setCellValue('A'.$summaryRow, 'إجمالي عدد المناطق الفرعية: '.$subAreas->count());
            $sheet->mergeCells('A'.$summaryRow.':G'.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':G'.$summaryRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('SubAreasExport: Spreadsheet created successfully', [
                'total_records' => $subAreas->count(),
                'execution_time' => $executionTime.' ms',
                'memory_usage' => memory_get_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('SubAreasExport: Failed to create spreadsheet', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw $e; // Re-throw to be handled by controller
        }
    }
}
