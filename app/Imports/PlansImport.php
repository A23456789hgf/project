<?php

namespace App\Imports;

use App\Imports\PlanImportSheets\GenericPlanSheetImport;
use App\Models\FundingSource;
use App\Models\InternalEntity;
use App\Models\Plan;
use App\Models\PlanProject;
use App\Models\PlanProjectActivity;
use App\Models\PlanProjectActivityAction;
use App\Models\Priority;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PlansImport implements WithMultipleSheets
{
    public $importMode; // 'add' or 'update'

    public $importedPlans = 0;

    public $importedProjects = 0;

    public $importedActivities = 0;

    public $importedActions = 0;

    public $errors = [];

    private array $planRows = [];

    private array $projectRows = [];

    private array $goalRows = [];

    private array $activityRows = [];

    private array $actionRows = [];

    public $trackingService;

    public $importLog;

    public function __construct($importMode = 'add', $trackingService = null, $importLog = null)
    {
        $this->importMode = $importMode;
        $this->trackingService = $trackingService;
        $this->importLog = $importLog;
        HeadingRowFormatter::default('none');
    }

    public function sheets(): array
    {
        return [
            'الخطط' => new GenericPlanSheetImport($this, 'plans'),
            'المشاريع' => new GenericPlanSheetImport($this, 'projects'),
            'الأهداف' => new GenericPlanSheetImport($this, 'goals'),
            'الأنشطة' => new GenericPlanSheetImport($this, 'activities'),
            'الإجراءات' => new GenericPlanSheetImport($this, 'actions'),
        ];
    }

    public function addRows(string $type, array $rows)
    {
        switch ($type) {
            case 'plans':      $this->planRows = $rows;
                break;
            case 'projects':   $this->projectRows = $rows;
                break;
            case 'goals':      $this->goalRows = $rows;
                break;
            case 'activities': $this->activityRows = $rows;
                break;
            case 'actions':    $this->actionRows = $rows;
                break;
        }
    }

    public function process()
    {
        DB::beginTransaction();
        try {
            // Process Plans
            foreach ($this->planRows as $row) {
                if (empty($row['رقم الخطة'])) {
                    continue;
                }
                $this->processPlan($row);
            }

            // Process Projects
            foreach ($this->projectRows as $row) {
                if (empty($row['رقم الخطة']) || empty($row['اسم المشروع'])) {
                    continue;
                }
                $this->processProject($row);
            }

            // Process Goals
            foreach ($this->goalRows as $row) {
                if (empty($row['اسم المشروع']) || empty($row['الهدف المحدد'])) {
                    continue;
                }
                $this->processGoal($row);
            }

            // Process Activities
            foreach ($this->activityRows as $row) {
                if (empty($row['اسم المشروع']) || empty($row['اسم النشاط'])) {
                    continue;
                }
                $this->processActivity($row);
            }

            // Process Actions
            foreach ($this->actionRows as $row) {
                if (empty($row['اسم النشاط']) || empty($row['اسم الإجراء'])) {
                    continue;
                }
                $this->processAction($row);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PlansImport Error: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
            $this->errors[] = $e->getMessage();
            throw $e;
        }
    }

    protected function processPlan(array $row)
    {
        $planNumber = $row['رقم الخطة'];
        $plan = null;

        if ($this->importMode === 'update') {
            $plan = Plan::where('plan_number', $planNumber)->first();
        }

        if (! $plan) {
            $priority = Priority::where('priority', $row['الأولوية'] ?? '')->first();
            $entity = InternalEntity::where('name', $row['الجهة المقدمة'] ?? '')->first();

            $plan = Plan::create([
                'plan_number' => $planNumber,
                'priority_id' => $priority ? $priority->id : null,
                'submitting_entity_id' => $entity ? $entity->id : Auth::user()->getUserEntityId(),
                'created_by' => Auth::id(),
            ]);
            $this->importedPlans++;
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $plan, 'created');
            }
        } else {
            // Optional: update plan details if in update mode
            $priority = Priority::where('priority', $row['الأولوية'] ?? '')->first();
            $entity = InternalEntity::where('name', $row['الجهة المقدمة'] ?? '')->first();
            $plan->update([
                'priority_id' => $priority ? $priority->id : $plan->priority_id,
                'submitting_entity_id' => $entity ? $entity->id : $plan->submitting_entity_id,
            ]);
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $plan, 'updated');
            }
        }
    }

    protected function processProject(array $row)
    {
        $planNumber = $row['رقم الخطة'];
        $projectName = $row['اسم المشروع'];

        $plan = Plan::where('plan_number', $planNumber)->first();
        if (! $plan) {
            $this->errors[] = "الخطة رقم $planNumber غير موجودة، تم تخطي المشروع $projectName.";

            return;
        }

        $project = PlanProject::where('plan_id', $plan->id)
            ->where('name', $projectName)
            ->first();

        $fundingSource = FundingSource::where('name', $row['مصدر التمويل'] ?? '')->first();
        $participatingEntity = InternalEntity::where('name', $row['الجهة المشاركة'] ?? '')->first();
        $isFundingAvailable = isset($row['توفر التمويل']) && in_array(strtolower($row['توفر التمويل']), ['نعم', 'yes', '1', 1], true);

        if (! $project) {
            $project = PlanProject::create([
                'plan_id' => $plan->id,
                'name' => $projectName,
                'importance' => $this->reverseImportanceLabel($row['الأهمية'] ?? ''),
                'status' => $this->reverseStatusLabel($row['حالة المشروع'] ?? ''),
                'baseline' => $row['خط الأساس'] ?? '',
                'cost_type' => $row['نوع التكلفة'] ?? '',
                'cost' => $row['التكلفة'] ?? null,
                'funding_availability' => $isFundingAvailable,
                'funding_source_id' => $fundingSource ? $fundingSource->id : null,
                'participating_entity_id' => $participatingEntity ? $participatingEntity->id : null,
            ]);
            $this->importedProjects++;
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $project, 'created');
            }
        } elseif ($this->importMode === 'update') {
            $project->update([
                'importance' => $this->reverseImportanceLabel($row['الأهمية'] ?? ''),
                'status' => $this->reverseStatusLabel($row['حالة المشروع'] ?? ''),
                'baseline' => $row['خط الأساس'] ?? '',
                'cost_type' => $row['نوع التكلفة'] ?? '',
                'cost' => $row['التكلفة'] ?? null,
                'funding_availability' => $isFundingAvailable,
                'funding_source_id' => $fundingSource ? $fundingSource->id : $project->funding_source_id,
                'participating_entity_id' => $participatingEntity ? $participatingEntity->id : $project->participating_entity_id,
            ]);
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $project, 'updated');
            }
        }
    }

    protected function processGoal(array $row)
    {
        $projectName = $row['اسم المشروع'];
        $goalName = trim($row['الهدف المحدد'] ?? '');

        // Note: Project names are expected to be globally unique within the import or at least uniquely identifiable
        $project = PlanProject::where('name', $projectName)->first();
        if (! $project) {
            $this->errors[] = "المشروع $projectName غير موجود، تم تخطي الهدف $goalName.";

            return;
        }

        $project->goals()->updateOrCreate(
            ['specific_goal' => $goalName],
            [
                'weight' => $row['الوزن %'] ?? 0,
                'indicator_value' => $row['قيمة المؤشر'] ?? 0,
                'unit_of_measurement' => $row['وحدة القياس'] ?? '',
            ]
        );
    }

    protected function processActivity(array $row)
    {
        $projectName = $row['اسم المشروع'];
        $activityName = trim($row['اسم النشاط'] ?? '');

        $project = PlanProject::where('name', $projectName)->first();
        if (! $project) {
            $this->errors[] = "المشروع $projectName غير موجود، تم تخطي النشاط $activityName.";

            return;
        }

        $activity = PlanProjectActivity::where('plan_project_id', $project->id)
            ->where('name', $activityName)
            ->first();

        if (! $activity) {
            $activity = PlanProjectActivity::create([
                'plan_project_id' => $project->id,
                'name' => $activityName,
                'weight' => $row['وزن النشاط'] ?? 0,
            ]);
            $this->importedActivities++;
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $activity, 'created');
            }
        } elseif ($this->importMode === 'update') {
            $activity->update([
                'weight' => $row['وزن النشاط'] ?? $activity->weight,
            ]);
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $activity, 'updated');
            }
        }
    }

    protected function processAction(array $row)
    {
        $activityName = trim($row['اسم النشاط'] ?? '');
        $actionName = trim($row['اسم الإجراء'] ?? '');

        // Find activity by name (could be ambiguous if activity names duplicate across projects, but this is best effort)
        $activity = PlanProjectActivity::where('name', $activityName)->first();
        if (! $activity) {
            $this->errors[] = "النشاط $activityName غير موجود، تم تخطي الإجراء $actionName.";

            return;
        }

        $action = PlanProjectActivityAction::where('plan_project_activity_id', $activity->id)
            ->where('name', $actionName)
            ->first();

        $startDate = $this->parseDate($row['تاريخ البداية'] ?? null);
        $endDate = $this->parseDate($row['تاريخ النهاية'] ?? null);

        if (! $action) {
            $action = PlanProjectActivityAction::create([
                'plan_project_activity_id' => $activity->id,
                'name' => $actionName,
                'weight' => $row['وزن الإجراء'] ?? 0,
                'start_date_g' => $startDate,
                'end_date_g' => $endDate,
                'duration' => $row['المدة'] ?? null,
            ]);
            $this->importedActions++;
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $action, 'created');
            }
        } elseif ($this->importMode === 'update') {
            $action->update([
                'weight' => $row['وزن الإجراء'] ?? $action->weight,
                'start_date_g' => $startDate ?: $action->start_date_g,
                'end_date_g' => $endDate ?: $action->end_date_g,
                'duration' => $row['المدة'] ?? $action->duration,
            ]);
            if ($this->trackingService && $this->importLog) {
                $this->trackingService->recordSuccess($this->importLog, $action, 'updated');
            }
        }
    }

    private function parseDate($val)
    {
        if (empty($val)) {
            return null;
        }
        try {
            if (is_numeric($val)) {
                return Date::excelToDateTimeObject($val);
            }

            return Carbon::parse($val);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function reverseImportanceLabel($label)
    {
        $labels = [
            'عادي' => 'normal',
            'هام' => 'important',
            'هام جداً' => 'very_important',
        ];

        return $labels[$label] ?? 'normal';
    }

    private function reverseStatusLabel($label)
    {
        $labels = [
            'جديد' => 'new',
            'مستمر' => 'terminated',
        ];

        return $labels[$label] ?? 'new';
    }
}
