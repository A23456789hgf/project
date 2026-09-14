<?php

namespace App\Imports;

use App\Services\ProjectImportService;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ProjectImport implements WithHeadingRow, WithMultipleSheets
{
    use Importable;

    public function __construct()
    {
        HeadingRowFormatter::default('none');
    }

    private $failures = [];

    private $report = [
        'total_rows' => 0,
        'successful_inserts' => 0,
        'successful_updates' => 0,
        'failed_rows' => 0,
        'skipped_rows' => 0,
    ];

    // Individual sheet importers store rows here
    private $projectInfoRows = [];

    private $detailRows = [];

    private $locationRows = [];

    private $mainObjectiveRows = [];

    private $specialObjectiveRows = [];

    private $resultsOutputRows = [];

    private $preliminaryActivityRows = [];

    private $executiveActivityRows = [];

    private $financingRows = [];

    private $entityRows = [];

    public function sheets(): array
    {
        return [
            'بيانات المشروع' => new ProjectImportSheets\ProjectInfoImport($this),
            'التفاصيل' => new ProjectImportSheets\GenericSheetImport($this, 'detail'),
            'المواقع' => new ProjectImportSheets\GenericSheetImport($this, 'locations'),
            'الأهداف الرئيسية' => new ProjectImportSheets\GenericSheetImport($this, 'main_objectives'),
            'الأهداف الخاصة' => new ProjectImportSheets\GenericSheetImport($this, 'special_objectives'),
            'النتائج والمخرجات' => new ProjectImportSheets\GenericSheetImport($this, 'results_outputs'),
            'الأنشطة الأولية' => new ProjectImportSheets\GenericSheetImport($this, 'preliminary_activities'),
            'الأنشطة التنفيذية' => new ProjectImportSheets\GenericSheetImport($this, 'executive_activities'),
            'التمويلات' => new ProjectImportSheets\GenericSheetImport($this, 'financings'),
            'الجهات' => new ProjectImportSheets\GenericSheetImport($this, 'entities'),
            'القيم المرجعية' => new ProjectImportSheets\SkipSheetImport,
        ];
    }

    /**
     * Called by sub-sheet importers to push rows
     */
    public function addRows(string $type, array $rows)
    {
        switch ($type) {
            case 'project_info':    $this->projectInfoRows = $rows;
                break;
            case 'detail':          $this->detailRows = $rows;
                break;
            case 'locations':       $this->locationRows = $rows;
                break;
            case 'main_objectives': $this->mainObjectiveRows = $rows;
                break;
            case 'special_objectives': $this->specialObjectiveRows = $rows;
                break;
            case 'results_outputs': $this->resultsOutputRows = $rows;
                break;
            case 'preliminary_activities': $this->preliminaryActivityRows = $rows;
                break;
            case 'executive_activities': $this->executiveActivityRows = $rows;
                break;
            case 'financings':      $this->financingRows = $rows;
                break;
            case 'entities':        $this->entityRows = $rows;
                break;
        }
    }

    /**
     * Process all imported sheets and delegate to ProjectImportService
     */
    public function process(): array
    {
        $service = new ProjectImportService;
        $projectNames = collect($this->projectInfoRows)->pluck('project_name')->filter()->unique();

        $this->report['total_rows'] = $projectNames->count();

        foreach ($projectNames as $projectName) {
            try {
                $projectInfo = collect($this->projectInfoRows)
                    ->firstWhere('project_name', $projectName);

                if (! $projectInfo) {
                    continue;
                }

                $data = $this->buildProjectData($projectName, $projectInfo);
                $service->import($data);
                $this->report['successful_inserts']++;

            } catch (\Exception $e) {
                $this->report['failed_rows']++;
                $this->failures[] = [
                    'row' => $projectName,
                    'attribute' => 'general',
                    'errors' => [$e->getMessage()],
                    'values' => [],
                ];
            }
        }

        return $this->report;
    }

    /**
     * Build the nested data array expected by ProjectImportService
     */
    private function buildProjectData(string $projectName, array $projectInfo): array
    {
        $data = $projectInfo;

        // Detail
        $detail = collect($this->detailRows)->firstWhere('project_name', $projectName);
        if ($detail) {
            unset($detail['project_name']);
            $data['detail'] = $detail;
        }

        // Locations
        $locations = collect($this->locationRows)->where('project_name', $projectName)->values();
        $data['locations'] = $locations->map(function ($loc) {
            unset($loc['project_name']);

            return $loc;
        })->toArray();

        // Main Objectives
        $mainObjectives = collect($this->mainObjectiveRows)->where('project_name', $projectName)->values();
        $data['main_objectives'] = $mainObjectives->map(function ($obj) {
            unset($obj['project_name']);

            return $obj;
        })->toArray();

        // Special Objectives
        $specialObjectives = collect($this->specialObjectiveRows)->where('project_name', $projectName)->values();
        $data['special_objectives'] = $specialObjectives->map(function ($obj) {
            unset($obj['project_name']);

            return $obj;
        })->toArray();

        // Results & Outputs — group by result_name, nest outputs
        $resultsOutputs = collect($this->resultsOutputRows)->where('project_name', $projectName);
        $grouped = $resultsOutputs->groupBy('result_name');
        $data['objective_results'] = $grouped->map(function ($group, $resultName) {
            $first = $group->first();

            return [
                'result_name' => $resultName,
                'target_value' => $first['target_value'] ?? null,
                'indicator_type' => $first['indicator_type'] ?? null,
                'indicator_unit' => $first['indicator_unit'] ?? null,
                'outputs' => $group->map(function ($row) {
                    return ['output' => $row['output'] ?? null];
                })->filter(fn ($o) => ! empty($o['output']))->values()->toArray(),
            ];
        })->values()->toArray();

        // Preliminary Activities — group by activity_name, nest procedures and costs
        $prelimActivities = collect($this->preliminaryActivityRows)->where('project_name', $projectName);
        $grouped = $prelimActivities->groupBy('activity_name');
        $data['preliminary_activities'] = $grouped->map(function ($group, $activityName) {
            $first = $group->first();

            return [
                'name' => $activityName,
                'weight' => $first['activity_weight'] ?? null,
                'procedures' => $group->groupBy('procedure_name')->map(function ($procGroup, $procName) {
                    return [
                        'procedure_name' => $procName,
                        'costs' => $procGroup->map(function ($row) {
                            return [
                                'financial_item_id' => $row['financial_item_id'] ?? null,
                                'amount' => $row['cost_amount'] ?? null,
                            ];
                        })->filter(fn ($c) => ! empty($c['financial_item_id']))->values()->toArray(),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        // Executive Activities — group by activity_name, nest actions, entities, costs
        $execActivities = collect($this->executiveActivityRows)->where('project_name', $projectName);
        $grouped = $execActivities->groupBy('activity_name');
        $data['executive_activities'] = $grouped->map(function ($group, $activityName) {
            $first = $group->first();

            return [
                'name' => $activityName,
                'weight' => $first['activity_weight'] ?? null,
                'actions' => $group->groupBy('action')->map(function ($actionGroup, $actionName) {
                    $first = $actionGroup->first();

                    return [
                        'action' => $actionName,
                        'weight' => $first['action_weight'] ?? null,
                        'start_date' => $first['start_date'] ?? null,
                        'end_date' => $first['end_date'] ?? null,
                        'verification_means' => $first['verification_means'] ?? null,
                        'assigned_entities' => $actionGroup
                            ->pluck('assigned_entity_id')
                            ->filter()
                            ->unique()
                            ->map(fn ($id) => ['entity_id' => $id])
                            ->values()->toArray(),
                        'costs' => $actionGroup->map(function ($row) {
                            return [
                                'financial_item_id' => $row['financial_item_id'] ?? null,
                                'amount' => $row['cost_amount'] ?? null,
                            ];
                        })->filter(fn ($c) => ! empty($c['financial_item_id']))->values()->toArray(),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        // Financings
        $financings = collect($this->financingRows)->where('project_name', $projectName)->values();
        $data['financings'] = $financings->map(function ($fin) {
            unset($fin['project_name']);

            return $fin;
        })->toArray();

        // Entities — split by role
        $entities = collect($this->entityRows)->where('project_name', $projectName);
        $data['supervising_authorities'] = $entities->where('entity_role', 'supervising')
            ->map(function ($e) {
                return [
                    'authority_type' => $e['authority_type'] ?? null,
                    'authority_id' => $e['authority_id'] ?? null,
                    'parent_id' => $e['parent_id'] ?? null,
                    'entity_name' => $e['authority_id'] ?? null,
                ];
            })->values()->toArray();

        $data['implementing_entities'] = $entities->where('entity_role', 'implementing')
            ->map(function ($e) {
                return [
                    'authority_type' => $e['authority_type'] ?? null,
                    'authority_id' => $e['authority_id'] ?? null,
                    'parent_id' => $e['parent_id'] ?? null,
                    'entity_name' => $e['authority_id'] ?? null,
                ];
            })->values()->toArray();

        $data['participating_entities'] = $entities->where('entity_role', 'participating')
            ->map(function ($e) {
                return [
                    'entity_type' => $e['authority_type'] ?? null,
                    'authority_id' => $e['authority_id'] ?? null,
                    'parent_id' => $e['parent_id'] ?? null,
                    'entity_name' => $e['authority_id'] ?? null,
                ];
            })->values()->toArray();

        return $data;
    }

    public function getReport()
    {
        return $this->report;
    }

    public function failures()
    {
        return $this->failures;
    }
}
