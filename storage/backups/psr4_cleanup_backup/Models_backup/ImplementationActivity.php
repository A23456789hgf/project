<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImplementationActivity extends Model
{
    use HasFactory;

    protected $table = 'implementation_activities';

    protected $fillable = [
        'project_id',
        'activity_name',
        'activity_weight',
        'outputs',
        'risks',
    ];

    /**
     * علاقة النموذج بالمشروع
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * علاقة النموذج بالإجراءات التنفيذية
     */
    public function actions(): HasMany
    {
        return $this->hasMany(ImplementationActivityAction::class, 'implementation_activity_id');
    }

    /**
     * علاقة النموذج بالمكلفين بالإجراءات
     */
    public function assignees(): HasMany
    {
        return $this->hasMany(ImplementationActionAssignee::class, 'implementation_activity_id');
    }

    /**
     * علاقة النموذج بتكاليف الإجراءات
     */
    public function costs(): HasMany
    {
        return $this->hasMany(ImplementationActionCost::class, 'implementation_activity_id');
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'activity_name' => 'required|string|max:255',
            'activity_weight' => 'required|numeric|min:0|max:100',
            'outputs' => 'nullable|string',
            'risks' => 'nullable|string',
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
            'activity_name.required' => 'حقل اسم النشاط مطلوب',
            'activity_name.string' => 'اسم النشاط يجب أن يكون نصاً',
            'activity_name.max' => 'اسم النشاط لا يجب أن يتجاوز 255 حرف',
            'activity_weight.required' => 'حقل وزن النشاط مطلوب',
            'activity_weight.numeric' => 'وزن النشاط يجب أن يكون رقماً',
            'activity_weight.min' => 'وزن النشاط يجب أن يكون أكبر من أو يساوي الصفر',
            'activity_weight.max' => 'وزن النشاط يجب أن يكون أقل من أو يساوي 100',
            'outputs.string' => 'المخرجات يجب أن تكون نصاً',
            'risks.string' => 'المخاطر يجب أن تكون نصاً',
        ];
    }
}
