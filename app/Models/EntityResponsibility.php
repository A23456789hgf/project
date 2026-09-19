<?php

namespace App\Models;

use App\Enums\EntityResponsibilityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class EntityResponsibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_entity_id',
        'responsibility_type',
        'user_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (EntityResponsibility $responsibility): void {
            if (! EntityResponsibilityType::tryFrom((string) $responsibility->responsibility_type)) {
                throw new InvalidArgumentException('Invalid entity responsibility type.');
            }

            $user = User::withoutGlobalScopes()->find($responsibility->user_id);
            if (! $user || $user->status !== 'Active' || (isset($user->is_active) && ! $user->is_active)) {
                throw new InvalidArgumentException('Entity responsibility user must be active.');
            }

            if ((int) $user->entity_id !== (int) $responsibility->internal_entity_id) {
                throw new InvalidArgumentException('Entity responsibility user must belong to the same entity.');
            }
        });
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'internal_entity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
