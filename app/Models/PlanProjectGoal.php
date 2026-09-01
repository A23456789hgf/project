<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanProjectGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_project_id',
        'specific_goal',
        'weight',
        'indicator_value',
        'unit_of_measurement',
        'results_json',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'indicator_value' => 'decimal:2',
        'results_json' => 'array',
    ];

    /**
     * Get the project that owns the specific goal.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(PlanProject::class, 'plan_project_id');
    }
}
