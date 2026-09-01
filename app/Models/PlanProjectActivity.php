<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanProjectActivity extends Model
{
    use HasDomainScope, HasFactory;

    protected $fillable = [
        'plan_project_id',
        'name',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    /**
     * Get the project that owns the activity.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(PlanProject::class, 'plan_project_id');
    }

    /**
     * Get the actions for the activity.
     */
    public function actions()
    {
        return $this->hasMany(PlanProjectActivityAction::class, 'plan_project_activity_id');
    }
}
