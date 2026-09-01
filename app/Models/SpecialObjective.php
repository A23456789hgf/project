<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'objective',
        'objective_weight',
        'target_value',
        'measurement_unit',
    ];

    protected $casts = [
        'objective_weight' => 'decimal:2',
        'target_value' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function results()
    {
        return $this->hasMany(ObjectiveResult::class);
    }

    public function outputs()
    {
        return $this->hasMany(ResultOutput::class);
    }

    /**
     * Append legacy attribute aliases so existing views/forms continue working
     * while the DB columns have newer names.
     */
    protected $appends = [
        'indicator',
        'indicator_unit',
        'indicator_value',
    ];

    // Legacy alias for the previous `indicator` attribute — maps to indicator_type
    public function getIndicatorAttribute()
    {
        return $this->attributes['indicator_type'] ?? null;
    }

    // Legacy alias for `indicator_unit` -> measurement_unit
    public function getIndicatorUnitAttribute()
    {
        return $this->attributes['measurement_unit'] ?? null;
    }

    // Legacy alias for `indicator_value` -> target_value
    public function getIndicatorValueAttribute()
    {
        return isset($this->attributes['target_value']) ? $this->attributes['target_value'] : null;
    }
}
