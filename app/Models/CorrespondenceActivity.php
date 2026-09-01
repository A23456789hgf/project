<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorrespondenceActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'correspondence_id',
        'user_id',
        'action',
        'notes',
    ];

    public function correspondence()
    {
        return $this->belongsTo(Correspondence::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action label in Arabic
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => 'إنشاء مراسلة',
            'replied' => 'إضافة رد',
            'returned' => 'إرجاع المراسلة',
            'referred' => 'إحالة المراسلة',
            'closed' => 'إغلاق المراسلة',
            'restored' => 'استعادة المراسلة',
            'updated' => 'تحديث البيانات',
        ];

        return $labels[$this->action] ?? $this->action;
    }
}
