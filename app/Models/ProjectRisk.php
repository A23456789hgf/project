<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRisk extends Model
{
    protected $fillable = [
        'project_id',
        'project_request_id',
        'risk',
        'risk_rate',
        'proposed_solution',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
