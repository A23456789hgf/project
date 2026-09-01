<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionBudgetJustification extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_type_id',
        'execution_type',
        'planned_amount',
        'actual_amount',
        'overage_amount',
        'justification',
        'attachments',
        'approval_status',
        'reviewer_notes',
        'rejection_attachment',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'overage_amount' => 'decimal:2',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function execution()
    {
        return $this->morphTo('execution', 'execution_type', 'execution_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }
}
