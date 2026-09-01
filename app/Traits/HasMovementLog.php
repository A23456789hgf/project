<?php

namespace App\Traits;

use App\Models\CorrespondenceMovementLog;
use Illuminate\Support\Facades\Schema;

trait HasMovementLog
{
    /**
     * تسجيل إجراء في سجل الحركة
     */
    public function logMovement(string $actionType, string $description, array $details = [], array $relatedIds = []): void
    {
        if (! Schema::hasTable('correspondence_movement_logs')) {
            // الجدول غير موجود، لا نفعل شيء
            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        CorrespondenceMovementLog::create([
            'correspondence_id' => $this->id,
            'user_id' => $user->id,
            'action_type' => $actionType,
            'action_description' => $description,
            'action_details' => $details,
            'related_ids' => $relatedIds,
            'from_entity' => $this->getFromEntityInfo(),
            'to_entity' => $this->getToEntityInfo(),
            'action_date' => now(),
        ]);
    }

    /**
     * الحصول على معلومات الجهة المرسلة
     */
    protected function getFromEntityInfo(): ?string
    {
        return $this->senderEntity ? $this->senderEntity->name.' ('.$this->senderEntity->entity_code.')' : null;
    }

    /**
     * الحصول على معلومات الجهة المستلمة
     */
    protected function getToEntityInfo(): ?string
    {
        return $this->recipientEntity ? $this->recipientEntity->name.' ('.$this->recipientEntity->entity_code.')' : null;
    }

    /**
     * الحصول على سجل الحركة الكامل
     */
    public function getMovementLog()
    {
        if (! Schema::hasTable('correspondence_movement_logs')) {
            return collect();
        }

        return $this->movementLogs()
            ->with('user')
            ->orderBy('action_date', 'desc')
            ->get();
    }

    /**
     * الحصول على ملخص سجل الحركة
     */
    public function getMovementSummary(): array
    {
        $logs = $this->getMovementLog();

        return [
            'total_actions' => $logs->count(),
            'replies_count' => $logs->where('action_type', 'reply')->count(),
            'referrals_count' => $logs->where('action_type', 'referral')->count(),
            'forwards_count' => $logs->where('action_type', 'forward')->count(),
            'status_changes' => $logs->where('action_type', 'status_change')->count(),
            'last_action' => $logs->first()?->action_description ?? 'لا توجد إجراءات',
            'last_action_date' => $logs->first()?->action_date,
        ];
    }

    /**
     * الحصول على الجدول الزمني للحركة لمجموعة من المراسلات
     */
    public static function getThreadMovementTimeline(array $correspondenceIds): array
    {
        if (! Schema::hasTable('correspondence_movement_logs')) {
            return [];
        }

        $timeline = [];
        $logs = CorrespondenceMovementLog::whereIn('correspondence_id', $correspondenceIds)
            ->with(['user', 'correspondence'])
            ->orderBy('action_date', 'asc')
            ->get();

        foreach ($logs as $log) {
            $timeline[] = [
                'id' => $log->id,
                'date' => $log->action_date,
                'action_label' => $log->action_type_label ?? $log->action_type,
                'action_description' => $log->action_description,
                'user_name' => $log->user->name ?? 'غير معروف',
                'from_entity' => $log->from_entity,
                'to_entity' => $log->to_entity,
                'action_details' => $log->action_details,
                'action_icon' => self::getActionIcon($log->action_type),
                'action_color' => self::getActionColor($log->action_type),
                'type' => $log->action_type,
                'correspondence_id' => $log->correspondence_id,
            ];
        }

        return $timeline;
    }

    /**
     * أيقونة الإجراء
     */
    public static function getActionIcon(string $actionType): string
    {
        $icons = [
            'create' => 'fas fa-plus-circle',
            'reply' => 'fas fa-reply',
            'referral' => 'fas fa-share',
            'forward' => 'fas fa-forward',
            'status_change' => 'fas fa-exchange-alt',
            'update' => 'fas fa-edit',
            'close' => 'fas fa-lock',
            'reopen' => 'fas fa-unlock',
            'attachment_add' => 'fas fa-paperclip',
            'deadline_set' => 'fas fa-calendar-plus',
        ];

        return $icons[$actionType] ?? 'fas fa-info-circle';
    }

    /**
     * لون الإجراء
     */
    public static function getActionColor(string $actionType): string
    {
        $colors = [
            'create' => 'success',
            'reply' => 'info',
            'referral' => 'warning',
            'forward' => 'secondary',
            'status_change' => 'primary',
            'update' => 'light',
            'close' => 'dark',
            'reopen' => 'success',
            'delete' => 'danger',
            'restore' => 'success',
        ];

        return $colors[$actionType] ?? 'light';
    }

    /**
     * علاقة مع سجل الحركة
     */
    public function movementLogs()
    {
        return $this->hasMany(CorrespondenceMovementLog::class);
    }
}
