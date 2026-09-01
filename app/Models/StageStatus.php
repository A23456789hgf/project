<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StageStatus extends Model
{
    use HasFactory;

    protected $table = 'stage_statuses';

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'category',
        'color',
        'icon',
        'order',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function projectApprovals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function isApproved(): bool
    {
        return $this->code === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->code === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->code === 'pending';
    }

    public function isNeedsRevision(): bool
    {
        return $this->code === 'needs_revision';
    }

    public function getNameAttribute(): string
    {
        return $this->name_ar ?? $this->name_en ?? $this->code;
    }

    public function getDescriptionAttribute(): string
    {
        return $this->description_ar ?? $this->description_en ?? '';
    }
}
