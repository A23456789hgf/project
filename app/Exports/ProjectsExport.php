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
    private $headers = [
        'A' => 'المعرف',
        'B' => 'رقم الاستمارة',
        'C' => 'اسم المشروع',
        'D' => 'البرنامج',
        'E' => 'المجال الرئيسي',
        'F' => 'المجال الفرعي',
        'G' => 'نوع التدخل',
        'H' => 'الأولوية',
        'I' => 'الفئة المستهدفة',
        'J' => 'تاريخ البداية',
        'K' => 'تاريخ النهاية',
        'L' => 'الحالة',
        'M' => 'عدد المستفيدين',
        'N' => 'تاريخ الإنشاء',
        'O' => 'تاريخ التحديث',

        'P' => 'المحافظة',
        'Q' => 'المديرية',
        'R' => 'المنطقة',
        'S' => 'القرية',

        'T' => 'الجهات المشرفة',
        'U' => 'الجهات المنفذة',
        'V' => 'الجهات المشاركة',
        'W' => 'الجهات المستفيدة',

        'X' => 'مصدر التمويل',
        'Y' => 'نوع التمويل',
        'Z' => 'جهة التمويل',
        'AA' => 'مبلغ التمويل',
        'AB' => 'نسبة التمويل (%)',

        'AC' => 'الأهداف الرئيسية',

        'AD' => 'الأهداف الخاصة',
        'AE' => 'النتائج',
        'AF' => 'المخرجات',
        'AG' => 'قيمة المستهدف',
        'AH' => 'وحدة القياس',

        'AI' => 'المخاطر',

        'AJ' => 'الأنشطة التمهيدية',
        'AK' => 'الإجراءات',
        'AL' => 'بند التكلفة (تمهيدي)',
        'AM' => 'مبلغ التكلفة (تمهيدي)',

        'AN' => 'الأنشطة التنفيذية',
        'AO' => 'الخطوات التنفيذية',
        'AP' => 'الجهات المنفذة للخطوة',
        'AQ' => 'بند التكلفة (تنفيذي)',
        'AR' => 'مبلغ التكلفة (تنفيذي)',
    ];

    public function export()
    {
        $startTime = microtime(true);

        try {
            Log::info('ProjectsExport: Starting hierarchical data processing', [
                'timestamp' => now()->toDateTimeString(),
            ]);

            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('المشاريع');
            $sheet->setRightToLeft(true);

            $lastCol = 'AR';

            // Add title row
            $sheet->setCellValue('A1', 'تقرير المشاريع التفصيلي');
            $sheet->mergeCells("A1:{$lastCol}1");

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
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Add export date
            $sheet->setCellValue('A2', 'تاريخ التصدير: '.date('Y-m-d H:i:s'));
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->getStyle("A2:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Set headers
            foreach ($this->headers as $col => $header) {
                $sheet->setCellValue($col.'4', $header);
            }

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

            $sheet->getStyle("A4:{$lastCol}4")->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(30);

            // Get projects data with relationships
            $projects = Project::with([
                'program', 'domain', 'subdomain', 'intervention', 'priority', 'targetCategory',
                'locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village',
                'supervisingAuthorities.authority', 'supervisingAuthorities.internalEntity',
                'implementingEntities.authority', 'implementingEntities.internalEntity',
                'participatingEntities.authority', 'participatingEntities.internalEntity',
                'beneficiaryEntities.authority', 'beneficiaryEntities.internalEntity',
                'financings.fundingSource', 'financings.financingType', 'financings.authority',
                'mainObjectives',
                'specialObjectives.results.outputs',
                'risks',
                'preliminaryActivities.procedures.costs.financialItem',
                'executiveActivities.actions.assignedEntities',
                'executiveActivities.actions.costs.financialItem',
            ])
                ->orderBy('project_name')
                ->get();

            Log::info('ProjectsExport: Data retrieved', [
                'total_records' => $projects->count(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            $currentRow = 5;

            foreach ($projects as $project) {
                // Calculate max rows for hierarchical sections
                $specObjRows = 0;
                $specObjData = [];
                foreach ($project->specialObjectives as $obj) {
                    $objRows = 0;
                    $resultsData = [];
                    foreach ($obj->results as $res) {
                        $resRows = max(1, $res->outputs->count());
                        $objRows += $resRows;
                        $resultsData[] = ['result' => $res, 'rows' => $resRows];
                    }
                    $objRows = max(1, $objRows);
                    $specObjRows += $objRows;
                    $specObjData[] = ['objective' => $obj, 'rows' => $objRows, 'results' => $resultsData];
                }

                $prelimRows = 0;
                $prelimData = [];
                foreach ($project->preliminaryActivities as $act) {
                    $actRows = 0;
                    $procData = [];
                    foreach ($act->procedures as $proc) {
                        $procRows = max(1, $proc->costs->count());
                        $actRows += $procRows;
                        $procData[] = ['procedure' => $proc, 'rows' => $procRows];
                    }
                    $actRows = max(1, $actRows);
                    $prelimRows += $actRows;
                    $prelimData[] = ['activity' => $act, 'rows' => $actRows, 'procedures' => $procData];
                }

                $execRows = 0;
                $execData = [];
                foreach ($project->executiveActivities as $act) {
                    $actRows = 0;
                    $actionData = [];
                    foreach ($act->actions as $action) {
                        $actionRows = max(1, $action->costs->count());
                        $actRows += $actionRows;
                        $actionData[] = ['action' => $action, 'rows' => $actionRows];
                    }
                    $actRows = max(1, $actRows);
                    $execRows += $actRows;
                    $execData[] = ['activity' => $act, 'rows' => $actRows, 'actions' => $actionData];
                }

                // Determine maximum rows required for this project block
                $projectTotalRows = max(
                    1,
                    $project->locations->count(),
                    $project->supervisingAuthorities->count(),
                    $project->implementingEntities->count(),
                    $project->participatingEntities->count(),
                    $project->beneficiaryEntities->count(),
                    $project->financings->count(),
                    $project->mainObjectives->count(),
                    $project->risks->count(),
                    $specObjRows,
                    $prelimRows,
                    $execRows
                );

                $endRow = $currentRow + $projectTotalRows - 1;

                // 1. Write Project Base Info (A - O)
                $projFields = [
                    'A' => $project->id,
                    'B' => $project->form_number ?? '',
                    'C' => $project->project_name ?? '',
                    'D' => $project->program ? $project->program->name : '',
                    'E' => $project->domain ? $project->domain->name : '',
                    'F' => $project->subdomain ? $project->subdomain->name : '',
                    'G' => $project->intervention ? $project->intervention->name : '',
                    'H' => $project->priority ? $project->priority->name : '',
                    'I' => $project->targetCategory ? $project->targetCategory->name : '',
                    'J' => $project->start_date_gregorian ?? '',
                    'K' => $project->end_date_gregorian ?? '',
                    'L' => $this->getStatusLabel($project->status),
                    'M' => $project->number_of_beneficiaries ?? '',
                    'N' => $project->created_at ? $project->created_at->format('Y-m-d H:i:s') : '',
                    'O' => $project->updated_at ? $project->updated_at->format('Y-m-d H:i:s') : '',
                ];

                foreach ($projFields as $col => $val) {
                    $sheet->setCellValue($col.$currentRow, $val);
                    if ($projectTotalRows > 1) {
                        $sheet->mergeCells($col.$currentRow.':'.$col.$endRow);
                    }
                }

                // 2. Write Independent Lists
                // Locations (P-S)
                $r = $currentRow;
                foreach ($project->locations as $loc) {
                    $sheet->setCellValue('P'.$r, optional($loc->governorate)->name);
                    $sheet->setCellValue('Q'.$r, optional($loc->directorate)->name);
                    $sheet->setCellValue('R'.$r, optional($loc->subArea)->name);
                    $sheet->setCellValue('S'.$r, optional($loc->village)->name);
                    $r++;
                }

                // Supervising Authorities (T)
                $r = $currentRow;
                foreach ($project->supervisingAuthorities as $ent) {
                    $name = optional($ent->authority)->name ?? optional($ent->internalEntity)->name;
                    $sheet->setCellValue('T'.$r, $name);
                    $r++;
                }

                // Implementing Entities (U)
                $r = $currentRow;
                foreach ($project->implementingEntities as $ent) {
                    $name = optional($ent->authority)->name ?? optional($ent->internalEntity)->name;
                    $sheet->setCellValue('U'.$r, $name);
                    $r++;
                }

                // Participating Entities (V)
                $r = $currentRow;
                foreach ($project->participatingEntities as $ent) {
                    $name = optional($ent->authority)->name ?? optional($ent->internalEntity)->name;
                    $sheet->setCellValue('V'.$r, $name);
                    $r++;
                }

                // Beneficiary Entities (W)
                $r = $currentRow;
                foreach ($project->beneficiaryEntities as $ent) {
                    $name = optional($ent->authority)->name ?? optional($ent->internalEntity)->name;
                    $sheet->setCellValue('W'.$r, $name);
                    $r++;
                }

                // Financings (X-AB)
                $r = $currentRow;
                foreach ($project->financings as $fin) {
                    $sheet->setCellValue('X'.$r, optional($fin->fundingSource)->name);
                    $sheet->setCellValue('Y'.$r, optional($fin->financingType)->name);
                    $sheet->setCellValue('Z'.$r, optional($fin->authority)->name);
                    $sheet->setCellValue('AA'.$r, $fin->financing_amount);
                    $sheet->setCellValue('AB'.$r, $fin->financing_percentage);
                    $r++;
                }

                // Main Objectives (AC)
                $r = $currentRow;
                foreach ($project->mainObjectives as $obj) {
                    $sheet->setCellValue('AC'.$r, $obj->objective);
                    $r++;
                }

                // Risks (AI)
                $r = $currentRow;
                foreach ($project->risks as $risk) {
                    $sheet->setCellValue('AI'.$r, $risk->risk);
                    $r++;
                }

                // 3. Write Hierarchical Nested Lists
                // Special Objectives (AD-AH)
                $r = $currentRow;
                foreach ($specObjData as $objData) {
                    $objRows = $objData['rows'];
                    $sheet->setCellValue('AD'.$r, $objData['objective']->objective);
                    if ($objRows > 1) {
                        $sheet->mergeCells('AD'.$r.':AD'.($r + $objRows - 1));
                    }

                    $resR = $r;
                    foreach ($objData['results'] as $resData) {
                        $resRows = $resData['rows'];
                        $sheet->setCellValue('AE'.$resR, $resData['result']->result_name);
                        if ($resRows > 1) {
                            $sheet->mergeCells('AE'.$resR.':AE'.($resR + $resRows - 1));
                        }

                        $outR = $resR;
                        foreach ($resData['result']->outputs as $out) {
                            $sheet->setCellValue('AF'.$outR, $out->output);
                            $sheet->setCellValue('AG'.$outR, $out->target_value);
                            $sheet->setCellValue('AH'.$outR, $out->unit);
                            $outR++;
                        }
                        $resR += $resRows;
                    }
                    $r += $objRows;
                }

                // Preliminary Activities (AJ-AM)
                $r = $currentRow;
                foreach ($prelimData as $actData) {
                    $actRows = $actData['rows'];
                    $sheet->setCellValue('AJ'.$r, $actData['activity']->name);
                    if ($actRows > 1) {
                        $sheet->mergeCells('AJ'.$r.':AJ'.($r + $actRows - 1));
                    }

                    $procR = $r;
                    foreach ($actData['procedures'] as $procData) {
                        $procRows = $procData['rows'];
                        $sheet->setCellValue('AK'.$procR, $procData['procedure']->procedure_name);
                        if ($procRows > 1) {
                            $sheet->mergeCells('AK'.$procR.':AK'.($procR + $procRows - 1));
                        }

                        $costR = $procR;
                        foreach ($procData['procedure']->costs as $cost) {
                            $itemName = optional($cost->financialItem)->name ?? $cost->financial_item_id;
                            $sheet->setCellValue('AL'.$costR, $itemName);
                            $sheet->setCellValue('AM'.$costR, $cost->amount);
                            $costR++;
                        }
                        $procR += $procRows;
                    }
                    $r += $actRows;
                }

                // Executive Activities (AN-AR)
                $r = $currentRow;
                foreach ($execData as $actData) {
                    $actRows = $actData['rows'];
                    $sheet->setCellValue('AN'.$r, $actData['activity']->name);
                    if ($actRows > 1) {
                        $sheet->mergeCells('AN'.$r.':AN'.($r + $actRows - 1));
                    }

                    $actnR = $r;
                    foreach ($actData['actions'] as $actionData) {
                        $actionRows = $actionData['rows'];
                        $sheet->setCellValue('AO'.$actnR, $actionData['action']->action);

                        // comma-separated assigned entities for the action
                        $assignedEntitiesStr = $actionData['action']->assignedEntities->map(function ($e) {
                            return $e->authority_name ?? $e->entity_name;
                        })->filter()->implode('، ');
                        $sheet->setCellValue('AP'.$actnR, $assignedEntitiesStr);

                        if ($actionRows > 1) {
                            $sheet->mergeCells('AO'.$actnR.':AO'.($actnR + $actionRows - 1));
                            $sheet->mergeCells('AP'.$actnR.':AP'.($actnR + $actionRows - 1));
                        }

                        $costR = $actnR;
                        foreach ($actionData['action']->costs as $cost) {
                            $itemName = optional($cost->financialItem)->name ?? $cost->financial_item_id;
                            $sheet->setCellValue('AQ'.$costR, $itemName);
                            $sheet->setCellValue('AR'.$costR, $cost->amount);
                            $costR++;
                        }
                        $actnR += $actionRows;
                    }
                    $r += $actRows;
                }

                // Apply borders and alignment to this project's block
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$endRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                // Outer border for the project block
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$endRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $currentRow = $endRow + 1;
            }

            // Set column auto-size and some manual widths
            $allCols = array_keys($this->headers);
            foreach ($allCols as $column) {
                // We'll let excel autosize, but we'll enforce text wrap which was set above
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Add summary at the bottom
            $summaryRow = $currentRow + 1;
            $sheet->setCellValue('A'.$summaryRow, 'إجمالي عدد المشاريع: '.$projects->count());
            $sheet->mergeCells('A'.$summaryRow.':'.$lastCol.$summaryRow);
            $sheet->getStyle('A'.$summaryRow.':'.$lastCol.$summaryRow)->applyFromArray([
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
            Log::error('ProjectsExport: Export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);
            throw $e;
        }
    }

    private function getStatusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'financial_review' => 'مراجعة مالية',
            'technical_review' => 'مراجعة فنية',
            'reviewed_completed' => 'مراجعة مكتملة',
            'pending_approval' => 'بانتظار الاعتماد',
            'assembly_approval' => 'اعتماد الجمعية العمومية',
            'union_approval' => 'اعتماد الاتحاد',
            'committee_approval' => 'اعتماد اللجنة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'returned' => 'معاد',
            'completed' => 'مكتمل',
            default => $status ?? 'غير محدد',
        };
    }
}
