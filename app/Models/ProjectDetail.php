<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDetail extends Model
{
    protected $fillable = [
        'project_id',
        'is_part_of_plan',
        'project_summary',
        'project_introduction',
        'problem_and_justification',
        'project_components',
        'expected_impact',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
