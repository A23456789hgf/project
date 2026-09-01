<?php

namespace App\Models;

use App\Traits\HasCreatorTracking;
use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanProject extends Model
{
    use HasCreatorTracking, HasDomainScope, HasFactory;

    protected $fillable = [
        'plan_id',
        'name',
        'importance',
        'status',
        'baseline',
        'cost_type',
        'cost',
        'funding_availability',
        'funding_source_id',
        'participating_entity_id',
        'creator_username',
        'creator_entity_id',
    ];

    protected $casts = [
        'funding_availability' => 'boolean',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the specific goals associated with this project.
     */
    public function goals(): HasMany
    {
        return $this->hasMany(PlanProjectGoal::class, 'plan_project_id');
    }

    /**
     * Get the plan this project belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    /**
     * Get the funding source if available.
     */
    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    /**
     * Get the participating internal entity.
     */
    public function participatingEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'participating_entity_id');
    }

    /**
     * Get the activities associated with this project.
     */
    public function activities()
    {
        return $this->hasMany(PlanProjectActivity::class, 'plan_project_id');
    }
}
