<?php

namespace App\Exports;

use App\Models\Village;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VillagesExport
{
    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('VillagesExport: Starting data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('القرى');

            // Add title row
            $sheet->setCellValue('A1', 'تقرير القرى');
            $sheet->mergeCells('A1:H1');

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
            $sheet->getStyle('A1:H1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Add export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Set headers
            $headers = [
                'المعرف',
                'اسم القرية',
                'المحافظة',
                'المديرية',
                'المنطقة الفرعية',
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

            $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(25);

            // Get villages data with relationships
            $villages = Village::with(['governorate', 'directorate', 'subArea'])
                ->orderBy('name')
                ->get();

            Log::info('VillagesExport: Data retrieved', [
                'total_records' => $villages->count(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $row = 5;

            foreach ($villages as $index => $village) {
                $sheet->setCellValue('A'.$row, $village->id);
                $sheet->setCellValue('B'.$row, $village->name);
                $sheet->setCellValue('C'.$row, $village->governorate ? $village->governorate->name : '');
                $sheet->setCellValue('D'.$row, $village->directorate ? $village->directorate->name : '');
                $sheet->setCellValue('E'.$row, $village->subArea ? $village->subArea->name : '');
                $sheet->setCellValue('F'.$row, $village->is_active ? 'نشط' : 'غير نشط');
                $sheet->setCellValue('G'.$row, $village->created_at ? $village->created_at->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue('H'.$row, $village->updated_at ? $village->updated_at->format('Y-m-d H:i:s') : '');

                // Add borders to data rows
                $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
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
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Center align all data
            $dataRange = 'A4:H'.($row - 1);
            $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Add summary at the bottom
            $summaryRow = $row + 1;
            $sheet->setCellValue('A'.$summaryRow, 'إجمالي عدد القرى: '.$villages->count());
            $sheet->mergeCells('A'.$summaryRow.':H'.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':H'.$summaryRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('VillagesExport: Spreadsheet created successfully', [
                'total_records' => $villages->count(),
                'execution_time' => $executionTime.' ms',
                'memory_usage' => memory_get_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('VillagesExport: Failed to create spreadsheet', [
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
