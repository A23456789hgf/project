<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

class ExecutiveActivityAction extends Model
{
    use HasDomainScope;

    protected $fillable = [
        'project_id', 'executive_activity_id', 'action', 'weight',
        'start_date', 'end_date', 'start_date_hijri', 'end_date_hijri', 'verification_means',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->weight === null) {
                $model->weight = 0;
            }

            $isDraftSave = request() && (request()->input('status') === 'draft' || in_array(request()->input('navigation_direction'), ['draft', 'prev', 'back']));

            // Validate dates against project timeframe
            if (! $isDraftSave && $model->project_id && ($model->start_date || $model->end_date)) {
                $project = Project::find($model->project_id);

                if ($project && $project->start_date_gregorian && $project->end_date_gregorian) {
                    $projectStart = Carbon::parse($project->start_date_gregorian);
                    $projectEnd = Carbon::parse($project->end_date_gregorian);

                    if ($model->start_date) {
                        $actionStart = Carbon::parse($model->start_date);
                        if ($actionStart->lt($projectStart) || $actionStart->gt($projectEnd)) {
                            throw ValidationException::withMessages([
                                'start_date' => "تاريخ بداية الإجراء ({$model->start_date}) يجب أن يكون بين تاريخ بداية المشروع ({$project->start_date_gregorian}) وتاريخ نهايته ({$project->end_date_gregorian})",
                            ]);
                        }
                    }

                    if ($model->end_date) {
                        $actionEnd = Carbon::parse($model->end_date);
                        if ($actionEnd->lt($projectStart) || $actionEnd->gt($projectEnd)) {
                            throw ValidationException::withMessages([
                                'end_date' => "تاريخ نهاية الإجراء ({$model->end_date}) يجب أن يكون بين تاريخ بداية المشروع ({$project->start_date_gregorian}) وتاريخ نهايته ({$project->end_date_gregorian})",
                            ]);
                        }
                    }
                }
            }

            // Convert Gregorian dates to Hijri
            if ($model->start_date) {
                $model->start_date_hijri = self::gregorianToHijri($model->start_date);
            }

            if ($model->end_date) {
                $model->end_date_hijri = self::gregorianToHijri($model->end_date);
            }
        });
    }

    /**
     * Convert Gregorian date to Hijri date
     */
    private static function gregorianToHijri($gregorianDate)
    {
        try {
            $date = Carbon::parse($gregorianDate);
            $arPHP = new Arabic;

            // Get Hijri date components
            $hijri = $arPHP->date('d/m/Y', $date->timestamp, 1); // 1 = Islamic calendar

            return $hijri;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivity::class, 'executive_activity_id');
    }

    public function assignedEntities(): HasMany
    {
        return $this->hasMany(ExecutiveActionAssigned::class, 'executive_activity_action_id');
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ExecutiveActionCost::class, 'executive_activity_action_id');
    }

    public function financialSummaries(): HasMany
    {
        return $this->hasMany(ExecutiveFinancialSummary::class, 'executive_activity_action_id');
    }

    public function execution(): HasOne
    {
        return $this->hasOne(ProjectExecution::class, 'executive_activity_action_id')->latest();
    }

    public function executions(): HasMany
    {
        return $this->hasMany(ProjectExecution::class, 'executive_activity_action_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'executive_activity_action_id');
    }

    public function technicalJustification()
    {
        return $this->morphOne(ExecutionDelayExplanation::class, 'execution', 'execution_type', 'execution_type_id');
    }

    public function technicalJustifications()
    {
        return $this->morphMany(ExecutionDelayExplanation::class, 'execution', 'execution_type', 'execution_type_id')->orderByDesc('created_at');
    }

    public function budgetJustification()
    {
        return $this->morphOne(ExecutionBudgetJustification::class, 'execution', 'execution_type', 'execution_type_id');
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ActivityAssignment::class, 'assignable');
    }

    public function getActionNameAttribute(): ?string
    {
        return $this->attributes['action'] ?? null;
    }

    public function setActionNameAttribute(?string $value): void
    {
        $this->attributes['action'] = $value;
    }
}
