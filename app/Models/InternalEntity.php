<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasActiveScope;
use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use App\Traits\HasModelVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InternalEntity extends Model
{
    use Auditable, HasActiveScope, HasCreatorTracking, HasDomainScope, HasFactory, HasModelVisibility {
        HasModelVisibility::scopeVisibleToUser insteadof HasDomainScope;
    }

    protected $table = 'internal_entities';

    protected $fillable = [
        'status',
        'name',
        'entity_type',
        'entity_code',
        'parent_id',
        'authority_id',
        'governorate_id',
        'directorate_id',
        'is_active',
        'creator_username',
        'creator_entity_id',
        'erpnext_id',
        'erpnext_type',
        'erpnext_parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ============================================
    // العلاقات
    // ============================================
    public function parent()
    {
        return $this->belongsTo(InternalEntity::class, 'parent_id');
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class, 'governorate_id');
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }

    public function children()
    {
        return $this->hasMany(InternalEntity::class, 'parent_id');
    }

    /**
     * Get all children (descendants) of this entity recursively
     * Uses DB directly to avoid lazy loading issues
     */
    public function getAllChildren()
    {
        $ids = static::getAllDescendantIds($this->id);

        if (empty($ids)) {
            return collect();
        }

        // Remove the current entity from the list if present
        $ids = array_diff($ids, [$this->id]);

        if (empty($ids)) {
            return collect();
        }

        return static::whereIn('id', $ids)->get();
    }

    /**
     * Get all descendant IDs for a given parent ID recursively
     * Uses DB directly to avoid lazy loading issues
     */
    public static function getAllDescendantIds($parentId): array
    {
        static $descendantsCache = [];

        if (! $parentId) {
            return [];
        }

        $cacheKey = (int) $parentId;
        if (isset($descendantsCache[$cacheKey])) {
            return $descendantsCache[$cacheKey];
        }

        $ids = [(int) $parentId];
        $toProcess = [(int) $parentId];
        $allDescendants = [];
        $visited = [(int) $parentId => true];

        while (! empty($toProcess)) {
            $children = DB::table('internal_entities')
                ->whereIn('parent_id', $toProcess)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            if (empty($children)) {
                break;
            }

            $newToProcess = [];
            foreach ($children as $child) {
                if (! isset($visited[$child])) {
                    $visited[$child] = true;
                    $allDescendants[] = $child;
                    $newToProcess[] = $child;
                }
            }

            $toProcess = $newToProcess;
        }

        return $descendantsCache[$cacheKey] = array_unique($allDescendants);
    }

    /**
     * جلب جميع معرفات الأطفال بشكل متكرر — يستخدم DB مباشرة لتجاوز DomainScope
     */
    public static function getAllChildrenIds($parentId): array
    {
        return static::getAllDescendantIds($parentId);
    }

    /**
     * جلب جميع معرفات الأباء بشكل متكرر — يستخدم DB مباشرة لتجاوز DomainScope
     */
    public static function getAllParentIds(int $entityId): array
    {
        $ids = [];
        $currentId = $entityId;
        $visited = [(int) $entityId => true];

        while (true) {
            $parent = DB::table('internal_entities')
                ->where('id', $currentId)
                ->value('parent_id');

            if (! $parent) {
                break;
            }

            $parentId = (int) $parent;
            if (isset($visited[$parentId])) {
                break; // Prevent infinite loop due to circular reference
            }

            $ids[] = $parentId;
            $visited[$parentId] = true;
            $currentId = $parentId;
        }

        return $ids;
    }

    /**
     * جلب كل الجهات في محافظة — يستخدم DB مباشرة لتجاوز DomainScope
     */
    public static function getAllByGovernorate($governorateId): array
    {
        static $govCache = [];
        $cacheKey = (int) $governorateId;

        if (isset($govCache[$cacheKey])) {
            return $govCache[$cacheKey];
        }

        return $govCache[$cacheKey] = DB::table('internal_entities')
            ->where('governorate_id', $governorateId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    /**
     * جلب كل الجهات في مديرية — يستخدم DB مباشرة لتجاوز DomainScope
     */
    public static function getAllByDirectorate($directorateId): array
    {
        static $dirCache = [];
        $cacheKey = (int) $directorateId;

        if (isset($dirCache[$cacheKey])) {
            return $dirCache[$cacheKey];
        }

        return $dirCache[$cacheKey] = DB::table('internal_entities')
            ->where('directorate_id', $directorateId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'entity_id');
    }

    public function sentCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'sender_entity_id');
    }

    public function receivedCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'recipient_entity_id');
    }

    public function memoirs()
    {
        return $this->hasMany(Memoir::class, 'entity_id');
    }

    // ============================================
    // Scopes
    // ============================================
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * فلترة حسب governorate أو directorate
     */
    public function scopeByLocation($query, $governorateId = null, $directorateId = null)
    {
        if ($governorateId) {
            $query->where('governorate_id', $governorateId);
        }
        if ($directorateId) {
            $query->where('directorate_id', $directorateId);
        }

        return $query;
    }

    // ============================================
    // Methods: Hierarchy & Approval
    // ============================================
    public function getHierarchyPath()
    {
        $path = [];
        $entity = $this;

        while ($entity) {
            array_unshift($path, $entity->name);
            $entity = $entity->parent;
        }

        return implode(' > ', $path);
    }

    public function getFullNameAttribute(): string
    {
        return $this->getHierarchyPath();
    }

    public function getApprovalChainToRoot()
    {
        $chain = collect([$this]);
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            $chain->push($current);
        }

        return $chain;
    }

    public function getParentChain()
    {
        $parents = collect();
        $current = $this->parent;

        while ($current) {
            $parents->push($current);
            $current = $current->parent;
        }

        return $parents;
    }

    public function isRootEntity(): bool
    {
        return is_null($this->parent_id);
    }

    public function getHierarchyLevel(): int
    {
        $level = 0;
        $current = $this;

        while ($current->parent) {
            $level++;
            $current = $current->parent;
        }

        return $level;
    }

    /**
     * Generate or return entity code
     */
    public function getEntityCode(): string
    {
        if (empty($this->entity_code)) {
            $words = explode(' ', trim($this->name));
            $code = count($words) >= 2 ? substr($words[0], 0, 1).substr($words[1], 0, 1) : substr($this->name, 0, 2);
            $code = strtoupper(preg_replace('/[^A-Z0-9]/', 'X', $code));
            $code = str_pad($code, 2, 'X');
            $this->entity_code = $code;
            $this->save();
        }

        return $this->entity_code;
    }

    public function setEntityCode(string $code): void
    {
        $this->entity_code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $code), 0, 2));
        $this->save();
    }

    /**
     * Find governorate-level ancestor
     */
    private function findGovernorateAncestor(InternalEntity $entity): ?InternalEntity
    {
        if (is_null($entity->parent_id)) {
            return $entity;
        }

        $current = $entity;
        while ($current->parent && ! is_null($current->parent->parent_id)) {
            $current = $current->parent;
        }

        return $current->parent_id ? $current : $entity;
    }

    public static function getEntitiesForUserGovernorate(User $user): array
    {
        $entityIds = [];

        foreach ($user->geographicScopes as $scope) {
            if ($scope->governorate_id) {
                $entityIds = array_merge(
                    $entityIds,
                    static::getAllByGovernorate($scope->governorate_id)
                );
            }
        }

        return array_values(array_unique($entityIds));
    }
}
