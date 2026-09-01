<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanProjectActivityAction extends Model
{
    use HasDomainScope, HasFactory;

    protected $fillable = [
        'plan_project_activity_id',
        'name',
        'weight',
        'start_date_g',
        'start_date_h',
        'end_date_g',
        'end_date_h',
        'duration',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'start_date_g' => 'date',
        'end_date_g' => 'date',
    ];

    /**
     * Get the activity that owns the action.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(PlanProjectActivity::class, 'plan_project_activity_id');
    }
}
