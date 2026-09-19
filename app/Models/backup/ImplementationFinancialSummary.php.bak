<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementationFinancialSummary extends Model
{
    use HasFactory;

    protected $table = 'implementation_financial_summaries';

    protected $fillable = [
        'project_id',
        'implementation_activity_id',
        'implementation_activity_action_id',
        'implementation_action_cost_id',
        'financial_item',
        'unit',
        'unit_price',
        'quantity',
        'total',
        'summary_type', // نوع الملخص (activity, action, cost)
        'summary_description',
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
     * علاقة النموذج بتكلفة الإجراء التنفيذي
     */
    public function actionCost(): BelongsTo
    {
        return $this->belongsTo(ImplementationActionCost::class, 'implementation_action_cost_id');
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
     * دالة لإنشاء ملخص مالي من تكاليف الإجراءات
     */
    public static function createFromActionCosts($projectId, $activityId = null, $actionId = null)
    {
        $query = ImplementationActionCost::where('project_id', $projectId);

        if ($activityId) {
            $query->where('implementation_activity_id', $activityId);
        }

        if ($actionId) {
            $query->where('implementation_activity_action_id', $actionId);
        }

        $costs = $query->get();

        // تجميع التكاليف حسب البند المالي والوحدة
        $groupedCosts = $costs->groupBy(function ($cost) {
            return $cost->financial_item.'|'.$cost->unit;
        });

        foreach ($groupedCosts as $key => $costGroup) {
            $parts = explode('|', $key);
            $financialItem = $parts[0];
            $unit = $parts[1];

            $totalQuantity = $costGroup->sum('quantity');
            $avgUnitPrice = $costGroup->avg('unit_price');
            $totalAmount = $costGroup->sum('total');

            self::updateOrCreate([
                'project_id' => $projectId,
                'implementation_activity_id' => $activityId,
                'implementation_activity_action_id' => $actionId,
                'financial_item' => $financialItem,
                'unit' => $unit,
                'summary_type' => $actionId ? 'action' : ($activityId ? 'activity' : 'project'),
            ], [
                'unit_price' => $avgUnitPrice,
                'quantity' => $totalQuantity,
                'total' => $totalAmount,
                'summary_description' => 'ملخص تلقائي للتكاليف',
            ]);
        }
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'implementation_activity_id' => 'nullable|exists:implementation_activities,id',
            'implementation_activity_action_id' => 'nullable|exists:implementation_activity_actions,id',
            'implementation_action_cost_id' => 'nullable|exists:implementation_action_costs,id',
            'financial_item' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'summary_type' => 'required|in:project,activity,action,cost',
            'summary_description' => 'nullable|string',
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
            'implementation_activity_id.exists' => 'النشاط التنفيذي المحدد غير موجود',
            'implementation_activity_action_id.exists' => 'الإجراء التنفيذي المحدد غير موجود',
            'implementation_action_cost_id.exists' => 'تكلفة الإجراء التنفيذي المحددة غير موجودة',
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
            'summary_type.required' => 'حقل نوع الملخص مطلوب',
            'summary_type.in' => 'نوع الملخص يجب أن يكون أحد القيم التالية: project, activity, action, cost',
            'summary_description.string' => 'وصف الملخص يجب أن يكون نصاً',
        ];
    }
}
