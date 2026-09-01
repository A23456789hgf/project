<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportValueMapping extends Model
{
    protected $fillable = [
        'import_session',
        'field_key',
        'field_label',
        'table_name',
        'original_value',
        'mapping_hash',
        'action',
        'target_id',
        'target_value',
        'processed_by',
        'processed_at',
    ];

    // -----------------------------------------------------------------------
    // Hash helper
    // -----------------------------------------------------------------------

    /**
     * Compute the unique hash for (session + field_key + original_value + parent_id).
     * Used instead of a composite unique index to avoid MySQL utf8mb4 key-length limits.
     */
    public static function computeHash(string $session, string $fieldKey, string $originalValue, ?int $parentId = null): string
    {
        return hash('sha256', $session.':::'.$fieldKey.':::'.mb_strtolower(trim($originalValue)).':::'.($parentId ?? ''));
    }

    protected $casts = [
        'processed_at' => 'datetime',
        'target_id' => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    /**
     * Filter to a specific import session owned by a user.
     */
    public function scopeForSession($query, string $session): mixed
    {
        return $query->where('import_session', $session);
    }

    /**
     * Find the mapping for a specific (field_key + original_value + parent_id) within a session.
     */
    public static function findMapping(string $session, string $fieldKey, string $originalValue, ?int $parentId = null): ?self
    {
        $hash = static::computeHash($session, $fieldKey, $originalValue, $parentId);

        return static::where('mapping_hash', $hash)->first();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'replace' => 'استبدال',
            'add' => 'إضافة',
            'edit_add' => 'تعديل وإضافة',
            default => $this->action,
        };
    }
}
