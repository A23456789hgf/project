<?php

namespace App\Exports;

use App\Models\Directorate;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DirectoratesExport
{
    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('DirectoratesExport: Starting data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('المديريات');

            // Add title row
            $sheet->setCellValue('A1', 'تقرير المديريات');
            $sheet->mergeCells('A1:F1');

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
            $sheet->getStyle('A1:F1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Add export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2:F2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Set headers
            $headers = [
                'المعرف',
                'اسم المديرية',
                'المحافظة',
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

            $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(25);

            // Get directorates data with governorate relationship
            $directorates = Directorate::with('governorate')->orderBy('name')->get();

            Log::info('DirectoratesExport: Data retrieved', [
                'total_records' => $directorates->count(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $row = 5;

            foreach ($directorates as $index => $directorate) {
                $sheet->setCellValue('A'.$row, $directorate->id);
                $sheet->setCellValue('B'.$row, $directorate->name);
                $sheet->setCellValue('C'.$row, $directorate->governorate ? $directorate->governorate->name : 'غير محدد');
                $sheet->setCellValue('D'.$row, $directorate->is_active ? 'نشط' : 'غير نشط');
                $sheet->setCellValue('E'.$row, $directorate->created_at ? $directorate->created_at->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue('F'.$row, $directorate->updated_at ? $directorate->updated_at->format('Y-m-d H:i:s') : '');

                // Add borders to data rows
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
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
            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Center align all data
            $dataRange = 'A4:F'.($row - 1);
            $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Add summary at the bottom
            $summaryRow = $row + 1;
            $activeCount = $directorates->where('is_active', true)->count();
            $inactiveCount = $directorates->where('is_active', false)->count();

            $sheet->setCellValue('A'.$summaryRow, 'إجمالي عدد المديريات: '.$directorates->count().' (نشط: '.$activeCount.', غير نشط: '.$inactiveCount.')');
            $sheet->mergeCells('A'.$summaryRow.':F'.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':F'.$summaryRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('DirectoratesExport: Spreadsheet created successfully', [
                'total_records' => $directorates->count(),
                'active_records' => $activeCount,
                'inactive_records' => $inactiveCount,
                'execution_time' => $executionTime.' ms',
                'memory_usage' => memory_get_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('DirectoratesExport: Failed to create spreadsheet', [
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
