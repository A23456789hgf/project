<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'order',
        'type',
        'is_active',
        'is_system',
        'parent_id', // <-- تم الإضافة لدعم الشجرة
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /** علاقات الشجرة */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Stage::class, 'parent_id');
    }

    /** علاقات الاعتماد الحالية */
    public function projectApprovals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class);
    }

    public function outgoingFlows(): HasMany
    {
        return $this->hasMany(StageFlow::class, 'from_stage_id');
    }

    public function incomingFlows(): HasMany
    {
        return $this->hasMany(StageFlow::class, 'to_stage_id');
    }

    public function nextStages(): HasManyThrough
    {
        return $this->hasManyThrough(
            Stage::class,
            StageFlow::class,
            'from_stage_id',
            'id',
            'id',
            'to_stage_id'
        );
    }

    public function previousStages(): HasManyThrough
    {
        return $this->hasManyThrough(
            Stage::class,
            StageFlow::class,
            'to_stage_id',
            'id',
            'id',
            'from_stage_id'
        );
    }

    /** Scopes */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /** دوال للمرحلة التالية والسابقة */
    public function getNextStage(?string $triggerStatus = null): ?Stage
    {
        $query = $this->outgoingFlows()->with('toStage');

        if ($triggerStatus) {
            $query->where('trigger_status', $triggerStatus);
        }

        $flow = $query->first();

        return $flow ? $flow->toStage : null;
    }

    public function getPreviousStage(?string $triggerStatus = null): ?Stage
    {
        $query = $this->incomingFlows()->with('fromStage');

        if ($triggerStatus) {
            $query->where('trigger_status', $triggerStatus);
        }

        $flow = $query->first();

        return $flow ? $flow->fromStage : null;
    }
    // App\Models\Stage.php

}
