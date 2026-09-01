<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectCardExport
{
    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('ProjectCardExport: Starting export', [
                'project_id' => $this->project->id,
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            // Set sheet title
            $sheet->setTitle('بطاقة المشروع');

            // Title
            $sheet->setCellValue('A1', 'بطاقة المشروع');
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1:D1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF007BFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(25);

            // Export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.now()->format('Y-m-d H:i:s'));
            $sheet->mergeCells('A2:D2');
            $sheet->getStyle('A2:D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 4;

            // Project Information
            $row = $this->addSectionHeader($sheet, $row, 'معلومات المشروع');

            $this->addInfoRow($sheet, $row++, 'رقم النموذج', $this->project->form_number);
            $this->addInfoRow($sheet, $row++, 'اسم المشروع', $this->project->project_name);
            $this->addInfoRow($sheet, $row++, 'البرنامج', $this->project->program?->name ?? 'غير محدد');
            $this->addInfoRow($sheet, $row++, 'المجال', $this->project->domain?->name ?? 'غير محدد');
            $this->addInfoRow($sheet, $row++, 'المجال الفرعي', $this->project->subdomain?->name ?? 'غير محدد');

            $row++;

            // Schedule Information
            $row = $this->addSectionHeader($sheet, $row, 'جدول التنفيذ');

            $startDate = $this->project->start_date_gregorian
                ? Carbon::parse($this->project->start_date_gregorian)->format('Y-m-d')
                : 'غير محدد';
            $endDate = $this->project->end_date_gregorian
                ? Carbon::parse($this->project->end_date_gregorian)->format('Y-m-d')
                : 'غير محدد';

            $this->addInfoRow($sheet, $row++, 'تاريخ البداية (ميلادي)', $startDate);
            $this->addInfoRow($sheet, $row++, 'تاريخ النهاية (ميلادي)', $endDate);
            $this->addInfoRow($sheet, $row++, 'المدة الزمنية (أيام)', $this->project->project_duration ?? 'غير محدد');

            $row++;

            // Project Summary
            $row = $this->addSectionHeader($sheet, $row, 'ملخص المشروع');
            $summary = $this->project->detail?->project_summary ?? 'لم يتم تحديد ملخص';
            $row = $this->addWrappedText($sheet, $row, 'الملخص', $summary);
            $row++;

            // Problems and Justifications
            $row = $this->addSectionHeader($sheet, $row, 'المشاكل والتبريرات');
            $problems = $this->project->detail?->problem_and_justification ?? 'لم يتم تحديد مشاكل';
            $row = $this->addWrappedText($sheet, $row, 'التفاصيل', $problems);
            $row++;

            // Project Locations Table
            if ($this->project->locations && $this->project->locations->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'المواقع الجغرافية');

                // Table headers
                $sheet->setCellValue('A'.$row, 'المحافظة');
                $sheet->setCellValue('B'.$row, 'المديرية');
                $sheet->setCellValue('C'.$row, 'المنطقة الفرعية');
                $sheet->setCellValue('D'.$row, 'القرية');

                $this->styleHeaderRow($sheet, $row, 'D');
                $row++;

                // Locations data
                foreach ($this->project->locations as $location) {
                    $sheet->setCellValue('A'.$row, $location->governorate?->name ?? 'غير محدد');
                    $sheet->setCellValue('B'.$row, $location->directorate?->name ?? 'غير محدد');
                    $sheet->setCellValue('C'.$row, $location->subArea?->name ?? 'غير محدد');
                    $sheet->setCellValue('D'.$row, $location->village?->name ?? 'غير محدد');

                    $this->styleDataRow($sheet, $row, 'D');
                    $row++;
                }
                $row++;
            }

            // Implementing Agencies Table
            if ($this->project->implementingEntities && $this->project->implementingEntities->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'جهات التنفيذ');

                // Table headers
                $sheet->setCellValue('A'.$row, 'نوع الجهة');
                $sheet->setCellValue('B'.$row, 'اسم الجهة');
                $sheet->mergeCells('C'.$row.':D'.$row);

                $this->styleHeaderRow($sheet, $row, 'D');
                $row++;

                // Implementing entities data
                foreach ($this->project->implementingEntities as $entity) {
                    $entityType = ($entity->authority_type ?? $entity->entity_type) === 'internal' ? 'داخلية' : 'خارجية';
                    $sheet->setCellValue('A'.$row, $entityType);
                    $sheet->setCellValue('B'.$row, $entity->authority?->agency_name ?? 'غير محدد');
                    $sheet->mergeCells('C'.$row.':D'.$row);

                    $this->styleDataRow($sheet, $row, 'D');
                    $row++;
                }
                $row++;
            }

            // Participating Agencies Table
            if ($this->project->participatingEntities && $this->project->participatingEntities->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'الجهات المشاركة');

                // Table headers
                $sheet->setCellValue('A'.$row, 'نوع الجهة');
                $sheet->setCellValue('B'.$row, 'اسم الجهة');
                $sheet->mergeCells('C'.$row.':D'.$row);

                $this->styleHeaderRow($sheet, $row, 'D');
                $row++;

                // Participating entities data
                foreach ($this->project->participatingEntities as $entity) {
                    $entityType = ($entity->authority_type ?? $entity->entity_type) === 'internal' ? 'داخلية' : 'خارجية';
                    $sheet->setCellValue('A'.$row, $entityType);
                    $sheet->setCellValue('B'.$row, $entity->authority?->agency_name ?? 'غير محدد');
                    $sheet->mergeCells('C'.$row.':D'.$row);

                    $this->styleDataRow($sheet, $row, 'D');
                    $row++;
                }
                $row++;
            }

            // Risks Table
            if ($this->project->risks && $this->project->risks->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'المخاطر');

                // Table headers
                $sheet->setCellValue('A'.$row, 'المخاطرة');
                $sheet->setCellValue('B'.$row, 'مستوى المخاطر');
                $sheet->setCellValue('C'.$row, 'الحل المقترح');

                $this->styleHeaderRow($sheet, $row, 'D');
                $row++;

                // Risks data
                foreach ($this->project->risks as $risk) {
                    $sheet->setCellValue('A'.$row, $risk->risk ?? 'غير محدد');
                    $sheet->setCellValue('B'.$row, $risk->risk_rate ?? 'غير محدد');
                    $sheet->setCellValue('C'.$row, $risk->proposed_solution ?? 'غير محدد');
                    $sheet->mergeCells('D'.$row.':D'.$row);

                    $this->styleDataRow($sheet, $row, 'D');
                    $row++;
                }
                $row++;
            }

            // General Objectives Table
            if ($this->project->mainObjectives && $this->project->mainObjectives->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'الأهداف الرئيسية');

                // Table headers
                $sheet->setCellValue('A'.$row, 'الهدف');
                $sheet->setCellValue('B'.$row, 'المؤشر');
                $sheet->setCellValue('C'.$row, 'وحدة القياس');

                $this->styleHeaderRow($sheet, $row, 'D');
                $row++;

                // General objectives data
                foreach ($this->project->mainObjectives as $objective) {
                    $sheet->setCellValue('A'.$row, $objective->objective ?? 'غير محدد');
                    $sheet->setCellValue('B'.$row, $objective->indicator ?? 'غير محدد');
                    $sheet->setCellValue('C'.$row, $objective->indicator_unit ?? 'غير محدد');
                    $sheet->mergeCells('D'.$row.':D'.$row);

                    $this->styleDataRow($sheet, $row, 'D');
                    $row++;
                }
                $row++;
            }

            // Specific Objectives with Results and Outputs Table
            if ($this->project->specialObjectives && $this->project->specialObjectives->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'الأهداف الخاصة مع النتائج والمخرجات');

                // Table headers
                $sheet->setCellValue('A'.$row, 'نوع البند');
                $sheet->setCellValue('B'.$row, 'البند');
                $sheet->setCellValue('C'.$row, 'المؤشر');
                $sheet->setCellValue('D'.$row, 'القيمة المستهدفة');

                $this->styleHeaderRow($sheet, $row, 'E');
                $row++;

                // Special objectives data with results and outputs
                foreach ($this->project->specialObjectives as $objective) {
                    // Special objective header row
                    $sheet->setCellValue('A'.$row, $objective->objective ?? 'غير محدد');
                    $sheet->mergeCells('A'.$row.':E'.$row);
                    $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFE7F3FF'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $row++;

                    // Results
                    if ($objective->results && $objective->results->count() > 0) {
                        foreach ($objective->results as $result) {
                            $sheet->setCellValue('A'.$row, 'نتيجة');
                            $sheet->setCellValue('B'.$row, $result->result_name ?? 'غير محدد');
                            $sheet->setCellValue('C'.$row, $result->indicator_type ?? 'غير محدد');
                            $sheet->setCellValue('D'.$row, $result->target_value ?? 'غير محدد');
                            $sheet->setCellValue('E'.$row, $result->indicator_unit ?? 'غير محدد');

                            $this->styleDataRow($sheet, $row, 'E');
                            $row++;
                        }
                    }

                    // Outputs
                    $outputs = $this->project->resultOutputs->where('special_objective_id', $objective->id);
                    if ($outputs && $outputs->count() > 0) {
                        foreach ($outputs as $output) {
                            $sheet->setCellValue('A'.$row, 'مخرج');
                            $sheet->setCellValue('B'.$row, $output->output ?? 'غير محدد');
                            $sheet->setCellValue('C'.$row, $output->indicator_type ?? 'غير محدد');
                            $sheet->setCellValue('D'.$row, $output->target_value ?? 'غير محدد');
                            $sheet->setCellValue('E'.$row, $output->indicator_unit ?? 'غير محدد');

                            $this->styleDataRow($sheet, $row, 'E');
                            $row++;
                        }
                    }
                }
                $row++;
            }

            // Implementation Activities with Procedures Table
            if ($this->project->executiveActivities && $this->project->executiveActivities->count() > 0) {
                $row = $this->addSectionHeader($sheet, $row, 'أنشطة التنفيذ مع الإجراءات');

                // Table headers
                $sheet->setCellValue('A'.$row, 'نوع البند');
                $sheet->setCellValue('B'.$row, 'اسم البند');
                $sheet->setCellValue('C'.$row, 'الوزن');
                $sheet->setCellValue('D'.$row, 'تاريخ البداية');
                $sheet->setCellValue('E'.$row, 'تاريخ النهاية');

                $this->styleHeaderRow($sheet, $row, 'F');
                $row++;

                // Implementation activities data with actions
                foreach ($this->project->executiveActivities as $activity) {
                    // Activity header row
                    $sheet->setCellValue('A'.$row, $activity->name ?? 'غير محدد');
                    $sheet->mergeCells('A'.$row.':F'.$row);
                    $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF0F8FF'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $row++;

                    // Actions
                    if ($activity->actions && $activity->actions->count() > 0) {
                        foreach ($activity->actions as $action) {
                            $sheet->setCellValue('A'.$row, 'إجراء');
                            $sheet->setCellValue('B'.$row, $action->action ?? 'غير محدد');
                            $sheet->setCellValue('C'.$row, $action->weight ?? '0');
                            $sheet->setCellValue('D'.$row, $action->start_date ? Carbon::parse($action->start_date)->format('Y-m-d') : 'غير محدد');
                            $sheet->setCellValue('E'.$row, $action->end_date ? Carbon::parse($action->end_date)->format('Y-m-d') : 'غير محدد');
                            $sheet->setCellValue('F'.$row, $action->verification_means ?? 'غير محدد');

                            $this->styleDataRow($sheet, $row, 'F');
                            $row++;
                        }
                    }
                }
                $row++;
            }

            // Implementation Procedures Costs Table
            if ($this->project->executiveActivities && $this->project->executiveActivities->count() > 0) {
                $hasCosts = $this->project->executiveActivities->some(function ($activity) {
                    return $activity->actions->some(function ($action) {
                        return $action->costs->count() > 0;
                    });
                });

                if ($hasCosts) {
                    $row = $this->addSectionHeader($sheet, $row, 'تكاليف الإجراءات التنفيذية');

                    // Table headers
                    $sheet->setCellValue('A'.$row, 'النشاط');
                    $sheet->setCellValue('B'.$row, 'الإجراء');
                    $sheet->setCellValue('C'.$row, 'البند المالي');
                    $sheet->setCellValue('D'.$row, 'الوحدة');
                    $sheet->setCellValue('E'.$row, 'السعر');
                    $sheet->setCellValue('F'.$row, 'الكمية');
                    $sheet->setCellValue('G'.$row, 'الإجمالي');

                    $this->styleHeaderRow($sheet, $row, 'G');
                    $row++;

                    // Costs data
                    foreach ($this->project->executiveActivities as $activity) {
                        foreach ($activity->actions as $action) {
                            if ($action->costs && $action->costs->count() > 0) {
                                foreach ($action->costs as $cost) {
                                    $sheet->setCellValue('A'.$row, $activity->name ?? 'غير محدد');
                                    $sheet->setCellValue('B'.$row, $action->action ?? 'غير محدد');
                                    $sheet->setCellValue('C'.$row, $cost->financialItem?->name ?? 'غير محدد');
                                    $sheet->setCellValue('D'.$row, $cost->unit ?? 'غير محدد');
                                    $sheet->setCellValue('E'.$row, $cost->amount ?? 0);
                                    $sheet->setCellValue('F'.$row, $cost->quantity ?? 0);
                                    $sheet->setCellValue('G'.$row, $cost->total ?? 0);

                                    $this->styleDataRow($sheet, $row, 'G');
                                    $row++;
                                }
                            }
                        }
                    }
                    $row++;
                }
            }

            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);

            // Set RTL direction
            $sheet->setRightToLeft(true);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('ProjectCardExport: Export completed successfully', [
                'project_id' => $this->project->id,
                'execution_time' => $executionTime.' ms',
                'timestamp' => now()->toDateTimeString(),
            ]);

            return $spreadsheet;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('ProjectCardExport: Export failed', [
                'project_id' => $this->project->id,
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

    private function addSectionHeader($sheet, $row, $title)
    {
        $sheet->setCellValue('A'.$row, $title);
        $sheet->mergeCells('A'.$row.':D'.$row);
        $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF003D7A'],
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
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    private function addInfoRow($sheet, $row, $label, $value)
    {
        $sheet->setCellValue('A'.$row, $label);
        $sheet->setCellValue('B'.$row, $value);
        $sheet->mergeCells('B'.$row.':D'.$row);

        $sheet->getStyle('A'.$row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0F0F0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension($row)->setRowHeight(18);
    }

    private function addWrappedText($sheet, $row, $label, $value)
    {
        $sheet->setCellValue('A'.$row, $label);
        $sheet->setCellValue('B'.$row, $value);
        $sheet->mergeCells('B'.$row.':D'.$row);

        $sheet->getStyle('A'.$row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF0F0F0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        $sheet->getStyle('B'.$row.':D'.$row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        $lineCount = substr_count($value, "\n") + 1;
        $height = max(60, $lineCount * 15);
        $sheet->getRowDimension($row)->setRowHeight($height);

        return $row + 1;
    }

    private function styleHeaderRow($sheet, $row, $endColumn)
    {
        $sheet->getStyle('A'.$row.':'.$endColumn.$row)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF007BFF'],
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
        ]);
        $sheet->getRowDimension($row)->setRowHeight(18);
    }

    private function styleDataRow($sheet, $row, $endColumn)
    {
        $sheet->getStyle('A'.$row.':'.$endColumn.$row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(18);
    }
}
