<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementationActionCost extends Model
{
    use HasFactory;

    protected $table = 'implementation_action_costs';

    protected $fillable = [
        'project_id',
        'implementation_activity_id',
        'implementation_activity_action_id',
        'financial_item',
        'unit',
        'unit_price',
        'quantity',
        'total',
    ];

    /**
     * علاقة النموذج بالمشروع
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * علاقة النموذج بالنشاط التنفيذي
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(ImplementationActivity::class, 'implementation_activity_id');
    }

    /**
     * علاقة النموذج بالإجراء التنفيذي
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(ImplementationActivityAction::class, 'implementation_activity_action_id');
    }

    /**
     * إعداد الإجمالي تلقائياً قبل الحفظ
     */
    protected static function booted(): void
    {
        static::saving(function ($model) {
            $model->calculateTotal();
        });
    }

    /**
     * حساب الإجمالي
     */
    public function calculateTotal(): void
    {
        $unitPrice = $this->unit_price ?? 0;
        $quantity = $this->quantity ?? 0;
        $this->total = $unitPrice * $quantity;
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'implementation_activity_id' => 'required|exists:implementation_activities,id',
            'implementation_activity_action_id' => 'required|exists:implementation_activity_actions,id',
            'financial_item' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
        ];
    }

    /**
     * رسائل الأخطاء
     */
    public static function validationMessages(): array
    {
        return [
            'project_id.required' => 'يجب اختيار المشروع',
            'project_id.exists' => 'المشروع المحدد غير موجود',
            'implementation_activity_id.required' => 'يجب اختيار النشاط التنفيذي',
            'implementation_activity_id.exists' => 'النشاط التنفيذي المحدد غير موجود',
            'implementation_activity_action_id.required' => 'يجب اختيار الإجراء التنفيذي',
            'implementation_activity_action_id.exists' => 'الإجراء التنفيذي المحدد غير موجود',
            'financial_item.required' => 'حقل البند المالي مطلوب',
            'financial_item.string' => 'البند المالي يجب أن يكون نصاً',
            'financial_item.max' => 'البند المالي لا يجب أن يتجاوز 255 حرف',
            'unit.required' => 'حقل الوحدة مطلوب',
            'unit.string' => 'الوحدة يجب أن تكون نصاً',
            'unit.max' => 'الوحدة لا يجب أن تتجاوز 50 حرف',
            'unit_price.required' => 'حقل سعر الوحدة مطلوب',
            'unit_price.numeric' => 'سعر الوحدة يجب أن يكون رقماً',
            'unit_price.min' => 'سعر الوحدة يجب أن يكون أكبر من أو يساوي الصفر',
            'quantity.required' => 'حقل الكمية مطلوب',
            'quantity.numeric' => 'الكمية يجب أن تكون رقماً',
            'quantity.min' => 'الكمية يجب أن تكون أكبر من أو يساوي الصفر',
        ];
    }
}
