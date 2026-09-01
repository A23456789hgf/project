<?php

namespace App\Services;

use App\Models\Authority;
use App\Models\Entity;
use App\Models\InternalEntity;

class EntityHierarchyService
{
    protected static $entityCache = [];

    protected static $chainCache = [];

    /**
     * Find an entity by its name (unique identifier in users and projects)
     */
    public function findEntityByName(string $entityName): ?InternalEntity
    {
        if (isset(static::$entityCache[$entityName])) {
            return static::$entityCache[$entityName];
        }

        return static::$entityCache[$entityName] = InternalEntity::where('name', $entityName)->first();
    }

    /**
     * Build approval chain from current entity to top parent
     * Returns array of entity names in order from current entity up to root
     */
    public function buildApprovalChain(string $entityName): array
    {
        $chain = [];
        $current = InternalEntity::withoutGlobalScopes()->where('name', $entityName)->first();
        $visited = []; // Prevent infinite loops

        while ($current && ! isset($visited[$current->id])) {
            $chain[] = [
                'id' => $current->id,
                'name' => $current->name,
            ];
            $visited[$current->id] = true;

            // Move up to parent without global scopes
            $current = $current->parent_id
                ? InternalEntity::withoutGlobalScopes()->find($current->parent_id)
                : null;
        }

        // Ensure ministry (Entity ID 1) is always the final stage in the chain if not present
        $hasMinistry = false;
        foreach ($chain as $item) {
            if ($item['id'] == 1) {
                $hasMinistry = true;
                break;
            }
        }
        if (! $hasMinistry) {
            $ministry = InternalEntity::withoutGlobalScopes()->find(1);
            if ($ministry) {
                $chain[] = [
                    'id' => $ministry->id,
                    'name' => $ministry->name,
                ];
            }
        }

        return $chain;
    }

    /**
     * Build approval chain in reverse order (from top parent down to current entity)
     */
    public function buildApprovalChainReverse(string $entityName): array
    {
        return array_reverse($this->buildApprovalChain($entityName));
    }

    /**
     * Get parent entity name for a given entity
     */
    public function getParentEntityName(string $entityName): ?string
    {
        $entity = $this->findEntityByName($entityName);

        return $entity && $entity->parent ? $entity->parent->name : null;
    }

    /**
     * Get all children entity names for a given parent
     */
    public function getChildrenEntities(string $parentName): array
    {
        $parent = $this->findEntityByName($parentName);
        if (! $parent) {
            return [];
        }

        return $parent->children()->pluck('name')->toArray();
    }

    /**
     * Generate dynamic approval stages from entity hierarchy chain
     * Returns array of stages starting from user's entity to top, plus implementation stage
     */
    public function generateApprovalStagesFromChain(string $userEntityId): array
    {
        // Build approval chain from user's entity to top parent
        // userEntityId is actually the entity name string
        $chain = $this->buildApprovalChain($userEntityId);

        // Create stage array with increasing orders
        $stages = [];
        foreach ($chain as $index => $entityData) {
            $entityName = $entityData['name'];
            $entityId = $entityData['id'];

            $stages[] = [
                'order' => $index + 1,
                'code' => 'entity_'.$entityId,
                'name_ar' => $entityName,
                'name_en' => $entityName.' Stage',
                'entity_id' => $entityId,
                'is_entity_stage' => true,
                'is_implementation' => false,
            ];
        }

        // Add implementation stage
        $stages[] = [
            'order' => count($chain) + 1,
            'code' => 'implementation',
            'name_ar' => 'التنفيذ',
            'name_en' => 'Implementation',
            'entity_id' => null,
            'is_entity_stage' => false,
            'is_implementation' => true,
        ];

        return $stages;
    }

