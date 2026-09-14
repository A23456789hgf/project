<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Services\ScopeService;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Render the edit entity modal with scoped authorities
     */
    public function renderEditEntityModal(Project $project)
    {
        $user = Auth::user();

        // Get filtered entities through ScopeService
        $entityIds = app(ScopeService::class)->getEditableEntityIds();

        $entities = InternalEntity::whereIn('id', $entityIds)
            ->orderBy('name')
            ->get();

        // Pass filtered entities to view
        return view('projects.partials.modals._edit_entity_modal', [
            'project' => $project,
            'entities' => $entities,
        ]);
    }
}
