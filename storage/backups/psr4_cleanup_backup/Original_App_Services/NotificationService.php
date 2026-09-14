<?php

namespace App\Services;

use App\Models\Authority;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectAchievement;
use App\Models\Stage;
use App\Models\User;
use App\Notifications\ProjectApprovalNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify users when project moves to next stage (Approved)
     */
    public function notifyStageTransition(
        Project $project,
        Stage $fromStage,
        Stage $toStage,
        ?int $nextAuthorityId,
        string $approvedBy
    ): void {
        try {
            // Get users at the next stage
            $users = $this->getUsersForStage($toStage, $nextAuthorityId);

            if ($users->isEmpty()) {
                Log::warning('No users found for stage transition notification', [
                    'project_id' => $project->id,
                    'to_stage' => $toStage->name_ar,
                    'authority_id' => $nextAuthorityId,
                ]);

                return;
            }

            $data = [
                'type' => 'stage_transition',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'from_stage' => $fromStage->name_ar,
                'to_stage' => $toStage->name_ar,
                'approved_by' => $approvedBy,
                'message' => "تم نقل المشروع \"{$project->project_name}\" من مرحلة {$fromStage->name_ar} إلى مرحلة {$toStage->name_ar}",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-arrow-circle-left',
                'color' => 'success',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('Stage transition notifications sent', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
                'to_stage' => $toStage->name_ar,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send stage transition notification', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify financial and technical reviewers
     */
    public function notifyReviewersAssigned(
        Project $project,
        Stage $currentStage,
        string $assignedBy
    ): void {
        try {
            $financialReviewers = $this->getFinancialReviewers();
            $technicalReviewers = $this->getTechnicalReviewers();

            $baseData = [
                'type' => 'review_assigned',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'stage' => $currentStage->name_ar,
                'assigned_by' => $assignedBy,
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-search-dollar',
            ];

            // Notify financial reviewers
            if ($financialReviewers->isNotEmpty()) {
                $financialData = array_merge($baseData, [
                    'review_type' => 'financial',
                    'message' => "تم تعيينك كمراجع مالي للمشروع \"{$project->project_name}\" في مرحلة {$currentStage->name_ar}",
                    'color' => 'info',
                ]);

                foreach ($financialReviewers as $reviewer) {
                    $reviewer->notify(new ProjectApprovalNotification($financialData));
                }
            }

            // Notify technical reviewers
            if ($technicalReviewers->isNotEmpty()) {
                $technicalData = array_merge($baseData, [
                    'review_type' => 'technical',
                    'message' => "تم تعيينك كمراجع فني للمشروع \"{$project->project_name}\" في مرحلة {$currentStage->name_ar}",
                    'color' => 'purple',
                ]);

                foreach ($technicalReviewers as $reviewer) {
                    $reviewer->notify(new ProjectApprovalNotification($technicalData));
                }
            }

            Log::info('Review assignment notifications sent', [
                'project_id' => $project->id,
                'financial_reviewers' => $financialReviewers->count(),
                'technical_reviewers' => $technicalReviewers->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send reviewer assignment notification', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify when action is required (Need Action)
     */
    public function notifyActionRequired(
        Project $project,
        Stage $currentStage,
        Stage $previousStage,
        string $requiredAction,
        string $requestedBy
    ): void {
        try {
            // Get users from previous stage (where project is being returned to)
            $users = $this->getPreviousStageUsers($project, $previousStage);

            if ($users->isEmpty()) {
                Log::warning('No users found for action required notification', [
                    'project_id' => $project->id,
                    'previous_stage' => $previousStage->name_ar,
                ]);

                return;
            }

            $data = [
                'type' => 'action_required',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'current_stage' => $currentStage->name_ar,
                'returned_to_stage' => $previousStage->name_ar,
                'required_action' => $requiredAction,
                'requested_by' => $requestedBy,
                'message' => "يتطلب المشروع \"{$project->project_name}\" إجراءً منك. تم إرجاعه من مرحلة {$currentStage->name_ar} إلى {$previousStage->name_ar}",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-exclamation-circle',
                'color' => 'warning',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('Action required notifications sent', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
                'returned_to' => $previousStage->name_ar,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send action required notification', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify when project is rejected
     */
    public function notifyRejection(
        Project $project,
        Stage $currentStage,
        Stage $previousStage,
        string $rejectionReason,
        string $rejectedBy
    ): void {
        try {
            // Get users from previous stage
            $users = $this->getPreviousStageUsers($project, $previousStage);

            if ($users->isEmpty()) {
                Log::warning('No users found for rejection notification', [
                    'project_id' => $project->id,
                    'previous_stage' => $previousStage->name_ar,
                ]);

                return;
            }

            $data = [
                'type' => 'rejected',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'current_stage' => $currentStage->name_ar,
                'returned_to_stage' => $previousStage->name_ar,
                'rejection_reason' => $rejectionReason,
                'rejected_by' => $rejectedBy,
                'message' => "تم رفض المشروع \"{$project->project_name}\" في مرحلة {$currentStage->name_ar} وإرجاعه إلى {$previousStage->name_ar}",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-times-circle',
                'color' => 'danger',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('Rejection notifications sent', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
                'returned_to' => $previousStage->name_ar,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify when project is resubmitted
     */
    public function notifyResubmission(
        Project $project,
        Stage $currentStage,
        string $resubmittedBy,
        ?string $notes = null
    ): void {
        try {
            // Get users at current stage (reviewers)
            $users = $this->getUsersForCurrentStage($project, $currentStage);

            if ($users->isEmpty()) {
                Log::warning('No users found for resubmission notification', [
                    'project_id' => $project->id,
                    'current_stage' => $currentStage->name_ar,
                ]);

                return;
            }

            $data = [
                'type' => 'resubmitted',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'stage' => $currentStage->name_ar,
                'resubmitted_by' => $resubmittedBy,
                'notes' => $notes,
                'message' => "تم إعادة تقديم المشروع \"{$project->project_name}\" للمراجعة في مرحلة {$currentStage->name_ar}",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-redo',
                'color' => 'secondary',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('Resubmission notifications sent', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
                'stage' => $currentStage->name_ar,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send resubmission notification', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get users for a specific stage and authority
     */
    private function getUsersForStage(Stage $stage, ?int $authorityId)
    {
        if (! $authorityId) {
            return collect();
        }

        // Get authority
        $authority = Authority::find($authorityId);

        if (! $authority) {
            return collect();
        }

        // Get users from this authority/entity
        return User::where('entity_id', $authorityId)
            ->where('status', 'active')
            ->whereHas('permissions', function ($query) {
                $query->where('slug', 'LIKE', 'approvals.%');
            })
            ->get();
    }

    /**
     * Get financial reviewers
     */
    private function getFinancialReviewers()
    {
        return User::where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('permissions', function ($q) {
                    $q->where('slug', 'approvals.financial-review');
                })
                    ->orWhereHas('roleEntity', function ($q) {
                        $q->where('name', 'LIKE', '%مالي%')
                            ->orWhere('name', 'LIKE', '%Financial%');
                    });
            })
            ->get();
    }

    /**
     * Get technical reviewers
     */
    private function getTechnicalReviewers()
    {
        return User::where('status', 'active')
            ->where(function ($query) {
                $query->whereHas('permissions', function ($q) {
                    $q->where('slug', 'approvals.technical-review');
                })
                    ->orWhereHas('roleEntity', function ($q) {
                        $q->where('name', 'LIKE', '%فني%')
                            ->orWhere('name', 'LIKE', '%Technical%');
                    });
            })
            ->get();
    }

    /**
     * Get users from previous stage
     */
    private function getPreviousStageUsers(Project $project, Stage $previousStage)
    {
        // Get the project creator and users who worked on previous stage
        $users = collect();

        // Add project creator
        if ($project->created_by_user_id) {
            $creator = User::find($project->created_by_user_id);
            if ($creator && $creator->status === 'active') {
                $users->push($creator);
            }
        }

        // Add users from the entity that created the project
        if ($project->creator_entity_id) {
            $entityUsers = User::where('entity_id', $project->creator_entity_id)
                ->where('status', 'active')
                ->get();
            $users = $users->merge($entityUsers);
        } elseif ($project->created_by_entity) {
            $entityUsers = User::where('department', $project->created_by_entity)
                ->where('status', 'active')
                ->get();
            $users = $users->merge($entityUsers);
        }

        return $users->unique('id');
    }

    /**
     * Get users for current stage
     */
    private function getUsersForCurrentStage(Project $project, Stage $currentStage)
    {
        // Get users who have approval permissions at current stage
        return User::where('status', 'active')
            ->whereHas('permissions', function ($query) {
                $query->where('slug', 'LIKE', 'approvals.%');
            })
            ->get();
    }

    // =====================================================================
    //  إشعارات المشاريع القديمة
    // =====================================================================

    /**
     * إشعار عند تغيير الجهة المقدمة للمشروع القديم
     * يُرسل لمستخدمي الجهة الجديدة المُسندة إليها المشروع
     */
    public function notifyOldProjectEntityChanged(Project $project, InternalEntity $newEntity): void
    {
        try {
            // جلب مستخدمي الجهة الجديدة
            $users = User::where('entity_id', $newEntity->id)
                ->where('status', 'active')
                ->get();

            if ($users->isEmpty()) {
                Log::warning('notifyOldProjectEntityChanged: لا يوجد مستخدمون نشطون في الجهة', [
                    'project_id' => $project->id,
                    'entity_id' => $newEntity->id,
                ]);

                return;
            }

            $data = [
                'type' => 'old_project_assigned',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'entity_name' => $newEntity->name,
                'message' => "تم إسناد مشروع \"{$project->project_name}\" إلى جهتكم ({$newEntity->name}) للاستكمال.",
                'action_url' => route('projects.complete-data', $project->id),
                'icon' => 'fa-building',
                'color' => 'warning',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('notifyOldProjectEntityChanged: تم إرسال الإشعارات', [
                'project_id' => $project->id,
                'entity_id' => $newEntity->id,
                'users_count' => $users->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('notifyOldProjectEntityChanged: فشل الإرسال', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * إشعار عند اكتمال بيانات المشروع القديم
     * يُرسل للمستخدمين ذوي صلاحيات الاعتماد وللمنشئ الأصلي
     */
    public function notifyOldProjectDataCompleted(Project $project): void
    {
        try {
            $users = collect();

            // إضافة المنشئ الأصلي
            if ($project->created_by_user_id) {
                $creator = User::find($project->created_by_user_id);
                if ($creator && $creator->status === 'active') {
                    $users->push($creator);
                }
            }

            // إضافة المشرفين (لديهم صلاحية projects.view-details)
            $supervisors = User::where('status', 'active')
                ->whereHas('permissions', function ($q) {
                    $q->where('slug', 'projects.view-details');
                })
                ->get();
            $users = $users->merge($supervisors)->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $entityName = $project->created_by_entity ?? optional($project->creatorEntity)->name ?? '-';

            $data = [
                'type' => 'old_project_data_completed',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'entity_name' => $entityName,
                'message' => "تم استكمال بيانات المشروع القديم \"{$project->project_name}\" بواسطة جهة ({$entityName}) وهو جاهز لتسجيل الإنجاز.",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-check-circle',
                'color' => 'success',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('notifyOldProjectDataCompleted: تم إرسال الإشعارات', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('notifyOldProjectDataCompleted: فشل الإرسال', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * إشعار عند تسجيل إنجاز جديد للمشروع القديم
     * يُرسل لمستخدمي الجهة المقدمة وللمشرفين
     */
    public function notifyOldProjectAchievementRecorded(Project $project, ProjectAchievement $achievement): void
    {
        try {
            $users = collect();

            // مستخدمو الجهة المقدمة
            if ($project->creator_entity_id) {
                $entityUsers = User::where('entity_id', $project->creator_entity_id)
                    ->where('status', 'active')
                    ->get();
                $users = $users->merge($entityUsers);
            }

            // المنشئ الأصلي
            if ($project->created_by_user_id) {
                $creator = User::find($project->created_by_user_id);
                if ($creator && $creator->status === 'active') {
                    $users->push($creator);
                }
            }

            $users = $users->unique('id');

            if ($users->isEmpty()) {
                return;
            }

            $entityName = $project->created_by_entity ?? optional($project->creatorEntity)->name ?? '-';
            $percentage = $achievement->new_achievement ?? 0;

            $data = [
                'type' => 'old_project_achievement',
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'entity_name' => $entityName,
                'achievement_pct' => $percentage,
                'message' => "تم تسجيل إنجاز جديد ({$percentage}%) للمشروع \"{$project->project_name}\" بواسطة جهة ({$entityName}).",
                'action_url' => route('projects.show', $project->id),
                'icon' => 'fa-trophy',
                'color' => 'info',
            ];

            foreach ($users as $user) {
                $user->notify(new ProjectApprovalNotification($data));
            }

            Log::info('notifyOldProjectAchievementRecorded: تم إرسال الإشعارات', [
                'project_id' => $project->id,
                'users_count' => $users->count(),
                'percentage' => $percentage,
            ]);

        } catch (\Exception $e) {
            Log::error('notifyOldProjectAchievementRecorded: فشل الإرسال', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
