<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectsExport
{
    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('ProjectsExport: Starting data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('المشاريع');

            // Add title row
            $sheet->setCellValue('A1', 'تقرير المشاريع');
            $sheet->mergeCells('A1:P1');

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
            $sheet->getStyle('A1:P1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Add export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
            $sheet->mergeCells('A2:P2');
            $sheet->getStyle('A2:P2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Set headers
            $headers = [
                'المعرف',
                'رقم الاستمارة',
                'اسم المشروع',
                'البرنامج',
                'المجال الرئيسي',
                'المجال الفرعي',
                'نوع التدخل',
                'الأولوية',
                'تاريخ البداية',
                'تاريخ النهاية',
                'الحالة',
                'عدد المستفيدين',
                'الموقع',
                'الجهة المنفذة',
                'تاريخ الإنشاء',
                'تاريخ التحديث',
            ];

            // Add headers to the fourth row
            $sheet->fromArray($headers, null, 'A4');

            // Style the header row
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 11,
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
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A4:P4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(25);

            // Get projects data with relationships
            $projects = Project::with([
                'program',
                'domain',
                'subdomain',
                'intervention',
                'priority',
                'locations.governorate',
                'implementingEntities.authority',
            ])
                ->orderBy('project_name')
                ->get();

            Log::info('ProjectsExport: Data retrieved', [
                'total_records' => $projects->count(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $row = 5;

            foreach ($projects as $index => $project) {
                // Get location names (governorates)
                $locations = $project->locations->pluck('governorate.name')->unique()->implode(', ');

                // Get implementing entities
                $implementingEntities = $project->implementingEntities->pluck('authority.name')->unique()->implode(', ');

                $sheet->setCellValue('A'.$row, $project->id);
                $sheet->setCellValue('B'.$row, $project->form_number ?? '');
                $sheet->setCellValue('C'.$row, $project->project_name ?? '');
                $sheet->setCellValue('D'.$row, $project->program ? $project->program->name : '');
                $sheet->setCellValue('E'.$row, $project->domain ? $project->domain->name : '');
                $sheet->setCellValue('F'.$row, $project->subdomain ? $project->subdomain->name : '');
                $sheet->setCellValue('G'.$row, $project->intervention ? $project->intervention->name : '');
                $sheet->setCellValue('H'.$row, $project->priority ? $project->priority->name : '');
                $sheet->setCellValue('I'.$row, $project->start_date_gregorian ?? '');
                $sheet->setCellValue('J'.$row, $project->end_date_gregorian ?? '');
                $sheet->setCellValue('K'.$row, $this->getStatusLabel($project->status));
                $sheet->setCellValue('L'.$row, $project->number_of_beneficiaries ?? '');
                $sheet->setCellValue('M'.$row, $locations);
                $sheet->setCellValue('N'.$row, $implementingEntities);
                $sheet->setCellValue('O'.$row, $project->created_at ? $project->created_at->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue('P'.$row, $project->updated_at ? $project->updated_at->format('Y-m-d H:i:s') : '');

                // Add borders to data rows
                $sheet->getStyle('A'.$row.':P'.$row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'P') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Set minimum column widths for better readability
            foreach (range('A', 'P') as $column) {
                $currentWidth = $sheet->getColumnDimension($column)->getWidth();
                $newWidth = max($currentWidth, 15);
                $sheet->getColumnDimension($column)->setWidth($newWidth);
            }

            // Add summary at the bottom
            $summaryRow = $row + 1;
            $sheet->setCellValue('A'.$summaryRow, 'إجمالي عدد المشاريع: '.$projects->count());
            $sheet->mergeCells('A'.$summaryRow.':P'.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':P'.$summaryRow)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFFE699',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('ProjectsExport: Spreadsheet created successfully', [
                'total_records' => $projects->count(),
                'execution_time' => $executionTime.' ms',
                'memory_usage' => memory_get_usage(true).' bytes',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('ProjectsExport: Failed to create spreadsheet', [
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

    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status)
    {
        $statusLabels = [
            'draft' => 'مسودة',
            'pending' => 'قيد الانتظار',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
        ];

        return $statusLabels[$status] ?? $status;
    }
}
