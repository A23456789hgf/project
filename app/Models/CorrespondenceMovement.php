<?php

// app/Models/CorrespondenceMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrespondenceMovement extends Model
{
    protected $fillable = [
        'correspondence_id',
        'user_id',
        'from_entity_id',
        'to_entity_id',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function correspondence(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'from_entity_id');
    }

    public function toEntity(): BelongsTo
    {
        return $this->belongsTo(InternalEntity::class, 'to_entity_id');
    }

    // تصنيف أنواع الحركات لأغراض العرض
    public function getDisplayTypeAttribute(): string
    {
        $types = [
            'create' => 'إنشاء',
            'reply' => 'رد',
            'referral' => 'إحالة',
            'forward' => 'توجيه',
            'return' => 'إرجاع',
            'close' => 'إغلاق',
            'status_change' => 'تغيير حالة',
            'acknowledge' => 'تأكيد استلام',
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getIconAttribute(): string
    {
        $icons = [
            'create' => '📄',
            'reply' => '↪️',
            'referral' => '🔄',
            'forward' => '↪️',
            'return' => '↩️',
            'close' => '🔒',
            'status_change' => '🔄',
            'acknowledge' => '✅',
        ];

        return $icons[$this->type] ?? 'ℹ️';
    }
}
