<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'drop',
        'stage_id',
        'referring_entity_id',
        'referring_user_id',
        'referred_entity_id',
        'referral_text',
        'referral_attachment',
        'referral_attachments',
        'response_text',
        'response_attachment',
        'response_attachments',
        'responding_user_id',
        'responded_at',
        'status',
    ];

    protected $appends = ['resolved_stage_name'];

    protected $casts = [
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function referringEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'referring_entity_id');
    }

    public function referredEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'referred_entity_id');
    }

    public function referringUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referring_user_id');
    }

    public function respondingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responding_user_id');
    }

    /**
     * Scopes
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    public function scopeForEntity($query, $entityId)
    {
        return $query->where('referred_entity_id', $entityId)
            ->orWhere('referring_entity_id', $entityId);
    }

    public function scopeForUserEntity($query, $entityId)
    {
        return $query->where(function ($q) use ($entityId) {
            $q->where('referred_entity_id', $entityId)
                ->orWhere('referring_entity_id', $entityId);
        });
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isResponded(): bool
    {
        return $this->status === 'responded';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Get resolved stage name (handles both static stage_id and dynamic entity-based drop)
     */
    public function getResolvedStageNameAttribute(): string
    {
        // 1. If we have a static stage relationship
        if ($this->stage) {
            return $this->stage->name_ar;
        }

        // 2. If it's an entity-based stage (drop starting with entity_)
        if ($this->drop && str_starts_with($this->drop, 'entity_')) {
            $entityId = str_replace('entity_', '', $this->drop);
            $entity = InternalEntity::find($entityId);

            return $entity ? $entity->name : 'جهة غير معروفة';
        }

        // 3. Fallback to drop code or generic label
        return match ($this->drop) {
            'implementation' => 'مرحلة التنفيذ',
            default => $this->drop ?: 'غير محدد'
        };
    }

    /**
     * Get formatted status label in Arabic
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'معلقة',
            'responded' => 'تم الرد',
            'returned' => 'تم الإرجاع',
            default => $this->status
        };
    }
}