    /**
     * Generate approval stages from a selected entity's parent chain
     * Only includes the entity and its parents up to the root
     * Used to filter approval chain based on selected entity_id
     */
    public function generateApprovalStagesFromSelectedEntity(int $entityId): array
    {
        if (isset(static::$chainCache[$entityId])) {
            return static::$chainCache[$entityId];
        }

        $entity = InternalEntity::withoutGlobalScopes()->find($entityId);
        if (! $entity) {
            \Log::error('generateApprovalStagesFromSelectedEntity: Entity not found in internal_entities!', ['entityId' => $entityId]);
            $auth = Authority::find($entityId);
            if ($auth) {
                \Log::error('generateApprovalStagesFromSelectedEntity: Found in authorities!', ['name' => $auth->name]);
            }
            $ext = Entity::find($entityId);
            if ($ext) {
                \Log::error('generateApprovalStagesFromSelectedEntity: Found in entities!', ['name' => $ext->name]);
            }

            return [];
        }

        // Build chain from selected entity to root.
        // IMPORTANT: use withoutGlobalScopes() on each parent lookup so that
        // DomainScope / ActiveScope do not silently cut the chain short.
        $chain = [];
        $currentId = $entity->id;
        $visited = [];

        while ($currentId && ! isset($visited[$currentId])) {
            $current = InternalEntity::withoutGlobalScopes()->find($currentId);
            if (! $current) {
                break;
            }

            $chain[] = [
                'id' => $current->id,
                'name' => $current->name,
            ];
            $visited[$currentId] = true;
            $currentId = $current->parent_id; // traverse up
        }

        // Ensure ministry (Entity ID 1) is always the final stage in the chain if not already present
        $hasMinistry = false;
        foreach ($chain as $item) {
            if ($item['id'] == 1) {
                $hasMinistry = true;
                break;
            }
        }
        if (! $hasMinistry) {
            $ministry = InternalEntity::withoutGlobalScopes()->find(1);
            if ($ministry) {
                $chain[] = [
                    'id' => $ministry->id,
                    'name' => $ministry->name,
                ];
            }
        }

        // Create stage array with increasing orders (origin entity = order 1, root = highest)
        $stages = [];
        foreach ($chain as $index => $entityData) {
            $stages[] = [
                'order' => $index + 1,
                'code' => 'entity_'.$entityData['id'],
                'name_ar' => $entityData['name'],
                'name_en' => $entityData['name'].' Stage',
                'entity_id' => $entityData['id'],
                'is_entity_stage' => true,
                'is_implementation' => false,
            ];
        }

        // Add implementation stage
        $stages[] = [
            'order' => count($chain) + 1,
            'code' => 'implementation',
            'name_ar' => 'التنفيذ',
            'name_en' => 'Implementation',
            'entity_id' => null,
            'is_entity_stage' => false,
            'is_implementation' => true,
        ];

        return static::$chainCache[$entityId] = $stages;
    }

    /**
     * Get total number of stages for a user's entity
     */
    public function getTotalStagesCount(string $userEntityId): int
    {
        return count($this->generateApprovalStagesFromChain($userEntityId));
    }

    /**
     * Get the next entity in the approval chain
     * Returns null if current entity is the top-level entity
     */
    public function getNextEntityInChain(string $currentEntityName): ?array
    {
        $currentEntity = $this->findEntityByName($currentEntityName);
        if (! $currentEntity || ! $currentEntity->parent) {
            return null;
        }

        return [
            'id' => $currentEntity->parent->id,
            'name' => $currentEntity->parent->name,
        ];
    }

    /**
     * Check if a user's entity is authorized to approve at a specific entity level
     */
    public function canUserApproveAtEntity(string $userEntityName, string $targetEntityName): bool
    {
        $userEntity = $this->findEntityByName($userEntityName);
        $targetEntity = $this->findEntityByName($targetEntityName);

        if (! $userEntity || ! $targetEntity) {
            return false;
        }

        // User can approve if their entity matches the target entity
        // or if their entity is a parent/ancestor of the target entity
        if ($userEntity->id === $targetEntity->id) {
            return true;
        }

        // Check if user's entity is an ancestor of target entity
        $current = $targetEntity;
        while ($current->parent) {
            if ($current->parent->id === $userEntity->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Get all ancestor entities (from current to root)
     */
    public function getAncestors(string $entityName): array
    {
        $ancestors = [];
        $current = $this->findEntityByName($entityName);

        while ($current && $current->parent) {
            $ancestors[] = [
                'id' => $current->parent->id,
                'name' => $current->parent->name,
            ];
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Check if an entity is in the approval path for a given starting entity
     */
    public function isEntityInApprovalPath(string $startEntityName, string $checkEntityName): bool
    {
        $chain = $this->buildApprovalChain($startEntityName);

        foreach ($chain as $entity) {
            if ($entity['name'] === $checkEntityName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the previous entity in the approval chain (for returns/rejections)
     */
    public function getPreviousEntityInChain(string $currentEntityName, string $projectStartEntityName): ?array
    {
        $chain = $this->buildApprovalChain($projectStartEntityName);

        for ($i = 0; $i < count($chain); $i++) {
            if ($chain[$i]['name'] === $currentEntityName && $i > 0) {
                return $chain[$i - 1];
            }
        }

        return null;
    }
}
