<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ProjectFixedStructureSheet implements WithEvents, WithTitle
{
    protected $project;

    protected $sheetTitle;

    // Professional Government Colors
    const COLOR_RED_DARK = 'FFC00000';

    const COLOR_RED_LIGHT = 'FFFF0000';

    const COLOR_GRAY_LIGHT = 'FFF2F2F2';

    const COLOR_WHITE = 'FFFFFFFF';

    const COLOR_BLACK = 'FF000000';

    public function __construct(Project $project, string $sheetTitle)
    {
        $this->project = $project;
        $this->sheetTitle = $sheetTitle;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $project = $this->project;

                // Page Setup
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageMargins()->setTop(0.5)->setRight(0.5)->setLeft(0.5)->setBottom(0.5);
                $sheet->setRightToLeft(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Set column widths
                for ($col = 'A'; $col <= 'Z'; $col++) {
                    $sheet->getColumnDimension($col)->setWidth(4);
                }

                $currentRow = 1;

                // HEADER
                $this->drawHeader($sheet, $currentRow);
                $currentRow += 3;

                // PROJECT CARD
                $this->drawProjectCard($sheet, $currentRow, $project);
                $currentRow += 2;

                // BASIC INFO
                $currentRow = $this->drawBasicInfoTable($sheet, $currentRow, $project);
                $currentRow++;

                // TECHNICAL SUPPORT
                $currentRow = $this->drawTechnicalSupportTable($sheet, $currentRow, $project);
                $currentRow++;

                // RISKS
                $currentRow = $this->drawRisksTable($sheet, $currentRow, $project);
                $currentRow++;

                // EXECUTIVE ACTIVITIES
                $currentRow = $this->drawExecutiveActivitiesTable($sheet, $currentRow, $project);
                $currentRow++;

                // PARTNERSHIP OUTPUTS (NEW)
                $currentRow = $this->drawPartnershipOutputsTable($sheet, $currentRow, $project);
                $currentRow++;

                // SIGNATURES
                $this->drawSignaturesSection($sheet, $currentRow);
            },
        ];
    }

    private function drawHeader($sheet, $row)
    {
        $imagePath = public_path('assets/images/header_project_card.png');

        if (file_exists($imagePath)) {
            $drawing = new Drawing;
            $drawing->setName('Header');
            $drawing->setDescription('Project Header');
            $drawing->setPath($imagePath);
            $drawing->setCoordinates("A{$row}");
            $drawing->setWidth(700); // Set width to cover A-Z columns approx
            // $drawing->setHeight(80); // Let height calculate based on aspect ratio
            $drawing->setWorksheet($sheet);

            // Increase row height to fit image
            $sheet->getRowDimension($row)->setRowHeight(50);
        } else {
            // Fallback if image not found
            $sheet->mergeCells("D{$row}:W{$row}");
            $sheet->setCellValue("D{$row}", ' مشاريع الأولويات الحكومية العاجلة');
            $this->applyStyle($sheet, "D{$row}:W{$row}", [
                'font' => ['size' => 14, 'bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
    }

    private function drawProjectCard($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'بطاقة بيانات المشروع');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $sheet->getRowDimension($row)->setRowHeight(25);
    }

    private function drawBasicInfoTable($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'البيانات الأساسية');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        $data = [
            ['اسم المشروع', $project->project_name ?? '-'],
            ['رقم المشروع', $project->form_number ?? '-'],
            ['البرنامج', optional($project->program)->name ?? '-'],
            ['القطاع', optional($project->domain)->name ?? '-'],
            ['المجال الفرعي', optional($project->subdomain)->name ?? '-'],
            ['نوع التدخل', optional($project->intervention)->name ?? '-'],
            ['الأولوية', optional($project->priority)->name ?? '-'],
            ['عدد المستفيدين', number_format($project->number_of_beneficiaries ?? 0)],
            ['تاريخ البدء', $project->start_date_gregorian ?? '-'],
            ['تاريخ الانتهاء', $project->end_date_gregorian ?? '-'],
            ['مدة المشروع (يوم)', $project->project_duration ?? '-'],
        ];

        foreach ($data as $item) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", $item[0]);
            $this->applyStyle($sheet, "A{$row}:H{$row}", [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_GRAY_LIGHT]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            $sheet->mergeCells("I{$row}:Z{$row}");
            $sheet->setCellValue("I{$row}", $item[1]);
            $this->applyStyle($sheet, "I{$row}:Z{$row}", [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;
        }

        return $row;
    }

    private function drawTechnicalSupportTable($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'الدعم الفني المطلوب');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        $headers = [
            ['A', 'M', 'نوع الدعم الفني'],
            ['N', 'Q', 'الجهة المقدمة'],
            ['R', 'U', 'التكلفة التقديرية'],
            ['V', 'Z', 'ملاحظات'],
        ];

        foreach ($headers as $header) {
            $sheet->mergeCells("{$header[0]}{$row}:{$header[1]}{$row}");
            $sheet->setCellValue("{$header[0]}{$row}", $header[2]);
        }
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        // Empty row for data entry
        $sheet->mergeCells("A{$row}:M{$row}");
        $sheet->mergeCells("N{$row}:Q{$row}");
        $sheet->mergeCells("R{$row}:U{$row}");
        $sheet->mergeCells("V{$row}:Z{$row}");
        $this->applyStyle($sheet, "A{$row}:Z{$row}", [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        return $row;
    }

    private function drawRisksTable($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'المخاطر المحتملة');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        $headers = [
            ['A', 'L', 'المخاطر'],
            ['M', 'Z', 'الإجراءات المتخذة للحد من المخاطر'],
        ];

        foreach ($headers as $header) {
            $sheet->mergeCells("{$header[0]}{$row}:{$header[1]}{$row}");
            $sheet->setCellValue("{$header[0]}{$row}", $header[2]);
        }
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        if ($project->risks && $project->risks->count() > 0) {
            foreach ($project->risks as $risk) {
                $sheet->mergeCells("A{$row}:L{$row}");
                $sheet->setCellValue("A{$row}", $risk->risk_description ?? '-');
                $sheet->mergeCells("M{$row}:Z{$row}");
                $sheet->setCellValue("M{$row}", $risk->mitigation_measures ?? '-');
                $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['wrapText' => true],
                ]);
                $row++;
            }
        } else {
            $sheet->mergeCells("A{$row}:Z{$row}");
            $sheet->setCellValue("A{$row}", 'لا توجد مخاطر محددة');
            $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        return $row;
    }

    private function drawExecutiveActivitiesTable($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'الأنشطة التنفيذية المقترحة');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        $mainHeaders = [
            ['A', 'D', 'النشاط'],
            ['E', 'H', 'الإجراء'],
            ['I', 'K', 'الجهة المنفذة'],
            ['L', 'N', 'البند المالي'],
            ['O', 'Q', 'الكمية'],
            ['R', 'T', 'الوحدة'],
            ['U', 'W', 'التكلفة'],
            ['X', 'Z', 'الإجمالي'],
        ];

        foreach ($mainHeaders as $header) {
            $sheet->mergeCells("{$header[0]}{$row}:{$header[1]}{$row}");
            $sheet->setCellValue("{$header[0]}{$row}", $header[2]);
        }
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        if ($project->executiveActivities && $project->executiveActivities->count() > 0) {
            foreach ($project->executiveActivities as $activity) {
                if ($activity->actions && $activity->actions->count() > 0) {
                    foreach ($activity->actions as $action) {
                        $sheet->mergeCells("A{$row}:D{$row}");
                        $sheet->setCellValue("A{$row}", $activity->name ?? '-');

                        $sheet->mergeCells("E{$row}:H{$row}");
                        $sheet->setCellValue("E{$row}", $action->action ?? '-');

                        $sheet->mergeCells("I{$row}:K{$row}");
                        $entities = $action->assignedEntities->pluck('agency_name')->implode(', ');
                        $sheet->setCellValue("I{$row}", $entities ?: '-');

                        $sheet->mergeCells("L{$row}:N{$row}");
                        $financialItems = $action->costs->pluck('financialItem.name')->filter()->implode(', ');
                        $sheet->setCellValue("L{$row}", $financialItems ?: '-');

                        $sheet->mergeCells("O{$row}:Q{$row}");
                        $sheet->setCellValue("O{$row}", '-');

                        $sheet->mergeCells("R{$row}:T{$row}");
                        $sheet->setCellValue("R{$row}", '-');

                        $totalCost = $action->costs->sum('amount');
                        $sheet->mergeCells("U{$row}:W{$row}");
                        $sheet->setCellValue("U{$row}", number_format($totalCost, 2));

                        $sheet->mergeCells("X{$row}:Z{$row}");
                        $sheet->setCellValue("X{$row}", number_format($totalCost, 2));

                        $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                            'alignment' => ['wrapText' => true],
                        ]);
                        $row++;
                    }
                }
            }
        } else {
            $sheet->mergeCells("A{$row}:Z{$row}");
            $sheet->setCellValue("A{$row}", 'لا توجد أنشطة تنفيذية محددة');
            $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        return $row;
    }

    private function drawPartnershipOutputsTable($sheet, $row, $project)
    {
        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'مخرجات المشروع');
        $this->applyStyle($sheet, "A{$row}:Z{$row}", [
            'font' => ['size' => 12, 'bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD3D3D3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        $headers = [
            ['A', 'L', 'المخرجات'],
            ['M', 'S', 'النتائج'],
            ['T', 'Z', 'ملاحظات'],
        ];

        foreach ($headers as $header) {
            $sheet->mergeCells("{$header[0]}{$row}:{$header[1]}{$row}");
            $sheet->setCellValue("{$header[0]}{$row}", $header[2]);
        }
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        // Get outputs from special objectives -> results -> outputs
        if ($project->specialObjectives && $project->specialObjectives->count() > 0) {
            foreach ($project->specialObjectives as $objective) {
                if ($objective->results && $objective->results->count() > 0) {
                    foreach ($objective->results as $result) {
                        if ($result->outputs && $result->outputs->count() > 0) {
                            foreach ($result->outputs as $output) {
                                $sheet->mergeCells("A{$row}:L{$row}");
                                $sheet->setCellValue("A{$row}", $output->output_description ?? '-');

                                $sheet->mergeCells("M{$row}:S{$row}");
                                $sheet->setCellValue("M{$row}", $result->result_description ?? '-');

                                $sheet->mergeCells("T{$row}:Z{$row}");
                                $sheet->setCellValue("T{$row}", '-');

                                $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                                    'alignment' => ['wrapText' => true],
                                ]);
                                $row++;
                            }
                        }
                    }
                }
            }
        } else {
            $sheet->mergeCells("A{$row}:Z{$row}");
            $sheet->setCellValue("A{$row}", 'لا توجد مخرجات محددة');
            $this->applyStyle($sheet, "A{$row}:Z{$row}", [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        return $row;
    }

    private function drawSignaturesSection($sheet, $row)
    {
        $row++;

        $sheet->mergeCells("A{$row}:Z{$row}");
        $sheet->setCellValue("A{$row}", 'التوقيعات');
        $this->applyRedHeader($sheet, "A{$row}:Z{$row}");
        $row++;

        $signatures = [
            ['A', 'H', 'رئيس الجمعية'],
            ['I', 'Q', 'المدير'],
            ['R', 'Z', 'المحاسب'],
        ];

        foreach ($signatures as $sig) {
            $sheet->mergeCells("{$sig[0]}{$row}:{$sig[1]}{$row}");
            $sheet->setCellValue("{$sig[0]}{$row}", $sig[2]);
            $this->applyStyle($sheet, "{$sig[0]}{$row}:{$sig[1]}{$row}", [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
        $row++;

        foreach ($signatures as $sig) {
            $sheet->mergeCells("{$sig[0]}{$row}:{$sig[1]}".($row + 2));
            $this->applyStyle($sheet, "{$sig[0]}{$row}:{$sig[1]}".($row + 2), [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
    }

    private function applyRedHeader($sheet, $range)
    {
        $this->applyStyle($sheet, $range, [
            'font' => ['size' => 12, 'bold' => true, 'color' => ['argb' => self::COLOR_WHITE]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_RED_DARK]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BLACK]]],
        ]);
    }

    private function applyStyle($sheet, $range, $styles)
    {
        $styleArray = [];

        if (isset($styles['font'])) {
            $styleArray['font'] = $styles['font'];
        }
        if (isset($styles['fill'])) {
            $styleArray['fill'] = $styles['fill'];
        }
        if (isset($styles['alignment'])) {
            $styleArray['alignment'] = $styles['alignment'];
        }
        if (isset($styles['borders'])) {
            $styleArray['borders'] = $styles['borders'];
        }

        $sheet->getStyle($range)->applyFromArray($styleArray);
    }
}
