<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\FrappeAPIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecutiveActivitiesController extends Controller
{
    /**
     * Entry point for processing executive activities from ProjectService.
     */
    public function handleExecutiveActivities(Project $project, array $activitiesData): void
    {
        Log::info('🚀 Starting stable executive activities persistence operation', [
            'project_id' => $project->id,
            'activities_count' => count($activitiesData),
        ]);

        DB::beginTransaction();
        try {
            $existingActivityIds = $project->executiveActivities()->pluck('id')->toArray();
            $processedActivityIds = [];

            foreach ($activitiesData as $activityData) {
                // Determine if we are updating an existing activity
                $activityId = $activityData['id'] ?? null;

                if ($activityId && in_array($activityId, $existingActivityIds)) {
                    $activity = $project->executiveActivities()->findOrFail($activityId);
                    $activity->update([
                        'name' => $activityData['name'] ?? '',
                        'weight' => $activityData['weight'] ?? 0,
                        'result_output_id' => $activityData['result_output_id'] ?? null,
                        'project_risk_id' => $activityData['project_risk_id'] ?? null,
                    ]);
                    $processedActivityIds[] = (int) $activity->id;
                } else {
                    $activity = $project->executiveActivities()->create([
                        'name' => $activityData['name'] ?? '',
                        'weight' => $activityData['weight'] ?? 0,
                        'result_output_id' => $activityData['result_output_id'] ?? null,
                        'project_risk_id' => $activityData['project_risk_id'] ?? null,
                    ]);
                    $processedActivityIds[] = (int) $activity->id;
                }

                // Process Actions for this activity
                $actionsData = $activityData['actions'] ?? [];
                $this->processActions($activity, $actionsData);
            }

            // Delete activities that were not in the request
            $activitiesToDelete = array_diff($existingActivityIds, $processedActivityIds);
            if (! empty($activitiesToDelete)) {
                $project->executiveActivities()->whereIn('id', $activitiesToDelete)->delete();
                Log::info('🗑️ Deleted removed executive activities', ['deleted_ids' => $activitiesToDelete]);
            }

            DB::commit();
            Log::info('✅ Executive activities persistence operation completed successfully');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Failed to persist executive activities', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Process actions for a given activity (Create/Update/Delete).
     */
    protected function processActions($activity, array $actionsData): void
    {
        $existingActionIds = $activity->actions()->pluck('id')->toArray();
        $processedActionIds = [];

        foreach ($actionsData as $actionData) {
            $actionId = $actionData['id'] ?? null;

            if ($actionId && in_array($actionId, $existingActionIds)) {
                $action = $activity->actions()->findOrFail($actionId);
                $action->update([
                    'action' => $actionData['action'] ?? '',
                    'weight' => $actionData['weight'] ?? 0,
                    'start_date' => $actionData['start_date'] ?? null,
                    'start_date_hijri' => $actionData['start_date_hijri'] ?? null,
                    'end_date' => $actionData['end_date'] ?? null,
                    'end_date_hijri' => $actionData['end_date_hijri'] ?? null,
                    'duration' => $actionData['duration'] ?? null,
                    'verification_means' => $actionData['verification_means'] ?? null,
                    'project_id' => $activity->project_id, // Ensure project_id is set
                ]);
                $processedActionIds[] = (int) $action->id;
            } else {
                $action = $activity->actions()->create([
                    'action' => $actionData['action'] ?? '',
                    'weight' => $actionData['weight'] ?? 0,
                    'start_date' => $actionData['start_date'] ?? null,
                    'start_date_hijri' => $actionData['start_date_hijri'] ?? null,
                    'end_date' => $actionData['end_date'] ?? null,
                    'end_date_hijri' => $actionData['end_date_hijri'] ?? null,
                    'duration' => $actionData['duration'] ?? null,
                    'verification_means' => $actionData['verification_means'] ?? null,
                    'project_id' => $activity->project_id,
                ]);
                $processedActionIds[] = (int) $action->id;
            }

            // Process assigned entities and costs for this action
            $this->processAssignedEntities($action, $actionData['assigned_entities'] ?? []);
            $this->processActionCosts($action, $actionData['costs'] ?? []);
        }

        // Delete actions that were not in the request
        $actionsToDelete = array_diff($existingActionIds, $processedActionIds);
        if (! empty($actionsToDelete)) {
            $activity->actions()->whereIn('id', $actionsToDelete)->delete();
        }
    }

    /**
     * Process assigned entities for a given action (Create/Update/Delete).
     */
    protected function processAssignedEntities($action, array $entitiesData): void
    {
        $existingEntityIds = $action->assignedEntities()->pluck('id')->toArray();
        $processedEntityIds = [];

        foreach ($entitiesData as $entityData) {
            $entityId = $entityData['id'] ?? null;

            if ($entityId && in_array($entityId, $existingEntityIds)) {
                $entity = $action->assignedEntities()->findOrFail($entityId);
                $entity->update([
                    'entity' => $entityData['entity'] ?? '',
                    'name' => $entityData['name'] ?? '',
                    'task' => $entityData['task'] ?? '',
                    'email' => $entityData['email'] ?? null,
                    'phone' => $entityData['phone'] ?? null,
                    'project_id' => $action->project_id,
                    'executive_activity_id' => $action->executive_activity_id,
                ]);
                $processedEntityIds[] = (int) $entity->id;
            } else {
                $entity = $action->assignedEntities()->create([
                    'entity' => $entityData['entity'] ?? '',
                    'name' => $entityData['name'] ?? '',
                    'task' => $entityData['task'] ?? '',
                    'email' => $entityData['email'] ?? null,
                    'phone' => $entityData['phone'] ?? null,
                    'project_id' => $action->project_id,
                    'executive_activity_id' => $action->executive_activity_id,
                ]);
                $processedEntityIds[] = (int) $entity->id;
            }
        }

        // Delete entities that were not in the request
        $entitiesToDelete = array_diff($existingEntityIds, $processedEntityIds);
        if (! empty($entitiesToDelete)) {
            $action->assignedEntities()->whereIn('id', $entitiesToDelete)->delete();
        }
    }

    /**
     * Process costs for a given action (Create/Update/Delete).
     */
    protected function processActionCosts($action, array $costsData): void
    {
        $existingCostIds = $action->costs()->pluck('id')->toArray();
        $processedCostIds = [];

        foreach ($costsData as $costData) {
            $costId = $costData['id'] ?? null;

            if ($costId && in_array($costId, $existingCostIds)) {
                $cost = $action->costs()->findOrFail($costId);
                $cost->update([
                    'financial_item_id' => $costData['financial_item_id'],
                    'unit_id' => $costData['unit_id'] ?? null,
                    'amount' => $costData['amount'] ?? 0,
                    'quantity' => $costData['quantity'] ?? 1,
                    'total' => ($costData['amount'] ?? 0) * ($costData['quantity'] ?? 1),
                    'project_id' => $action->project_id,
                    'executive_activity_id' => $action->executive_activity_id,
                    // If unit_name is required in older parts of the system:
                    'unit_name' => $costData['unit_id'] ?? null,
                ]);
                $processedCostIds[] = (int) $cost->id;
            } else {
                $cost = $action->costs()->create([
                    'financial_item_id' => $costData['financial_item_id'],
                    'unit_id' => $costData['unit_id'] ?? null,
                    'amount' => $costData['amount'] ?? 0,
                    'quantity' => $costData['quantity'] ?? 1,
                    'total' => ($costData['amount'] ?? 0) * ($costData['quantity'] ?? 1),
                    'project_id' => $action->project_id,
                    'executive_activity_id' => $action->executive_activity_id,
                    'unit_name' => $costData['unit_id'] ?? null,
                ]);
                $processedCostIds[] = (int) $cost->id;
            }
        }

        // Delete costs that were not in the request
        $costsToDelete = array_diff($existingCostIds, $processedCostIds);
        if (! empty($costsToDelete)) {
            $action->costs()->whereIn('id', $costsToDelete)->delete();
        }
    }

    /**
     * Old entry point for processing executive activities (Legacy support if needed).
     */
    public function handle(Project $project, array $data, bool $isDraft = false)
    {
        $activities = $data['executive_activities'] ?? [];
        $this->handleExecutiveActivities($project, $activities);

        return [
            'status' => 'success',
            'message' => 'Executive activities processed successfully',
        ];
    }

    /**
     * Post/Sync executive actions (procedures) of the project to Frappe API Procedure doctype.
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
