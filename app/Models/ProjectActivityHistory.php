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
     * Translate stage name to Arabic
     */
    public static function translateStageName(?string $stageName): ?string
    {
        if (! $stageName) {
            return null;
        }

        $translations = [
            'association' => 'موافقة الجمعية',
            'union' => 'موافقة الاتحاد',
            'committee' => 'موافقة اللجنة',
            'implementation' => 'مرحلة التنفيذ',
            'execution' => 'متابعة التنفيذ',
            'draft' => 'المسودة',
            'pending' => 'قيد الانتظار',
            'review' => 'قيد المراجعة',
            'under_review' => 'قيد المراجعة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'need_action' => 'بحاجة لإجراء',
            'requires_action' => 'طلب إجراء',
            'completed' => 'مكتمل',
            'financial' => 'المراجعة المالية',
            'technical' => 'المراجعة الفنية',
            'financial_review' => 'المراجعة المالية',
            'technical_review' => 'المراجعة الفنية',
            'financial_technical_review' => 'المراجعة المالية والفنية',
            'Association' => 'موافقة الجمعية',
            'Union' => 'موافقة الاتحاد',
            'Committee' => 'موافقة اللجنة',
            'Implementation' => 'مرحلة التنفيذ',
            'Draft' => 'المسودة',
            'Pending' => 'قيد الانتظار',
            'Under Review' => 'قيد المراجعة',
            'Approved' => 'معتمد',
            'Rejected' => 'مرفوض',
            'Need Action' => 'بحاجة لإجراء',
            'Requires Action' => 'طلب إجراء',
            'Completed' => 'مكتمل',
            'Financial' => 'المراجعة المالية',
            'Technical' => 'المراجعة الفنية',
            'basic-data' => 'البيانات الأساسية',
            'technical-details' => 'التفاصيل الفنية والمالية',
            'approval-workflow' => 'سير عمل الاعتماد',
        ];

        $cleaned = trim($stageName);
        $lower = strtolower($cleaned);

        return $translations[$cleaned] ?? $translations[$lower] ?? $stageName;
    }

    /**
     * Get human-readable action description in Arabic
     */
    public function getActionDescription(): string
    {
        $statusDescriptions = [
            'financial_technical_review' => 'أرسل للمراجعة المالية والفنية',
            'financial_review' => 'أرسل للمراجعة المالية',
            'technical_review' => 'أرسل للمراجعة الفنية',
            'review_completed' => 'أكمل المراجعة المالية والفنية',
            'financial_review_completed' => 'أكمل المراجعة المالية',
            'technical_review_completed' => 'أكمل المراجعة الفنية',
            'need_action' => 'طلب إجراء وإرجاع مرحلي',
            'requires_action' => 'طلب إجراء على المشروع',
            'rejected' => 'رفض المشروع',
            'resubmitted' => 'أعاد تقديم المشروع للاعتماد',
            'approved' => 'وافق على مرحلة الاعتماد',
            'completed' => 'اكتملت جميع مراحل الاعتماد بنجاح',
            'returned_to_previous' => 'إرجاع المشروع إلى المرحلة السابقة',
            'returned_for_revision' => 'إرجاع المشروع للجهة المنشئة للتعديل',
        ];

        $descriptions = [
            'approval' => 'وافق على مرحلة الاعتماد',
            'approved' => 'وافق على مرحلة الاعتماد',
            'project_completion' => 'اكتملت جميع مراحل الاعتماد بنجاح',
            'completed' => 'اكتملت جميع مراحل الاعتماد بنجاح',
            'stage_progression' => 'الانتقال للمرحلة التالية',
            'stage_regression' => 'إرجاع المشروع إلى مرحلة سابقة',
            'reverted_to_draft' => 'إعادة المشروع إلى المسودة للجهة المنشئة',
            'rejected' => 'رفض المشروع',
            'rejection' => 'رفض المشروع',
            'requires_action' => 'طلب إجراء على المشروع',
            'need_action' => 'طلب إجراء وإرجاع مرحلي',
            'action_required' => 'مطلوب إجراء على المشروع',
            'resubmitted' => 'أعاد تقديم المشروع للاعتماد',
            'resubmission' => 'أعاد تقديم المشروع للاعتماد',
            'sent_for_review' => 'أرسل للمراجعة المالية والفنية',
            'financial_technical_review' => 'أرسل للمراجعة المالية والفنية',
            'review_completed' => 'أكمل المراجعة المالية والفنية',
            'financial_review_completed' => 'أكمل المراجعة المالية',
            'technical_review_completed' => 'أكمل المراجعة الفنية',
            'initiated' => 'إنشاء مسودة مشروع',
            'initiation' => 'إنشاء مسودة مشروع',
            'finalized' => 'إغلاق المسودة وتقديم المشروع للاعتماد',
            'returned_for_revision' => 'إرجاع المشروع للجهة المنشئة للتعديل',
            'returned_to_previous' => 'إرجاع المشروع إلى المرحلة السابقة',
            'returned' => 'إرجاع المشروع',
            'approved_to_implementation' => 'وافق ونقل المشروع إلى مرحلة التنفيذ',
            'execution_approved' => 'وافق على سجل التنفيذ',
            'execution_rejected' => 'رفض سجل التنفيذ',
            'created' => 'إنشاء المشروع',
            'project_created' => 'إنشاء المشروع',
            'updated' => 'تحديث بيانات المشروع',
            'project_updated' => 'تحديث بيانات المشروع',
            'deleted' => 'حذف المشروع',
            'project_deleted' => 'حذف المشروع',
            'assigned' => 'تكليف بمهمة جديدة',
            'task_created' => 'إنشاء مهمة جديدة',
            'task_updated' => 'تحديث المهمة',
            'task_deleted' => 'حذف المهمة',
            'task_completed' => 'إكمال المهمة',
            'attachment_uploaded' => 'إرفاق ملف',
            'attachment_deleted' => 'حذف مرفق',
            'comment_posted' => 'إضافة تعليق',
            'document_note_added' => 'إضافة ملاحظة على الوثيقة',
            'execution_note_added' => 'إضافة ملاحظة على التنفيذ',
            'memo_added' => 'إضافة مذكرة إدارية',
            'memo_signed' => 'توقيع المذكرة الإدارية',
            'memo_deleted' => 'حذف المذكرة الإدارية',
            'status_changed' => 'تغيير حالة المشروع',
            'project_referral' => 'إحالة المشروع',
            'referral' => 'إحالة المشروع',
            'referral_response' => 'الرد على الإحالة',
            'responded' => 'الرد على الإحالة',
            'stage_transition' => 'انتقال لمرحلة جديدة',
            'review_assigned' => 'تعيين مراجعة جديدة',
            'old_project_assigned' => 'إسناد مشروع قديم',
            'old_project_data_completed' => 'استكمال بيانات المشروع القديم',
            'old_project_achievement' => 'تسجيل إنجاز للمشروع القديم',
        ];

        $action = null;
        if (! empty($this->status) && isset($statusDescriptions[$this->status])) {
            $action = $statusDescriptions[$this->status];
        }

        if (! $action) {
            $typeKey = strtolower(trim((string) $this->action_type));
            $action = $descriptions[$this->action_type] ?? $descriptions[$typeKey] ?? null;
        }

        if (! $action) {
            $action = match ($this->action_type) {
                'approval' => 'وافق على مرحلة الاعتماد',
                'project_completion' => 'اكتملت جميع مراحل الاعتماد بنجاح',
                'stage_progression' => 'الانتقال للمرحلة التالية',
                'stage_regression' => 'إرجاع المشروع إلى مرحلة سابقة',
                default => 'إجراء على المشروع: '.str_replace(['_', '-'], ' ', (string) $this->action_type)
            };
        }

        $fromStage = self::translateStageName($this->from_stage_name);
        $toStage = self::translateStageName($this->to_stage_name);

        if ($fromStage && $toStage) {
            return "{$action} (من مرحلة {$fromStage} إلى مرحلة {$toStage})";
        } elseif ($fromStage) {
            return "{$action} (في مرحلة {$fromStage})";
        } elseif ($toStage) {
            return "{$action} (إلى مرحلة {$toStage})";
        }

        return $action;
    }

    /**
     * Get the associated Arabic page name
     */
    public function getPageName(): string
    {
        return match ($this->action_type) {
            'created', 'project_created' => 'تفاصيل المشروع (البيانات الأساسية)',
            'updated', 'project_updated' => 'تفاصيل المشروع (التفاصيل الفنية والمالية)',
            'approval', 'approved', 'rejected', 'requires_action', 'need_action', 'resubmitted',
            'sent_for_review', 'financial_technical_review', 'review_completed', 'financial_review_completed',
            'technical_review_completed', 'initiated', 'finalized', 'reverted_to_draft',
            'stage_regression', 'stage_progression', 'returned_for_revision', 'returned_to_previous',
            'completed', 'project_completion', 'approved_to_implementation' => 'تفاصيل المشروع (سير عمل الاعتماد)',
            'execution_approved', 'execution_rejected' => 'متابعة التنفيذ',
            'old_project_assigned', 'old_project_data_completed' => 'استكمال بيانات المشروع',
            'old_project_achievement' => 'إنجازات المشاريع السابقة',
            'project_referral', 'referral', 'responded', 'referral_response' => 'إحالات المشاريع',
            'task_created', 'task_updated', 'task_deleted', 'task_completed', 'assigned' => 'إدارة المهام',
            'memo_added', 'memo_signed', 'memo_deleted' => 'المذكرات الإدارية',
            default => 'تفاصيل المشروع',
        };
    }

    /**
     * Get icon class for action type
     */
    public function getIconClass(): string
    {
        $icons = [
            'approval' => 'fas fa-check-circle text-success',
            'approved' => 'fas fa-check-circle text-success',
            'approved_to_implementation' => 'fas fa-play-circle text-success',
            'project_completion' => 'fas fa-flag-checkered text-success',
            'completed' => 'fas fa-flag-checkered text-success',
            'stage_progression' => 'fas fa-arrow-right text-info',
            'stage_regression' => 'fas fa-arrow-left text-warning',
            'rejected' => 'fas fa-times-circle text-danger',
            'requires_action' => 'fas fa-exclamation-triangle text-warning',
            'need_action' => 'fas fa-undo text-warning',
            'resubmitted' => 'fas fa-paper-plane text-info',
            'sent_for_review' => 'fas fa-search text-primary',
            'financial_technical_review' => 'fas fa-search text-primary',
            'review_completed' => 'fas fa-clipboard-check text-success',
            'financial_review_completed' => 'fas fa-file-invoice-dollar text-success',
            'technical_review_completed' => 'fas fa-tools text-success',
            'initiated' => 'fas fa-file-alt text-secondary',
            'finalized' => 'fas fa-lock text-primary',
            'reverted_to_draft' => 'fas fa-history text-warning',
            'returned_for_revision' => 'fas fa-undo-alt text-warning',
            'returned_to_previous' => 'fas fa-backward text-warning',
            'execution_approved' => 'fas fa-check-double text-success',
            'execution_rejected' => 'fas fa-ban text-danger',
            'created' => 'fas fa-plus-circle text-success',
            'project_created' => 'fas fa-plus-circle text-success',
            'updated' => 'fas fa-edit text-primary',
            'project_updated' => 'fas fa-edit text-primary',
            'assigned' => 'fas fa-user-check text-info',
            'task_created' => 'fas fa-tasks text-primary',
            'task_updated' => 'fas fa-edit text-info',
            'task_deleted' => 'fas fa-trash text-danger',
            'task_completed' => 'fas fa-check text-success',
            'project_referral' => 'fas fa-exchange-alt text-purple',
            'referral' => 'fas fa-exchange-alt text-purple',
            'referral_response' => 'fas fa-reply text-success',
            'responded' => 'fas fa-reply text-success',
            'old_project_assigned' => 'fas fa-building text-warning',
            'old_project_data_completed' => 'fas fa-check-circle text-success',
            'old_project_achievement' => 'fas fa-trophy text-info',
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
