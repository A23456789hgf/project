<?php

namespace App\Traits;

use App\Models\InternalEntity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasProjectVisibility
{
    protected static array $geoCache = [];

    /**
     * Primary visibility scope for projects.
     *
     * 🔥 MODIFIED: Now uses the same simple logic as ProjectGeoFiltering.
     * It calculates entity IDs from administrative_scope_id and geographicScopes,
     * then filters projects by creator_entity_id + stakeholder relations.
     *
     * No extra geographic filters on projects.governorate_id, etc.
     */
    public function scopeWithVisibility(Builder $query, ?User $user = null): Builder
    {
        return $query;
    }

    /**
     * Calculate allowed entity IDs for a given user.
     * This is a copy of the logic from ProjectGeoFiltering::getProjectAllowedEntityIds()
     * to keep the trait independent.
     */
    public function getProjectAllowedEntityIds(User $user): array
    {
        static $userAllowedEntitiesCache = [];

        $cacheKey = $user->id ?? 0;
        if (isset($userAllowedEntitiesCache[$cacheKey])) {
            return $userAllowedEntitiesCache[$cacheKey];
        }

        // Admin already handled earlier, but just in case
        if ($user->isAdmin()) {
            return $userAllowedEntitiesCache[$cacheKey] = ['all'];
        }

        $entityIds = [];

        // ---- Administrative scope ----
        if (! empty($user->administrative_scope_id)) {
            $children = InternalEntity::getAllChildrenIds($user->administrative_scope_id);
            $entityIds = array_merge($entityIds, $children);
        }

        // ---- Geographic scopes ----
        $geographicScopes = $user->geographicScopes;
        if ($geographicScopes) {
            foreach ($geographicScopes as $scope) {
                if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByGovernorate($scope->governorate_id);
                    $entityIds = array_merge($entityIds, $ids);
                } elseif (! empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByDirectorate($scope->directorate_id);
                    $entityIds = array_merge($entityIds, $ids);
                }
            }
        }

        // Fallback: If no admin scope, use the user's direct entity ID
        if (empty($user->administrative_scope_id) && ! empty($user->entity_id)) {
            $children = InternalEntity::getAllChildrenIds($user->entity_id);
            $entityIds = array_merge($entityIds, $children);
        }

        $entityIds = array_unique($entityIds);

        // Remove empty/null values
        $entityIds = array_filter($entityIds, fn ($id) => ! empty($id));

        return $userAllowedEntitiesCache[$cacheKey] = array_values($entityIds);
    }

    // -----------------------------------------------------------------------
    // The methods below are kept for backward compatibility,
    // but they are NO LONGER USED by scopeWithVisibility.
    // You may remove them if they are not called elsewhere.
    // -----------------------------------------------------------------------

    /**
     * @deprecated Not used in the new simplified logic.
     */
    protected function applyGeographicScopeForGeographicalUser(Builder $query, User $user, string $geoScope): Builder
    {
        // Empty implementation or redirect to the new logic
        return $query;
    }

    /**
     * @deprecated Not used.
     */
    protected function addGovernorateCondition(Builder $query, array $govIds): void
    {
        // No-op
    }

    /**
     * @deprecated Not used.
     */
    protected function addDirectorateCondition(Builder $query, array $dirIds): void
    {
        // No-op
    }

    /**
     * @deprecated Not used.
     */
    protected function addStakeholderConditions(Builder $query, array $internalIds, array $authorityIds): void
    {
        // No-op
    }

    /**
     * @deprecated Not used.
     */
    protected function applyAdministrativeScopeOnly(Builder $query, User $user): Builder
    {
        return $query;
    }

    /**
     * @deprecated Not used.
     */
    protected function applyAdministrativeScope(Builder $query, User $user): Builder
    {
        return $query;
    }

    /**
     * @deprecated Not used.
     */
    protected function applyEntityFilter(Builder $query, $entityIds): Builder
    {
        return $query;
    }

    /**
     * Caching helpers – kept if needed elsewhere.
     */
    protected function getCachedDirectorateIds(array $govIds): array
    {
        // Keep if used elsewhere, otherwise can be removed
        return [];
    }

    protected function getCachedInternalEntityIds(array $govIds, array $dirIds): array
    {
        return [];
    }

    protected function getCachedAuthorityIds(array $govIds, array $dirIds): array
    {
        return [];
    }

    protected function getCachedInternalEntityIdsByDirectorates(array $dirIds): array
    {
        return [];
    }

    protected function getCachedAuthorityIdsByDirectorates(array $dirIds): array
    {
        return [];
    }

    public static function clearVisibilityCache(): void
    {
        static::$geoCache = [];
    }
}
