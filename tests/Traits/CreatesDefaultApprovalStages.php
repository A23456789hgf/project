<?php

namespace Tests\Traits;

use App\Enums\EntityResponsibilityType;
use App\Enums\UserResponsibilityType;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\User;

/**
 * Creates default entity_approval_stages for test entities.
 *
 * Since the new approval workflow requires explicitly configured stages per entity,
 * this trait ensures backward-compatible test scaffolding: it creates all three
 * standard stages (TECHNICAL_REVIEW, FINANCIAL_REVIEW, APPROVAL) for each entity,
 * using dedicated responsible users created per entity.
 */
trait CreatesDefaultApprovalStages
{
    /**
     * Configure all three default approval stages for an entity.
     *
     * Creates three users (technical reviewer, financial reviewer, entity approver)
     * if dedicated responsible users are not already provided, and inserts
     * entity_approval_stages rows for each stage type.
     *
     * @param  int  $roleId  Role ID to assign to auto-created users.
     * @param  array{
     *     technical_user?: User,
     *     financial_user?: User,
     *     approver_user?: User,
     * }  $users  Optional pre-created users to assign as responsible.
     * @return array{technical: User, financial: User, approver: User} The responsible users.
     */
    protected function createDefaultApprovalStages(
        InternalEntity $entity,
        int $roleId,
        array $users = []
    ): array {
        $technicalUser = $users['technical_user'] ?? $this->createStageUser(
            $entity, $roleId, UserResponsibilityType::Technical, 'فني'
        );
        $financialUser = $users['financial_user'] ?? $this->createStageUser(
            $entity, $roleId, UserResponsibilityType::Financial, 'مالي'
        );
        $approverUser = $users['approver_user'] ?? $this->createStageUser(
            $entity, $roleId, UserResponsibilityType::EntityApprover, 'معتمد'
        );

        $stageMapping = [
            EntityResponsibilityType::TechnicalReview->value => $technicalUser,
            EntityResponsibilityType::FinancialReview->value => $financialUser,
            EntityResponsibilityType::Approval->value => $approverUser,
        ];

        foreach ($stageMapping as $stageValue => $user) {
            $stageEnum = EntityResponsibilityType::from($stageValue);
            EntityApprovalStage::updateOrCreate(
                [
                    'entity_id' => $entity->id,
                    'stage' => $stageValue,
                ],
                [
                    'stage_order' => $stageEnum->stageOrder(),
                    'responsible_user_id' => $user->id,
                ]
            );
        }

        return [
            'technical' => $technicalUser,
            'financial' => $financialUser,
            'approver' => $approverUser,
        ];
    }

    /**
     * Configure only specific stages for an entity (e.g., Approval only).
     *
     * @param  array<EntityResponsibilityType, User>  $stagesWithUsers  Stage enum → responsible user.
     */
    protected function createApprovalStagesFor(InternalEntity $entity, array $stagesWithUsers): void
    {
        foreach ($stagesWithUsers as $stageEnum => $user) {
            EntityApprovalStage::updateOrCreate(
                [
                    'entity_id' => $entity->id,
                    'stage' => $stageEnum->value,
                ],
                [
                    'stage_order' => $stageEnum->stageOrder(),
                    'responsible_user_id' => $user->id,
                ]
            );
        }
    }

    /**
     * Create a user suitable for a specific stage responsibility.
     */
    private function createStageUser(
        InternalEntity $entity,
        int $roleId,
        UserResponsibilityType $responsibility,
        string $labelSuffix
    ): User {
        return User::withoutGlobalScopes()->create([
            'name' => "{$entity->name} - {$labelSuffix}",
            'username' => 'stage_'.$responsibility->value.'_'.$entity->id.'_'.uniqid(),
            'user_id' => 'UID_'.uniqid(),
            'phone' => '77'.rand(10000000, 99999999),
            'email' => 'stage_'.$responsibility->value.'_'.$entity->id.'_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'entity_id' => $entity->id,
            'role_id' => $roleId,
            'responsibility' => $responsibility,
            'status' => 'Active',
            'signature_path' => 'signatures/test_signature.png',
        ]);
    }
}
