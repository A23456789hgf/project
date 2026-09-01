<?php

namespace App\Http\Controllers\Project\Services;

use App\Models\ExecutiveActivity;
use App\Models\ExecutiveActivityAction;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryProcedure;
use App\Models\Project;
use App\Models\ProjectCost;

/**
 * ProjectMultiStepService
 *
 * Handles multi-step form creation and updates using:
 * - CREATE for initial project in Step 1
 * - UPDATE for project in Steps 2-7
 * - updateOrCreate for related records (activities, procedures, actions, costs)
 */
class ProjectMultiStepService
{
    /**
     * CREATE a new project (Step 1 only)
     * This creates the project record ONE time
     */
    public function createProjectStep1(array $data): Project
    {
        $data['created_by_user_id'] = auth()->id();
        $data['status'] = 'draft';
        $data['last_saved_step'] = 1;
        $data['draft_saved_at'] = now();

        return Project::create($data);
    }

    /**
     * UPDATE existing project (Steps 2-7)
     * This updates the same project created in Step 1
     */
    public function updateProjectStep(Project $project, array $data, int $step): Project
    {
        $data['last_saved_step'] = $step;
        $data['draft_saved_at'] = now();

        $project->update($data);

        return $project;
    }

    /**
     * UPSERT Preliminary Activities
     *
     * If activity has ID -> UPDATE
     * If activity has no ID -> CREATE
     *
     * Expected input format:
     * [
     *     'preliminary_activities' => [
     *         [
     *             'id' => 1,  // null or missing = create, present = update
     *             'activity_name' => 'Activity Name',
     *             'description' => 'Description',
     *             'planned_amount' => 1000
     *         ]
     *     ]
     * ]
     */
    public function syncPreliminaryActivities(Project $project, array $activities): void
    {
        if (empty($activities)) {
            return;
        }

        foreach ($activities as $activityData) {
            $activityId = $activityData['id'] ?? null;

            // Build updateOrCreate keys and values
            $attributes = ['project_id' => $project->id];
            if ($activityId) {
                $attributes['id'] = $activityId;
            }

            // Data to update or create
            $values = [
                'activity_name' => $activityData['activity_name'] ?? null,
                'description' => $activityData['description'] ?? null,
                'planned_amount' => $activityData['planned_amount'] ?? 0,
            ];

            // updateOrCreate: finds by attributes, updates or creates with values
            PreliminaryActivity::updateOrCreate($attributes, $values);
        }
    }

    /**
     * UPSERT Preliminary Procedures (under activities)
     *
     * Expected input format:
     * [
     *     'preliminary_procedures' => [
     *         [
     *             'id' => 1,  // null or missing = create, present = update
     *             'preliminary_activity_id' => 1,
     *             'procedure_name' => 'Procedure Name',
     *             'planned_amount' => 500
     *         ]
     *     ]
     * ]
     */
    public function syncPreliminaryProcedures(Project $project, array $procedures): void
    {
        if (empty($procedures)) {
            return;
        }

        foreach ($procedures as $procedureData) {
            $procedureId = $procedureData['id'] ?? null;

            $attributes = ['project_id' => $project->id];
            if ($procedureId) {
                $attributes['id'] = $procedureId;
            }

            $values = [
                'preliminary_activity_id' => $procedureData['preliminary_activity_id'] ?? null,
                'procedure_name' => $procedureData['procedure_name'] ?? null,
                'planned_amount' => $procedureData['planned_amount'] ?? 0,
            ];

            PreliminaryProcedure::updateOrCreate($attributes, $values);
        }
    }

    /**
     * UPSERT Executive Activities
     *
     * Expected input format:
     * [
     *     'executive_activities' => [
     *         [
     *             'id' => 1,  // null or missing = create, present = update
     *             'activity_name' => 'Activity Name',
     *             'planned_amount' => 2000
     *         ]
     *     ]
     * ]
     */
    public function syncExecutiveActivities(Project $project, array $activities): void
    {
        if (empty($activities)) {
            return;
        }

        foreach ($activities as $activityData) {
            $activityId = $activityData['id'] ?? null;

            $attributes = ['project_id' => $project->id];
            if ($activityId) {
                $attributes['id'] = $activityId;
            }

            $values = [
                'activity_name' => $activityData['activity_name'] ?? null,
                'planned_amount' => $activityData['planned_amount'] ?? 0,
            ];

            ExecutiveActivity::updateOrCreate($attributes, $values);
        }
    }

