<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'approval_stage_id',
        'user_id',
        'status',
        'action_required',
        'notes',
        'submitted_at',
        'completed_at',
        'reviewer_name',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
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
