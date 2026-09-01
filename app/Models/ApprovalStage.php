<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ProjectApprovalRequest::class);
    }

    public function movementLogs(): HasMany
    {
        return $this->hasMany(ProjectMovementLog::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'current_approval_stage_id');
    }
}
