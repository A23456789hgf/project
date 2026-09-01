<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ExecutiveActivity extends Model
{
    use HasDomainScope;

    protected $fillable = [
        'project_id', 'name', 'weight', 'output', 'risk', 'result_output_id', 'project_risk_id',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function resultOutput(): BelongsTo
    {
        return $this->belongsTo(ResultOutput::class);
    }

    public function projectRisk(): BelongsTo
    {
        return $this->belongsTo(ProjectRisk::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ExecutiveActivityAction::class);
    }

    public function assignedEntities(): HasMany
    {
        return $this->hasMany(ExecutiveActionAssigned::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ExecutiveActionCost::class);
    }

    public function financialSummaries(): HasMany
    {
        return $this->hasMany(ExecutiveFinancialSummary::class);
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ActivityAssignment::class, 'assignable');
    }
}