    /**
     * UPSERT Executive Activity Actions (under activities)
     *
     * Expected input format:
     * [
     *     'executive_actions' => [
     *         [
     *             'id' => 1,  // null or missing = create, present = update
     *             'executive_activity_id' => 1,
     *             'action_name' => 'Action Name',
     *             'planned_amount' => 500
     *         ]
     *     ]
     * ]
     */
    public function syncExecutiveActions(Project $project, array $actions): void
    {
        if (empty($actions)) {
            return;
        }

        foreach ($actions as $actionData) {
            $actionId = $actionData['id'] ?? null;

            $attributes = ['project_id' => $project->id];
            if ($actionId) {
                $attributes['id'] = $actionId;
            }

            $values = [
                'executive_activity_id' => $actionData['executive_activity_id'] ?? null,
                'action_name' => $actionData['action_name'] ?? null,
                'planned_amount' => $actionData['planned_amount'] ?? 0,
            ];

            ExecutiveActivityAction::updateOrCreate($attributes, $values);
        }
    }

    /**
     * UPSERT Project Costs
     *
     * Expected input format:
     * [
     *     'project_costs' => [
     *         [
     *             'id' => 1,  // null or missing = create, present = update
     *             'cost_name' => 'Cost Name',
     *             'amount' => 1000,
     *             'cost_type' => 'salaries|equipment|materials|etc'
     *         ]
     *     ]
     * ]
     */
    public function syncProjectCosts(Project $project, array $costs): void
    {
        if (empty($costs)) {
            return;
        }

        foreach ($costs as $costData) {
            $costId = $costData['id'] ?? null;

            $attributes = ['project_id' => $project->id];
            if ($costId) {
                $attributes['id'] = $costId;
            }

            $values = [
                'cost_name' => $costData['cost_name'] ?? null,
                'amount' => $costData['amount'] ?? 0,
                'cost_type' => $costData['cost_type'] ?? null,
                'description' => $costData['description'] ?? null,
            ];

            ProjectCost::updateOrCreate($attributes, $values);
        }
    }

    /**
     * DELETE related records that were marked for deletion
     *
     * Send array of IDs to delete
     * Expected format: ['deleted_activity_ids' => [1, 2, 3]]
     */
    public function deleteRecords(string $modelClass, array $ids): void
    {
        if (! empty($ids)) {
            $modelClass::whereIn('id', $ids)->delete();
        }
    }

    /**
     * Get project data for resuming from session
     * Returns all related records that were previously saved
     */
    public function getProjectDataForResume(Project $project): array
    {
        return [
            'project' => $project,
            'preliminary_activities' => $project->preliminaryActivities()->with('procedures')->get(),
            'executive_activities' => $project->executiveActivities()->with('actions')->get(),
            'costs' => $project->costs()->get(),
        ];
    }

    /**
     * Validate that a project has minimum required data before submission
     */
    public function validateProjectForSubmission(Project $project): array
    {
        $errors = [];

        // Step 1 validation
        if (empty($project->project_name)) {
            $errors[] = 'اسم المشروع مطلوب';
        }
        if (empty($project->program_id)) {
            $errors[] = 'البرنامج مطلوب';
        }

        // Step 2 validation
        if (empty($project->main_directives)) {
            $errors[] = 'الهدف الرئيسي مطلوب';
        }

        // Step 4 validation
        if ($project->preliminaryActivities()->count() === 0) {
            $errors[] = 'يجب إضافة نشاط تمهيدي واحد على الأقل';
        }

        // Step 5 validation
        if ($project->executiveActivities()->count() === 0) {
            $errors[] = 'يجب إضافة نشاط تنفيذي واحد على الأقل';
        }

        // Step 6 validation
        if ($project->costs()->sum('amount') === 0) {
            $errors[] = 'يجب إضافة تكاليف للمشروع';
        }

        return $errors;
    }
}
