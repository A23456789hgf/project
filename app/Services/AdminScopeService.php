<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminScopeService
{
    public function apply(Builder $query, $user, Model $model)
    {
        $table = $model->getTable();

        if (! $user->isCentralUser()) {
            return $query;
        }

        return match ($user->admin_scope_type) {

            'none' => $query->whereRaw('0=1'),

            'own_projects' => $query->where("{$table}.created_by_user_id", $user->id),

            'own_entity' => $query->where("{$table}.entity_id", $user->entity_id),

            'sub_entities' => $query->whereIn("{$table}.entity_id", $user->sub_entity_ids ?? []),

            'top_hierarchy' => $query->whereIn("{$table}.entity_id", $user->hierarchy_entity_ids ?? []),

            'all' => $query,

            default => $query,
        };
    }
}
