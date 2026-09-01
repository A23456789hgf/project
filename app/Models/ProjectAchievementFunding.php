<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAchievementFunding extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_achievement_id',
        'authority_id',          // تمت الإضافة
        'funding_entity',        // يمكن الاحتفاظ به مؤقتاً للتوافق مع البيانات القديمة
        'total_funding',
        'previous_disbursement',
        'previous_remaining_disbursement',
        'new_disbursement',
        'disbursement_percentage',
    ];

    protected $casts = [
        'total_funding' => 'decimal:2',
        'previous_disbursement' => 'decimal:2',
        'previous_remaining_disbursement' => 'decimal:2',
        'new_disbursement' => 'decimal:2',
        'disbursement_percentage' => 'decimal:2',
        'authority_id' => 'integer', // اختياري
    ];

    /**
     * Relationship to the main achievement.
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(ProjectAchievement::class, 'project_achievement_id');
    }

    /**
     * Relationship to the authority (funding entity).
     */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class, 'authority_id');
    }

    /**
     * Accessor to get the funding entity name either from authority relation or from old text field.
     */
    public function getFundingEntityNameAttribute(): string
    {
        return $this->authority?->agency_name ?? $this->funding_entity ?? 'غير محدد';
    }
}
