<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectAuthorizationService
{
    /**
     * Determine if the current request or action is within the project creation workflow context.
     */
    public static function isCreationContext(?Request $request = null): bool
    {
        $request = $request ?? request();

        // 1. Check if the request explicitly declares creation context via parameter or header
        if ($request->has('creation_context') || $request->header('X-Creation-Context')) {
            return true;
        }

        // 2. Check the route name
        $route = $request->route();
        if ($route) {
            $routeName = $route->getName();
            if ($routeName && (
                str_contains($routeName, 'projects.create') ||
                str_contains($routeName, 'projects.store') ||
                str_contains($routeName, 'projects.save-draft') ||
                str_contains($routeName, 'projects.step')
            )) {
                return true;
            }
        }

        // 3. Fallback to centralized URL path matching
        $path = $request->decodedPath();

        return str_contains($path, 'projects/create') ||
               str_contains($path, 'projects/step/') ||
               str_contains($path, 'projects/auto-save') ||
               str_contains($path, 'api/projects/save-draft') ||
               (str_contains($path, 'api/projects/') && str_contains($path, '/save-draft')) ||
               (str_contains($path, 'projects/') && str_contains($path, '/draft/'));
    }

    /**
     * Check if a user is authorized to perform project creation operations.
     *
     * @param  mixed  $project
     */
    public static function authorizeCreation(User $user, string $permission, $project = null): bool
    {
        // Must have the umbrella permission projects.create
        if (! $user->hasRawPermission('projects.create')) {
            return false;
        }

        // If a specific project is passed, we strictly check that its status is 'draft' or 'completed_draft'
        if ($project) {
            if (is_numeric($project)) {
                $project = Project::find($project);
            }
            if ($project && ! in_array($project->status, ['draft', 'completed_draft'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a user is authorized to edit a project, strictly separating creation and editing.
     *
     * @param  mixed  $project
     */
    public static function authorizeEdit(User $user, $project): bool
    {
        if (is_numeric($project)) {
            $project = Project::find($project);
        }

        if (! $project) {
            return false;
        }

        // 1. If project is a draft (incomplete, completed, or returned for review), allow users with projects.create to edit it
        if (in_array($project->status, ['draft', 'completed_draft', 'rolled_back_for_review'])) {
            if ($user->hasRawPermission('projects.create')) {
                return $user->checkDatabasePermission('projects.create', $project);
            }
        }

        // 2. For non-draft (finalized) projects, they MUST have the explicit projects.edit permission
        if ($user->hasRawPermission('projects.edit')) {
            return $user->checkDatabasePermission('projects.edit', $project);
        }

        return false;
    }
}
