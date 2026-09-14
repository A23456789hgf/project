<?php

namespace App\Enums;

enum ApprovalPhase: string
{
    case TechnicalReview = 'technical_review';
    case FinancialReview = 'financial_review';
    case StageApproval = 'stage_approval';

    public function label(): string
    {
        return match ($this) {
            self::TechnicalReview => 'المراجعة الفنية',
            self::FinancialReview => 'المراجعة المالية',
            self::StageApproval => 'اعتماد المرحلة',
        };
    }

    public function arabicName(): string
    {
        return $this->label();
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::TechnicalReview => 'Technical Review',
            self::FinancialReview => 'Financial Review',
            self::StageApproval => 'Stage Approval',
        };
    }

    public function orderInEntity(): int
    {
        return match ($this) {
            self::TechnicalReview => 1,
            self::FinancialReview => 2,
            self::StageApproval => 3,
        };
    }

    public function permissionSlug(): string
    {
        return match ($this) {
            self::TechnicalReview => 'approvals.technical-review',
            self::FinancialReview => 'approvals.financial-review',
            self::StageApproval => 'approvals.approve',
        };
    }

    public function nextPhase(): ?self
    {
        return match ($this) {
            self::TechnicalReview => self::FinancialReview,
            self::FinancialReview => self::StageApproval,
            self::StageApproval => null,
        };
    }

    public function previousPhase(): ?self
    {
        return match ($this) {
            self::TechnicalReview => null,
            self::FinancialReview => self::TechnicalReview,
            self::StageApproval => self::FinancialReview,
        };
    }
}
