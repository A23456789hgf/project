<?php

namespace App\Services;

use App\Http\Controllers\Project\BeneficiaryEntitiesController;
use App\Http\Controllers\Project\ExecutiveActivitiesController;
use App\Http\Controllers\Project\ImplementingEntitiesController;
use App\Http\Controllers\Project\ParticipatingEntitiesController;
use App\Http\Controllers\Project\PreliminaryActivitiesController;
use App\Http\Controllers\Project\ProjectDetailsController;
use App\Http\Controllers\Project\ProjectFinancingsController;
use App\Http\Controllers\Project\ProjectSupervisingAuthoritiesController;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryFinancialSummary;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectComponentHandler
{
    protected $supervisingAuthoritiesController;

    protected $projectDetailsController;

    protected $implementingEntitiesController;

    protected $participatingEntitiesController;

    protected $beneficiaryEntitiesController;

    protected $preliminaryActivitiesController;

    protected $executiveActivitiesController;

    protected $projectFinancingsController;

    public function __construct()
    {
        $this->supervisingAuthoritiesController = new ProjectSupervisingAuthoritiesController;
        $this->projectDetailsController = new ProjectDetailsController;
        $this->implementingEntitiesController = new ImplementingEntitiesController;
        $this->participatingEntitiesController = new ParticipatingEntitiesController;
        $this->beneficiaryEntitiesController = new BeneficiaryEntitiesController;
        $this->preliminaryActivitiesController = new PreliminaryActivitiesController;
        $this->executiveActivitiesController = new ExecutiveActivitiesController;
        $this->projectFinancingsController = new ProjectFinancingsController;
    }

    /**
     * معالجة جميع مكونات المشروع
     */
    public function processAllComponents(Project $project, array $data, Request $request): void
    {
        Log::info('Processing all project components', ['project_id' => $project->id]);

        $this->handleProjectDetails($project, $data);
        $this->handleProjectLocations($project, $data);
        $this->handleProjectObjectives($project, $data);
        $this->createOrUpdateRisks($project, $data['risks'] ?? []);
        $this->handleSupervisingAuthorities($project, $request);
        $this->handleImplementingEntities($project, $request);
        $this->handleParticipatingEntities($project, $request);
        $this->handleBeneficiaryEntities($project, $request);
        $this->handleProjectCosts($project, $data);
        $this->handlePreliminaryActivities($project, $data);
        $this->handleExecutiveActivities($project, $data);
        $this->handleProjectFinancings($project, $data);

        // تحديث الـ Financial Summaries بعد معالجة جميع البيانات
        $this->updatePreliminaryFinancialSummaries($project);

        Log::info('All project components processed successfully', ['project_id' => $project->id]);
    }

    /**
     * معالجة تفاصيل المشروع
     */
    public function handleProjectDetails(Project $project, array $data): void
    {
        try {
            $this->projectDetailsController->handle($project, $data);

            Log::info('Project details saved successfully', [
                'project_id' => $project->id,
                'table' => 'project_details',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save project details', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'table' => 'project_details',
            ]);
            throw $e;
        }
    }

    /**
     * معالجة مواقع المشروع
     */
    public function handleProjectLocations(Project $project, array $data): void
    {
        try {
            if (isset($data['locations'])) {
                $project->locations()->delete();

                foreach ($data['locations'] as $location) {
                    $project->locations()->create($location);
                }

                Log::info('Project locations saved successfully', [
                    'project_id' => $project->id,
                    'locations_count' => count($data['locations']),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save project locations', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة أهداف المشروع
     */
    public function handleProjectObjectives(Project $project, array $data): void
    {
        try {
            // Step 1: Save main objectives
            if (isset($data['main_objectives'])) {
                $project->mainObjectives()->delete();
                foreach ($data['main_objectives'] as $objective) {
                    $project->mainObjectives()->create($objective);
                }
                Log::info('Main objectives saved', [
                    'project_id' => $project->id,
                    'count' => count($data['main_objectives']),
                ]);
            }

            // Step 2: Save special objectives and map their IDs
            $specialObjectiveIdMap = [];
            if (isset($data['special_objectives'])) {
                $project->specialObjectives()->delete();
                foreach ($data['special_objectives'] as $index => $objective) {
                    $createdObjective = $project->specialObjectives()->create($objective);
                    $specialObjectiveIdMap[$index] = $createdObjective->id;
                }
                Log::info('Special objectives saved', [
                    'project_id' => $project->id,
                    'count' => count($data['special_objectives']),
                    'id_map' => $specialObjectiveIdMap,
                ]);
            }

            // Step 3: Save objective results with corrected special_objective_id and map their IDs
            $objectiveResultIdMap = [];
            if (isset($data['objective_results'])) {
                $project->objectiveResults()->delete();
                foreach ($data['objective_results'] as $index => $result) {
                    $originalSpecialObjectiveId = $result['special_objective_id'] ?? null;

                    $resolvedSpecialObjectiveId = $originalSpecialObjectiveId;

                    if (isset($result['special_objective_id']) && isset($specialObjectiveIdMap[$result['special_objective_id']])) {
                        $resolvedSpecialObjectiveId = $specialObjectiveIdMap[$result['special_objective_id']];
                    }

                    if (empty($resolvedSpecialObjectiveId) && ! empty($specialObjectiveIdMap)) {
                        $resolvedSpecialObjectiveId = reset($specialObjectiveIdMap);
                    }

                    if (empty($resolvedSpecialObjectiveId)) {
                        Log::warning('Skipping objective result due to missing special_objective_id', [
                            'project_id' => $project->id,
                            'result_index' => $index,
                            'result_data' => $result,
                        ]);

                        continue;
                    }

                    $result['special_objective_id'] = $resolvedSpecialObjectiveId;
                    $result['target_value'] = $result['target_value'] ?? null;
                    $result['indicator_type'] = $result['indicator_type'] ?? null;
                    $result['indicator_unit'] = $result['indicator_unit'] ?? null;

                    $createdResult = $project->objectiveResults()->create($result);
                    $objectiveResultIdMap[$index] = $createdResult->id;
                }
                Log::info('Objective results saved', [
                    'project_id' => $project->id,
                    'count' => count($objectiveResultIdMap),
                    'id_map' => $objectiveResultIdMap,
                ]);
            }

            // Step 4: Save result outputs with corrected IDs
            if (isset($data['result_outputs'])) {
                $project->resultOutputs()->delete();
                foreach ($data['result_outputs'] as $index => $output) {
                    $originalSpecialObjectiveId = $output['special_objective_id'] ?? null;
                    $originalObjectiveResultId = $output['objective_result_id'] ?? null;

                    $resolvedSpecialObjectiveId = $originalSpecialObjectiveId;
                    $resolvedObjectiveResultId = $originalObjectiveResultId;

                    if (isset($output['special_objective_id']) && isset($specialObjectiveIdMap[$output['special_objective_id']])) {
                        $resolvedSpecialObjectiveId = $specialObjectiveIdMap[$output['special_objective_id']];
                    }

                    if (empty($resolvedSpecialObjectiveId) && ! empty($specialObjectiveIdMap)) {
                        $resolvedSpecialObjectiveId = reset($specialObjectiveIdMap);
                    }

                    if (isset($output['objective_result_id']) && isset($objectiveResultIdMap[$output['objective_result_id']])) {
                        $resolvedObjectiveResultId = $objectiveResultIdMap[$output['objective_result_id']];
                    }

                    if (empty($resolvedObjectiveResultId) && ! empty($objectiveResultIdMap)) {
                        $resolvedObjectiveResultId = reset($objectiveResultIdMap);
                    }

                    if (empty($resolvedSpecialObjectiveId)) {
                        Log::warning('Skipping result output due to missing special_objective_id', [
                            'project_id' => $project->id,
                            'output_index' => $index,
                            'output_data' => $output,
                        ]);

                        continue;
                    }

                    if (empty($resolvedObjectiveResultId)) {
                        Log::info('Creating placeholder objective result for output due to missing objective_result_id', [
                            'project_id' => $project->id,
                            'output_index' => $index,
                            'output_data' => $output,
                        ]);

                        $placeholderResult = $project->objectiveResults()->create([
                            'special_objective_id' => $resolvedSpecialObjectiveId,
                            'result_name' => $output['output'] ?? 'Placeholder Result',
                            'target_value' => $output['target_value'] ?? null,
                            'indicator_type' => $output['indicator_type'] ?? null,
                            'indicator_unit' => $output['indicator_unit'] ?? null,
                        ]);

                        $resolvedObjectiveResultId = $placeholderResult->id;
                        $objectiveResultIdMap[] = $placeholderResult->id;
                    }

                    $output['special_objective_id'] = $resolvedSpecialObjectiveId;
                    $output['objective_result_id'] = $resolvedObjectiveResultId;
                    $output['target_value'] = $output['target_value'] ?? null;
                    $output['indicator_type'] = $output['indicator_type'] ?? null;
                    $output['indicator_unit'] = $output['indicator_unit'] ?? null;

                    $project->resultOutputs()->create($output);
                }
                Log::info('Result outputs saved', [
                    'project_id' => $project->id,
                    'count' => count($data['result_outputs']),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to save project objectives', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * إنشاء أو تحديث مخاطر المشروع
     */
    public function createOrUpdateRisks(Project $project, array $risks): void
    {
        try {
            $project->risks()->delete();

            foreach ($risks as $risk) {
                $project->risks()->create($risk);
            }

            Log::info('Project risks saved successfully', [
                'project_id' => $project->id,
                'risks_count' => count($risks),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save project risks', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة تكاليف المشروع
     */
    public function handleProjectCosts(Project $project, array $data): void
    {
        try {
            if (isset($data['project_cost'])) {
                $project->cost()->updateOrCreate(
                    ['project_id' => $project->id],
                    $data['project_cost']
                );

                Log::info('Project costs saved successfully', [
                    'project_id' => $project->id,
                    'total_cost' => $data['project_cost']['total_cost'] ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save project costs', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة تمويل المشروع
     */
    public function handleProjectFinancings(Project $project, array $data): void
    {
        try {
            $isDraft = ($data['status'] ?? 'draft') === 'draft';
            $financings = $data['financings'] ?? [];

            if ($isDraft && empty($financings)) {
                Log::info('Skipping financings for draft project', [
                    'project_id' => $project->id,
                    'status' => 'draft',
                ]);

                return;
            }

            if (! $isDraft && empty($financings)) {
                Log::error('Financings are required for final project', [
                    'project_id' => $project->id,
                    'status' => 'final',
                ]);
                throw new \Exception('At least one financing source is required to save the project as final.');
            }

            $result = $this->projectFinancingsController->createOrUpdateProjectFinancings($project, $financings);

            if ($result['status'] === 'error') {
                Log::error('Failed to save project financings', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Project financings saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
                'created_count' => $result['total_created'] ?? 0,
                'total_amount' => $result['total_amount'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in handleProjectFinancings', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة الهيئات المشرفة
     */
    public function handleSupervisingAuthorities(Project $project, Request $request): void
    {
        try {
            $isDraft = $request->input('status') === 'draft';
            $supervisingAuthorities = $request->input('supervising_authorities', []);

            if ($isDraft && empty($supervisingAuthorities)) {
                Log::info('Skipping supervising authorities for draft project', [
                    'project_id' => $project->id,
                    'status' => 'draft',
                ]);

                return;
            }

            if (! $isDraft && empty($supervisingAuthorities)) {
                Log::error('Supervising authorities are required for final project', [
                    'project_id' => $project->id,
                    'status' => 'final',
                ]);
                throw new \Exception('At least one supervising authority is required to save the project as final.');
            }

            $result = $this->supervisingAuthoritiesController->createOrUpdate($project, $supervisingAuthorities);

            if ($result['status'] === 'error') {
                Log::error('Failed to save supervising authorities', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Supervising authorities saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
                'created_count' => $result['created_count'] ?? 0,
                'authorities' => $result['authorities'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in handleSupervisingAuthorities', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة الجهات المنفذة
     */
    public function handleImplementingEntities(Project $project, Request $request): void
    {
        try {
            $isDraft = $request->input('status') === 'draft';
            $implementingEntities = $request->input('implementing_entities', []);

            if ($isDraft && empty($implementingEntities)) {
                Log::info('Skipping implementing entities for draft project', [
                    'project_id' => $project->id,
                    'status' => 'draft',
                ]);

                return;
            }

            if (! $isDraft && empty($implementingEntities)) {
                Log::error('Implementing entities are required for final project', [
                    'project_id' => $project->id,
                    'status' => 'final',
                ]);
                throw new \Exception('At least one implementing entity is required to save the project as final.');
            }

            $result = $this->implementingEntitiesController->handle($project, ['implementing_entities' => $implementingEntities]);

            if ($result['status'] === 'error') {
                Log::error('Failed to save implementing entities', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Implementing entities saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
                'entities_count' => $result['entities_count'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in handleImplementingEntities', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة الجهات المشاركة
     */
    public function handleParticipatingEntities(Project $project, Request $request): void
    {
        try {
            $isDraft = $request->input('status') === 'draft';
            $participatingEntities = $request->input('participating_entities', []);

            if ($isDraft && empty($participatingEntities)) {
                Log::info('Skipping participating entities for draft project', [
                    'project_id' => $project->id,
                    'status' => 'draft',
                ]);

                return;
            }

            if (! $isDraft && empty($participatingEntities)) {
                Log::error('Participating entities are required for final project', [
                    'project_id' => $project->id,
                    'status' => 'final',
                ]);
                throw new \Exception('At least one participating entity is required to save the project as final.');
            }

            $result = $this->participatingEntitiesController->handle($project, ['participating_entities' => $participatingEntities]);

            if ($result['status'] === 'error') {
                Log::error('Failed to save participating entities', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Participating entities saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
                'entities_count' => $result['entities_count'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Exception in handleParticipatingEntities', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة الأنشطة الأولية
     */
    public function handlePreliminaryActivities(Project $project, array $data): void
    {
        try {
            $result = $this->preliminaryActivitiesController->handle($project, $data);

            if ($result['status'] === 'error') {
                Log::error('Failed to save preliminary activities', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Preliminary activities saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in handlePreliminaryActivities', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * معالجة الأنشطة التنفيذية
     */
    public function handleExecutiveActivities(Project $project, array $data): void
    {
        try {
            $result = $this->executiveActivitiesController->handle($project, $data);

            if ($result['status'] === 'error') {
                Log::error('Failed to save executive activities', [
                    'project_id' => $project->id,
                    'error' => $result['message'],
                ]);
                throw new \Exception($result['message']);
            }

            Log::info('Executive activities saved successfully', [
                'project_id' => $project->id,
                'message' => $result['message'],
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in handleExecutiveActivities', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * تحديث الـ Financial Summaries للمشروع
     */
    public function updatePreliminaryFinancialSummaries(Project $project): void
    {
        try {
            Log::info('🟡 Updating Preliminary Financial Summaries for Project', [
                'project_id' => $project->id,
                'project_name' => $project->project_name,
            ]);

            $aggregatedCosts = PreliminaryCost::where('project_id', $project->id)
                ->select('financial_item_id', DB::raw('SUM(total) as aggregated_total'))
                ->groupBy('financial_item_id')
                ->get();

            $summariesCount = 0;

            foreach ($aggregatedCosts as $costData) {
                $latestCost = PreliminaryCost::where('project_id', $project->id)
                    ->where('financial_item_id', $costData->financial_item_id)
                    ->with(['activity', 'procedure'])
                    ->latest()
                    ->first();

                if ($latestCost) {
                    $summaryData = [
                        'project_id' => $project->id,
                        'financial_item_id' => $costData->financial_item_id,
                        'aggregated_total' => $costData->aggregated_total,
                    ];

                    if ($latestCost->activity_id) {
                        $summaryData['activity_id'] = $latestCost->activity_id;
                    }
                    if ($latestCost->procedure_id) {
                        $summaryData['procedure_id'] = $latestCost->procedure_id;
                    }
                    if ($latestCost->id) {
                        $summaryData['cost_id'] = $latestCost->id;
                    }

                    $summary = PreliminaryFinancialSummary::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'financial_item_id' => $costData->financial_item_id,
                        ],
                        $summaryData
                    );

                    $summariesCount++;

                    Log::debug('🟢 Preliminary Financial Summary Updated', [
                        'project_id' => $project->id,
                        'financial_item_id' => $costData->financial_item_id,
                        'aggregated_total' => $costData->aggregated_total,
                        'summary_id' => $summary->id,
                    ]);
                }
            }

            Log::info('🟢 Preliminary Financial Summaries Updated Successfully', [
                'project_id' => $project->id,
                'summaries_count' => $summariesCount,
            ]);

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Update Preliminary Financial Summaries', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * التحقق من اكتمال جميع المكونات المطلوبة للحالة النهائية
     */
    public function validateRequiredComponents(Project $project): array
    {
        $errors = [];

        // التحقق من البيانات الأساسية
        if (empty($project->project_name)) {
            $errors[] = 'اسم المشروع مطلوب';
        }

        if (empty($project->program_id)) {
            $errors[] = 'البرنامج مطلوب';
        }

        if (empty($project->domain_id)) {
            $errors[] = 'المجال الرئيسي مطلوب';
        }

        // التحقق من المكونات
        if ($project->supervisingAuthorities->isEmpty()) {
            $errors[] = 'يجب وجود هيئة مشرفة واحدة على الأقل';
        }

        if ($project->implementingEntities->isEmpty()) {
            $errors[] = 'يجب وجود جهة منفذة واحدة على الأقل';
        }

        if ($project->financings->isEmpty()) {
            $errors[] = 'يجب وجود مصدر تمويل واحد على الأقل';
        }

        // التحقق من التكاليف والتمويل
        $totalCost = $project->cost->total_cost ?? 0;
        $totalFinancing = $project->financings->sum('financing_amount');

        if ($totalCost > 0 && abs($totalCost - $totalFinancing) > 0.01) {
            $errors[] = "إجمالي التمويل ({$totalFinancing}) يجب أن يتطابق مع التكلفة الإجمالية ({$totalCost})";
        }

        return $errors;
    }

    /**
     * الحصول على إحصائيات المكونات
     */
    public function getComponentStatistics(Project $project): array
    {
        return [
            'main_objectives' => $project->mainObjectives->count(),
            'special_objectives' => $project->specialObjectives->count(),
            'objective_results' => $project->objectiveResults->count(),
            'result_outputs' => $project->resultOutputs->count(),
            'risks' => $project->risks->count(),
            'locations' => $project->locations->count(),
            'financings' => $project->financings->count(),
            'supervising_authorities' => $project->supervisingAuthorities->count(),
            'implementing_entities' => $project->implementingEntities->count(),
            'participating_entities' => $project->participatingEntities->count(),
            'preliminary_activities' => $project->preliminaryActivities->count(),
            'executive_activities' => $project->executiveActivities->count(),
        ];
    }

    /**
     * حذف جميع مكونات المشروع
     */
    public function deleteAllComponents(Project $project): void
    {
        try {
            Log::info('Deleting all project components', ['project_id' => $project->id]);

            $project->detail()->delete();
            $project->locations()->delete();
            $project->resultOutputs()->delete();
            $project->objectiveResults()->delete();
            $project->mainObjectives()->delete();
            $project->specialObjectives()->delete();
            $project->risks()->delete();
            $project->cost()->delete();
            $project->financings()->delete();
            $project->supervisingAuthorities()->delete();
            $project->implementingEntities()->delete();
            $project->participatingEntities()->delete();
            $project->beneficiaryEntities()->delete();
            $project->preliminaryActivities()->delete();
            $project->preliminaryProcedures()->delete();
            $project->preliminaryCosts()->delete();
            $project->preliminaryFinancialSummaries()->delete();

            // حذف بيانات الأنشطة التنفيذية
            $project->executiveFinancialSummaries()->delete();

            if (method_exists($project, 'executiveActionCosts')) {
                $project->executiveActionCosts()->delete();
            }

            if (method_exists($project, 'executiveActionAssigned')) {
                $project->executiveActionAssigned()->delete();
            }

            if (method_exists($project, 'executiveActivityActions')) {
                $project->executiveActivityActions()->delete();
            }

            $project->executiveActivities()->delete();

            Log::info('All project components deleted successfully', ['project_id' => $project->id]);

        } catch (\Exception $e) {
            Log::error('Failed to delete project components', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * استنساخ مكونات المشروع
     */
    public function cloneProjectComponents(Project $sourceProject, Project $targetProject): void
    {
        try {
            Log::info('Cloning project components', [
                'source_project_id' => $sourceProject->id,
                'target_project_id' => $targetProject->id,
            ]);

            // استنساخ المواقع
            foreach ($sourceProject->locations as $location) {
                $targetProject->locations()->create($location->toArray());
            }

            // استنساخ الأهداف الرئيسية
            foreach ($sourceProject->mainObjectives as $objective) {
                $targetProject->mainObjectives()->create($objective->toArray());
            }

            // استنساخ الأهداف الخاصة والنتائج والمخرجات (يتطلب معالجة خاصة للعلاقات)
            $this->cloneSpecialObjectivesAndRelated($sourceProject, $targetProject);

            // استنساخ المخاطر
            foreach ($sourceProject->risks as $risk) {
                $targetProject->risks()->create($risk->toArray());
            }

            // استنساخ التكاليف
            if ($sourceProject->cost) {
                $targetProject->cost()->create($sourceProject->cost->toArray());
            }

            // استنساخ التمويل
            foreach ($sourceProject->financings as $financing) {
                $targetProject->financings()->create($financing->toArray());
            }

            // استنساخ الهيئات والجهات
            $this->cloneAuthoritiesAndEntities($sourceProject, $targetProject);

            Log::info('Project components cloned successfully', [
                'source_project_id' => $sourceProject->id,
                'target_project_id' => $targetProject->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clone project components', [
                'source_project_id' => $sourceProject->id,
                'target_project_id' => $targetProject->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * استنساخ الأهداف الخاصة والمرتبطة بها
     */
    private function cloneSpecialObjectivesAndRelated(Project $sourceProject, Project $targetProject): void
    {
        $specialObjectiveMap = [];

        // استنساخ الأهداف الخاصة
        foreach ($sourceProject->specialObjectives as $objective) {
            $newObjective = $targetProject->specialObjectives()->create($objective->toArray());
            $specialObjectiveMap[$objective->id] = $newObjective->id;
        }

        // استنساخ نتائج الأهداف
        foreach ($sourceProject->objectiveResults as $result) {
            $newResult = $targetProject->objectiveResults()->create([
                'special_objective_id' => $specialObjectiveMap[$result->special_objective_id] ?? null,
                'result_name' => $result->result_name,
                'target_value' => $result->target_value,
                'indicator_type' => $result->indicator_type,
                'indicator_unit' => $result->indicator_unit,
            ]);

            $resultOutputs = $sourceProject->resultOutputs->where('objective_result_id', $result->id);
            foreach ($resultOutputs as $output) {
                $targetProject->resultOutputs()->create([
                    'special_objective_id' => $specialObjectiveMap[$output->special_objective_id] ?? null,
                    'objective_result_id' => $newResult->id,
                    'output' => $output->output,
                    'target_value' => $output->target_value,
                    'indicator_type' => $output->indicator_type,
                    'indicator_unit' => $output->indicator_unit,
                ]);
            }
        }
    }

    /**
     * استنساخ الهيئات والجهات
     */
    private function cloneAuthoritiesAndEntities(Project $sourceProject, Project $targetProject): void
    {
        // استنساخ الهيئات المشرفة
        foreach ($sourceProject->supervisingAuthorities as $authority) {
            $targetProject->supervisingAuthorities()->create($authority->toArray());
        }

        // استنساخ الجهات المنفذة
        foreach ($sourceProject->implementingEntities as $entity) {
            $targetProject->implementingEntities()->create($entity->toArray());
        }

        // استنساخ الجهات المشاركة
        foreach ($sourceProject->participatingEntities as $entity) {
            $targetProject->participatingEntities()->create($entity->toArray());
        }

        // استنساخ الجهات المستفيدة
        foreach ($sourceProject->beneficiaryEntities as $entity) {
            $targetProject->beneficiaryEntities()->create($entity->toArray());
        }
    }
}
