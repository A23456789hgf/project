<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GeoScopeService
{
    public function apply(Builder $query, $user, Model $model)
    {
        $table = $model->getTable();

        if (! $user->isGeoUser()) {
            return $query;
        }

        return match ($user->geo_scope_type) {

            'none' => $query->whereRaw('0=1'),

            'governorate' => $query->where("{$table}.governorate_id", $user->governorate_id),

            'directorate' => $query->where("{$table}.directorate_id", $user->directorate_id),

            'custom' => $query->whereIn("{$table}.governorate_id", $user->custom_governorates ?? []),

            'all' => $query,

            default => $query,
        };
    }
}
