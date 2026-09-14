<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImplementationActionAssignee extends Model
{
    use HasFactory;

    protected $table = 'implementation_action_assignees';

    protected $fillable = [
        'project_id',
        'implementation_activity_id',
        'implementation_activity_action_id',
        'entity_id',
        'name',
        'task',
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
     * علاقة النموذج بالجهة
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
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
            'entity_id' => 'required|exists:entities,id',
            'name' => 'required|string|max:255',
            'task' => 'required|string',
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
            'entity_id.required' => 'يجب اختيار الجهة',
            'entity_id.exists' => 'الجهة المحددة غير موجودة',
            'name.required' => 'حقل الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصاً',
            'name.max' => 'الاسم لا يجب أن يتجاوز 255 حرف',
            'task.required' => 'حقل المهمة مطلوب',
            'task.string' => 'المهمة يجب أن تكون نصاً',
        ];
    }
}
