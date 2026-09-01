<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSupervisingAuthority extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_supervising_authorities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'project_request_id',
        'authority_type',
        'authority_id',
        'internal_entity_id',
        'entity_name',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the supervising authority.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the authority (entity name).
     */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'authority_id')->withoutGlobalScope('active_only');
    }

    /**
     * Get the parent authority.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'parent_id')->withoutGlobalScope('active_only');
    }

    /**
     * Get the internal entity (if authority_type is internal).
     */
    public function internalEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'internal_entity_id')->withoutGlobalScope('active_only');
    }

    /**
     * Get the parent internal entity.
     */
    public function parentInternalEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'parent_id')->withoutGlobalScope('active_only');
    }

    /**
     * Accessor for entity_id
     */
    public function getEntityIdAttribute()
    {
        return $this->authority_type === 'internal' ? $this->internal_entity_id : $this->authority_id;
    }

    public function getInternalEntityIdAttribute()
    {
        return $this->attributes['internal_entity_id'] ?? null;
    }
}
