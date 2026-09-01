<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'special_objective_id',
        'objective_result_id',
        'output',
        'target_value',
        'indicator_type',
        'indicator_unit',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function specialObjective()
    {
        return $this->belongsTo(SpecialObjective::class);
    }

    public function objectiveResult()
    {
        return $this->belongsTo(ObjectiveResult::class);
    }
}
