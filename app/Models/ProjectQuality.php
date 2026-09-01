<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectQuality extends Model
{
    use HasFactory;

    protected $table = 'project_quality_records';

    protected $fillable = [
        'project_id',
        'record_type',
        'activity_id',
        'procedure_or_action_id',
        'quality_aspect',
        'quality_status',
        'time_status',
        'financial_status',
        'planned_start_date',
        'actual_start_date',
        'planned_end_date',
        'actual_end_date',
        'planned_amount',
        'actual_amount',
        'variance_days',
        'variance_amount',
        'proposed_solution',
        'attachments',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'actual_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'attachments' => 'array',
    ];

    /**
     * Relationship with Project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Preliminary Activity
     */
    public function preliminaryActivity(): BelongsTo
    {
        return $this->belongsTo(PreliminaryActivity::class, 'activity_id');
    }

    /**
     * Executive Activity
     */
    public function executiveActivity(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivity::class, 'activity_id');
    }

    /**
     * Preliminary Procedure
     */
    public function preliminaryProcedure(): BelongsTo
    {
        return $this->belongsTo(PreliminaryProcedure::class, 'procedure_or_action_id');
    }

    /**
     * Executive Action
     */
    public function executiveAction(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivityAction::class, 'procedure_or_action_id');
    }

    /**
     * Filter by quality status
     */
    public function scopeByQualityStatus($query, string $status)
    {
        return $query->where('quality_status', $status);
    }

    /**
     * Filter by quality aspect
     */
    public function scopeByAspect($query, string $aspect)
    {
        return $query->where('quality_aspect', $aspect);
    }

    /**
     * Filter by record type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('record_type', $type);
    }

    /**
     * Negative records only
     */
    public function scopeNegative($query)
    {
        return $query->where('quality_status', 'negative');
    }

    /**
     * Positive records only
     */
    public function scopePositive($query)
    {
        return $query->where('quality_status', 'positive');
    }

    /**
     * Format variance
     */
    public function getFormattedVarianceAttribute(): string
    {
        if ($this->quality_aspect === 'time' && $this->variance_days !== null) {
            return abs($this->variance_days).' '.(abs($this->variance_days) === 1 ? 'يوم' : 'أيام');
        }

        if ($this->quality_aspect === 'financial' && $this->variance_amount !== null) {
            return number_format(abs((float) $this->variance_amount), 2).' ريال';
        }

        return '-';
    }

    /**
     * Has solution?
     */
    public function hasSolution(): bool
    {
        return ! empty($this->proposed_solution);
    }

    /**
     * Has attachments?
     */
    public function hasAttachments(): bool
    {
        return ! empty($this->attachments)
            && is_array($this->attachments)
            && count($this->attachments) > 0;
    }

    /**
     * Attachment count
     */
    public function getAttachmentCountAttribute(): int
    {
        return $this->hasAttachments() ? count($this->attachments) : 0;
    }
}
