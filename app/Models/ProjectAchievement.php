<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'report_type_id',
        'start_date_gregorian',
        'start_date_hijri',
        'end_date_gregorian',
        'end_date_hijri',
        'duration',
        'previous_achievement',
        'new_achievement',
        'achieved_outputs',
        'achieved_indicators',
        'number_of_beneficiaries',
        'notes_on_beneficiaries',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'start_date_gregorian' => 'date',
        'end_date_gregorian' => 'date',
        'previous_achievement' => 'decimal:2',
        'new_achievement' => 'decimal:2',
        'number_of_beneficiaries' => 'integer',
    ];

    /**
     * Relationship to the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship to the report type.
     */
    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    /**
     * Relationship to the funding details.
     */
    public function fundings(): HasMany
    {
        return $this->hasMany(ProjectAchievementFunding::class);
    }

    /**
     * Relationship to the uploaded documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectAchievementDocument::class);
    }

    /**
     * Relationship to the user who recorded the achievement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
