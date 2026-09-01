<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImplementationActivityAction extends Model
{
    use HasFactory;

    protected $table = 'implementation_activity_actions';

    protected $fillable = [
        'project_id',
        'implementation_activity_id',
        'action_name',
        'action_weight',
        'start_date',
        'completion_date',
        'verification_means',
    ];

    protected $appends = ['duration'];

    protected $dates = ['start_date', 'completion_date'];

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
     * علاقة النموذج بالتكاليف
     */
    public function costs(): HasMany
    {
        return $this->hasMany(ImplementationActionCost::class, 'implementation_activity_action_id');
    }

    /**
     * علاقة النموذج بالمكلفين
     */
    public function assignees(): HasMany
    {
        return $this->hasMany(ImplementationActionAssignee::class, 'implementation_activity_action_id');
    }

    /**
     * حساب المدة بين تاريخ البداية والانتهاء
     */
    public function getDurationAttribute()
    {
        if ($this->start_date && $this->completion_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->completion_date);

            return $end->diffInDays($start);
        }

        return 0;
    }

    /**
     * قواعد التحقق من صحة البيانات
     */
    public static function validationRules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'implementation_activity_id' => 'required|exists:implementation_activities,id',
            'action_name' => 'required|string|max:255',
            'action_weight' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'completion_date' => 'required|date|after_or_equal:start_date',
            'verification_means' => 'nullable|string',
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
            'action_name.required' => 'حقل اسم الإجراء مطلوب',
            'action_name.string' => 'اسم الإجراء يجب أن يكون نصاً',
            'action_name.max' => 'اسم الإجراء لا يجب أن يتجاوز 255 حرف',
            'action_weight.required' => 'حقل وزن الإجراء مطلوب',
            'action_weight.numeric' => 'وزن الإجراء يجب أن يكون رقماً',
            'action_weight.min' => 'وزن الإجراء يجب أن يكون أكبر من أو يساوي الصفر',
            'action_weight.max' => 'وزن الإجراء يجب أن يكون أقل من أو يساوي 100',
            'start_date.required' => 'حقل تاريخ البداية مطلوب',
            'start_date.date' => 'تاريخ البداية يجب أن يكون تاريخاً صحيحاً',
            'completion_date.required' => 'حقل تاريخ الانتهاء مطلوب',
            'completion_date.date' => 'تاريخ الانتهاء يجب أن يكون تاريخاً صحيحاً',
            'completion_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية',
            'verification_means.string' => 'وسائل التحقق يجب أن تكون نصاً',
        ];
    }
}
