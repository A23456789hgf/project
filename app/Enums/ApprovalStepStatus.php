<?php

namespace App\Enums;

enum ApprovalStepStatus: string
{
    case Locked = 'locked';
    case Pending = 'pending';
    case Approved = 'approved';
    case NeedAction = 'need_action';
    case Rejected = 'rejected';
    case Resubmitted = 'resubmitted';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Locked => 'مغلقة',
            self::Pending => 'قيد الانتظار (نشطة)',
            self::Approved => 'تمت الموافقة',
            self::NeedAction => 'يتطلب إجراء / استكمال',
            self::Rejected => 'مرفوض',
            self::Resubmitted => 'تمت إعادة التقديم',
            self::Returned => 'تم الإرجاع',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Locked => 'badge-secondary',
            self::Pending => 'badge-warning',
            self::Approved => 'badge-success',
            self::NeedAction => 'badge-danger',
            self::Rejected => 'badge-dark',
            self::Resubmitted => 'badge-info',
            self::Returned => 'badge-light',
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::Approved;
    }

    public function isActionable(): bool
    {
        return $this === self::Pending;
    }

    public function isLocked(): bool
    {
        return $this === self::Locked;
    }
}
