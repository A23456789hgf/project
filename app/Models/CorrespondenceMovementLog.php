<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrespondenceMovementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'correspondence_id',
        'user_id',
        'action_type',
        'action_description',
        'action_details',
        'related_ids',
        'from_entity',
        'to_entity',
        'action_date',
    ];

    protected $casts = [
        'action_details' => 'array',
        'related_ids' => 'array',
        'action_date' => 'datetime',
    ];

    /**
     * علاقة مع المراسلة
     */
    public function correspondence(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class);
    }

    /**
     * علاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الحصول على تسمية نوع الإجراء
     */
    public function getActionTypeLabelAttribute(): string
    {
        $labels = [
            'create' => 'إنشاء مراسلة',
            'reply' => 'إضافة رد',
            'referral' => 'إحالة',
            'forward' => 'توجيه داخلي',
            'status_change' => 'تغيير الحالة',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'restore' => 'استعادة',
            'close' => 'إغلاق',
            'reopen' => 'إعادة فتح',
            'attachment_add' => 'إضافة مرفق',
            'attachment_remove' => 'حذف مرفق',
            'deadline_set' => 'تعيين موعد نهائي',
            'deadline_extend' => 'تمديد الموعد النهائي',
            'priority_change' => 'تغيير الأولوية',
        ];

        return $labels[$this->action_type] ?? $this->action_type;
    }

    /**
     * الحصول على التاريخ المنسق
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->action_date->format('Y-m-d H:i:s');
    }

    /**
     * الحصول على معلومات تفصيلية
     */
    public function getFormattedDetailsAttribute(): string
    {
        if (empty($this->action_details)) {
            return '';
        }

        $details = [];
        foreach ($this->action_details as $key => $value) {
            if (! empty($value)) {
                $details[] = ucfirst($key).': '.$value;
            }
        }

        return implode(' | ', $details);
    }
}
