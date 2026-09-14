<?php

namespace App\Exports;

use App\Models\ValueChainFinancingType;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ValueChainFinancingTypesExport
{
    protected $customHeaders;

    protected $customData;

    public function __construct($headers = null, $data = null)
    {
        $this->customHeaders = $headers;
        $this->customData = $data;
    }

    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('ValueChainFinancingTypesExport: Starting data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setTitle('أنواع تمويل سلاسل القيمة');

            $sheet->setCellValue('A1', 'تقرير أنواع تمويل سلاسل القيمة');

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

            if ($this->customHeaders) {
                $columnCount = count($this->customHeaders);
                $lastColumn = chr(64 + $columnCount);
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->getStyle('A1:'.$lastColumn.'1')->applyFromArray($titleStyle);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:'.$lastColumn.'2');
                $sheet->getStyle('A2:'.$lastColumn.'2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(20);

                $headers = $this->customHeaders;
                $data = $this->customData;
            } else {
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1:E1')->applyFromArray($titleStyle);
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(2)->setRowHeight(20);

                $headers = [
                    'المعرف',
                    'اسم النوع',
                    'الحالة',
                    'تاريخ الإنشاء',
                    'تاريخ التحديث',
                ];

                $types = ValueChainFinancingType::orderBy('name')->get();
                $data = [];
                foreach ($types as $type) {
                    $data[] = [
                        $type->id,
                        $type->name,
                        $type->is_active ? 'نشط' : 'غير نشط',
                        $type->created_at ? $type->created_at->format('Y-m-d H:i:s') : '',
                        $type->updated_at ? $type->updated_at->format('Y-m-d H:i:s') : '',
                    ];
                }
            }

            $sheet->fromArray($headers, null, 'A4');

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

            $lastColumn = chr(64 + count($headers));
            $sheet->getStyle('A4:'.$lastColumn.'4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(25);

            Log::info('ValueChainFinancingTypesExport: Data retrieved', [
                'total_records' => count($data),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $row = 5;

            foreach ($data as $rowData) {
                foreach ($rowData as $columnIndex => $value) {
                    $column = chr(65 + $columnIndex);
                    $sheet->setCellValue($column.$row, $value);
                }

                $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $row++;
            }

            for ($i = 0; $i < count($headers); $i++) {
                $column = chr(65 + $i);
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $dataRange = 'A4:'.$lastColumn.($row - 1);
            $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $summaryRow = $row + 1;
            $sheet->setCellValue('A'.$summaryRow, 'إجمالي الأنواع: '.count($data));
            $sheet->mergeCells('A'.$summaryRow.':'.$lastColumn.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':'.$lastColumn.$summaryRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('ValueChainFinancingTypesExport: Spreadsheet created successfully', [
                'total_records' => count($data),
                'execution_time' => $executionTime.' ms',
                'memory_usage' => memory_get_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('ValueChainFinancingTypesExport: Failed to create spreadsheet', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time' => $executionTime.' ms',
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw $e;
        }
    }
}
