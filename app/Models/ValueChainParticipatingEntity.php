<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValueChainParticipatingEntity extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'value_chain_id',
        'entity_type', // 'internal' or 'external'
        'internal_entity_id',
        'authority_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the value chain this entity participates in.
     */
    public function valueChain(): BelongsTo
    {
        return $this->belongsTo(ValueChain::class);
    }

    /**
     * Get the internal entity if type is internal.
     */
    public function internalEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'internal_entity_id');
    }

    /**
     * Get the authority if type is external.
     */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the entity's name based on its type.
     */
    public function getEntityNameAttribute(): string
    {
        if ($this->entity_type === 'internal' && $this->internalEntity) {
            return $this->internalEntity->name;
        }

        if ($this->entity_type === 'external' && $this->authority) {
            return $this->authority->name;
        }

        return 'جهة غير معروفة';
    }

    /**
     * Automatically set tracking fields on creation/update.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
