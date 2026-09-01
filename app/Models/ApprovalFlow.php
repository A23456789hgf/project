<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'authority_id',
        'step_order',
        'step_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function projectApprovals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAuthority($query, $authorityId)
    {
        return $query->where('authority_id', $authorityId);
    }

    public function scopeOrderedByStep($query)
    {
        return $query->orderBy('step_order');
    }
}
