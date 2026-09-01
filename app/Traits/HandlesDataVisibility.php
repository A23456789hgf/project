<?php

namespace App\Traits;

use App\Scopes\DomainScope;
use App\Services\SchemaCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trait HandlesDataVisibility
 *
 * Provides manual geographic and administrative scoping for controllers.
 * Delegates core logic to the User model for consistency across the application.
 */
trait HandlesDataVisibility
{
    /**
     * Apply geographic and administrative filters to a query.
     *
     * @param  string  $module  The module identifier (projects, correspondence, planning, governorates, etc.)
     */
    protected function applyVisibility(Builder $query, string $module): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('0=1');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        // 2. Resolve decentralized modules (handled by localized model scopes)
        // We include geographic entity lookups here to ensure they are searchable across the system
        if (in_array($module, ['projects', 'planning', 'correspondence', 'governorates', 'directorates', 'sub_areas', 'villages', 'internal-entities', 'internal_entities'])) {
            return $query;
        }

        // 2. Resolve geographic scope from matrix
        $geoScope = $user->getModuleGeoScope($module);

        // 3. Matrix "All" Access Priority — geo-linked entities are unrestricted.
        // Exception: entity modules still apply admin scope to central entities (NULL geo).
        if ($geoScope === 'all') {
            $isEntityModule = in_array($module, ['authorities', 'internal-entities']);
            if ($isEntityModule) {
                $adminScope = $user->getModuleAdminScope($module);
                if ($adminScope !== 'all') {
                    $table = $query->getModel()->getTable();
                    if ($adminScope === 'none') {
                        return $query->where(function ($q) use ($table) {
                            $q->whereNotNull("{$table}.governorate_id")
                                ->orWhereNotNull("{$table}.directorate_id");
                        });
                    } else {
                        $scopeService = new DomainScope;

                        return $query->where(function ($q) use ($table, $user, $adminScope, $scopeService) {
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
            }

            return $query;
        }

        // 4. Strict Domain Enforcement for non-central users on core models (if not 'all')
        $scopeService = new DomainScope;
        $isCoreDomainModel = in_array($module, ['governorates', 'directorates', 'authorities', 'internal-entities']);
        if ($isCoreDomainModel && ! $user->isCentralUser()) {
            if ($geoScope === 'none') {
                return $query->whereRaw('0=1');
            }
            $scopeService->applyStrictDomainConstraint($query, $user, $module);

            return $query;
        }

        // 5. Special modules that only use geographic scope (no administrative scope)
        $geoOnlyModules = ['governorates', 'directorates', 'authorities', 'internal-entities'];
        if (in_array($module, $geoOnlyModules)) {
            if ($geoScope === 'none') {
                return $query->whereRaw('0=1');
            }
            $this->applyGeographicScopeFilter($query, $user, $module);

            return $query;
        }

        // 6. For other modules, resolve both scopes
        $adminScope = $user->getModuleAdminScope($module);
        $geoScope = $user->getModuleGeoScope($module);

        // 5. Early exit if both are none
        if ($adminScope === 'none' && $geoScope === 'none') {
            return $query->whereRaw('0=1');
        }

        // 6. Apply combined scopes (strict AND logic)
        return $query->where(function ($q) use ($user, $adminScope, $geoScope, $module) {
            // Geographic filter (unless all/none/custom)
            if (! in_array($geoScope, ['all', 'none', 'custom'])) {
                $this->applyGeographicScopeFilter($q, $user, $module);
            }

            // Administrative filter (unless all/none)
            if (! in_array($adminScope, ['all', 'none'])) {
                $this->applyAdministrativeScopeFilter($q, $user, $adminScope, $module);
            }

            // Explicitly block if admin scope is none
            if ($adminScope === 'none') {
                $q->whereRaw('0=1');
            }
        });
    }

    /**
     * GEOGRAPHIC FILTERING LOGIC
     * Enhanced to handle governorates, directorates, authorities, internal entities
     */
    protected function applyGeographicScopeFilter(Builder $query, $user, string $module): void
    {
        $allowedIds = $user->getAllowedGeographicIds($module);
        if ($allowedIds === 'all' || $allowedIds === 'custom') {
            return;
        }

        // For modules that are geographic entities themselves, we need special column mapping
        $geoColumn = $this->getGeoColumnForModule($module, $query->getModel()->getTable());

        if ($geoColumn) {
            // For entity modules, also OR-include central entities (no geo link) based on admin scope
            if (in_array($module, ['internal-entities', 'authorities'])) {
                $adminScope = $user->getModuleAdminScope($module);
                $table = $query->getModel()->getTable();
                $query->where(function ($q) use ($geoColumn, $allowedIds, $table, $user, $adminScope) {
                    // Geographically linked records
                    if (is_array($allowedIds)) {
                        $q->whereIn($geoColumn, $allowedIds);
                    } else {
                        $q->where($geoColumn, $allowedIds);
                    }
                    // OR central entities with no geo link, filtered by admin scope
                    if ($adminScope !== 'none') {
                        $q->orWhere(function ($c) use ($table, $user, $adminScope) {
                            $c->whereNull("{$table}.governorate_id")
                                ->whereNull("{$table}.directorate_id");
                            (new DomainScope)->applyCentralEntityAdminScope($c, $user, $adminScope, $table);
                        });
                    }
                });

                return;
            }

            if (is_array($allowedIds)) {
                $query->whereIn($geoColumn, $allowedIds);
            } else {
                $query->where($geoColumn, $allowedIds);
            }

            return;
        }

        // Fallback: try relationship (e.g., projects through locations)
        if (method_exists($query->getModel(), 'locations')) {
            $query->whereHas('locations', function ($locQ) use ($allowedIds) {
                if (is_array($allowedIds)) {
                    $locQ->whereIn('governorate_id', $allowedIds);
                } else {
                    $locQ->where('governorate_id', $allowedIds);
                }
            });

            return;
        }

        // If no way to filter, block access for security
        Log::warning('No geographic column or relationship found for module', [
            'module' => $module,
            'table' => $query->getModel()->getTable(),
            'user_id' => $user->id,
        ]);
        $query->whereRaw('0=1');
    }

    /**
     * Determine which column to filter on for geographic scoping based on module and table.
     */
    protected function getGeoColumnForModule(string $module, string $table): ?string
    {
        switch ($module) {
            case 'governorates':
                // The governorate table's ID is the geographic identifier
                return 'id';

            case 'directorates':
                // Directorate belongs to a governorate via governorate_id
                return 'governorate_id';

            case 'authorities':
                // Authority belongs to a governorate via governorate_id
                return 'governorate_id';

            case 'internal-entities':
                // Internal entity can have governorate_id or directorate_id
                // For geographic scope, we prefer governorate_id
                if (SchemaCache::hasColumn($table, 'governorate_id')) {
                    return 'governorate_id';
                }

                // If no governorate_id, we might need special handling (e.g., via directorate)
                // We'll handle that in applyDirectorateBasedGeoFilter if needed
                return null;

            default:
                // For other modules (projects, correspondence, etc.), use standard geographic columns
                if (SchemaCache::hasColumn($table, 'geographic_scope_id')) {
                    return 'geographic_scope_id';
                }
                if (SchemaCache::hasColumn($table, 'governorate_id')) {
                    return 'governorate_id';
                }

                return null;
        }
    }

    /**
     * Special handler for when geographic scope is 'same_directorate' but the module
     * expects governorate-level filtering (e.g., internal-entities without governorate_id).
     * This method is called internally if needed.
     */
    protected function applyDirectorateBasedGeoFilter(Builder $query, $user, string $module): void
    {
        $dirId = $user->getAssignedDirectorateId();
        if (! $dirId) {
            $query->whereRaw('0=1');

            return;
        }

        $govId = DB::table('directorates')->where('id', $dirId)->value('governorate_id');
        if ($govId) {
            // For modules that have governorate_id column
            $table = $query->getModel()->getTable();
            if (SchemaCache::hasColumn($table, 'governorate_id')) {
                $query->where('governorate_id', $govId);
            } else {
                // Fallback: if no governorate_id, maybe filter by directorate_id directly?
                if (SchemaCache::hasColumn($table, 'directorate_id')) {
                    $query->where('directorate_id', $dirId);
                } else {
                    $query->whereRaw('0=1');
                }
            }
        } else {
            $query->whereRaw('0=1');
        }
    }

    /**
     * ADMINISTRATIVE FILTERING LOGIC
     */
    protected function applyAdministrativeScopeFilter(Builder $query, $user, string $adminScope, string $module): void
    {
        $allowedIds = $user->getAllowedAdministrativeIds($module);
        if ($allowedIds === 'all') {
            return;
        }

        $table = $query->getModel()->getTable();

        // Special handling for 'user' scope
        if ($allowedIds === 'user') {
            $col = $this->detectUserCol($table);
            if ($col) {
                $query->where($col, $user->id);
            } else {
                $query->whereRaw('0=1');
            }

            return;
        }

        // Standard ID-based filtering
        $col = SchemaCache::hasColumn($table, 'administrative_scope_id')
            ? 'administrative_scope_id'
            : $this->detectEntityCol($table, $module);

        if (! $col) {
            Log::warning('No administrative column found for module', [
                'module' => $module,
                'table' => $table,
                'user_id' => $user->id,
            ]);

            return;
        }

        if (is_array($allowedIds)) {
            $query->whereIn($col, $allowedIds);
        } else {
            $query->where($col, $allowedIds);
        }
    }

    /**
     * Detect the column that stores the user ID for 'user' scope.
     */
    protected function detectUserCol(string $table): ?string
    {
        $candidates = ['created_by_user_id', 'user_id', 'created_by', 'processed_by'];
        foreach ($candidates as $col) {
            if (SchemaCache::hasColumn($table, $col)) {
                return $col;
            }
        }

        return null;
    }

    /**
     * Detect the column that stores the entity ID for administrative scope.
     */
    protected function detectEntityCol(string $table, string $module): ?string
    {
        $candidates = ['internal_entity_id', 'entity_id', 'submitting_entity_id', 'sender_entity_id', 'created_by_entity'];
        foreach ($candidates as $col) {
            if (SchemaCache::hasColumn($table, $col)) {
                return $col;
            }
        }

        return null;
    }

    /**
     * Check for suspicious patterns in request input (basic SQLi protection).
     */
    protected function hasSuspiciousPattern(): bool
    {
        $input = request()->all();
        $inputString = json_encode($input);

        $patterns = [
            '/(UNION|SELECT|INSERT|DELETE|DROP|ALTER|UPDATE|CREATE)\s+/i',
            '/;\s*--|\/\*|\*\//',
            '/\bOR\b\s+\d+\s*=/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $inputString)) {
                return true;
            }
        }

        return false;
    }
}
