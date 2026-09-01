<?php

namespace App\Models;

use App\Traits\HasDomainScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivityHistory extends Model
{
    use HasDomainScope, HasFactory;

    protected $table = 'project_activity_history';

    protected $fillable = [
        'project_id',
        'user_id',
        'action_type',
        'from_stage_order',
        'from_stage_name',
        'to_stage_order',
        'to_stage_name',
        'notes',
        'action_details',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'notes' => 'string',
        'action_details' => 'string',
    ];

    /**
     * Get the project that owns the activity
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action description
     */
    public function getActionDescription(): string
    {
        $descriptions = [
            'approved' => 'وافق على مرحلة الاعتماد',
            'rejected' => 'رفض المشروع',
            'requires_action' => 'طلب إجراء',
            'need_action' => 'طلب إجراء وإرجاع مرحلي',
            'resubmitted' => 'أعاد تقديم المشروع للاعتماد',
            'sent_for_review' => 'أرسل للمراجعة المالية والفنية',
            'review_completed' => 'أكمل المراجعة المالية والفنية',
            'initiated' => 'إنشاء مسودة مشروع',
            'finalized' => 'إغلاق المسودة وتقديم المشروع للاعتماد',
            'reverted_to_draft' => 'إعادة المشروع إلى المسودة للجهة المنشئة',
            'stage_regression' => 'إرجاع المشروع إلى مرحلة سابقة',
            'returned_for_revision' => 'إرجاع المشروع للجهة المنشئة للتعديل',
            'completed' => 'اكتملت جميع مراحل الاعتماد بنجاح',
        ];

        $action = $descriptions[$this->action_type] ?? $this->action_type;

        if ($this->from_stage_name && $this->to_stage_name) {
            return "{$action} (من {$this->from_stage_name} إلى {$this->to_stage_name})";
        } elseif ($this->from_stage_name) {
            return "{$action} (في مرحلة {$this->from_stage_name})";
        } elseif ($this->to_stage_name) {
            return "{$action} (إلى مرحلة {$this->to_stage_name})";
        }

        return $action;
    }

    /**
     * Get icon class for action type
     */
    public function getIconClass(): string
    {
        $icons = [
            'approved' => 'fas fa-check-circle text-success',
            'rejected' => 'fas fa-times-circle text-danger',
            'requires_action' => 'fas fa-exclamation-triangle text-warning',
            'need_action' => 'fas fa-undo text-warning',
            'resubmitted' => 'fas fa-paper-plane text-info',
            'sent_for_review' => 'fas fa-search text-primary',
            'review_completed' => 'fas fa-clipboard-check text-success',
            'initiated' => 'fas fa-file-alt text-secondary',
            'finalized' => 'fas fa-lock text-primary',
            'reverted_to_draft' => 'fas fa-history text-warning',
            'stage_regression' => 'fas fa-arrow-right text-warning',
            'returned_for_revision' => 'fas fa-undo-alt text-warning',
            'completed' => 'fas fa-flag-checkered text-success',
        ];

        return $icons[$this->action_type] ?? 'fas fa-circle text-secondary';
    }

    protected function setNotesAttribute($value)
    {
        $this->attributes['notes'] = is_array($value) ? implode(', ', $value) : (string) ($value ?? '');
    }

    protected function setActionDetailsAttribute($value)
    {
        $this->attributes['action_details'] = is_array($value) ? implode(', ', $value) : (string) ($value ?? '');
    }
}
