<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMovementLog extends Model
{
    use HasDomainScope, HasFactory;

    protected $fillable = [
        'project_id',
        'approval_stage_id',
        'user_id',
        'entity',
        'status',
        'action_required',
        'notes',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvalStage(): BelongsTo
    {
        return $this->belongsTo(ApprovalStage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
