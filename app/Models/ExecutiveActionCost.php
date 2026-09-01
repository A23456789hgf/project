<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveActionCost extends Model
{
    protected $fillable = [
        'project_id',
        'executive_activity_id',
        'executive_activity_action_id',
        'financial_item_id', // تم التغيير من financial_item إلى financial_item_id
        'unit_id',
        'amount',
        'quantity',
        'total',
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
     * العلاقة مع البند المالي
     */
    public function financialItem(): BelongsTo
    {
        return $this->belongsTo(FinancialItem::class, 'financial_item_id');
    }

    /**
     * العلاقة مع الوحدة
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    /**
     * الوصول السهل لاسم البند المالي
     */
    public function getFinancialItemNameAttribute(): string
    {
        return $this->financialItem->name ?? '';
    }

    /**
     * حساب المجموع تلقائيًا عند الحفظ إذا لم يتم تقديمه
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (ExecutiveActionCost $model) {
            if ((is_null($model->total) || $model->total === 0) && $model->amount && $model->quantity) {
                $model->total = $model->amount * $model->quantity;
            }
        });
    }

    /**
     * نطاق الاستعلام للحصول على التكاليف حسب البند المالي
     */
    public function scopeByFinancialItem($query, int $financialItemId)
    {
        return $query->where('financial_item_id', $financialItemId);
    }

    /**
     * نطاق الاستعلام للحصول على التكاليف حسب النشاط
     */
    public function scopeByActivity($query, int $activityId)
    {
        return $query->where('executive_activity_id', $activityId);
    }

    /**
     * نطاق الاستعلام للحصول على التكاليف حسب الإجراء
     */
    public function scopeByAction($query, int $actionId)
    {
        return $query->where('executive_activity_action_id', $actionId);
    }
}
