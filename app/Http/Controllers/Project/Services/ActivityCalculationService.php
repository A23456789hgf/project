<?php

namespace App\Http\Controllers\Project\Services;

use App\Models\ExecutiveActivity;
use App\Models\PreliminaryActivity;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ActivityCalculationService
{
    /**
     * Calculate preliminary activity metrics for a project
     */
    public function calculatePreliminaryActivityMetrics(Project $project): array
    {
        Log::info('Calculating Preliminary Activity Metrics', [
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

            return [
                'type' => 'preliminary',
                'activities_count' => $activitiesCount,
                'total_weight' => round($totalActivityWeight, 2),
                'activities_breakdown' => $activitiesBreakdown,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to Calculate Preliminary Activity Metrics', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate executive activity metrics for a project
     */
    public function calculateExecutiveActivityMetrics(Project $project): array
    {
        Log::info('Calculating Executive Activity Metrics', [
            'project_id' => $project->id,
        ]);

        try {
            $activities = $project->executiveActivities()
                ->with(['actions'])
                ->get();

            $activitiesCount = $activities->count();
            $totalActivityWeight = (float) $activities->sum('weight');

            $activitiesBreakdown = $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'weight' => (float) $activity->weight,
                    'actions_count' => $activity->actions->count(),
                ];
            })->values();

            return [
                'type' => 'executive',
                'activities_count' => $activitiesCount,
                'total_weight' => round($totalActivityWeight, 2),
                'activities_breakdown' => $activitiesBreakdown,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to Calculate Executive Activity Metrics', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate procedure metrics for a preliminary activity
     */
    public function calculateProcedureMetrics(PreliminaryActivity $activity): array
    {
        Log::info('Calculating Procedure Metrics', [
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

            return [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'procedures_count' => $proceduresCount,
                'total_weight' => round($totalProcedureWeight, 2),
                'procedures_breakdown' => $proceduresBreakdown,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to Calculate Procedure Metrics', [
                'activity_id' => $activity->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate action metrics for an executive activity
     */
    public function calculateActionMetrics(ExecutiveActivity $activity): array
    {
        Log::info('Calculating Action Metrics', [
            'activity_id' => $activity->id,
            'project_id' => $activity->project_id,
        ]);

        try {
            $actions = $activity->actions()->with(['assignedEntities', 'costs'])->get();

            $actionsCount = $actions->count();
            $totalActionWeight = (float) $actions->sum('weight');

            $actionsBreakdown = $actions->map(function ($action) {
                return [
                    'id' => $action->id,
                    'name' => $action->action,
                    'weight' => (float) $action->weight,
                    'assigned_entities_count' => $action->assignedEntities->count(),
                    'costs_count' => $action->costs->count(),
                ];
            })->values();

            return [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'actions_count' => $actionsCount,
                'total_weight' => round($totalActionWeight, 2),
                'actions_breakdown' => $actionsBreakdown,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to Calculate Action Metrics', [
                'activity_id' => $activity->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate comprehensive metrics for a project (both preliminary and executive)
     */
    public function calculateProjectActivityMetrics(Project $project): array
    {
        Log::info('Calculating Comprehensive Project Activity Metrics', [
            'project_id' => $project->id,
        ]);

        try {
            $preliminaryMetrics = $this->calculatePreliminaryActivityMetrics($project);
            $executiveMetrics = $this->calculateExecutiveActivityMetrics($project);

            return [
                'project_id' => $project->id,
                'preliminary' => $preliminaryMetrics,
                'executive' => $executiveMetrics,
                'summary' => [
                    'total_preliminary_activities' => $preliminaryMetrics['activities_count'],
                    'total_preliminary_weight' => $preliminaryMetrics['total_weight'],
                    'total_executive_activities' => $executiveMetrics['activities_count'],
                    'total_executive_weight' => $executiveMetrics['total_weight'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to Calculate Comprehensive Project Metrics', [
                'project_id' => $project->id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
