<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveFinancialSummary extends Model
{
    protected $fillable = [
        'project_id',
        'executive_activity_id',
        'executive_activity_action_id',
        'executive_action_cost_id',
        'financial_item_id', // مفتاح أجنبي يشير إلى البند المالي
        'amount',
    ];

    /**
     * العلاقة مع المشروع
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * العلاقة مع النشاط التنفيذي
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivity::class, 'executive_activity_id');
    }

    /**
     * العلاقة مع الإجراء التنفيذي
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActivityAction::class, 'executive_activity_action_id');
    }

    /**
     * العلاقة مع تكلفة الإجراء التنفيذي
     */
    public function cost(): BelongsTo
    {
        return $this->belongsTo(ExecutiveActionCost::class, 'executive_action_cost_id');
    }

    /**
     * العلاقة مع البند المالي
     */
    public function financialItem(): BelongsTo
    {
        return $this->belongsTo(FinancialItem::class, 'financial_item_id');
    }

    /**
     * وصول سهل لاسم البند المالي
     */
    public function getFinancialItemNameAttribute(): string
    {
        return $this->financialItem->name ?? '';
    }
}
