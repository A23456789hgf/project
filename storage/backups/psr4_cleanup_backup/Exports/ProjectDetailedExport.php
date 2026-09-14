<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectDetailedExport
{
    protected $project;

    public function __construct(?Project $project = null)
    {
        $this->project = $project;
    }

    /**
     * Export a single project with all its data
     */
    public function exportSingleProject(Project $project)
    {
        $this->project = $project;

        return $this->createDetailedSpreadsheet();
    }

    /**
     * Export multiple projects
     */
    public function exportMultipleProjects($projects)
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        foreach ($projects as $project) {
            $this->project = $project;
            $this->addProjectSheet($spreadsheet);
        }

        return $spreadsheet;
    }

    /**
     * Create detailed spreadsheet for single project
     */
    private function createDetailedSpreadsheet()
    {
        $spreadsheet = new Spreadsheet;
        $this->addProjectSheet($spreadsheet);

        return $spreadsheet;
    }

    /**
     * Add a complete project sheet to spreadsheet
     */
    private function addProjectSheet(&$spreadsheet)
    {
        // Load all relationships
        $this->project->load([
            'program',
            'domain',
            'subdomain',
            'intervention',
            'priority',
            'mainRouter',
            'detail',
            'locations.governorate',
            'locations.directorate',
            'locations.subArea',
            'locations.village',
            'mainObjectives',
            'specialObjectives.results.outputs',
            'risks',
            'cost',
            'financings.fundingSource',
            'financings.authority',
            'supervisingAuthorities.authority',
            'implementingEntities.authority',
            'participatingEntities.authority',
            'beneficiaryEntities.authority',
            'preliminaryActivities.procedures.costs',
            'preliminaryFinancialSummaries.financialItem',
            'executiveActivities.actions.assignedEntities',
            'executiveActivities.actions.costs',
            'executiveFinancialSummaries.financialItem',
        ]);

        // Create main sheet
        $sheet = $spreadsheet->createSheet();
        $sheetTitle = substr('Project_'.$this->project->form_number, 0, 31); // Excel sheet name limit
        $sheet->setTitle($sheetTitle);

        $currentRow = 1;

        // 1. Project Header Section
        $currentRow = $this->addProjectHeader($sheet, $currentRow);

        // 2. Basic Information
        $currentRow = $this->addBasicInformation($sheet, $currentRow);

        // 3. Objectives
        $currentRow = $this->addObjectivesSection($sheet, $currentRow);

        // 4. Risks
        $currentRow = $this->addRisksSection($sheet, $currentRow);

        // 5. Locations
        $currentRow = $this->addLocationsSection($sheet, $currentRow);

        // 6. Entities
        $currentRow = $this->addEntitiesSection($sheet, $currentRow);

        // 7. Preliminary Activities
        $currentRow = $this->addPreliminaryActivitiesSection($sheet, $currentRow);

        // 8. Executive Activities
        $currentRow = $this->addExecutiveActivitiesSection($sheet, $currentRow);

        // 9. Financing
        $currentRow = $this->addFinancingSection($sheet, $currentRow);

        // 10. Costs
        $currentRow = $this->addCostsSection($sheet, $currentRow);

        // Auto-fit columns
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }

    /**
     * Add project header with basic metadata
     */
    private function addProjectHeader(&$sheet, $row)
    {
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'تقرير المشروع');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($titleStyle);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // Project Name
        $sheet->setCellValue("A{$row}", 'اسم المشروع:');
        $sheet->setCellValue("C{$row}", $this->project->project_name);
        $this->styleLabelCell("A{$row}:B{$row}", $sheet);
        $row++;

        // Form Number
        $sheet->setCellValue("A{$row}", 'رقم الاستمارة:');
        $sheet->setCellValue("C{$row}", $this->project->form_number);
        $this->styleLabelCell("A{$row}:B{$row}", $sheet);
        $row++;

        // Status
        $sheet->setCellValue("A{$row}", 'الحالة:');
        $sheet->setCellValue("C{$row}", $this->getStatusLabel($this->project->status));
        $this->styleLabelCell("A{$row}:B{$row}", $sheet);
        $row++;

        // Export Date
        $sheet->setCellValue("A{$row}", 'تاريخ التصدير:');
        $sheet->setCellValue("C{$row}", now()->format('Y-m-d H:i:s'));
        $this->styleLabelCell("A{$row}:B{$row}", $sheet);
        $row += 2;

        return $row;
    }

    /**
     * Add basic information section
     */
    private function addBasicInformation(&$sheet, $row)
    {
        $row = $this->addSectionTitle($sheet, $row, 'المعلومات الأساسية');

        $basicData = [
            'البرنامج' => $this->project->program->name ?? '',
            'المجال الرئيسي' => $this->project->domain->name ?? '',
            'المجال الفرعي' => $this->project->subdomain->name ?? '',
            'نوع التدخل' => $this->project->intervention->name ?? '',
            'الأولوية' => $this->project->priority->name ?? '',
            'التوجيه الرئيسي' => $this->project->mainRouter->main_router ?? '',
            'عدد المستفيدين' => $this->project->number_of_beneficiaries ?? 0,
            'تاريخ البداية' => $this->project->start_date_gregorian ?? '',
            'تاريخ النهاية' => $this->project->end_date_gregorian ?? '',
            'المدة بالأيام' => $this->project->project_duration ?? 0,
        ];

        foreach ($basicData as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("C{$row}", $value);
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;
        }

        // Project Details
        if ($this->project->detail) {
            $row++;
            $row = $this->addSectionTitle($sheet, $row, 'تفاصيل المشروع');

            $detailData = [
                'المكون الرئيسي' => $this->project->detail->main_component ?? '',
                'المكونات الثانوية' => $this->project->detail->sub_components ?? '',
                'النتائج المتوقعة' => $this->project->detail->expected_results ?? '',
                'المخرجات المتوقعة' => $this->project->detail->expected_outputs ?? '',
            ];

            foreach ($detailData as $label => $value) {
                if (! empty($value)) {
                    $sheet->setCellValue("A{$row}", $label);
                    $sheet->setCellValue("C{$row}", $value);
                    $this->styleLabelCell("A{$row}:B{$row}", $sheet);
                    $row++;
                }
            }
        }

        return $row + 1;
    }

    /**
     * Add objectives section
     */
    private function addObjectivesSection(&$sheet, $row)
    {
        $row = $this->addSectionTitle($sheet, $row, 'الأهداف');

        // Main Objectives
        if ($this->project->mainObjectives->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الهدف العام');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->mainObjectives as $index => $objective) {
                $sheet->setCellValue("A{$row}", ($index + 1).'.');
                $sheet->setCellValue("B{$row}", $objective->objective ?? '');
                $row++;
            }
            $row++;
        }

        // Special Objectives
        if ($this->project->specialObjectives->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الأهداف الخاصة');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->specialObjectives as $index => $objective) {
                $sheet->setCellValue("A{$row}", ($index + 1).'.');
                $sheet->setCellValue("B{$row}", $objective->objective ?? '');
                $row++;

                if ($objective->results->count() > 0) {
                    $sheet->setCellValue("A{$row}", 'النتائج');
                    $row++;
                    foreach ($objective->results as $result) {
                        $sheet->setCellValue("B{$row}", $result->result ?? '');
                        $row++;

                        if ($result->outputs->count() > 0) {
                            foreach ($result->outputs as $output) {
                                $sheet->setCellValue("C{$row}", '- '.($output->output ?? ''));
                                $row++;
                            }
                        }
                    }
                }
                $row++;
            }
        }

        return $row + 1;
    }

    /**
     * Add risks section
     */
    private function addRisksSection(&$sheet, $row)
    {
        if ($this->project->risks->count() === 0) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'المخاطر');

        // Table headers
        $headers = ['#', 'الوصف', 'الاحتمالية', 'التأثير', 'المخفف'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->risks as $index => $risk) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $risk->description ?? '');
            $sheet->setCellValue("C{$row}", $risk->probability ?? '');
            $sheet->setCellValue("D{$row}", $risk->impact ?? '');
            $sheet->setCellValue("E{$row}", $risk->mitigation ?? '');
            $row++;
        }

        return $row + 1;
    }

    /**
     * Add locations section
     */
    private function addLocationsSection(&$sheet, $row)
    {
        if ($this->project->locations->count() === 0) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'المواقع');

        $headers = ['#', 'المحافظة', 'المديرية', 'المنطقة', 'القرية'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->locations as $index => $location) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $location->governorate->name ?? '');
            $sheet->setCellValue("C{$row}", $location->directorate->name ?? '');
            $sheet->setCellValue("D{$row}", $location->subArea->name ?? '');
            $sheet->setCellValue("E{$row}", $location->village->name ?? '');
            $row++;
        }

        return $row + 1;
    }

    /**
     * Add entities section (supervising, implementing, participating, beneficiary)
     */
    private function addEntitiesSection(&$sheet, $row)
    {
        // Supervising Authorities
        if ($this->project->supervisingAuthorities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المشرفة');
            $sheet->setCellValue("A{$row}", 'الجهة');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->supervisingAuthorities as $entity) {
                $sheet->setCellValue("A{$row}", $entity->authority->name ?? '');
                $row++;
            }
            $row++;
        }

        // Implementing Entities
        if ($this->project->implementingEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المنفذة');
            $sheet->setCellValue("A{$row}", 'الجهة');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->implementingEntities as $entity) {
                $sheet->setCellValue("A{$row}", $entity->authority->name ?? '');
                $row++;
            }
            $row++;
        }

        // Participating Entities
        if ($this->project->participatingEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المشاركة');
            $sheet->setCellValue("A{$row}", 'الجهة');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->participatingEntities as $entity) {
                $sheet->setCellValue("A{$row}", $entity->authority->name ?? '');
                $row++;
            }
            $row++;
        }

        // Beneficiary Entities
        if ($this->project->beneficiaryEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المستفيدة');
            $sheet->setCellValue("A{$row}", 'الجهة');
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            foreach ($this->project->beneficiaryEntities as $entity) {
                $sheet->setCellValue("A{$row}", $entity->authority->name ?? '');
                $row++;
            }
            $row++;
        }

        return $row;
    }

    /**
     * Add preliminary activities section
     */
    private function addPreliminaryActivitiesSection(&$sheet, $row)
    {
        if ($this->project->preliminaryActivities->count() === 0) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'الأنشطة التحضيرية');

        foreach ($this->project->preliminaryActivities as $activityIndex => $activity) {
            $sheet->setCellValue("A{$row}", 'النشاط '.($activityIndex + 1));
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            $sheet->setCellValue("A{$row}", 'الوصف:');
            $sheet->setCellValue("C{$row}", $activity->description ?? '');
            $row++;

            // Procedures
            if ($activity->procedures->count() > 0) {
                $sheet->setCellValue("A{$row}", 'الإجراءات');
                $this->styleLabelCell("A{$row}:B{$row}", $sheet);
                $row++;

                foreach ($activity->procedures as $procIndex => $procedure) {
                    $sheet->setCellValue("B{$row}", ($procIndex + 1).'. '.($procedure->procedure_description ?? ''));
                    $row++;

                    // Procedure Costs
                    if ($procedure->costs->count() > 0) {
                        foreach ($procedure->costs as $cost) {
                            $sheet->setCellValue("C{$row}", 'التكلفة: '.($cost->total_cost ?? 0));
                            $row++;
                        }
                    }
                }
            }

            $row++;
        }

        // Preliminary Financial Summary
        if ($this->project->preliminaryFinancialSummaries->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'ملخص التمويل التحضيري');

            $headers = ['#', 'البند المالي', 'المبلغ'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->preliminaryFinancialSummaries as $index => $summary) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $summary->financialItem->name ?? '');
                $sheet->setCellValue("C{$row}", $summary->amount ?? 0);
                $row++;
            }
            $row++;
        }

        return $row;
    }

    /**
     * Add executive activities section
     */
    private function addExecutiveActivitiesSection(&$sheet, $row)
    {
        if ($this->project->executiveActivities->count() === 0) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'الأنشطة التنفيذية');

        foreach ($this->project->executiveActivities as $activityIndex => $activity) {
            $sheet->setCellValue("A{$row}", 'النشاط التنفيذي '.($activityIndex + 1));
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;

            $sheet->setCellValue("A{$row}", 'الوصف:');
            $sheet->setCellValue("C{$row}", $activity->activity_description ?? '');
            $row++;

            // Actions
            if ($activity->actions->count() > 0) {
                $sheet->setCellValue("A{$row}", 'العمليات');
                $this->styleLabelCell("A{$row}:B{$row}", $sheet);
                $row++;

                foreach ($activity->actions as $actionIndex => $action) {
                    $sheet->setCellValue("B{$row}", ($actionIndex + 1).'. '.($action->action_description ?? ''));
                    $row++;

                    // Assigned Entities
                    if ($action->assignedEntities->count() > 0) {
                        $sheet->setCellValue("C{$row}", 'الجهات المسؤولة:');
                        $row++;
                        foreach ($action->assignedEntities as $assigned) {
                            $sheet->setCellValue("D{$row}", '- '.($assigned->entity_name ?? ''));
                            $row++;
                        }
                    }

                    // Costs
                    if ($action->costs->count() > 0) {
                        $sheet->setCellValue("C{$row}", 'التكاليف:');
                        $row++;
                        foreach ($action->costs as $cost) {
                            $sheet->setCellValue("D{$row}", 'التكلفة: '.($cost->total_cost ?? 0));
                            $row++;
                        }
                    }
                }
            }

            $row++;
        }

        // Executive Financial Summary
        if ($this->project->executiveFinancialSummaries->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'ملخص التمويل التنفيذي');

            $headers = ['#', 'البند المالي', 'المبلغ'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->executiveFinancialSummaries as $index => $summary) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $summary->financialItem->name ?? '');
                $sheet->setCellValue("C{$row}", $summary->amount ?? 0);
                $row++;
            }
            $row++;
        }

        return $row;
    }

    /**
     * Add financing section
     */
    private function addFinancingSection(&$sheet, $row)
    {
        if ($this->project->financings->count() === 0) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'التمويل');

        $headers = ['#', 'المصدر', 'النوع', 'الشكل', 'المبلغ'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->financings as $index => $financing) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $financing->fundingSource->name ?? '');
            $sheet->setCellValue("C{$row}", $financing->financingType->name ?? '');
            $sheet->setCellValue("D{$row}", $financing->financingForm->name ?? '');
            $sheet->setCellValue("E{$row}", $financing->amount ?? 0);
            $row++;
        }

        return $row + 1;
    }

    /**
     * Add costs section
     */
    private function addCostsSection(&$sheet, $row)
    {
        if (! $this->project->cost) {
            return $row;
        }

        $row = $this->addSectionTitle($sheet, $row, 'التكاليف');

        $costData = [
            'التكلفة الإجمالية' => $this->project->cost->total_cost ?? 0,
            'تكلفة العمالة' => $this->project->cost->labour_cost ?? 0,
            'تكلفة المواد' => $this->project->cost->material_cost ?? 0,
            'تكاليف أخرى' => $this->project->cost->other_costs ?? 0,
        ];

        foreach ($costData as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("C{$row}", $value);
            $this->styleLabelCell("A{$row}:B{$row}", $sheet);
            $row++;
        }

        return $row + 1;
    }

    /**
     * Add section title
     */
    private function addSectionTitle(&$sheet, $row, $title)
    {
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($titleStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    /**
     * Add table header
     */
    private function addTableHeader(&$sheet, $row, $headers)
    {
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF70AD47']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        foreach ($headers as $colIndex => $header) {
            $colLetter = chr(65 + $colIndex);
            $sheet->setCellValue("{$colLetter}{$row}", $header);
        }

        $lastCol = chr(65 + count($headers) - 1);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($headerStyle);
    }

    /**
     * Style label cell
     */
    private function styleLabelCell($range, &$sheet)
    {
        $labelStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE7E6E6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($labelStyle);
    }

    /**
     * Get status label in Arabic
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'مسودة',
            'pending' => 'قيد الانتظار',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
        ];

        return $labels[$status] ?? $status;
    }
}
