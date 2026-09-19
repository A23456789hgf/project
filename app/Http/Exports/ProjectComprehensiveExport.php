<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectComprehensiveExport
{
    protected $projects;

    public function __construct($projects = null)
    {
        $this->projects = $projects;
    }

    /**
     * Export projects to a single-sheet comprehensive Excel
     */
    public function export($projects = null)
    {
        if ($projects) {
            $this->projects = $projects;
        }

        if (! $this->projects) {
            return null;
        }

        // Ensure projects is a collection
        if (is_array($this->projects)) {
            $this->projects = collect($this->projects);
        } elseif ($this->projects instanceof Project) {
            $this->projects = collect([$this->projects]);
        }

        // Load all required relationships
        $this->projects->load([
            'program', 'domain', 'subdomain', 'intervention', 'priority', 'mainRouter', 'subRouter', 'targetCategory', 'detail',
            'locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village',
            'mainObjectives', 'specialObjectives.results.outputs', 'risks', 'cost',
            'financings.fundingSource', 'financings.authority', 'financings.financingType', 'financings.financingForm', 'financings.subFinancingForm',
            'supervisingAuthorities.authority', 'implementingEntities.authority', 'participatingEntities.authority', 'beneficiaryEntities.authority',
            'beneficiaryGroups',
            'preliminaryActivities.procedures.costs.financialItem',
            'preliminaryFinancialSummaries.financialItem',
            'executiveActivities.actions.assignedEntities',
            'executiveActivities.actions.costs.financialItem',
            'executiveFinancialSummaries.financialItem',
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('بيانات المشروع');

        $headers = $this->getHeaders();
        $this->writeHeader($sheet, $headers);

        $currentRow = 2;
        foreach ($this->projects as $project) {
            $currentRow = $this->writeProjectData($sheet, $project, $currentRow);
        }

        $this->finalizeSheet($sheet);

        return $spreadsheet;
    }

    protected function getHeaders()
    {
        return [
            // المعلومات الأساسية
            'المعرف', 'رقم المشروع', 'اسم المشروع', 'الحالة', 'البرنامج', 'المجال الرئيسي', 'المجال الفرعي', 'التدخل', 'الأولوية',
            'تاريخ البدء', 'تاريخ الانتهاء', 'المدة (أيام)', 'عدد المستفيدين', 'المسار الرئيسي', 'الفئة المستهدفة',
            // تفاصيل المشروع
            'الملخص', 'المقدمة', 'المشكلة والمبررات', 'المكونات', 'الأثر المتوقع', 'خطة؟',
            // قسم البيانات
            'قسم البيانات',
            // أعمدة ديناميكية للعلاقات
            'العمود 1 (الاسم/الوصف)', 'العمود 2 (النوع/الاحتمالية)', 'العمود 3 (المحافظة/الأثر)', 'العمود 4 (المديرية/التخفيف)', 'العمود 5 (العزلة/التكلفة)', 'العمود 6 (القرية/الوحدة)', 'العمود 7 (الكمية)', 'العمود 8 (السعر)', 'العمود 9 (الإجمالي)',
        ];
    }

    protected function writeHeader($sheet, $headers)
    {
        foreach ($headers as $index => $header) {
            $col = $index + 1;
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
        }

        $lastCol = count($headers);
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0C5B47']], // Dark Green
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:'.$this->getColLetter($lastCol).'1')->applyFromArray($headerStyle);
    }

    protected function writeProjectData($sheet, $project, $startRow)
    {
        $row = $startRow;
        $baseData = $this->getProjectBaseData($project);

        // 1. Locations
        foreach ($project->locations as $loc) {
            $this->writeRow($sheet, $row, $baseData, 'المواقع', [
                $loc->governorate->name ?? '-',
                $loc->directorate->name ?? '-',
                $loc->subArea->name ?? '-',
                $loc->village->name ?? '-',
                $loc->notes ?? '-',
            ]);
            $row++;
        }

        // 2. الهدف العام (General Objective)
        foreach ($project->mainObjectives as $obj) {
            $this->writeRow($sheet, $row, $baseData, 'الهدف العام', [
                $obj->objective,
                $obj->indicator ?? '-',
                $obj->indicator_unit ?? '-',
                $obj->indicator_value ?? '-',
            ]);
            $row++;
        }

        // 3. الأهداف الخاصة والنتائج والمخرجات (Specific Objectives with Results and Outputs)
        foreach ($project->specialObjectives as $obj) {
            foreach ($obj->results as $res) {
                foreach ($res->outputs as $out) {
                    $this->writeRow($sheet, $row, $baseData, 'الأهداف الخاصة والنتائج والمخرجات', [
                        $obj->objective,
                        $res->result_name,
                        $out->output,
                        'الوزن: '.($obj->objective_weight ?? '-'),
                    ]);
                    $row++;
                }
                if ($res->outputs->isEmpty()) {
                    $this->writeRow($sheet, $row, $baseData, 'الأهداف الخاصة والنتائج والمخرجات', [
                        $obj->objective,
                        $res->result_name,
                    ]);
                    $row++;
                }
            }
            if ($obj->results->isEmpty()) {
                $this->writeRow($sheet, $row, $baseData, 'الأهداف الخاصة والنتائج والمخرجات', [$obj->objective]);
                $row++;
            }
        }

        // 4. Risks
        foreach ($project->risks as $risk) {
            $this->writeRow($sheet, $row, $baseData, 'المخاطر', [
                $risk->description ?? $risk->risk ?? '-',
                $risk->probability ?? $risk->risk_rate ?? '-',
                $risk->impact ?? '-',
                $risk->mitigation ?? '-',
            ]);
            $row++;
        }

        // 5. الجهات (Supervising, Participating, Implementing)
        $entityGroups = [
            'الجهات المشرفة' => $project->supervisingAuthorities,
            'الجهات المشاركة' => $project->participatingEntities,
            'الجهات المنفذة' => $project->implementingEntities,
            'الجهات المستفيدة' => $project->beneficiaryEntities,
        ];

        foreach ($entityGroups as $sectionName => $relation) {
            foreach ($relation as $item) {
                $this->writeRow($sheet, $row, $baseData, $sectionName, [
                    $item->authority->agency_name ?? '-',
                    $item->authority_type ?? $item->entity_type ?? '-',
                ]);
                $row++;
            }
        }

        // 6. الأنشطة التمهيدية والإجراءات والتكاليف والملخص المالي
        foreach ($project->preliminaryActivities as $act) {
            foreach ($act->procedures as $proc) {
                foreach ($proc->costs as $cost) {
                    $this->writeRow($sheet, $row, $baseData, 'الأنشطة التمهيدية والإجراءات والتكاليف', [
                        $act->name ?? '-',
                        $proc->procedure_name ?? $proc->procedure_description ?? '-',
                        $cost->financialItem->name ?? '-',
                        $cost->unit->unit_name ?? '-',
                        $cost->quantity ?? 0,
                        $cost->amount ?? 0,
                        $cost->total ?? 0,
                    ]);
                    $row++;
                }
            }
        }

        foreach ($project->preliminaryFinancialSummaries as $summary) {
            $this->writeRow($sheet, $row, $baseData, 'الملخص المالي للأنشطة التمهيدية', [
                $summary->financialItem->name ?? '-',
                'إجمالي: '.number_format($summary->aggregated_total ?? 0),
                'الكمية: '.($summary->aggregated_quantity ?? 0),
                $summary->notes ?? '-',
            ]);
            $row++;
        }

        // 7. أنشطة التنفيذ والإجراءات والتكاليف والملخص المالي
        foreach ($project->executiveActivities as $act) {
            foreach ($act->actions as $action) {
                $entitiesStr = $action->assignedEntities->pluck('agency_name')->implode(', ');
                foreach ($action->costs as $cost) {
                    $this->writeRow($sheet, $row, $baseData, 'أنشطة التنفيذ والإجراءات والتكاليف', [
                        $act->name ?? '-',
                        $action->action ?? '-',
                        $entitiesStr,
                        $cost->financialItem->name ?? '-',
                        $cost->unit->unit_name ?? '-',
                        $cost->quantity ?? 0,
                        $cost->amount ?? 0,
                        $cost->total ?? 0,
                    ]);
                    $row++;
                }
            }
        }

        foreach ($project->executiveFinancialSummaries as $summary) {
            $this->writeRow($sheet, $row, $baseData, 'الملخص المالي لأنشطة التنفيذ', [
                $summary->financialItem->name ?? '-',
                'إجمالي: '.number_format($summary->aggregated_total ?? 0),
                'الكمية: '.($summary->aggregated_quantity ?? 0),
                $summary->notes ?? '-',
            ]);
            $row++;
        }

        // 8. Financing
        foreach ($project->financings as $fin) {
            $this->writeRow($sheet, $row, $baseData, 'التمويل', [
                $fin->fundingSource->name ?? '-',
                $fin->financingType->name ?? '-',
                $fin->financingForm->name ?? '-',
                $fin->authority->agency_name ?? '-',
                $fin->financing_amount ?? 0,
                ($fin->financing_percentage ?? 0).'%',
            ]);
            $row++;
        }

        // 9. تكاليف المشروع (Project Costs)
        if ($project->cost) {
            $this->writeRow($sheet, $row, $baseData, 'تكاليف المشروع', [
                'إجمالي التكلفة: '.number_format($project->cost->total_cost),
                'تكلفة العمالة: '.number_format($project->cost->labour_cost),
                'تكلفة المواد: '.number_format($project->cost->material_cost),
                'تكاليف أخرى: '.number_format($project->cost->other_costs),
            ]);
            $row++;
        }

        // If no extra rows were added, add at least one base row
        if ($row == $startRow) {
            $this->writeRow($sheet, $row, $baseData, 'معلومات أساسية', []);
            $row++;
        }

        return $row;
    }

    protected function getProjectBaseData($project)
    {
        return [
            $project->id,
            $project->form_number,
            $project->project_name,
            $this->getStatusLabel($project->status),
            $project->program->name ?? '-',
            $project->domain->name ?? '-',
            $project->subdomain->name ?? '-',
            $project->intervention->name ?? '-',
            $project->priority->name ?? '-',
            $project->start_date_gregorian ?? '-',
            $project->end_date_gregorian ?? '-',
            $project->project_duration ?? '-',
            $project->number_of_beneficiaries ?? '-',
            $project->mainRouter->main_router ?? '-',
            $project->targetCategory->name ?? '-',
            $project->detail->project_summary ?? '-',
            $project->detail->project_introduction ?? '-',
            $project->detail->problem_and_justification ?? '-',
            $project->detail->project_components ?? '-',
            $project->detail->expected_impact ?? '-',
            ($project->detail->is_part_of_plan ?? false) ? 'نعم' : 'لا',
        ];
    }

    protected function writeRow($sheet, $row, $baseData, $section, $extraData)
    {
        $col = 1;
        // Write base data
        foreach ($baseData as $val) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $val);
        }

        // Write section name
        $sheet->setCellValueByColumnAndRow($col++, $row, $section);

        // Write extra data
        foreach ($extraData as $val) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $val);
        }

        // Style the row
        $sheet->getStyle("A$row:".$this->getColLetter($col - 1).$row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
    }

    protected function finalizeSheet($sheet)
    {
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    protected function getColLetter($colIndex)
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $colIndex = intval(($colIndex - $mod) / 26);
        }

        return $letter;
    }

    protected function getStatusLabel($status)
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
