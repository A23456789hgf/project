<?php

namespace App\Enums;

/**
 * Classifies the approval-workflow responsibility of a user within their entity.
 *
 * TECHNICAL      → المراجع الفني     (handles TECHNICAL_REVIEW stages)
 * FINANCIAL      → المراجع المالي    (handles FINANCIAL_REVIEW stages)
 * ENTITY_APPROVER → مسؤول الجهة     (handles APPROVAL stages)
 * NULL (no value) → لا يشارك في دورة الموافقات
 *
 * This classification is stored in users.responsibility and is used:
 *   - To filter eligible users when configuring entity_approval_stages
 *   - As a guardrail to ensure the right type of user is assigned to each stage
 *
 * It does NOT grant any Permission on its own.
 */
enum UserResponsibilityType: string
{
    case Technical = 'TECHNICAL';
    case Financial = 'FINANCIAL';
    case EntityApprover = 'ENTITY_APPROVER';

    /** Human-readable Arabic label for UI display. */
    public function label(): string
    {
        return match ($this) {
            self::Technical => 'الفنية',
            self::Financial => 'المالية',
            self::EntityApprover => 'مسؤول الجهة',
        };
    }

    /**
     * Map this responsibility type to the stage type that it covers.
     * Used to filter users when setting a stage's responsible_user_id.
     */
    public function coversStage(): EntityResponsibilityType
    {
        return match ($this) {
            self::Technical => EntityResponsibilityType::TechnicalReview,
            self::Financial => EntityResponsibilityType::FinancialReview,
            self::EntityApprover => EntityResponsibilityType::Approval,
        };
    }

    /**
     * Derive the UserResponsibilityType required for a given stage.
     */
    public static function forStage(EntityResponsibilityType $stage): self
    {
        return match ($stage) {
            EntityResponsibilityType::TechnicalReview => self::Technical,
            EntityResponsibilityType::FinancialReview => self::Financial,
            EntityResponsibilityType::Approval => self::EntityApprover,
            default => throw new \InvalidArgumentException("No user responsibility type mapped for stage: {$stage->value}"),
        };
    }

    /**
     * All options for dropdowns, as value => label.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
