<?php

namespace App\Services;

use App\Models\InternalEntity;
use Illuminate\Support\Facades\Auth;

class ScopeService
{
    /**
     * Get the list of internal entity IDs that the current user can edit.
     * Includes both directly selected entities and children via hierarchy.
     */
    public function getEditableEntityIds(): array
    {
        $user = Auth::user();

        // If the user is an admin, they can see all internal entities
        if ($user->isAdmin()) {
            return InternalEntity::pluck('id')->toArray();
        }

        // Otherwise, start with the user’s session scope (selected_entity_id)
        $selectedEntityId = session('selected_entity_id', $user->entity_id);

        // If no entity scope is set, return an empty list
        if (empty($selectedEntityId)) {
            return [];
        }

        // Finally, get all descendant internal entities (including self)
        return InternalEntity::getAllChildrenIds((int) $selectedEntityId);
    }
}
