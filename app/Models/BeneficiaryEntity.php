<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiaryEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_request_id',
        'authority_type',
        'authority_id',
        'parent_id',
    ];

    protected $casts = [
        'authority_type' => 'string',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'parent_id');
    }

    /**
     * العلاقة مع الجهة الداخلية (إذا كان النوع داخلي)
     */
    public function internalEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'authority_id');
    }

    /**
     * Get the parent internal entity.
     */
    public function parentInternalEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'parent_id');
    }

    /**
     * Accessor for entity_id to match the view's expectations
     */
    public function getEntityIdAttribute()
    {
        return $this->authority_type === 'internal' ? $this->authority_id : null;
    }
}
