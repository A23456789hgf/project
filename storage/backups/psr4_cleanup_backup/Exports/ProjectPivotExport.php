<?php

namespace App\Exports;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectPivotExport
{
    protected $project;

    protected $rowData = [];

    public function __construct(?Project $project = null)
    {
        $this->project = $project;
    }

    /**
     * Export a single project as pivot table data
     */
    public function exportSingleProject(Project $project)
    {
        $this->project = $project;

        return $this->createPivotSpreadsheet();
    }

    /**
     * Export multiple projects as pivot table data
     */
    public function exportMultipleProjects($projects)
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        // Create raw data sheet
        $sheet = $spreadsheet->createSheet(null, 'Data');
        $this->populatePivotDataSheet($sheet, $projects);

        return $spreadsheet;
    }

    /**
     * Create pivot table spreadsheet for single project
     */
    private function createPivotSpreadsheet()
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        // Create raw data sheet
        $sheet = $spreadsheet->createSheet(null, 'Data');
        $this->populatePivotDataSheet($sheet, collect([$this->project]));

        return $spreadsheet;
    }

    /**
     * Populate pivot data sheet with all project data
     */
    private function populatePivotDataSheet($sheet, $projects)
    {
        // Load relationships
        $projects->load([
            'program',
            'domain',
            'subdomain',
            'intervention',
            'priority',
            'detail',
            'locations',
            'mainObjectives',
            'specialObjectives.results.outputs',
            'risks',
            'cost',
            'financings',
            'supervisingAuthorities.authority',
            'implementingEntities.authority',
            'participatingEntities.authority',
            'beneficiaryEntities.authority',
            'preliminaryActivities.procedures.costs',
            'preliminaryFinancialSummaries',
            'executiveActivities.actions.assignedEntities',
            'executiveActivities.actions.costs',
            'executiveFinancialSummaries',
        ]);

        // Collect all pivot data
        $allRows = [];

        foreach ($projects as $project) {
            $baseData = $this->getProjectBaseData($project);

            // Expand with objectives
            $expandedRows = $this->expandWithObjectives($baseData, $project);
            $allRows = array_merge($allRows, $expandedRows);
        }

        // If no rows yet, create base rows
        if (empty($allRows)) {
            foreach ($projects as $project) {
                $baseData = $this->getProjectBaseData($project);
                $allRows[] = $baseData;
            }
        }

        // Add rows to sheet
        $this->writeDataToSheet($sheet, $allRows);

        // Merge cells with identical values
        $this->mergeDuplicateCells($sheet, $allRows);
    }

    /**
     * Get base project data
     */
    private function getProjectBaseData($project)
    {
        return [
            'Project_ID' => $project->id,
            'Project_Number' => $project->form_number,
            'Project_Name' => $project->project_name,
            'Status' => $project->status === 'draft' ? 'مسودة / Draft' : 'نهائي / Final',
            'Program' => optional($project->program)->name ?? '',
            'Domain' => optional($project->domain)->name ?? '',
            'Subdomain' => optional($project->subdomain)->name ?? '',
            'Intervention' => optional($project->intervention)->name ?? '',
            'Priority' => optional($project->priority)->name ?? '',
            'Start_Date' => $project->start_date ?? '',
            'End_Date' => $project->end_date ?? '',
            'Description' => $project->description ?? '',
        ];
    }

    /**
     * Expand base data with objectives, risks, and entities
     */
    private function expandWithObjectives($baseData, $project)
    {
        $rows = [];

        // Get all related data - convert collections to arrays to prevent array_splice issues
        $mainObjectives = $project->mainObjectives ? $project->mainObjectives->all() : [];
        $specialObjectives = $project->specialObjectives ? $project->specialObjectives->all() : [];
        $objectives = array_merge($mainObjectives, $specialObjectives);

        $risks = $project->risks ? $project->risks->all() : [];
        $supervisingAgencies = $project->supervisingAuthorities ? $project->supervisingAuthorities->all() : [];
        $implementingAgencies = $project->implementingEntities ? $project->implementingEntities->all() : [];
        $participatingAgencies = $project->participatingEntities ? $project->participatingEntities->all() : [];
        $beneficiaryAgencies = $project->beneficiaryEntities ? $project->beneficiaryEntities->all() : [];
        $preliminaryActivities = $project->preliminaryActivities ? $project->preliminaryActivities->all() : [];
        $executiveActivities = $project->executiveActivities ? $project->executiveActivities->all() : [];

        // If has objectives, expand with them
        if (! empty($objectives)) {
            foreach ($objectives as $objective) {
                $resultsCount = 0;
                $outputsCount = 0;

                if (isset($objective->results) && $objective->results) {
                    $results = $objective->results->all();
                    $resultsCount = count($results);

                    foreach ($results as $result) {
                        if (isset($result->outputs) && $result->outputs) {
                            $outputsCount += count($result->outputs->all());
                        }
                    }
                }

                $row = array_merge($baseData, [
                    'Objective_Type' => $objective->type ?? 'عام / General',
                    'Objective_Description' => $objective->description ?? '',
                    'Results' => $resultsCount,
                    'Outputs' => $outputsCount,
                ]);
                $rows[] = $row;
            }
        } else {
            // Add base row if no objectives
            $rows[] = array_merge($baseData, [
                'Objective_Type' => '',
                'Objective_Description' => '',
                'Results' => 0,
                'Outputs' => 0,
            ]);
        }

        // Add risk rows
        foreach ($risks as $risk) {
            $rows[] = array_merge($baseData, [
                'Risk_Description' => $risk->description ?? '',
                'Risk_Probability' => $risk->probability ?? '',
                'Risk_Impact' => $risk->impact ?? '',
                'Risk_Mitigation' => $risk->mitigation_strategy ?? '',
            ]);
        }

        // Add supervising agencies
        foreach ($supervisingAgencies as $agency) {
            $rows[] = array_merge($baseData, [
                'Agency_Type' => 'إشرافية / Supervising',
                'Agency_Name' => optional($agency->authority)->name ?? '',
            ]);
        }

        // Add implementing agencies
        foreach ($implementingAgencies as $agency) {
            $rows[] = array_merge($baseData, [
                'Agency_Type' => 'منفذة / Implementing',
                'Agency_Name' => optional($agency->authority)->name ?? '',
            ]);
        }

        // Add participating agencies
        foreach ($participatingAgencies as $agency) {
            $rows[] = array_merge($baseData, [
                'Agency_Type' => 'مشاركة / Participating',
                'Agency_Name' => optional($agency->authority)->name ?? '',
            ]);
        }

        // Add beneficiary agencies
        foreach ($beneficiaryAgencies as $agency) {
            $rows[] = array_merge($baseData, [
                'Agency_Type' => 'المستفيدة / Beneficiary',
                'Agency_Name' => optional($agency->authority)->name ?? '',
            ]);
        }

        // Add preliminary activities
        foreach ($preliminaryActivities as $activity) {
            $procedures = isset($activity->procedures) && $activity->procedures ? $activity->procedures->all() : [];

            if (! empty($procedures)) {
                foreach ($procedures as $procedure) {
                    $procedureCost = 0;
                    if (isset($procedure->costs) && $procedure->costs) {
                        $procedureCost = $procedure->costs->sum('total');
                    }

                    $rows[] = array_merge($baseData, [
                        'Activity_Type' => 'تمهيدية / Preliminary',
                        'Activity_Description' => $activity->name ?? '',
                        'Procedure_Description' => $procedure->procedure_name ?? '',
                        'Procedure_Cost' => $procedureCost,
                    ]);
                }
            } else {
                $rows[] = array_merge($baseData, [
                    'Activity_Type' => 'تمهيدية / Preliminary',
                    'Activity_Description' => $activity->name ?? '',
                    'Procedure_Description' => '',
                    'Procedure_Cost' => 0,
                ]);
            }
        }

        // Add executive activities
        foreach ($executiveActivities as $activity) {
            $actions = isset($activity->actions) && $activity->actions ? $activity->actions->all() : [];

            if (! empty($actions)) {
                foreach ($actions as $action) {
                    $responsibleEntity = '';
                    $actionCost = 0;

                    if (isset($action->assignedEntities) && $action->assignedEntities) {
                        $responsibleEntity = implode(', ', $action->assignedEntities->pluck('name')->toArray());
                    }

                    if (isset($action->costs) && $action->costs) {
                        $actionCost = $action->costs->sum('total');
                    }

                    $rows[] = array_merge($baseData, [
                        'Activity_Type' => 'تنفيذية / Executive',
                        'Activity_Description' => $activity->name ?? '',
                        'Action_Description' => $action->action ?? '',
                        'Responsible_Entity' => $responsibleEntity,
                        'Action_Cost' => $actionCost,
                    ]);
                }
            } else {
                $rows[] = array_merge($baseData, [
                    'Activity_Type' => 'تنفيذية / Executive',
                    'Activity_Description' => $activity->name ?? '',
                    'Action_Description' => '',
                    'Responsible_Entity' => '',
                    'Action_Cost' => 0,
                ]);
            }
        }

        // If no data was added, return base data
        if (count($rows) === 0) {
            $rows[] = $baseData;
        }

        return $rows;
    }

    /**
     * Write data to sheet with formatting
     */
    private function writeDataToSheet($sheet, $allRows)
    {
        if (empty($allRows)) {
            return;
        }

        // Get headers from first row
        $headers = array_keys($allRows[0]);

        // Write headers
        $col = 1;
        foreach ($headers as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($header);

            // Style header
            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F4E78'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            $col++;
        }

        // Write data rows
        $rowNum = 2;
        foreach ($allRows as $rowData) {
            $col = 1;
            foreach ($headers as $header) {
                $value = $rowData[$header] ?? '';
                $cell = $sheet->getCellByColumnAndRow($col, $rowNum);
                $cell->setValue($value);

                // Apply styling to each cell
                $sheet->getStyleByColumnAndRow($col, $rowNum)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => $rowNum % 2 == 0 ? 'FFF2F2F2' : 'FFFFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $col++;
            }
            $rowNum++;
        }

        // Set column widths
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze first row
        $sheet->freezePane('A2');
    }

    /**
     * Merge cells with identical consecutive values
     */
    private function mergeDuplicateCells($sheet, $allRows)
    {
        if (empty($allRows)) {
            return;
        }

        $headers = array_keys($allRows[0]);
        $dataRowCount = count($allRows);

        // Process each column
        foreach ($headers as $colIndex => $header) {
            $col = $colIndex + 1; // PhpSpreadsheet columns are 1-indexed
            $mergeStart = 2; // Start from row 2 (row 1 is header)
            $prevValue = $allRows[0][$header] ?? '';

            // Scan through each row
            for ($row = 2; $row <= $dataRowCount + 1; $row++) {
                $currentValue = isset($allRows[$row - 2][$header]) ? $allRows[$row - 2][$header] : '';

                // Check if value changed or we reached the last row
                if ($currentValue !== $prevValue || $row === $dataRowCount + 1) {
                    // Merge cells if more than one row with same value
                    if ($row - $mergeStart > 0) {
                        $mergeEnd = $row - 1;
                        if ($mergeStart !== $mergeEnd) {
                            // Merge the range
                            $cellStart = $sheet->getCellByColumnAndRow($col, $mergeStart);
                            $cellEnd = $sheet->getCellByColumnAndRow($col, $mergeEnd);
                            $sheet->mergeCells($cellStart->getCoordinate().':'.$cellEnd->getCoordinate());

                            // Apply center alignment to merged cells
                            $sheet->getStyle($cellStart->getCoordinate().':'.$cellEnd->getCoordinate())
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                        }
                    }

                    // Move to next merge range
                    $mergeStart = $row;
                    $prevValue = $currentValue;
                }
            }
        }
    }
}
