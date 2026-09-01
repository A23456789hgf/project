<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionAssignee extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'preliminary_activity_id',
        'preliminary_activity_action_id',
        'entity_id',
        'name',
        'task',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PreliminaryActivity::class, 'preliminary_activity_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(PreliminaryActivityAction::class, 'preliminary_activity_action_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
