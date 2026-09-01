<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'preliminary_activity_id',
        'preliminary_activity_action_id',
        'financial_item',
        'unit',
        'amount',
        'number',
        'total',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(PreliminaryActivity::class, 'preliminary_activity_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(PreliminaryActivityAction::class, 'preliminary_activity_action_id');
    }

    protected static function booted(): void
    {
        static::saving(function ($model) {
            $model->calculateTotal();
        });
    }

    public function calculateTotal(): void
    {
        $amount = $this->amount ?? 0;
        $number = $this->number ?? 0;
        $this->total = $amount * $number;
    }

    public static function validationRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'preliminary_activity_id' => 'required|exists:preliminary_activities,id',
            'preliminary_activity_action_id' => 'required|exists:preliminary_activity_actions,id',
            'financial_item' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'number' => 'required|numeric|min:0',
        ];
    }
}
