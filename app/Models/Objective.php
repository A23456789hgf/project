<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective_name',
        'objective_weight',
        'target_value',
        'indicator_type',
        'indicator_unit_of_measure',
        'project_id',
    ];

    protected $casts = [
        'objective_weight' => 'decimal:2',
    ];

    /**
     * Get the project that owns the objective.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
