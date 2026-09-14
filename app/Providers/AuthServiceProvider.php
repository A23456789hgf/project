<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Project;
use App\Services\PermissionResolver;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Project' => 'App\Policies\ProjectPolicy',
        'App\Models\ProjectRequest' => 'App\Policies\ProjectRequestPolicy',
        'App\Models\Plan' => 'App\Policies\PlanPolicy',
        'App\Models\Correspondence' => 'App\Policies\CorrespondencePolicy',
        'reports' => 'App\Policies\ReportPolicy',
        'App\Models\EmpowermentProject' => 'App\Policies\EmpowermentPolicy',
        'App\Models\EmpowermentBeneficiary' => 'App\Policies\EmpowermentPolicy',
        'empowerment' => 'App\Policies\EmpowermentPolicy',
        'App\Models\Program' => 'App\Policies\ProgramPolicy',
        'App\Models\Task' => 'App\Policies\TaskPolicy',
        'App\Models\TaskDiscussion' => 'App\Policies\TaskDiscussionPolicy',
        'App\Models\TaskMemo' => 'App\Policies\TaskMemoPolicy',
        'App\Models\Memoir' => 'App\Policies\MemoirPolicy',
        'App\Models\ValueChain' => 'App\Policies\ValueChainPolicy',
        'App\Models\ValueChainFinancing' => 'App\Policies\ValueChainFinancingPolicy',
        'App\Models\ValueChainFinancingType' => 'App\Policies\ValueChainFinancingTypePolicy',
        'App\Models\ValueChainMember' => 'App\Policies\ValueChainMemberPolicy',
        'App\Models\ChainPlan' => 'App\Policies\ChainPlanPolicy',
        'App\Models\ProjectReferral' => 'App\Policies\ProjectReferralPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * 1. Global Matrix Validation & Admin Bypass
         */
        Gate::before(function ($user, $ability, $args) {
            // A. Fully privileged Admin bypass
            if ($user->isAdmin()) {
                return true;
            }

            // A. Step Permission Umbrella Bypass for projects.create
            if (str_starts_with($ability, 'projects.step.')) {
                if ($user->hasPermission('projects.create')) {
                    // Strictly separate creation/editing contexts: step permissions only valid in creation/draft status!
                    $record = ! empty($args) ? (is_array($args) ? $args[0] : $args) : null;
                    if ($record) {
                        $project = null;
                        if ($record instanceof Project) {
                            $project = $record;
                        } elseif (is_object($record) && isset($record->project)) {
                            $project = $record->project;
                        }

                        if ($project && ! in_array($project->status, ['draft', 'completed_draft'])) {
                            return false;
                        }

                        return $user->checkDatabasePermission('projects.create', $record);
                    }

                    return true;
                }
            }

            // B. Matrix Validation (Single Source of Truth)
            // If the ability contains a dot, it is treated as a Matrix Slug.
            if (str_contains($ability, '.')) {
                if (! PermissionResolver::isValidPermission($ability)) {
                    // Strictly block if not registered in the matrix registry
                    return false;
                }

                // Authoritatively check and resolve matrix slug permissions dynamically
                $record = ! empty($args) ? (is_array($args) ? $args[0] : $args) : null;

                return $user->checkDatabasePermission($ability, $record);
            } else {
                // B. Generic Policy Verbs (view, create, update, etc.)
                // These are allowed to proceed to the Model Policy, which MUST then
                // perform its own matrix-validated slug check (e.g. correspondence.view).
                $policyVerbs = [
                    'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'viewAny',
                    'refer', 'respond', 'reply', 'forward', 'close', 'approve', 'reject', 'closeDraft', 'requestAction', 'resubmit',
                    'resume', 'revert', 'execute', 'viewSchedule', 'reviewFinancial', 'reviewTechnical',
                    'print', 'viewWorkflow', 'import', 'exportExcel', 'exportPdf',
                    'exportPivot', 'exportComprehensive', 'viewDraft', 'review',
                    'viewExecution', 'createExecution', 'updateExecution', 'deleteExecution',
                    'export', 'updateStatus', 'duplicate', 'exportWord', 'completeData',
                    'achievements', 'editEntity',
                ];
                if (in_array($ability, $policyVerbs) && ! empty($args)) {
                    return null; // Continue to Policy logic
                }

                // If it's not a slug and not a recognized policy action with a model, block it.
                // This prevents "unregistered" generic gates from being used as backdoors.
                return false;
            }

            return null; // Fall through to specific Gate definitions
        });
    }
}
