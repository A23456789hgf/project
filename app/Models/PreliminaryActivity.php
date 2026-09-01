<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PreliminaryActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function procedures()
    {
        return $this->hasMany(PreliminaryProcedure::class, 'activity_id');
    }

    public function costs()
    {
        return $this->hasMany(PreliminaryCost::class, 'activity_id');
    }

    public function financialSummaries()
    {
        return $this->hasMany(PreliminaryFinancialSummary::class, 'activity_id');
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ActivityAssignment::class, 'assignable');
    }
}
