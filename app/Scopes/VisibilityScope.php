<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class VisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // 2. geo filter
        $builder = app(GeoScopeService::class)
            ->apply($builder, $user, $model);

        // 3. admin filter
        $builder = app(AdminScopeService::class)
            ->apply($builder, $user, $model);
    }
}
