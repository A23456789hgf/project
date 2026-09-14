<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\SheetView;

class ProjectFixedStructureExport implements WithEvents
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $project = $this->project;

                // ═══════════════════════════════════════════════════════════
                // 1. PAGE SETUP (A4 Portrait, RTL, Margins)
                // ═══════════════════════════════════════════════════════════
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageMargins()->setTop(0.4);
                $sheet->getPageMargins()->setRight(0.4);
                $sheet->getPageMargins()->setLeft(0.4);
                $sheet->getPageMargins()->setBottom(0.4);
                $sheet->setRightToLeft(true);

                // Print settings
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getSheetView()->setView(SheetView::SHEETVIEW_PAGE_LAYOUT);

                // ═══════════════════════════════════════════════════════════
                // 2. GLOBAL STYLING (Font, Alignment)
                // ═══════════════════════════════════════════════════════════
                // $sheet->getParent()->getDefaultStyle()->getFont()->setName('Traditional Arabic')->setSize(11);
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('cairo')->setSize(11);
                $sheet->getParent()->getDefaultStyle()->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ═══════════════════════════════════════════════════════════
                // 3. GRID LAYOUT (Column Widths A-Z)
                // ═══════════════════════════════════════════════════════════
                $columnWidths = [
                    'A' => 3.5, 'B' => 3.5, 'C' => 3.5, 'D' => 3.5, 'E' => 3.5,
                    'F' => 3.5, 'G' => 3.5, 'H' => 3.5, 'I' => 3.5, 'J' => 3.5,
                    'K' => 3.5, 'L' => 3.5, 'M' => 3.5, 'N' => 3.5, 'O' => 3.5,
                    'P' => 3.5, 'Q' => 3.5, 'R' => 3.5, 'S' => 3.5, 'T' => 3.5,
                    'U' => 3.5, 'V' => 3.5, 'W' => 3.5, 'X' => 3.5, 'Y' => 3.5, 'Z' => 3.5,
                ];

                foreach ($columnWidths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // ═══════════════════════════════════════════════════════════
                // 4. HEADER SECTION (Rows 1-5)
                // ═══════════════════════════════════════════════════════════
                $currentRow = 1;

                // Main Header
                $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'الجمهورية اليمنية');
                $this->styleHeader($sheet, "A{$currentRow}:Z{$currentRow}", 14, true);

                $currentRow++;
                $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'وزارة الزراعة والثروة السمكية والموارد المائية ');
                $this->styleHeader($sheet, "A{$currentRow}:Z{$currentRow}", 13, false);

                $currentRow++;
                $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'بطاقة مشروع تنموي');
                $this->styleHeader($sheet, "A{$currentRow}:Z{$currentRow}", 15, true);
                $sheet->getStyle("A{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE8F4F8');

                $currentRow++;
                $sheet->getRowDimension($currentRow)->setRowHeight(5); // Spacer

                // ═══════════════════════════════════════════════════════════
                // 5. SECTION 1: BASIC PROJECT INFORMATION
                // ═══════════════════════════════════════════════════════════
                $currentRow++;
                $this->drawSectionTitle($sheet, $currentRow, 'القسم الأول: معلومات المشروع الأساسية');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'اسم المشروع:', 'F', 'Z', $project->project_name ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'رقم المشروع:', 'F', 'M', $project->form_number ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'الحالة:', 'R', 'Z', $this->getStatusText($project->status));

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'البرنامج:', 'F', 'M', optional($project->program)->name ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'القطاع:', 'R', 'Z', optional($project->domain)->name ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'المجال الفرعي:', 'F', 'M', optional($project->subdomain)->name ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'نوع التدخل:', 'R', 'Z', optional($project->intervention)->name ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'الأولوية:', 'F', 'M', optional($project->priority)->name ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'الفئة المستهدفة:', 'R', 'Z', optional($project->targetCategory)->name ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'عدد المستفيدين:', 'F', 'M', number_format($project->number_of_beneficiaries ?? 0));
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'مدة المشروع (يوم):', 'R', 'Z', $project->project_duration ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'تاريخ البدء (ميلادي):', 'F', 'M', $project->start_date_gregorian ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'تاريخ البدء (هجري):', 'R', 'Z', $project->start_date_hijri ?? '-');

                $currentRow++;
                $this->drawLabelValue($sheet, $currentRow, 'A', 'E', 'تاريخ الانتهاء (ميلادي):', 'F', 'M', $project->end_date_gregorian ?? '-');
                $this->drawLabelValue($sheet, $currentRow, 'N', 'Q', 'تاريخ الانتهاء (هجري):', 'R', 'Z', $project->end_date_hijri ?? '-');

                // ═══════════════════════════════════════════════════════════
                // 6. SECTION 2: PROJECT DETAILS (from ProjectDetail)
                // ═══════════════════════════════════════════════════════════
                $currentRow++;
                $sheet->getRowDimension($currentRow)->setRowHeight(5);

                $currentRow++;
                $this->drawSectionTitle($sheet, $currentRow, 'القسم الثاني: تفاصيل المشروع');

                $detail = $project->detail;

                $currentRow++;
                $this->drawTextBlock($sheet, $currentRow, 'ملخص المشروع:', $detail->project_summary ?? 'لا يوجد ملخص متاح.', 3);
                $currentRow += 4;

                $this->drawTextBlock($sheet, $currentRow, 'مقدمة المشروع:', $detail->project_introduction ?? 'لا توجد مقدمة متاحة.', 3);
                $currentRow += 4;

                $this->drawTextBlock($sheet, $currentRow, 'المشكلة والمبررات:', $detail->problem_and_justification ?? 'لا توجد مبررات محددة.', 3);
                $currentRow += 4;

                $this->drawTextBlock($sheet, $currentRow, 'مكونات المشروع:', $detail->project_components ?? 'لا توجد مكونات محددة.', 3);
                $currentRow += 4;

                $this->drawTextBlock($sheet, $currentRow, 'الأثر المتوقع:', $detail->expected_impact ?? 'لا يوجد أثر محدد.', 3);
                $currentRow += 4;

                // ═══════════════════════════════════════════════════════════
                // 7. SECTION 3: LOCATIONS
                // ═══════════════════════════════════════════════════════════
                $currentRow++;
                $this->drawSectionTitle($sheet, $currentRow, 'القسم الثالث: المواقع الجغرافية');

                $currentRow++;
                if ($project->locations && $project->locations->count() > 0) {
                    // Table Header
                    $this->drawTableHeader($sheet, $currentRow, [
                        ['A', 'F', 'المحافظة'],
                        ['G', 'L', 'المديرية'],
                        ['M', 'R', 'المنطقة'],
                        ['S', 'Z', 'القرية'],
                    ]);

                    foreach ($project->locations as $location) {
                        $currentRow++;
                        $this->drawTableRow($sheet, $currentRow, [
                            ['A', 'F', optional($location->governorate)->name ?? '-'],
                            ['G', 'L', optional($location->directorate)->name ?? '-'],
                            ['M', 'R', optional($location->subArea)->name ?? '-'],
                            ['S', 'Z', optional($location->village)->name ?? '-'],
                        ]);
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد مواقع محددة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                }

                // ═══════════════════════════════════════════════════════════
                // 8. SECTION 4: ENTITIES (Supervising, Implementing, Participating, Beneficiary)
                // ═══════════════════════════════════════════════════════════
                $currentRow++;
                $sheet->getRowDimension($currentRow)->setRowHeight(5);

                $currentRow++;
                $this->drawSectionTitle($sheet, $currentRow, 'القسم الرابع: الجهات ذات العلاقة');

                // Supervising Authorities
                $currentRow++;
                $this->drawSubSectionTitle($sheet, $currentRow, 'الجهات المشرفة:');
                $currentRow++;
                if ($project->supervisingAuthorities && $project->supervisingAuthorities->count() > 0) {
                    foreach ($project->supervisingAuthorities as $entity) {
                        $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", '• '.(optional($entity->authority)->agency_name ?? '-'));
                        $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}");
                        $currentRow++;
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد جهات مشرفة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                    $currentRow++;
                }

                // Implementing Entities
                $this->drawSubSectionTitle($sheet, $currentRow, 'الجهات المنفذة:');
                $currentRow++;
                if ($project->implementingEntities && $project->implementingEntities->count() > 0) {
                    foreach ($project->implementingEntities as $entity) {
                        $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", '• '.(optional($entity->authority)->agency_name ?? '-'));
                        $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}");
                        $currentRow++;
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد جهات منفذة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                    $currentRow++;
                }

                // Participating Entities
                $this->drawSubSectionTitle($sheet, $currentRow, 'الجهات المشاركة:');
                $currentRow++;
                if ($project->participatingEntities && $project->participatingEntities->count() > 0) {
                    foreach ($project->participatingEntities as $entity) {
                        $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", '• '.($entity->entity_name ?? '-'));
                        $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}");
                        $currentRow++;
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد جهات مشاركة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                    $currentRow++;
                }

                // Beneficiary Entities
                $this->drawSubSectionTitle($sheet, $currentRow, 'الجهات المستفيدة:');
                $currentRow++;
                if ($project->beneficiaryEntities && $project->beneficiaryEntities->count() > 0) {
                    foreach ($project->beneficiaryEntities as $entity) {
                        $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", '• '.($entity->entity_name ?? '-'));
                        $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}");
                        $currentRow++;
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد جهات مستفيدة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                    $currentRow++;
                }

                // ═══════════════════════════════════════════════════════════
                // 9. SECTION 5: FINANCIAL INFORMATION
                // ═══════════════════════════════════════════════════════════
                $currentRow++;
                $sheet->getRowDimension($currentRow)->setRowHeight(5);

                $currentRow++;
                $this->drawSectionTitle($sheet, $currentRow, 'القسم الخامس: المعلومات المالية');

                // Cost Summary
                if ($project->cost) {
                    $currentRow++;
                    $this->drawLabelValue($sheet, $currentRow, 'A', 'H', 'الكلفة الإجمالية:', 'I', 'Z', number_format($project->cost->total_cost ?? 0, 2).' ريال');

                    $currentRow++;
                    $this->drawLabelValue($sheet, $currentRow, 'A', 'H', 'مساهمة المجتمع:', 'I', 'Z', number_format($project->cost->community_contribution ?? 0, 2).' ريال');

                    $currentRow++;
                    $this->drawLabelValue($sheet, $currentRow, 'A', 'H', 'المبلغ المطلوب:', 'I', 'Z', number_format($project->cost->required_amount ?? 0, 2).' ريال');
                }

                // Funding Sources
                $currentRow++;
                $this->drawSubSectionTitle($sheet, $currentRow, 'مصادر التمويل:');

                $currentRow++;
                if ($project->financings && $project->financings->count() > 0) {
                    $this->drawTableHeader($sheet, $currentRow, [
                        ['A', 'H', 'الجهة الممولة'],
                        ['I', 'P', 'نوع التمويل'],
                        ['Q', 'V', 'المبلغ'],
                        ['W', 'Z', 'ملاحظات'],
                    ]);

                    foreach ($project->financings as $funding) {
                        $currentRow++;
                        $this->drawTableRow($sheet, $currentRow, [
                            ['A', 'H', optional($funding->fundingSource)->name ?? '-'],
                            ['I', 'P', optional($funding->financingType)->name ?? '-'],
                            ['Q', 'V', number_format($funding->amount ?? 0, 2)],
                            ['W', 'Z', $funding->notes ?? '-'],
                        ]);
                    }
                } else {
                    $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'لا توجد مصادر تمويل محددة');
                    $this->styleCell($sheet, "A{$currentRow}:Z{$currentRow}", false, Alignment::HORIZONTAL_CENTER);
                }

                // ═══════════════════════════════════════════════════════════
                // 10. FOOTER
                // ═══════════════════════════════════════════════════════════
                $currentRow += 2;
                $sheet->mergeCells("A{$currentRow}:Z{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'تم إصدار هذه البطاقة بتاريخ: '.date('Y-m-d H:i'));
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$currentRow}")->getFont()->setSize(9)->setItalic(true);
                $sheet->getStyle("A{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F5F5');

                // Apply outer border to entire document
                $lastRow = $currentRow;
                $sheet->getStyle("A1:Z{$lastRow}")->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_THICK)
                    ->getColor()->setARGB('FF000000');
            },
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function styleHeader($sheet, $range, $fontSize, $bold)
    {
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getFont()->setSize($fontSize);
        if ($bold) {
            $sheet->getStyle($range)->getFont()->setBold(true);
        }
    }

    private function drawSectionTitle($sheet, $row, $title)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E9F7');
        $sheet->getStyle("A{$row}:Z{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    private function drawSubSectionTitle($sheet, $row, $title)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEEEEEE');
        $sheet->getStyle("A{$row}:Z{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    private function drawLabelValue($sheet, $row, $labelStart, $labelEnd, $labelText, $valStart, $valEnd, $valText)
    {
        // Label
        $sheet->mergeCells("{$labelStart}{$row}:{$labelEnd}{$row}");
        $sheet->setCellValue("{$labelStart}{$row}", $labelText);
        $sheet->getStyle("{$labelStart}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("{$labelStart}{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF8F9FA');
        $sheet->getStyle("{$labelStart}{$row}:{$labelEnd}{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Value
        $sheet->mergeCells("{$valStart}{$row}:{$valEnd}{$row}");
        $sheet->setCellValue("{$valStart}{$row}", $valText);
        $sheet->getStyle("{$valStart}{$row}:{$valEnd}{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    private function drawTextBlock($sheet, $row, $label, $text, $height)
    {
        // Label row
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEEEEEE');
        $sheet->getStyle("A{$row}:Z{$row}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Text row
        $row++;
        $endRow = $row + $height - 1;
        $sheet->mergeCells("A{$row}:Z{$endRow}");
        $sheet->setCellValue("A{$row}", $text);
        $sheet->getStyle("A{$row}")->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$row}:Z{$endRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        for ($i = $row; $i <= $endRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(18);
        }
    }

    private function drawTableHeader($sheet, $row, $columns)
    {
        foreach ($columns as $col) {
            $sheet->mergeCells("{$col[0]}{$row}:{$col[1]}{$row}");
            $sheet->setCellValue("{$col[0]}{$row}", $col[2]);
            $sheet->getStyle("{$col[0]}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("{$col[0]}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$col[0]}{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2EDF9');
            $sheet->getStyle("{$col[0]}{$row}:{$col[1]}{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }
    }

    private function drawTableRow($sheet, $row, $columns)
    {
        foreach ($columns as $col) {
            $sheet->mergeCells("{$col[0]}{$row}:{$col[1]}{$row}");
            $sheet->setCellValue("{$col[0]}{$row}", $col[2]);
            $sheet->getStyle("{$col[0]}{$row}:{$col[1]}{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }
    }

    private function styleCell($sheet, $range, $bold = false, $align = Alignment::HORIZONTAL_RIGHT)
    {
        if ($bold) {
            $sheet->getStyle($range)->getFont()->setBold(true);
        }
        $sheet->getStyle($range)->getAlignment()->setHorizontal($align);
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    private function getStatusText($status)
    {
        $statusMap = [
            'draft' => 'مسودة',
            'final' => 'نهائي',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'pending' => 'قيد المراجعة',
        ];

        return $statusMap[$status] ?? $status ?? 'غير محدد';
    }
}
