<?php

namespace App\Traits;

use App\Scopes\DomainScope;
use App\Services\SchemaCache;

trait HasDomainScope
{
    /**
     * Boot the DomainScope trait for the model.
     */
    public static function bootHasDomainScope()
    {
        static::addGlobalScope(new DomainScope);

        static::creating(function ($model) {
            if (auth()->check()) {
                $user = auth()->user();
                $table = $model->getTable();

                if (SchemaCache::hasColumn($table, 'geographic_scope_id')) {
                    if (! $model->geographic_scope_id && isset($user->governorate_id)) {
                        $model->geographic_scope_id = $user->governorate_id;
                    }
                }

                if (SchemaCache::hasColumn($table, 'administrative_scope_id')) {
                    if (! $model->administrative_scope_id) {
                        $model->administrative_scope_id = $user->administrative_scope_id ?? $user->entity_id;
                    }
                }
            }
        });
    }

    /**
     * Scope for domain visibility.
     * Since DomainScope is already applied globally, this is now a compatibility wrapper.
     */
    public function scopeVisibleToUser($builder, $module = null)
    {
        if (! auth()->check()) {
            return $builder;
        }
        $user = auth()->user();

        // Admins and full_access roles bypass everything
        if ($user->isAdmin() || optional($user->role)->full_access) {
            return $builder;
        }

        $scopeService = new DomainScope;

        // Resolve module name if not provided
        if (! $module) {
            $module = $scopeService->resolveModule($this);
        }

        $geoScope = $user->getModuleGeoScope($module);
        $adminScope = $user->getModuleAdminScope($module);

        // 1. Matrix "All" Access Priority — geo-linked entities are unrestricted.
        // Exception: entity modules still apply admin scope to central entities (NULL geo).
        if ($geoScope === 'all') {
            $isEntityModule = in_array($module, ['authorities', 'internal-entities']);
            if ($isEntityModule && $adminScope !== 'all') {
                $table = $builder->getModel()->getTable();
                if ($adminScope === 'none') {
                    $builder->where(function ($q) use ($table) {
                        $q->whereNotNull("{$table}.governorate_id")
                            ->orWhereNotNull("{$table}.directorate_id");
                    });
                } else {
                    $builder->where(function ($q) use ($table, $user, $adminScope, $scopeService) {
                        $q->where(function ($geo) use ($table) {
                            $geo->whereNotNull("{$table}.governorate_id")
                                ->orWhereNotNull("{$table}.directorate_id");
                        })->orWhere(function ($c) use ($table, $user, $adminScope, $scopeService) {
                            $c->whereNull("{$table}.governorate_id")
                                ->whereNull("{$table}.directorate_id");
                            $scopeService->applyCentralEntityAdminScope($c, $user, $adminScope, $table);
                        });
                    });
                }
            }

            return $builder;
        }

        // 2. Strict Domain Enforcement for non-central users on core models
        $isCoreDomainModel = in_array($module, ['governorates', 'directorates', 'authorities', 'internal-entities']);
        if ($isCoreDomainModel && ! $user->isCentralUser()) {
            if ($geoScope === 'none') {
                return $builder->whereRaw('0=1');
            }
            $scopeService->applyStrictDomainConstraint($builder, $user, $module);

            return $builder;
        }

        // 3. Both scopes are none — block access
        if ($adminScope === 'none' && $geoScope === 'none') {
            return $builder->whereRaw('0=1');
        }

        // 4. Apply centralized logic to ensure consistency with Global Scope
        $builder->where(function ($q) use ($scopeService, $user, $module, $adminScope, $geoScope) {
            if (in_array($module, ['projects', 'planning', 'correspondence'])) {
                $scopeService->applyEntityBasedScope($q, $user, $module, $adminScope, $geoScope);
            } else {
                $scopeService->applyGenericScope($q, $user, $module, $adminScope, $geoScope);
            }
        });

        return $builder;
    }
}
