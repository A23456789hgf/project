<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\FinancialItem;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryFinancialSummary;
use App\Models\PreliminaryProcedure;
use App\Models\Project;
use App\Services\FrappeAPIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PreliminaryActivitiesController extends Controller
{
    /**
     * Handle preliminary activities data
     */
    public function handle(Project $project, array $data, bool $isDraft = false)
    {
        Log::info('🟡 STARTING Preliminary Activities Processing', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'data_count' => isset($data['preliminary_activities']) ? count($data['preliminary_activities']) : 0,
            'is_draft' => $isDraft,
            'timestamp' => now()->toISOString(),
        ]);

        DB::beginTransaction();

        try {
            Log::info('🟢 Database Transaction Started Successfully', [
                'project_id' => $project->id,
                'transaction_level' => DB::transactionLevel(),
            ]);

            $result = $this->handlePreliminaryActivities($project, $data, $isDraft);

            DB::commit();

            Log::info('🟢 PRELIMINARY ACTIVITIES PROCESSING COMPLETED SUCCESSFULLY', [
                'project_id' => $project->id,
                'activities_processed' => $result['activities_count'] ?? 0,
                'procedures_processed' => $result['procedures_count'] ?? 0,
                'costs_processed' => $result['costs_count'] ?? 0,
                'summaries_created' => $result['summaries_count'] ?? 0,
                'total_activities' => $project->preliminaryActivities()->count(),
                'timestamp' => now()->toISOString(),
            ]);

            return [
                'status' => 'success',
                'message' => 'Preliminary activities data saved successfully',
                'summary' => $result,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('🔴 PRELIMINARY ACTIVITIES PROCESSING FAILED', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'transaction_rolled_back' => true,
                'timestamp' => now()->toISOString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to save preliminary activities: '.$e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ];
        }
    }

    /**
     * Handle preliminary activities and their procedures and costs
     * Now supports both CREATE (new) and UPDATE (existing) operations
     */
    private function handlePreliminaryActivities(Project $project, array $data, bool $isDraft = false)
    {
        if (! isset($data['preliminary_activities']) || empty($data['preliminary_activities'])) {
            Log::warning('🟡 No Preliminary Activities Found in Input Data', [
                'project_id' => $project->id,
                'available_keys' => array_keys($data),
            ]);

            // Delete all existing activities if submitted as empty (prevent orphaned data)
            $existingCount = $project->preliminaryActivities()->count();
            if ($existingCount > 0) {
                $project->preliminaryActivities()->delete();
                Log::info('🟡 Deleted All Preliminary Activities', [
                    'project_id' => $project->id,
                    'deleted_count' => $existingCount,
                ]);
            }

            return ['activities_count' => 0, 'procedures_count' => 0, 'costs_count' => 0, 'summaries_count' => 0];
        }

        $activityIdMap = [];
        $totalActivities = 0;
        $totalProcedures = 0;
        $totalCosts = 0;
        $totalActivityWeight = 0;
        $processedActivityIds = [];

        // Validate total weight of preliminary activities equals 100 (skip for drafts)
        if (! $isDraft) {
            foreach ($data['preliminary_activities'] as $activityData) {
                $totalActivityWeight += (float) ($activityData['weight'] ?? 0);
            }

            if (abs($totalActivityWeight - 100.0) > 0.01) {
                throw new \InvalidArgumentException("The sum of preliminary activity weights must equal 100%. Current total: {$totalActivityWeight}%");
            }
        }

        Log::info('🟡 Processing Preliminary Activities', [
            'project_id' => $project->id,
            'total_activities_to_process' => count($data['preliminary_activities']),
            'total_activity_weight' => $totalActivityWeight,
        ]);

        foreach ($data['preliminary_activities'] as $activityIndex => $activityData) {
            try {
                $activityId = $activityData['id'] ?? null;
                $isUpdate = ! empty($activityId);

                if ($isUpdate) {
                    Log::debug('🟡 Updating Preliminary Activity', [
                        'project_id' => $project->id,
                        'activity_id' => $activityId,
                        'activity_name' => $activityData['name'],
                        'activity_weight' => $activityData['weight'] ?? 0,
                    ]);

                    $activity = PreliminaryActivity::findOrFail($activityId);
                    $activity->update([
                        'name' => $activityData['name'],
                        'weight' => $activityData['weight'] ?? 0,
                    ]);

                    Log::info('🟢 Preliminary Activity Updated Successfully', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'activity_name' => $activity->name,
                    ]);
                } else {
                    Log::debug('🟡 Creating Preliminary Activity', [
                        'project_id' => $project->id,
                        'activity_index' => $activityIndex,
                        'activity_name' => $activityData['name'],
                        'activity_weight' => $activityData['weight'] ?? 0,
                    ]);

                    $activity = $project->preliminaryActivities()->create([
                        'name' => $activityData['name'],
                        'weight' => $activityData['weight'] ?? 0,
                    ]);

                    Log::info('🟢 Preliminary Activity Created Successfully', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'activity_name' => $activity->name,
                    ]);
                }

                $activityIdMap[$activityIndex] = $activity->id;
                $processedActivityIds[] = $activity->id;
                $totalActivities++;

                // Validate total weight of procedures within this activity equals 100
                if (isset($activityData['procedures'])) {
                    // Validate procedure weights only for final submissions, not drafts
                    if (! $isDraft) {
                        $totalProcedureWeight = 0;
                        foreach ($activityData['procedures'] as $procedureData) {
                            $totalProcedureWeight += (float) ($procedureData['weight'] ?? 0);
                        }

                        if (abs($totalProcedureWeight - 100.0) > 0.01) {
                            throw new \InvalidArgumentException("The sum of procedure weights for activity '{$activityData['name']}' must equal 100%. Current total: {$totalProcedureWeight}%");
                        }
                    }

                    $procedureResult = $this->handleProcedures($project, $activity, $activityData['procedures']);
                    $totalProcedures += $procedureResult['procedures_count'] ?? 0;
                    $totalCosts += $procedureResult['costs_count'] ?? 0;

                    Log::info('🟢 Activity Procedures Completed', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'procedures_count' => $procedureResult['procedures_count'] ?? 0,
                        'costs_count' => $procedureResult['costs_count'] ?? 0,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('🔴 Failed to Process Preliminary Activity', [
                    'project_id' => $project->id,
                    'activity_index' => $activityIndex,
                    'activity_data' => $activityData,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);
                throw $e;
            }
        }

        // Delete activities that are no longer in the form (for existing projects)
        $existingActivities = $project->preliminaryActivities()->pluck('id')->toArray();
        $activitiesToDelete = array_diff($existingActivities, $processedActivityIds);
        if (! empty($activitiesToDelete)) {
            PreliminaryActivity::whereIn('id', $activitiesToDelete)->delete();
            Log::info('🟡 Deleted Removed Activities', [
                'project_id' => $project->id,
                'deleted_count' => count($activitiesToDelete),
                'deleted_ids' => $activitiesToDelete,
            ]);
        }

        // إنشاء أو تحديث الـ financial summaries بعد معالجة جميع التكاليف
        $summariesCount = $this->updateFinancialSummaries($project);

        Log::info('🟢 ALL PRELIMINARY ACTIVITIES PROCESSED SUCCESSFULLY', [
            'project_id' => $project->id,
            'total_activities_processed' => $totalActivities,
            'total_procedures_processed' => $totalProcedures,
            'total_costs_processed' => $totalCosts,
            'financial_summaries_created' => $summariesCount,
        ]);

        return [
            'activities_count' => $totalActivities,
            'procedures_count' => $totalProcedures,
            'costs_count' => $totalCosts,
            'summaries_count' => $summariesCount,
        ];
    }

    /**
     * Handle procedures for an activity
     * Now supports both CREATE (new) and UPDATE (existing) operations
     */
    private function handleProcedures(Project $project, PreliminaryActivity $activity, array $procedures)
    {
        $totalProcedures = 0;
        $totalCosts = 0;
        $processedProcedureIds = [];

        Log::debug('🟡 Processing Procedures for Activity', [
            'project_id' => $project->id,
            'activity_id' => $activity->id,
            'activity_name' => $activity->name,
            'total_procedures_to_process' => count($procedures),
        ]);

        foreach ($procedures as $procedureIndex => $procedureData) {
            try {
                $procedureId = $procedureData['id'] ?? null;
                $isUpdate = ! empty($procedureId);

                if ($isUpdate) {
                    Log::debug('🟡 Updating Procedure', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'procedure_id' => $procedureId,
                        'procedure_name' => $procedureData['procedure_name'],
                    ]);

                    $procedure = PreliminaryProcedure::findOrFail($procedureId);
                    $procedure->update([
                        'procedure_name' => $procedureData['procedure_name'],
                        'weight' => $procedureData['weight'] ?? 0,
                        'start_date' => $procedureData['start_date'] ?? null,
                        'end_date' => $procedureData['end_date'] ?? null,
                        'start_date_hijri' => $procedureData['start_date_hijri'] ?? null,
                        'end_date_hijri' => $procedureData['end_date_hijri'] ?? null,
                        'verification_means' => $procedureData['verification_means'] ?? null,
                    ]);

                    Log::info('🟢 Procedure Updated Successfully', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'procedure_id' => $procedure->id,
                        'procedure_name' => $procedure->procedure_name,
                    ]);
                } else {
                    Log::debug('🟡 Creating Procedure', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'procedure_index' => $procedureIndex,
                        'procedure_name' => $procedureData['procedure_name'],
                    ]);

                    $procedure = $activity->procedures()->create([
                        'project_id' => $project->id,
                        'procedure_name' => $procedureData['procedure_name'],
                        'weight' => $procedureData['weight'] ?? 0,
                        'start_date' => $procedureData['start_date'] ?? null,
                        'end_date' => $procedureData['end_date'] ?? null,
                        'start_date_hijri' => $procedureData['start_date_hijri'] ?? null,
                        'end_date_hijri' => $procedureData['end_date_hijri'] ?? null,
                        'verification_means' => $procedureData['verification_means'] ?? null,
                    ]);

                    Log::info('🟢 Procedure Created Successfully', [
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'procedure_id' => $procedure->id,
                        'procedure_name' => $procedure->procedure_name,
                    ]);
                }

                $processedProcedureIds[] = $procedure->id;
                $totalProcedures++;

                // Handle costs for this procedure
                if (isset($procedureData['costs'])) {
                    $costsCount = $this->handleCosts($project, $activity, $procedure, $procedureData['costs']);
                    $totalCosts += $costsCount;

                    Log::info('🟢 Procedure Costs Completed', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'costs_count' => $costsCount,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('🔴 Failed to Process Procedure', [
                    'project_id' => $project->id,
                    'activity_id' => $activity->id,
                    'procedure_index' => $procedureIndex,
                    'procedure_data' => $procedureData,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);
                throw $e;
            }
        }

        // Delete procedures that are no longer in the form (for existing activities)
        $existingProcedures = $activity->procedures()->pluck('id')->toArray();
        $proceduresToDelete = array_diff($existingProcedures, $processedProcedureIds);
        if (! empty($proceduresToDelete)) {
            PreliminaryProcedure::whereIn('id', $proceduresToDelete)->delete();
            Log::info('🟡 Deleted Removed Procedures', [
                'project_id' => $project->id,
                'activity_id' => $activity->id,
                'deleted_count' => count($proceduresToDelete),
                'deleted_ids' => $proceduresToDelete,
            ]);
        }

        return [
            'procedures_count' => $totalProcedures,
            'costs_count' => $totalCosts,
        ];
    }

    /**
     * Handle costs for a procedure
     * Now supports both CREATE (new) and UPDATE (existing) operations
     */
    private function handleCosts(Project $project, PreliminaryActivity $activity, PreliminaryProcedure $procedure, array $costs)
    {
        $totalCosts = 0;
        $processedCostIds = [];

        Log::debug('🟡 Processing Costs for Procedure', [
            'project_id' => $project->id,
            'activity_id' => $activity->id,
            'procedure_id' => $procedure->id,
            'procedure_name' => $procedure->procedure_name,
            'total_costs_to_process' => count($costs),
        ]);

        foreach ($costs as $costIndex => $costData) {
            try {
                // استخدم financial_item_id مباشرة بدلاً من البحث بالاسم
                $financialItemId = $costData['financial_item_id'] ?? null;

                if (! $financialItemId) {
                    Log::error('🔴 Missing financial_item_id in cost data', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'cost_index' => $costIndex,
                        'cost_data' => $costData,
                    ]);
                    throw new \Exception('Financial item ID is required for each cost entry.');
                }

                // تحقق من وجود البند المالي
                $financialItem = FinancialItem::find($financialItemId);

                if (! $financialItem) {
                    Log::error('🔴 Financial Item Not Found', [
                        'project_id' => $project->id,
                        'financial_item_id' => $financialItemId,
                        'available_financial_items' => FinancialItem::pluck('id', 'name')->toArray(),
                    ]);
                    throw new \Exception("Financial item with ID {$financialItemId} not found.");
                }

                // حساب المجموع
                $amount = floatval($costData['amount'] ?? 0);
                $quantity = intval($costData['quantity'] ?? 1);
                $total = $amount * $quantity;

                $costId = $costData['id'] ?? null;
                $isUpdate = ! empty($costId);

                if ($isUpdate) {
                    Log::debug('🟡 Updating Cost Entry', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'cost_id' => $costId,
                        'financial_item_id' => $financialItemId,
                        'financial_item_name' => $financialItem->name,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'total' => $total,
                    ]);

                    $cost = PreliminaryCost::findOrFail($costId);
                    $cost->update([
                        'financial_item_id' => $financialItemId,
                        'unit_id' => $costData['unit_id'] ?? null,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'total' => $total,
                    ]);

                    Log::info('🟢 Cost Entry Updated Successfully', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'cost_id' => $cost->id,
                        'financial_item_id' => $cost->financial_item_id,
                        'financial_item_name' => $financialItem->name,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'total_amount' => $total,
                    ]);
                } else {
                    Log::debug('🟡 Creating Cost Entry', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'cost_index' => $costIndex,
                        'financial_item_id' => $financialItemId,
                        'financial_item_name' => $financialItem->name,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'unit_id' => $costData['unit_id'] ?? null,
                    ]);

                    $cost = $procedure->costs()->create([
                        'project_id' => $project->id,
                        'activity_id' => $activity->id,
                        'financial_item_id' => $financialItemId,
                        'unit_id' => $costData['unit_id'] ?? null,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'total' => $total,
                    ]);

                    Log::info('🟢 Cost Entry Created Successfully', [
                        'project_id' => $project->id,
                        'procedure_id' => $procedure->id,
                        'cost_id' => $cost->id,
                        'financial_item_id' => $cost->financial_item_id,
                        'financial_item_name' => $financialItem->name,
                        'amount' => $amount,
                        'quantity' => $quantity,
                        'total_amount' => $total,
                    ]);
                }

                $processedCostIds[] = $cost->id;
                $totalCosts++;

            } catch (\Exception $e) {
                Log::error('🔴 Failed to Process Cost Entry', [
                    'project_id' => $project->id,
                    'procedure_id' => $procedure->id,
                    'cost_index' => $costIndex,
                    'cost_data' => $costData,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                ]);
                throw $e;
            }
        }

        // Delete costs that are no longer in the form (for existing procedures)
        $existingCosts = $procedure->costs()->pluck('id')->toArray();
        $costsToDelete = array_diff($existingCosts, $processedCostIds);
        if (! empty($costsToDelete)) {
            PreliminaryCost::whereIn('id', $costsToDelete)->delete();
            Log::info('🟡 Deleted Removed Costs', [
                'project_id' => $project->id,
                'procedure_id' => $procedure->id,
                'deleted_count' => count($costsToDelete),
                'deleted_ids' => $costsToDelete,
            ]);
        }

        Log::debug('🟢 All Costs Processed for Procedure', [
            'project_id' => $project->id,
            'procedure_id' => $procedure->id,
            'total_costs_processed' => $totalCosts,
        ]);

        return $totalCosts;
    }

    /**
     * إنشاء أو تحديث الـ Financial Summaries
     */
    private function updateFinancialSummaries(Project $project)
    {
        Log::info('🟡 Updating Financial Summaries', [
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        try {
            // احصل على جميع التكاليف المجمعة حسب البند المالي
            $aggregatedCosts = PreliminaryCost::where('project_id', $project->id)
                ->select('financial_item_id', DB::raw('SUM(total) as aggregated_total'))
                ->groupBy('financial_item_id')
                ->get();

            $summariesCount = 0;

            foreach ($aggregatedCosts as $costData) {
                // احصل على آخر تكلفة لهذا البند المالي للحصول على العلاقات
                $latestCost = PreliminaryCost::where('project_id', $project->id)
                    ->where('financial_item_id', $costData->financial_item_id)
                    ->latest()
                    ->first();

                if ($latestCost) {
                    $summary = PreliminaryFinancialSummary::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'financial_item_id' => $costData->financial_item_id,
                        ],
                        [
                            'activity_id' => $latestCost->activity_id,
                            'procedure_id' => $latestCost->procedure_id,
                            'cost_id' => $latestCost->id,
                            'aggregated_total' => $costData->aggregated_total,
                        ]
                    );

                    $summariesCount++;

                    Log::debug('🟢 Financial Summary Updated', [
                        'project_id' => $project->id,
                        'financial_item_id' => $costData->financial_item_id,
                        'aggregated_total' => $costData->aggregated_total,
                        'summary_id' => $summary->id,
                    ]);
                }
            }

            Log::info('🟢 Financial Summaries Updated Successfully', [
                'project_id' => $project->id,
                'summaries_count' => $summariesCount,
            ]);

            return $summariesCount;

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Update Financial Summaries', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Get preliminary activities data for a project
     */
    public function getByProject(Project $project)
    {
        Log::info('🟡 Fetching Preliminary Activities for Project', [
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        try {
            $activities = $project->preliminaryActivities()
                ->with([
                    'procedures.costs.financialItem',
                    'procedures.financialSummaries',
                    'financialSummaries',
                ])
                ->get();

            // احصل على الـ financial summaries للمشروع
            $financialSummaries = PreliminaryFinancialSummary::where('project_id', $project->id)
                ->with(['financialItem', 'activity', 'procedure', 'cost'])
                ->get();

            Log::info('🟢 Successfully Fetched Preliminary Activities', [
                'project_id' => $project->id,
                'activities_count' => $activities->count(),
                'financial_summaries_count' => $financialSummaries->count(),
                'total_procedures' => $activities->sum(function ($activity) {
                    return $activity->procedures->count();
                }),
                'total_costs' => $activities->sum(function ($activity) {
                    return $activity->procedures->sum(function ($procedure) {
                        return $procedure->costs->count();
                    });
                }),
            ]);

            return [
                'activities' => $activities,
                'financial_summaries' => $financialSummaries,
            ];

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Fetch Preliminary Activities', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate financial summary for preliminary activities
     */
    public function calculateFinancialSummary(Project $project)
    {
        Log::info('🟡 Calculating Financial Summary for Project', [
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        try {
            $totalCost = $project->preliminaryCosts()->sum('total');

            $activitiesSummary = $project->preliminaryActivities()
                ->with(['procedures.costs'])
                ->get()
                ->map(function ($activity) {
                    $activityTotal = $activity->procedures->sum(function ($procedure) {
                        return $procedure->costs->sum('total');
                    });

                    return [
                        'activity_name' => $activity->name,
                        'total_cost' => $activityTotal,
                    ];
                });

            // احصل على الـ financial summaries
            $financialSummaries = PreliminaryFinancialSummary::where('project_id', $project->id)
                ->with('financialItem')
                ->get()
                ->map(function ($summary) {
                    return [
                        'financial_item_name' => $summary->financialItem->name,
                        'aggregated_total' => $summary->aggregated_total,
                    ];
                });

            Log::info('🟢 Financial Summary Calculated Successfully', [
                'project_id' => $project->id,
                'total_preliminary_cost' => $totalCost,
                'activities_count' => $activitiesSummary->count(),
                'financial_summaries_count' => $financialSummaries->count(),
                'summary_breakdown' => $activitiesSummary->toArray(),
            ]);

            return [
                'total_preliminary_cost' => $totalCost,
                'activities_summary' => $activitiesSummary,
                'financial_summaries' => $financialSummaries,
            ];

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Calculate Financial Summary', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete all preliminary activities for a project
     */
    public function deleteAll(Project $project)
    {
        Log::info('🟡 Deleting All Preliminary Activities for Project', [
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        try {
            $activitiesCount = $project->preliminaryActivities()->count();
            $proceduresCount = $project->preliminaryProcedures()->count();
            $costsCount = $project->preliminaryCosts()->count();
            $summariesCount = $project->preliminaryFinancialSummaries()->count();

            // حذف جميع السجلات المرتبطة
            $project->preliminaryFinancialSummaries()->delete();
            $project->preliminaryActivities()->delete();

            Log::info('🟢 All Preliminary Activities Deleted Successfully', [
                'project_id' => $project->id,
                'activities_deleted' => $activitiesCount,
                'procedures_deleted' => $proceduresCount,
                'costs_deleted' => $costsCount,
                'summaries_deleted' => $summariesCount,
            ]);

            return [
                'status' => 'success',
                'message' => 'All preliminary activities deleted successfully',
                'deleted_counts' => [
                    'activities' => $activitiesCount,
                    'procedures' => $proceduresCount,
                    'costs' => $costsCount,
                    'summaries' => $summariesCount,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Delete Preliminary Activities', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate and return activity count and weight metrics
     */
    public function calculateActivityMetrics(Project $project): array
    {
        Log::info('🟡 Calculating Preliminary Activity Metrics', [
            'project_id' => $project->id,
        ]);

        try {
            $activities = $project->preliminaryActivities()
                ->with(['procedures'])
                ->get();

            $activitiesCount = $activities->count();
            $totalActivityWeight = (float) $activities->sum('weight');

            $activitiesBreakdown = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'weight' => (float) $activity->weight,
                    'procedures_count' => $activity->procedures->count(),
                ];
            })->values();

            $metrics = [
                'activities_count' => $activitiesCount,
                'total_weight' => round($totalActivityWeight, 2),
                'activities_breakdown' => $activitiesBreakdown,
            ];

            Log::info('🟢 Activity Metrics Calculated Successfully', [
                'project_id' => $project->id,
                'activities_count' => $activitiesCount,
                'total_weight' => $totalActivityWeight,
            ]);

            return $metrics;

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Calculate Activity Metrics', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate and return procedure count and weight metrics for a specific activity
     */
    public function calculateProcedureMetrics(PreliminaryActivity $activity): array
    {
        Log::info('🟡 Calculating Procedure Metrics', [
            'activity_id' => $activity->id,
            'project_id' => $activity->project_id,
        ]);

        try {
            $procedures = $activity->procedures()->with(['costs'])->get();

            $proceduresCount = $procedures->count();
            $totalProcedureWeight = (float) $procedures->sum('weight');

            $proceduresBreakdown = $procedures->map(function ($procedure) {
                return [
                    'id' => $procedure->id,
                    'name' => $procedure->procedure_name,
                    'weight' => (float) $procedure->weight,
                    'costs_count' => $procedure->costs->count(),
                ];
            })->values();

            $metrics = [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'procedures_count' => $proceduresCount,
                'total_weight' => round($totalProcedureWeight, 2),
                'procedures_breakdown' => $proceduresBreakdown,
            ];

            Log::info('🟢 Procedure Metrics Calculated Successfully', [
                'activity_id' => $activity->id,
                'procedures_count' => $proceduresCount,
                'total_weight' => $totalProcedureWeight,
            ]);

            return $metrics;

        } catch (\Exception $e) {
            Log::error('🔴 Failed to Calculate Procedure Metrics', [
                'activity_id' => $activity->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Post/Sync preliminary procedures of the project to Frappe API Procedure doctype.
     * Endpoint: http://172.16.10.239:8856/api/resource/Procedure
     */
    public function postProceduresToFrappe(Project $project): array
    {
        $frappeService = app(FrappeAPIService::class);
        $erpProjectId = $project->erpnext_project_id ?: $frappeService->findProjectByName($project->project_name);

        if (! $erpProjectId) {
            return [
                'success' => false,
                'message' => 'ERPNext Project ID is missing for this project.',
            ];
        }

        return $frappeService->syncProjectProcedures($project, $erpProjectId);
    }
}
