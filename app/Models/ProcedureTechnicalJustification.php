<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureTechnicalJustification extends Model
{
    use HasFactory;

    protected $fillable = [
        'preliminary_procedure_id',
        'planned_end_date',
        'actual_end_date',
        'delay_days',
        'justification',
        'attachments',
        'approval_status',
        'reviewer_notes',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    protected $casts = [
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(PreliminaryProcedure::class, 'preliminary_procedure_id');
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
