<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveActionAssigned extends Model
{
    protected $table = 'executive_action_assigned';

    protected $fillable = [
        'project_id', 'executive_activity_id', 'executive_activity_action_id',
        'entity', 'name', 'task',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivity::class, 'executive_activity_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivityAction::class, 'executive_activity_action_id');
    }
}
