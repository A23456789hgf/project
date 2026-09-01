<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectHierarchicalExport
{
    protected $project;

    protected $options;

    public function __construct($options = [])
    {
        if ($options instanceof Request) {
            $this->options = [
                'include_executions' => $options->get('include_executions') == '1',
                'include_budget' => $options->get('include_budget') == '1',
                'include_risks' => $options->get('include_risks') == '1',
                'include_logs' => $options->get('include_logs') == '1',
            ];
        } elseif (is_array($options)) {
            $this->options = array_merge([
                'include_executions' => true,
                'include_budget' => true,
                'include_risks' => true,
                'include_logs' => true,
            ], $options);
        } else {
            $this->options = [
                'include_executions' => true,
                'include_budget' => true,
                'include_risks' => true,
                'include_logs' => true,
            ];
            if ($options instanceof Project) {
                $this->project = $options;
            }
        }
    }

    /**
     * Export single project with hierarchical structure
     */
    public function exportSingleProject(Project $project)
    {
        $this->project = $project;

        return $this->createHierarchicalSpreadsheet();
    }

    /**
     * Export multiple projects with hierarchical structure
     */
    public function exportMultipleProjects($projects)
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        // Initialize Consolidated Sheets with Headers
        $sheets = $this->initConsolidatedSheets($spreadsheet);

        foreach ($projects as $project) {
            $this->project = $project;
            $this->project->loadMissing([
                'program', 'domain', 'subdomain', 'intervention', 'priority', 'targetCategory', 'detail',
                'locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village',
                'mainObjectives', 'specialObjectives.results.outputs', 'risks', 'cost',
                'financings.fundingSource', 'financings.authority', 'financings.financingType', 'financings.financingForm',
                'supervisingAuthorities.authority', 'implementingEntities.authority', 'participatingEntities.authority', 'beneficiaryEntities.authority',
                'beneficiaryGroups',
                'preliminaryActivities.procedures.costs.financialItem',
                'preliminaryActivities.procedures.executions',
                'preliminaryFinancialSummaries.financialItem',
                'executiveActivities.actions.assignedEntities',
                'executiveActivities.actions.costs.financialItem',
                'executiveActivities.actions.executions',
                'executiveFinancialSummaries.financialItem',
                'projectApprovals.stage', 'projectApprovals.entity', 'projectApprovals.createdBy',
                'activityHistory.user',
                'documents.uploader',
            ]);

            $this->addConsolidatedProjectRows($sheets);
        }

        // Auto-fit all sheets
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $this->autoFitColumns($sheet);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Initialize all consolidated sheets with headers
     */
    private function initConsolidatedSheets(Spreadsheet $spreadsheet)
    {
        $sheets = [];

        // 1. نظرة عامة على المشاريع
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('نظرة عامة على المشاريع');
        $headers = [
            'المعرف', 'رقم المشروع', 'اسم المشروع', 'البرنامج', 'المجال الرئيسي', 'المجال الفرعي',
            'التدخل', 'الأولوية', 'الحالة', 'حالة الاعتماد', 'المستفيدين',
            'تاريخ البدء (ميلادي)', 'تاريخ البدء (هجري)', 'تاريخ الانتهاء (ميلادي)', 'تاريخ الانتهاء (هجري)', 'المدة',
            'الفئة المستهدفة',
            'الملخص', 'المقدمة', 'المشكلة والمبررات', 'المكونات', 'الأثر المتوقع',
            'جزء من خطة', 'الجهة المنشئة', 'تاريخ الإنشاء', 'معرف ERPNext', 'حالة مزامنة Frappe',
        ];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['overview'] = ['sheet' => $sheet, 'row' => 2];

        // 2. المواقع
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('المواقع');
        $headers = ['معرف المشروع', 'اسم المشروع', 'المحافظة', 'المديرية', 'العزلة', 'القرية', 'ملاحظات'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['locations'] = ['sheet' => $sheet, 'row' => 2];

        // 3. الأهداف والنتائج
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الأهداف والنتائج');
        $headers = ['معرف المشروع', 'اسم المشروع', 'النوع', 'الهدف', 'الوزن', 'النتيجة', 'وزن النتيجة', 'المخرج'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['objectives'] = ['sheet' => $sheet, 'row' => 2];

        // 4. المخاطر
        if ($this->options['include_risks']) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('المخاطر');
            $headers = ['معرف المشروع', 'اسم المشروع', 'الوصف', 'الاحتمالية', 'الأثر', 'التخفيف', 'الحالة'];
            $this->addConsolidatedHeader($sheet, $headers);
            $sheets['risks'] = ['sheet' => $sheet, 'row' => 2];
        }

        // 5. الجهات والمجموعات المستهدفة
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الجهات والمجموعات المستهدفة');
        $headers = ['معرف المشروع', 'اسم المشروع', 'الفئة', 'الاسم', 'النوع/التفاصيل'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['entities'] = ['sheet' => $sheet, 'row' => 2];

        // 6. الأنشطة التمهيدية
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الأنشطة التمهيدية');
        $headers = [
            'معرف المشروع', 'اسم المشروع', 'النشاط', 'وزن النشاط',
            'الإجراء', 'البند المالي', 'التكلفة المخططة', 'التكلفة الفعلية', 'ملاحظات',
        ];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['preliminary'] = ['sheet' => $sheet, 'row' => 2];

        // 7. الأنشطة التنفيذية
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('الأنشطة التنفيذية');
        $headers = [
            'معرف المشروع', 'اسم المشروع', 'النشاط', 'وزن النشاط',
            'العملية', 'الجهات المسؤولة', 'البند المالي', 'التكلفة', 'ملاحظات',
        ];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['executive'] = ['sheet' => $sheet, 'row' => 2];

        // 8. التنفيذ (اختياري)
        if ($this->options['include_executions']) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('التنفيذ');
            $headers = ['معرف المشروع', 'اسم المشروع', 'نوع النشاط', 'اسم النشاط', 'الإجراء/العملية', 'تفاصيل التنفيذ', 'الحالة', 'التاريخ'];
            $this->addConsolidatedHeader($sheet, $headers);
            $sheets['executions'] = ['sheet' => $sheet, 'row' => 2];
        }

        // 9. التمويل
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('التمويل');
        $headers = ['معرف المشروع', 'اسم المشروع', 'المصدر', 'النوع', 'النموذج', 'الجهة', 'المبلغ', 'ملاحظات'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['financing'] = ['sheet' => $sheet, 'row' => 2];

        // 10. الملخص المالي
        if ($this->options['include_budget']) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الملخص المالي');
            $headers = [
                'معرف المشروع', 'اسم المشروع',
                'إجمالي التكلفة', 'تكلفة العمالة', 'تكلفة المواد', 'تكاليف أخرى',
                'إجمالي التمويل', 'إجمالي الأنشطة التمهيدية', 'إجمالي الأنشطة التنفيذية',
            ];
            $this->addConsolidatedHeader($sheet, $headers);
            $sheets['summary'] = ['sheet' => $sheet, 'row' => 2];
        }

        // 11. سجل الاعتمادات
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('سجل الاعتمادات');
        $headers = ['معرف المشروع', 'اسم المشروع', 'المرحلة', 'الجهة', 'القرار/الحالة', 'المراجع', 'ملاحظات', 'التاريخ'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['approvals'] = ['sheet' => $sheet, 'row' => 2];

        // 12. سجل الأنشطة
        if ($this->options['include_logs']) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('سجل الأنشطة');
            $headers = ['معرف المشروع', 'اسم المشروع', 'المستخدم', 'الإجراء', 'الوصف', 'التاريخ'];
            $this->addConsolidatedHeader($sheet, $headers);
            $sheets['logs'] = ['sheet' => $sheet, 'row' => 2];
        }

        // 13. المستندات
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('المستندات');
        $headers = ['معرف المشروع', 'اسم المشروع', 'نوع المستند', 'اسم الملف', 'بواسطة', 'التاريخ'];
        $this->addConsolidatedHeader($sheet, $headers);
        $sheets['documents'] = ['sheet' => $sheet, 'row' => 2];

        return $sheets;
    }

    private function addConsolidatedHeader($sheet, $headers)
    {
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        $lastCol = $sheet->getHighestColumn();
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
    }

    private function addConsolidatedProjectRows(&$sheets)
    {
        $id = $this->project->id;
        $name = $this->project->project_name;
        $formNum = $this->project->form_number;

        // 1. Overview
        $s = $sheets['overview'];
        $r = $s['row'];
        $sheet = $s['sheet'];

        $overviewData = [
            $id,
            $formNum,
            $name,
            $this->project->program->name ?? '',
            $this->project->domain->name ?? '',
            $this->project->subdomain->name ?? '',
            $this->project->intervention->name ?? '',
            $this->project->priority->name ?? $this->project->priority ?? '',
            $this->getStatusLabel($this->project->status),
            $this->project->approval_status ?? '',
            $this->project->number_of_beneficiaries ?? 0,
            $this->project->start_date_gregorian ?? '',
            $this->project->start_date_hijri ?? '',
            $this->project->end_date_gregorian ?? '',
            $this->project->end_date_hijri ?? '',
            $this->project->project_duration ?? 0,
            $this->project->targetCategory->name ?? '',
            $this->project->detail->project_summary ?? '',
            $this->project->detail->project_introduction ?? '',
            $this->project->detail->problem_and_justification ?? '',
            $this->project->detail->project_components ?? '',
            $this->project->detail->expected_impact ?? '',
            ($this->project->detail->is_part_of_plan ?? false) ? 'نعم' : 'لا',
            $this->project->created_by_entity ?? '',
            $this->project->created_at ? $this->project->created_at->toDateTimeString() : '',
            $this->project->erpnext_project_id ?? '',
            $this->project->frappe_sync_status ?? '',
        ];

        foreach ($overviewData as $index => $val) {
            $sheet->setCellValueByColumnAndRow($index + 1, $r, $val);
        }
        $sheets['overview']['row']++;

        // 2. Locations
        $s = $sheets['locations'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->locations as $loc) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, $loc->governorate->name ?? '');
            $sheet->setCellValueByColumnAndRow(4, $r, $loc->directorate->name ?? '');
            $sheet->setCellValueByColumnAndRow(5, $r, $loc->subArea->name ?? '');
            $sheet->setCellValueByColumnAndRow(6, $r, $loc->village->name ?? '');
            $sheet->setCellValueByColumnAndRow(7, $r, $loc->notes ?? '');
            $r++;
        }
        $sheets['locations']['row'] = $r;

        // 3. Objectives
        $s = $sheets['objectives'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->mainObjectives as $obj) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, 'رئيسي');
            $sheet->setCellValueByColumnAndRow(4, $r, $obj->objective);
            $r++;
        }
        foreach ($this->project->specialObjectives as $obj) {
            $hasResults = false;
            foreach ($obj->results as $res) {
                $hasResults = true;
                $hasOutputs = false;
                foreach ($res->outputs as $out) {
                    $hasOutputs = true;
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, 'خاص');
                    $sheet->setCellValueByColumnAndRow(4, $r, $obj->objective);
                    $sheet->setCellValueByColumnAndRow(5, $r, $obj->objective_weight);
                    $sheet->setCellValueByColumnAndRow(6, $r, $res->result);
                    $sheet->setCellValueByColumnAndRow(7, $r, $res->result_weight);
                    $sheet->setCellValueByColumnAndRow(8, $r, $out->output);
                    $r++;
                }
                if (! $hasOutputs) {
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, 'خاص');
                    $sheet->setCellValueByColumnAndRow(4, $r, $obj->objective);
                    $sheet->setCellValueByColumnAndRow(5, $r, $obj->objective_weight);
                    $sheet->setCellValueByColumnAndRow(6, $r, $res->result);
                    $sheet->setCellValueByColumnAndRow(7, $r, $res->result_weight);
                    $r++;
                }
            }
            if (! $hasResults) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, 'خاص');
                $sheet->setCellValueByColumnAndRow(4, $r, $obj->objective);
                $sheet->setCellValueByColumnAndRow(5, $r, $obj->objective_weight);
                $r++;
            }
        }
        $sheets['objectives']['row'] = $r;

        // 4. Risks
        if ($this->options['include_risks'] && isset($sheets['risks'])) {
            $s = $sheets['risks'];
            $r = $s['row'];
            $sheet = $s['sheet'];
            foreach ($this->project->risks as $risk) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, $risk->description ?? $risk->risk ?? '');
                $sheet->setCellValueByColumnAndRow(4, $r, $risk->probability ?? $risk->risk_rate ?? '');
                $sheet->setCellValueByColumnAndRow(5, $r, $risk->impact ?? '');
                $sheet->setCellValueByColumnAndRow(6, $r, $risk->mitigation ?? '');
                $sheet->setCellValueByColumnAndRow(7, $r, $risk->status ?? '');
                $r++;
            }
            $sheets['risks']['row'] = $r;
        }

        // 5. Entities & Groups
        $s = $sheets['entities'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        $roles = [
            'supervisingAuthorities' => 'مشرفة',
            'implementingEntities' => 'منفذة',
            'participatingEntities' => 'مشاركة',
            'beneficiaryEntities' => 'مستفيدة',
        ];
        foreach ($roles as $relation => $roleLabel) {
            foreach ($this->project->$relation as $entity) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, $roleLabel);
                $sheet->setCellValueByColumnAndRow(4, $r, $entity->authority->agency_name ?? '');
                $sheet->setCellValueByColumnAndRow(5, $r, $entity->authority_type ?? '');
                $r++;
            }
        }
        foreach ($this->project->beneficiaryGroups as $group) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, 'مجموعة مستهدفة');
            $sheet->setCellValueByColumnAndRow(4, $r, $group->name);
            $r++;
        }
        $sheets['entities']['row'] = $r;

        // 6. Preliminary
        $s = $sheets['preliminary'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->preliminaryActivities as $act) {
            $hasProcs = false;
            foreach ($act->procedures as $proc) {
                $hasProcs = true;
                $hasCosts = false;
                foreach ($proc->costs as $cost) {
                    $hasCosts = true;
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, $act->description);
                    $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                    $sheet->setCellValueByColumnAndRow(5, $r, $proc->procedure_description);
                    $sheet->setCellValueByColumnAndRow(6, $r, $cost->financialItem->name ?? $cost->financial_item ?? '');
                    $sheet->setCellValueByColumnAndRow(7, $r, $cost->total_cost);
                    $sheet->setCellValueByColumnAndRow(8, $r, 0);
                    $sheet->setCellValueByColumnAndRow(9, $r, $proc->notes);
                    $r++;
                }
                if (! $hasCosts) {
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, $act->description);
                    $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                    $sheet->setCellValueByColumnAndRow(5, $r, $proc->procedure_description);
                    $sheet->setCellValueByColumnAndRow(7, $r, 0);
                    $sheet->setCellValueByColumnAndRow(8, $r, $proc->actual_amount);
                    $sheet->setCellValueByColumnAndRow(9, $r, $proc->notes);
                    $r++;
                }

                // Add to Executions if needed
                if ($this->options['include_executions'] && isset($sheets['executions']) && $proc->executions) {
                    foreach ($proc->executions as $exec) {
                        $exs = $sheets['executions'];
                        $exr = $exs['row'];
                        $exsheet = $exs['sheet'];
                        $exsheet->setCellValueByColumnAndRow(1, $exr, $id);
                        $exsheet->setCellValueByColumnAndRow(2, $exr, $name);
                        $exsheet->setCellValueByColumnAndRow(3, $exr, 'تمهيدي');
                        $exsheet->setCellValueByColumnAndRow(4, $exr, $act->description);
                        $exsheet->setCellValueByColumnAndRow(5, $exr, $proc->procedure_description);
                        $exsheet->setCellValueByColumnAndRow(6, $exr, $exec->notes ?? '');
                        $exsheet->setCellValueByColumnAndRow(7, $exr, $exec->status ?? '');
                        $exsheet->setCellValueByColumnAndRow(8, $exr, $exec->created_at);
                        $sheets['executions']['row']++;
                    }
                }
            }
            if (! $hasProcs) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, $act->description);
                $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                $r++;
            }
        }
        // Add Preliminary Financial Summaries
        foreach ($this->project->preliminaryFinancialSummaries as $sum) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, 'ملخص مالي');
            $sheet->setCellValueByColumnAndRow(6, $r, $sum->financialItem->name ?? '');
            $sheet->setCellValueByColumnAndRow(7, $r, $sum->aggregated_total ?? 0);
            $sheet->setCellValueByColumnAndRow(9, $r, $sum->notes ?? '');
            $r++;
        }
        $sheets['preliminary']['row'] = $r;

        // 7. Executive
        $s = $sheets['executive'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->executiveActivities as $act) {
            $hasActions = false;
            foreach ($act->actions as $action) {
                $hasActions = true;
                $entities = $action->assignedEntities->pluck('entity_name')->implode(', ');
                $hasCosts = false;
                foreach ($action->costs as $cost) {
                    $hasCosts = true;
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, $act->activity_description);
                    $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                    $sheet->setCellValueByColumnAndRow(5, $r, $action->action_description);
                    $sheet->setCellValueByColumnAndRow(6, $r, $entities);
                    $sheet->setCellValueByColumnAndRow(7, $r, $cost->financialItem->name ?? $cost->financial_item ?? '');
                    $sheet->setCellValueByColumnAndRow(8, $r, $cost->total_cost);
                    $sheet->setCellValueByColumnAndRow(9, $r, $action->notes);
                    $r++;
                }
                if (! $hasCosts) {
                    $sheet->setCellValueByColumnAndRow(1, $r, $id);
                    $sheet->setCellValueByColumnAndRow(2, $r, $name);
                    $sheet->setCellValueByColumnAndRow(3, $r, $act->activity_description);
                    $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                    $sheet->setCellValueByColumnAndRow(5, $r, $action->action_description);
                    $sheet->setCellValueByColumnAndRow(6, $r, $entities);
                    $sheet->setCellValueByColumnAndRow(8, $r, 0);
                    $sheet->setCellValueByColumnAndRow(9, $r, $action->notes);
                    $r++;
                }

                // Add to Executions if needed
                if ($this->options['include_executions'] && isset($sheets['executions']) && $action->executions) {
                    foreach ($action->executions as $exec) {
                        $exs = $sheets['executions'];
                        $exr = $exs['row'];
                        $exsheet = $exs['sheet'];
                        $exsheet->setCellValueByColumnAndRow(1, $exr, $id);
                        $exsheet->setCellValueByColumnAndRow(2, $exr, $name);
                        $exsheet->setCellValueByColumnAndRow(3, $exr, 'تنفيذي');
                        $exsheet->setCellValueByColumnAndRow(4, $exr, $act->activity_description);
                        $exsheet->setCellValueByColumnAndRow(5, $exr, $action->action_description);
                        $exsheet->setCellValueByColumnAndRow(6, $exr, $exec->notes ?? '');
                        $exsheet->setCellValueByColumnAndRow(7, $exr, $exec->status ?? '');
                        $exsheet->setCellValueByColumnAndRow(8, $exr, $exec->created_at);
                        $sheets['executions']['row']++;
                    }
                }
            }
            if (! $hasActions) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, $act->activity_description);
                $sheet->setCellValueByColumnAndRow(4, $r, $act->activity_weight);
                $r++;
            }
        }
        // Add Executive Financial Summaries
        foreach ($this->project->executiveFinancialSummaries as $sum) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, 'ملخص مالي');
            $sheet->setCellValueByColumnAndRow(7, $r, $sum->financialItem->name ?? '');
            $sheet->setCellValueByColumnAndRow(8, $r, $sum->aggregated_total ?? 0);
            $sheet->setCellValueByColumnAndRow(9, $r, $sum->notes ?? '');
            $r++;
        }
        $sheets['executive']['row'] = $r;

        // 9. Financing
        $s = $sheets['financing'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->financings as $fin) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, $fin->fundingSource->name ?? '');
            $sheet->setCellValueByColumnAndRow(4, $r, $fin->financingType->name ?? '');
            $sheet->setCellValueByColumnAndRow(5, $r, $fin->financingForm->name ?? '');
            $sheet->setCellValueByColumnAndRow(6, $r, $fin->authority->name ?? '');
            $sheet->setCellValueByColumnAndRow(7, $r, $fin->amount);
            $sheet->setCellValueByColumnAndRow(8, $r, $fin->notes);
            $r++;
        }
        $sheets['financing']['row'] = $r;

        // 10. Summary
        if ($this->options['include_budget'] && isset($sheets['summary'])) {
            $s = $sheets['summary'];
            $r = $s['row'];
            $sheet = $s['sheet'];
            $totalCost = $this->project->cost->total_cost ?? 0;
            $totalFinancing = $this->project->financings->sum('amount');
            $prelimTotal = $this->project->preliminaryActivities->flatMap(function ($a) {
                return $a->procedures->flatMap(function ($p) {
                    return $p->costs;
                });
            })->sum('total_cost');
            $execTotal = $this->project->executiveActivities->flatMap(function ($a) {
                return $a->actions->flatMap(function ($ac) {
                    return $ac->costs;
                });
            })->sum('total_cost');
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, $totalCost);
            $sheet->setCellValueByColumnAndRow(4, $r, $this->project->cost->labour_cost ?? 0);
            $sheet->setCellValueByColumnAndRow(5, $r, $this->project->cost->material_cost ?? 0);
            $sheet->setCellValueByColumnAndRow(6, $r, $this->project->cost->other_costs ?? 0);
            $sheet->setCellValueByColumnAndRow(7, $r, $totalFinancing);
            $sheet->setCellValueByColumnAndRow(8, $r, $prelimTotal);
            $sheet->setCellValueByColumnAndRow(9, $r, $execTotal);
            $sheets['summary']['row']++;
        }

        // 11. Approvals
        $s = $sheets['approvals'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->projectApprovals as $app) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, $app->stage->name ?? $app->stage_id ?? '');
            $sheet->setCellValueByColumnAndRow(4, $r, $app->authority->name ?? $app->authority_id ?? '');
            $sheet->setCellValueByColumnAndRow(5, $r, $app->status);
            $sheet->setCellValueByColumnAndRow(6, $r, $app->createdBy->name ?? '');
            $sheet->setCellValueByColumnAndRow(7, $r, ($app->notes ?? '').' '.($app->rejection_reason ?? '').' '.($app->required_action ?? ''));
            $sheet->setCellValueByColumnAndRow(8, $r, $app->created_at);
            $r++;
        }
        $sheets['approvals']['row'] = $r;

        // 12. Logs
        if ($this->options['include_logs'] && isset($sheets['logs'])) {
            $s = $sheets['logs'];
            $r = $s['row'];
            $sheet = $s['sheet'];
            foreach ($this->project->activityHistory as $log) {
                $sheet->setCellValueByColumnAndRow(1, $r, $id);
                $sheet->setCellValueByColumnAndRow(2, $r, $name);
                $sheet->setCellValueByColumnAndRow(3, $r, $log->user->name ?? '');
                $sheet->setCellValueByColumnAndRow(4, $r, ($log->action_type ?? '').' '.($log->from_stage_name ?? '').' -> '.($log->to_stage_name ?? ''));
                $sheet->setCellValueByColumnAndRow(5, $r, ($log->notes ?? '').' '.($log->action_details ?? ''));
                $sheet->setCellValueByColumnAndRow(6, $r, $log->created_at);
                $r++;
            }
            $sheets['logs']['row'] = $r;
        }

        // 13. Documents
        $s = $sheets['documents'];
        $r = $s['row'];
        $sheet = $s['sheet'];
        foreach ($this->project->documents as $doc) {
            $sheet->setCellValueByColumnAndRow(1, $r, $id);
            $sheet->setCellValueByColumnAndRow(2, $r, $name);
            $sheet->setCellValueByColumnAndRow(3, $r, $doc->document_type);
            $sheet->setCellValueByColumnAndRow(4, $r, $doc->file_name);
            $sheet->setCellValueByColumnAndRow(5, $r, $doc->uploader->name ?? '');
            $sheet->setCellValueByColumnAndRow(6, $r, $doc->created_at);
            $r++;
        }
        $sheets['documents']['row'] = $r;
    }

    /**
     * Create hierarchical spreadsheet for single project
     */
    private function createHierarchicalSpreadsheet()
    {
        $spreadsheet = new Spreadsheet;
        $this->addHierarchicalProjectSheets($spreadsheet);

        return $spreadsheet;
    }

    /**
     * Add complete hierarchical sheets for a project
     */
    private function addHierarchicalProjectSheets(&$spreadsheet)
    {
        $this->project->load([
            'program', 'domain', 'subdomain', 'intervention', 'priority', 'targetCategory', 'detail',
            'locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village',
            'mainObjectives', 'specialObjectives.results.outputs', 'risks', 'cost',
            'financings.fundingSource', 'financings.authority', 'financings.financingType', 'financings.financingForm',
            'supervisingAuthorities.authority', 'implementingEntities.authority', 'participatingEntities.authority', 'beneficiaryEntities.authority',
            'beneficiaryGroups',
            'preliminaryActivities.procedures.costs.financialItem',
            'preliminaryActivities.procedures.executions',
            'preliminaryFinancialSummaries.financialItem',
            'executiveActivities.actions.assignedEntities',
            'executiveActivities.actions.costs.financialItem',
            'executiveActivities.actions.executions',
            'executiveFinancialSummaries.financialItem',
            'projectApprovals.stage', 'projectApprovals.entity', 'projectApprovals.createdBy',
            'activityHistory.user',
            'documents.uploader',
        ]);

        $projectCode = $this->project->form_number ?? 'P'.$this->project->id;

        // 1. Project Overview
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('نظرة عامة');
        $this->addProjectOverview($sheet);

        // 2. Locations
        if ($this->project->locations->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('المواقع');
            $this->addLocationsSheet($sheet);
        }

        // 3. Objectives & Results
        if ($this->project->specialObjectives->count() > 0 || $this->project->mainObjectives->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الأهداف والنتائج');
            $this->addObjectivesSheet($sheet);
        }

        // 4. Risks
        if ($this->options['include_risks'] && $this->project->risks->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('المخاطر');
            $this->addRisksSheet($sheet);
        }

        // 5. Entities
        if ($this->project->supervisingAuthorities->count() > 0 || $this->project->implementingEntities->count() > 0 ||
            $this->project->participatingEntities->count() > 0 || $this->project->beneficiaryEntities->count() > 0 ||
            $this->project->beneficiaryGroups->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الجهات والمجموعات');
            $this->addEntitiesSheet($sheet);
        }

        // 6. Preliminary Activities
        if ($this->project->preliminaryActivities->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الأنشطة التمهيدية');
            $this->addPreliminaryActivitiesSheet($sheet);
        }

        // 7. Executive Activities
        if ($this->project->executiveActivities->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الأنشطة التنفيذية');
            $this->addExecutiveActivitiesSheet($sheet);
        }

        // 8. Financing
        if ($this->project->financings->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('التمويل');
            $this->addFinancingSheet($sheet);
        }

        // 9. Financial Summary
        if ($this->options['include_budget']) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('الملخص المالي');
            $this->addFinancialSummarySheet($sheet);
        }

        // 10. Approval History
        if ($this->project->projectApprovals->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('سجل الاعتمادات');
            $this->addApprovalHistorySheet($sheet);
        }

        // 11. Activity Logs
        if ($this->options['include_logs'] && $this->project->activityHistory->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('سجل الأنشطة');
            $this->addLogsSheet($sheet);
        }

        // 12. Documents
        if ($this->project->documents->count() > 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('المستندات');
            $this->addDocumentsHierarchicalSheet($sheet);
        }
    }

    /**
     * Add project overview sheet
     */
    private function addProjectOverview(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'نظرة عامة على المشروع');

        $data = [
            'رقم المشروع' => $this->project->form_number,
            'اسم المشروع' => $this->project->project_name,
            'البرنامج' => $this->project->program->name ?? '',
            'المجال الرئيسي' => $this->project->domain->name ?? '',
            'المجال الفرعي' => $this->project->subdomain->name ?? '',
            'نوع التدخل' => $this->project->intervention->name ?? '',
            'الأولوية' => $this->project->priority->name ?? '',
            'الحالة' => $this->getStatusLabel($this->project->status),
            'عدد المستفيدين' => $this->project->number_of_beneficiaries ?? 0,
            'تاريخ البداية' => $this->project->start_date_gregorian ?? '',
            'تاريخ النهاية' => $this->project->end_date_gregorian ?? '',
            'المدة بالأيام' => $this->project->project_duration ?? 0,
        ];

        foreach ($data as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $value);
            $this->styleLabelCell("A{$row}", $sheet);
            $row++;
        }

        // Project Details
        if ($this->project->detail) {
            $row += 2;
            $row = $this->addSectionTitle($sheet, $row, 'تفاصيل المشروع');

            $details = [
                'المكون الرئيسي' => $this->project->detail->main_component ?? '',
                'المكونات الثانوية' => $this->project->detail->sub_components ?? '',
                'النتائج المتوقعة' => $this->project->detail->expected_results ?? '',
                'المخرجات المتوقعة' => $this->project->detail->expected_outputs ?? '',
            ];

            foreach ($details as $label => $value) {
                if (! empty($value)) {
                    $sheet->setCellValue("A{$row}", $label);
                    $sheet->setCellValue("B{$row}", $value);
                    $this->styleLabelCell("A{$row}", $sheet);
                    $row++;
                }
            }
        }

        // Main Objectives
        if ($this->project->mainObjectives->count() > 0) {
            $row += 2;
            $row = $this->addSectionTitle($sheet, $row, 'الأهداف الرئيسية / Main Objectives');

            foreach ($this->project->mainObjectives as $index => $objective) {
                $sheet->setCellValue("A{$row}", ($index + 1).'.');
                $sheet->setCellValue("B{$row}", $objective->objective ?? '');
                $row++;
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add locations sheet with hierarchical structure
     */
    private function addLocationsSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'المواقع / Locations');

        $headers = ['#', 'المحافظة / Governorate', 'المديرية / Directorate', 'المنطقة / Sub Area', 'القرية / Village', 'الملاحظات / Notes'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->locations as $index => $location) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $location->governorate->name ?? '');
            $sheet->setCellValue("C{$row}", $location->directorate->name ?? '');
            $sheet->setCellValue("D{$row}", $location->subArea->name ?? '');
            $sheet->setCellValue("E{$row}", $location->village->name ?? '');
            $sheet->setCellValue("F{$row}", $location->notes ?? '');
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add objectives, results, and outputs hierarchical sheet
     */
    private function addObjectivesSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'الأهداف والنتائج والمخرجات / Objectives, Results & Outputs');

        // Main Objectives
        if ($this->project->mainObjectives->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الأهداف الرئيسية / Main Objectives');
            $this->styleSectionHeader($sheet, "A{$row}:D{$row}");
            $row++;

            foreach ($this->project->mainObjectives as $index => $objective) {
                $sheet->setCellValue("A{$row}", ($index + 1).'.');
                $sheet->setCellValue("B{$row}", $objective->objective ?? '');
                $row++;
            }
            $row++;
        }

        // Special Objectives with Results and Outputs
        if ($this->project->specialObjectives->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الأهداف الخاصة / Special Objectives');
            $this->styleSectionHeader($sheet, "A{$row}:D{$row}");
            $row++;

            foreach ($this->project->specialObjectives as $objIndex => $objective) {
                // Objective
                $sheet->setCellValue("A{$row}", ($objIndex + 1).'.');
                $sheet->setCellValue("B{$row}", $objective->objective ?? '');
                $sheet->setCellValue("C{$row}", 'الوزن / Weight: '.$objective->objective_weight ?? 0);
                $this->styleDataCell($sheet, "B{$row}:C{$row}");
                $row++;

                // Results
                if ($objective->results->count() > 0) {
                    foreach ($objective->results as $resIndex => $result) {
                        $sheet->setCellValue("B{$row}", '↳ النتيجة '.($resIndex + 1).' / Result '.($resIndex + 1));
                        $sheet->setCellValue("C{$row}", $result->result ?? '');
                        $sheet->setCellValue("D{$row}", 'الوزن / Weight: '.$result->result_weight ?? 0);
                        $this->styleNestedCell($sheet, "B{$row}:D{$row}");
                        $row++;

                        // Outputs
                        if ($result->outputs->count() > 0) {
                            foreach ($result->outputs as $outIndex => $output) {
                                $sheet->setCellValue("C{$row}", '→ المخرج '.($outIndex + 1).' / Output '.($outIndex + 1));
                                $sheet->setCellValue("D{$row}", $output->output ?? '');
                                $this->styleDeepNestedCell($sheet, "C{$row}:D{$row}");
                                $row++;
                            }
                        }
                    }
                    $row++;
                }
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add risks sheet
     */
    private function addRisksSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'المخاطر / Risks');

        $headers = ['#', 'الوصف / Description', 'الاحتمالية / Probability', 'التأثير / Impact', 'المخفف / Mitigation', 'حالة المخاطرة / Status'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->risks as $index => $risk) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $risk->description ?? '');
            $sheet->setCellValue("C{$row}", $risk->probability ?? '');
            $sheet->setCellValue("D{$row}", $risk->impact ?? '');
            $sheet->setCellValue("E{$row}", $risk->mitigation ?? '');
            $sheet->setCellValue("F{$row}", $risk->status ?? '');
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add entities sheet (all entity types)
     */
    private function addEntitiesSheet(&$sheet)
    {
        $row = 1;

        // Supervising Authorities
        if ($this->project->supervisingAuthorities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المشرفة / Supervising Authorities');
            $headers = ['#', 'الجهة / Authority', 'النوع / Type'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->supervisingAuthorities as $index => $entity) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $entity->authority->name ?? '');
                $sheet->setCellValue("C{$row}", $entity->authority->entity_type ?? '');
                $row++;
            }
            $row++;
        }

        // Implementing Entities
        if ($this->project->implementingEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المنفذة / Implementing Entities');
            $headers = ['#', 'الجهة / Entity', 'النوع / Type'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->implementingEntities as $index => $entity) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $entity->authority->name ?? '');
                $sheet->setCellValue("C{$row}", $entity->authority->entity_type ?? '');
                $row++;
            }
            $row++;
        }

        // Participating Entities
        if ($this->project->participatingEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المشاركة / Participating Entities');
            $headers = ['#', 'الجهة / Entity', 'النوع / Type'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->participatingEntities as $index => $entity) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $entity->authority->name ?? '');
                $sheet->setCellValue("C{$row}", $entity->authority->entity_type ?? '');
                $row++;
            }
            $row++;
        }

        // Beneficiary Entities
        if ($this->project->beneficiaryEntities->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'الجهات المستفيدة / Beneficiary Entities');
            $headers = ['#', 'الجهة / Entity', 'النوع / Type'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->beneficiaryEntities as $index => $entity) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $entity->authority->name ?? '');
                $sheet->setCellValue("C{$row}", $entity->authority->entity_type ?? '');
                $row++;
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add preliminary activities with hierarchical procedures and costs
     */
    private function addPreliminaryActivitiesSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'الأنشطة التحضيرية / Preliminary Activities');

        foreach ($this->project->preliminaryActivities as $actIndex => $activity) {
            // Activity Header
            $sheet->setCellValue("A{$row}", 'النشاط '.($actIndex + 1).' / Activity '.($actIndex + 1));
            $sheet->setCellValue("B{$row}", 'الوزن / Weight: '.$activity->activity_weight ?? 0);
            $this->styleSectionHeader($sheet, "A{$row}:E{$row}");
            $row++;

            // Activity Description
            $sheet->setCellValue("A{$row}", 'الوصف / Description:');
            $sheet->setCellValue("B{$row}", $activity->description ?? '');
            $this->styleDataCell($sheet, "B{$row}");
            $row++;

            // Procedures
            if ($activity->procedures->count() > 0) {
                $sheet->setCellValue("A{$row}", 'الإجراءات / Procedures');
                $this->styleNestedHeader($sheet, "A{$row}:E{$row}");
                $row++;

                $headers = ['#', 'الإجراء / Procedure', 'التكلفة المخطط لها / Planned Cost', 'التكلفة الفعلية / Actual Cost', 'الملاحظات / Notes'];
                $this->addTableHeader($sheet, $row, $headers);
                $row++;

                foreach ($activity->procedures as $procIndex => $procedure) {
                    $plannedCost = $procedure->costs->sum('total_cost') ?? 0;
                    $sheet->setCellValue("A{$row}", $procIndex + 1);
                    $sheet->setCellValue("B{$row}", $procedure->procedure_description ?? '');
                    $sheet->setCellValue("C{$row}", $plannedCost);
                    $sheet->setCellValue("D{$row}", $procedure->actual_amount ?? 0);
                    $sheet->setCellValue("E{$row}", $procedure->notes ?? '');
                    $row++;

                    // Costs for this procedure
                    if ($procedure->costs->count() > 0) {
                        foreach ($procedure->costs as $cost) {
                            $sheet->setCellValue("B{$row}", '  ├─ '.$cost->financial_item ?? 'التكلفة');
                            $sheet->setCellValue("C{$row}", $cost->total_cost ?? 0);
                            $this->styleDeepNestedCell($sheet, "B{$row}:C{$row}");
                            $row++;
                        }
                    }
                }
                $row++;
            }
        }

        // Preliminary Financial Summary
        if ($this->project->preliminaryFinancialSummaries->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'ملخص التمويل التحضيري / Preliminary Financial Summary');
            $headers = ['#', 'البند المالي / Financial Item', 'المبلغ / Amount'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->preliminaryFinancialSummaries as $index => $summary) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $summary->financialItem->name ?? '');
                $sheet->setCellValue("C{$row}", $summary->amount ?? 0);
                $row++;
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add executive activities with hierarchical actions and costs
     */
    private function addExecutiveActivitiesSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'الأنشطة التنفيذية / Executive Activities');

        foreach ($this->project->executiveActivities as $actIndex => $activity) {
            // Activity Header
            $sheet->setCellValue("A{$row}", 'النشاط '.($actIndex + 1).' / Activity '.($actIndex + 1));
            $sheet->setCellValue("B{$row}", 'الوزن / Weight: '.$activity->activity_weight ?? 0);
            $this->styleSectionHeader($sheet, "A{$row}:E{$row}");
            $row++;

            // Activity Description
            $sheet->setCellValue("A{$row}", 'الوصف / Description:');
            $sheet->setCellValue("B{$row}", $activity->activity_description ?? '');
            $this->styleDataCell($sheet, "B{$row}");
            $row++;

            // Actions
            if ($activity->actions->count() > 0) {
                $sheet->setCellValue("A{$row}", 'الإجراءات / Actions');
                $this->styleNestedHeader($sheet, "A{$row}:E{$row}");
                $row++;

                $headers = ['#', 'الإجراء / Action', 'الجهات المسؤولة / Responsible Entities', 'التكلفة / Cost', 'الملاحظات / Notes'];
                $this->addTableHeader($sheet, $row, $headers);
                $row++;

                foreach ($activity->actions as $actionIndex => $action) {
                    $totalCost = $action->costs->sum('total_cost') ?? 0;
                    $entities = $action->assignedEntities->pluck('entity_name')->implode(', ');

                    $sheet->setCellValue("A{$row}", $actionIndex + 1);
                    $sheet->setCellValue("B{$row}", $action->action_description ?? '');
                    $sheet->setCellValue("C{$row}", $entities);
                    $sheet->setCellValue("D{$row}", $totalCost);
                    $sheet->setCellValue("E{$row}", $action->notes ?? '');
                    $row++;

                    // Costs for this action
                    if ($action->costs->count() > 0) {
                        foreach ($action->costs as $cost) {
                            $sheet->setCellValue("B{$row}", '  ├─ '.$cost->financial_item ?? 'التكلفة');
                            $sheet->setCellValue("D{$row}", $cost->total_cost ?? 0);
                            $this->styleDeepNestedCell($sheet, "B{$row}:D{$row}");
                            $row++;
                        }
                    }
                }
                $row++;
            }
        }

        // Executive Financial Summary
        if ($this->project->executiveFinancialSummaries->count() > 0) {
            $row = $this->addSectionTitle($sheet, $row, 'ملخص التمويل التنفيذي / Executive Financial Summary');
            $headers = ['#', 'البند المالي / Financial Item', 'المبلغ / Amount'];
            $this->addTableHeader($sheet, $row, $headers);
            $row++;

            foreach ($this->project->executiveFinancialSummaries as $index => $summary) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $summary->financialItem->name ?? '');
                $sheet->setCellValue("C{$row}", $summary->amount ?? 0);
                $row++;
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add financing sheet
     */
    private function addFinancingSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'التمويل / Financing');

        $headers = ['#', 'المصدر / Source', 'النوع / Type', 'الشكل / Form', 'الجهة / Authority', 'المبلغ / Amount', 'الملاحظات / Notes'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->financings as $index => $financing) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $financing->fundingSource->name ?? '');
            $sheet->setCellValue("C{$row}", $financing->financingType->name ?? '');
            $sheet->setCellValue("D{$row}", $financing->financingForm->name ?? '');
            $sheet->setCellValue("E{$row}", $financing->authority->name ?? '');
            $sheet->setCellValue("F{$row}", $financing->amount ?? 0);
            $sheet->setCellValue("G{$row}", $financing->notes ?? '');
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add financial summary sheet
     */
    private function addFinancialSummarySheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'الملخص المالي / Financial Summary');

        // Project Costs
        if ($this->project->cost) {
            $costData = [
                'التكلفة الإجمالية / Total Cost' => $this->project->cost->total_cost ?? 0,
                'تكلفة العمالة / Labour Cost' => $this->project->cost->labour_cost ?? 0,
                'تكلفة المواد / Material Cost' => $this->project->cost->material_cost ?? 0,
                'تكاليف أخرى / Other Costs' => $this->project->cost->other_costs ?? 0,
            ];

            foreach ($costData as $label => $value) {
                $sheet->setCellValue("A{$row}", $label);
                $sheet->setCellValue("B{$row}", $value);
                $this->styleLabelCell("A{$row}", $sheet);
                $row++;
            }
            $row += 2;
        }

        // Total Financing
        if ($this->project->financings->count() > 0) {
            $totalFinancing = $this->project->financings->sum('amount');
            $sheet->setCellValue("A{$row}", 'إجمالي التمويل / Total Financing');
            $sheet->setCellValue("B{$row}", $totalFinancing);
            $this->styleLabelCell("A{$row}", $sheet);
            $row += 2;
        }

        // Summary by Activity
        $row = $this->addSectionTitle($sheet, $row, 'ملخص التكاليف حسب النشاط / Cost Summary by Activity');

        if ($this->project->preliminaryActivities->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الأنشطة التحضيرية / Preliminary Activities');
            $this->styleNestedHeader($sheet, "A{$row}:B{$row}");
            $row++;

            foreach ($this->project->preliminaryActivities as $activity) {
                $totalCost = $activity->procedures->flatMap(function ($p) {
                    return $p->costs;
                })->sum('total_cost');

                $sheet->setCellValue("A{$row}", $activity->description ?? '');
                $sheet->setCellValue("B{$row}", $totalCost);
                $row++;
            }
            $row++;
        }

        if ($this->project->executiveActivities->count() > 0) {
            $sheet->setCellValue("A{$row}", 'الأنشطة التنفيذية / Executive Activities');
            $this->styleNestedHeader($sheet, "A{$row}:B{$row}");
            $row++;

            foreach ($this->project->executiveActivities as $activity) {
                $totalCost = $activity->actions->flatMap(function ($a) {
                    return $a->costs;
                })->sum('total_cost');

                $sheet->setCellValue("A{$row}", $activity->activity_description ?? '');
                $sheet->setCellValue("B{$row}", $totalCost);
                $row++;
            }
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Helper: Add section title
     */
    private function addSectionTitle(&$sheet, $row, $title)
    {
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($titleStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    /**
     * Helper: Add table header
     */
    private function addTableHeader(&$sheet, $row, $headers)
    {
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
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
     * Helper: Style label cell
     */
    private function styleLabelCell($cell, &$sheet)
    {
        $style = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE7E6E6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($cell)->applyFromArray($style);
    }

    /**
     * Helper: Style section header
     */
    private function styleSectionHeader(&$sheet, $range)
    {
        $style = [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Helper: Style nested header
     */
    private function styleNestedHeader(&$sheet, $range)
    {
        $style = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF2F5496']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEBF4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Helper: Style data cell
     */
    private function styleDataCell(&$sheet, $range)
    {
        $style = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Helper: Style nested cell
     */
    private function styleNestedCell(&$sheet, $range)
    {
        $style = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Helper: Style deep nested cell
     */
    private function styleDeepNestedCell(&$sheet, $range)
    {
        $style = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFAFAFA']],
            'font' => ['italic' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Add approval history sheet
     */
    private function addApprovalHistorySheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'سجل الموافقات / Approval History');

        $headers = ['#', 'المرحلة / Stage', 'الجهة / Authority', 'القرار / Decision', 'المتخذ / By', 'الملاحظات / Notes', 'التاريخ / Date'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->projectApprovals as $index => $app) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $app->stage->name ?? $app->stage_id ?? '');
            $sheet->setCellValue("C{$row}", $app->authority->name ?? $app->authority_id ?? '');
            $sheet->setCellValue("D{$row}", $app->status);
            $sheet->setCellValue("E{$row}", $app->createdBy->name ?? '');
            $sheet->setCellValue("F{$row}", ($app->notes ?? '').' '.($app->rejection_reason ?? '').' '.($app->required_action ?? ''));
            $sheet->setCellValue("G{$row}", $app->created_at);
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add activity logs sheet
     */
    private function addLogsSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'سجل العمليات / Activity Logs');

        $headers = ['#', 'المستخدم / User', 'العملية / Action', 'الوصف / Description', 'التاريخ / Date'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->activityHistory as $index => $log) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $log->user->name ?? '');
            $sheet->setCellValue("C{$row}", ($log->action_type ?? '').' '.($log->from_stage_name ?? '').' -> '.($log->to_stage_name ?? ''));
            $sheet->setCellValue("D{$row}", ($log->notes ?? '').' '.($log->action_details ?? ''));
            $sheet->setCellValue("E{$row}", $log->created_at);
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Add documents sheet
     */
    private function addDocumentsHierarchicalSheet(&$sheet)
    {
        $row = 1;
        $row = $this->addSectionTitle($sheet, $row, 'الوثائق والمرفقات / Documents & Attachments');

        $headers = ['#', 'نوع الوثيقة / Type', 'اسم الملف / File Name', 'بواسطة / By', 'التاريخ / Date'];
        $this->addTableHeader($sheet, $row, $headers);
        $row++;

        foreach ($this->project->documents as $index => $doc) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $doc->document_type);
            $sheet->setCellValue("C{$row}", $doc->file_name);
            $sheet->setCellValue("D{$row}", $doc->uploader->name ?? '');
            $sheet->setCellValue("E{$row}", $doc->created_at);
            $row++;
        }

        $this->autoFitColumns($sheet);
    }

    /**
     * Helper: Auto fit columns
     */
    public function autoFitColumns(&$sheet)
    {
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Helper: Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'مسودة',
            'pending' => 'قيد الانتظار',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            'implementation' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'suspended' => 'معلق',
        ];

        return $labels[$status] ?? $status;
    }
}
