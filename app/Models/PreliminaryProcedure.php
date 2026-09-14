<?php

namespace App\Models;

use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

class PreliminaryProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'activity_id',
        'project_entities_id',
        'procedure_name',
        'weight',
        'start_date',
        'end_date',
        'start_date_hijri',
        'end_date_hijri',
        'duration_days',
        'verification_means',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $appends = ['duration'];

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
                        $procedureStart = Carbon::parse($model->start_date);
                        if ($procedureStart->lt($projectStart) || $procedureStart->gt($projectEnd)) {
                            throw ValidationException::withMessages([
                                'start_date' => "تاريخ بداية الإجراء ({$model->start_date}) يجب أن يكون بين تاريخ بداية المشروع ({$project->start_date_gregorian}) وتاريخ نهايته ({$project->end_date_gregorian})",
                            ]);
                        }
                    }

                    if ($model->end_date) {
                        $procedureEnd = Carbon::parse($model->end_date);
                        if ($procedureEnd->lt($projectStart) || $procedureEnd->gt($projectEnd)) {
                            throw ValidationException::withMessages([
                                'end_date' => "تاريخ نهاية الإجراء ({$model->end_date}) يجب أن يكون بين تاريخ بداية المشروع ({$project->start_date_gregorian}) وتاريخ نهايته ({$project->end_date_gregorian})",
                            ]);
                        }
                    }
                }
            }

            if ($model->start_date && $model->end_date) {
                $start = Carbon::parse($model->start_date);
                $end = Carbon::parse($model->end_date);
                $model->duration_days = $start->diffInDays($end);
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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Accessor for duration attribute (maps to duration_days)
     */
    public function getDurationAttribute()
    {
        return $this->duration_days;
    }

    /**
     * Mutator for duration attribute (maps to duration_days)
     */
    public function setDurationAttribute($value)
    {
        $this->attributes['duration_days'] = $value;
    }

    public function getProcedureAttribute(): ?string
    {
        return $this->attributes['procedure_name'] ?? null;
    }

    public function setProcedureAttribute(?string $value): void
    {
        $this->attributes['procedure_name'] = $value;
    }

    public function activity()
    {
        return $this->belongsTo(PreliminaryActivity::class, 'activity_id');
    }

    public function costs()
    {
        return $this->hasMany(PreliminaryCost::class, 'procedure_id');
    }

    public function financialSummaries()
    {
        return $this->hasMany(PreliminaryFinancialSummary::class, 'procedure_id');
    }

    public function executions()
    {
        return $this->hasMany(PreliminaryProcedureExecution::class, 'preliminary_procedure_id');
    }

    public function execution()
    {
        return $this->hasOne(PreliminaryProcedureExecution::class, 'preliminary_procedure_id');
    }

    public function budgetJustification()
    {
        return $this->morphOne(ExecutionBudgetJustification::class, 'execution', 'execution_type', 'execution_type_id');
    }

    public function budgetJustifications()
    {
        return $this->morphMany(ExecutionBudgetJustification::class, 'execution', 'execution_type', 'execution_type_id');
    }

    public function procedureBudgetJustification()
    {
        return $this->hasOne(ProcedureBudgetJustification::class, 'preliminary_procedure_id');
    }

    public function technicalJustification()
    {
        return $this->hasOne(ProcedureTechnicalJustification::class, 'preliminary_procedure_id');
    }

    public function technicalJustifications()
    {
        return $this->hasMany(ProcedureTechnicalJustification::class, 'preliminary_procedure_id')->orderByDesc('created_at');
    }

    public function assignedEntity()
    {
        return $this->belongsTo(ProjectEntity::class, 'project_entities_id');
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ActivityAssignment::class, 'assignable');
    }
}
