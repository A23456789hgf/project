<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\Authority;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectActivityHistory;
use App\Models\ProjectApproval;
use App\Models\ProjectMovementLog;
use App\Models\Stage;
use App\Models\StageFlow;
use App\Models\StageStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService
{
    /**
     * Initialize first approval stage for a new project based on user's entity
     */
    public function initializeRecursiveApprovals(Project $project): ?ProjectApproval
    {
        try {
            $user = auth()->user();
            $entityId = $user->entity_id;

            if (! $entityId) {
                Log::warning('User has no entity assigned', ['user_id' => $user->id]);

                return null;
            }

            $currentEntity = InternalEntity::find($entityId);
            if (! $currentEntity) {
                Log::warning('Internal Entity not found', ['entity_id' => $entityId]);

                return null;
            }

            // Create the first approval stage for the user's entity
            $firstApproval = ProjectApproval::create([
                'project_id' => $project->id,
                'authority_id' => $entityId,
                'drop' => 'entity_'.$entityId, // Match code format from EntityHierarchyService
                'step_order' => 1,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Update project status and entity tracking
            $project->update([
                'creator_entity_id' => $currentEntity->id,
                'approval_status' => 'pending',
                'current_stage' => 'entity_'.$entityId,
                'current_stage_order' => 1,
                'current_approval_stage_id' => null,
            ]);

            // Log activity
            $this->logActivity($project, 'initiated', [
                'notes' => 'Project initiated by '.$user->name.' at '.$currentEntity->name,
                'from_stage_name' => 'Draft',
                'to_stage_name' => $currentEntity->name,
                'to_stage_order' => 1,
            ]);

            return $firstApproval;
        } catch (\Exception $e) {
            Log::error('Failed to initialize recursive approvals', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Progress project to the next parent entity in the hierarchy
     */
    public function progressToNextParentStage(ProjectApproval $currentApproval): ?ProjectApproval
    {
        $project = $currentApproval->project;
        $currentEntityId = $currentApproval->entity_id;
        $currentEntity = InternalEntity::find($currentEntityId);

        if (! $currentEntity || ! $currentEntity->parent_id) {
            // Reached the root or no parent found - Move to Implementation Stage
            $implementationApproval = ProjectApproval::create([
                'project_id' => $project->id,
                'authority_id' => $currentEntity ? $currentEntity->id : null, // keep same authority or central?
                'drop' => 'implementation',
                'step_order' => $currentApproval->step_order + 1,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            $project->update(['status' => 'implementation']);
            $this->logActivity($project, 'approved_to_implementation', [
                'notes' => 'Project has passed all entity approvals and moved to Implementation Stage.',
                'from_stage_name' => $currentEntity ? $currentEntity->name : 'Unknown',
                'to_stage_name' => 'Implementation Stage',
            ]);

            return $implementationApproval;
        }

        $parentEntity = $currentEntity->parent;

        // Create the next stage for the parent entity
        $nextApproval = ProjectApproval::create([
            'project_id' => $project->id,
            'authority_id' => $parentEntity->id,
            'drop' => 'entity_'.$parentEntity->id, // Match code format
            'step_order' => $currentApproval->step_order + 1,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        $this->logActivity($project, 'approved', [
            'from_stage_name' => $currentEntity->name,
            'from_stage_order' => $currentApproval->step_order,
            'to_stage_name' => $parentEntity->name,
            'to_stage_order' => $nextApproval->step_order,
        ]);

        return $nextApproval;
    }

    /**
     * Create approval stages for a project based on its supervising authorities
     */
    public function createProjectApprovalStages(Project $project): Collection
    {
        $approvals = collect();

        $supervisingAuthorities = $project->supervisingAuthorities()->get();

        if ($supervisingAuthorities->isEmpty()) {
            return $approvals;
        }

        foreach ($supervisingAuthorities as $supervising) {
            $authority = $supervising->authority;
            if (! $authority) {
                continue;
            }

            $hierarchy = $this->getAuthorityHierarchy($authority);

            foreach ($hierarchy as $auth) {
                $flows = ApprovalFlow::where('authority_id', $auth->id)
                    ->where('is_active', true)
                    ->orderBy('step_order')
                    ->get();

                foreach ($flows as $flow) {
                    $existing = ProjectApproval::where('project_id', $project->id)
                        ->where('authority_id', $auth->id)
                        ->where('step_order', $flow->step_order)
                        ->first();

                    if (! $existing) {
                        $approval = ProjectApproval::create([
                            'project_id' => $project->id,
                            'authority_id' => $auth->id,
                            'approval_flow_id' => $flow->id,
                            'step_order' => $flow->step_order,
                            'status' => 'pending',
                        ]);

                        $approvals->push($approval);
                    }
                }
            }
        }

        return $approvals;
    }

    /**
     * Get all authorities in a hierarchy (from child to root)
     */
    public function getAuthorityHierarchy(Authority $authority): array
    {
        $hierarchy = [];
        $current = $authority;

        while ($current) {
            array_unshift($hierarchy, $current);
            $current = $current->parent;
        }

        return $hierarchy;
    }

    /**
     * Get all internal entities in a hierarchy (from child to root)
     */
    public function getEntityHierarchy(InternalEntity $entity): array
    {
        $hierarchy = [];
        $current = $entity;

        while ($current) {
            $hierarchy[] = $current;
            $current = $current->parent;
        }

        return $hierarchy;
    }

    /**
     * Create project activity history log
     */
    public function logActivity(Project $project, string $actionType, array $data = []): ProjectActivityHistory
    {
        $lastActivity = ProjectActivityHistory::where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $metadata = $data['metadata'] ?? [];
        if ($lastActivity) {
            $metadata['elapsed_time'] = now()->diffForHumans($lastActivity->created_at);
            $metadata['time_seconds'] = now()->diffInSeconds($lastActivity->created_at);
        }

        return ProjectActivityHistory::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'from_stage_order' => $data['from_stage_order'] ?? null,
            'from_stage_name' => $data['from_stage_name'] ?? null,
            'to_stage_order' => $data['to_stage_order'] ?? null,
            'to_stage_name' => $data['to_stage_name'] ?? null,
            'notes' => $data['notes'] ?? null,
            'action_details' => $data['action_details'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Approve an approval stage
     */
    public function approveStage(
        ProjectApproval $approval,
        string $notes = '',
        ?string $attachment = null,
        ?int $userId = null
    ): ProjectApproval {
        $approval->update([
            'status' => 'approved',
            'notes' => $notes,
            'attachment' => $attachment,
            'reviewed_at' => now(),
            'created_by' => $userId ?? auth()->id(),
        ]);

        return $approval;
    }

    /**
     * Split a stage into financial and technical reviews
     */
    public function splitToFinancialAndTechnicalReview(ProjectApproval $currentApproval): array
    {
        $project = $currentApproval->project;

        $currentApproval->update([
            'status' => 'financial_technical_review',
            'financial_review_completed' => false,
            'technical_review_completed' => false,
        ]);

        $this->logActivity($project, 'sent_for_review', [
            'notes' => 'Project sent for simultaneous Financial and Technical review.',
            'from_stage_name' => $currentApproval->drop,
        ]);

        return [
            'success' => true,
            'approval' => $currentApproval,
        ];
    }

    /**
     * Submit a specific review (financial or technical)
     */
    public function submitReview(ProjectApproval $approval, string $type, ?string $notes = null, ?string $attachment = null): bool
    {
        $data = [
            "{$type}_review_completed" => true,
            "{$type}_review_completed_at" => now(),
            "{$type}_review_user_id" => Auth::id(),
            "{$type}_review_notes" => $notes,
        ];

        if ($attachment) {
            $approval->attachment = $attachment;
        }

        $approval->update($data);

        $this->logActivity($approval->project, 'review_completed', [
            'notes' => ucfirst($type).' review completed by '.Auth::user()->name,
            'action_details' => $notes,
            'from_stage_name' => $approval->drop,
        ]);

        // Check if both are completed
        if ($approval->financial_review_completed && $approval->technical_review_completed) {
            return true;
        }

        return false;
    }

    /**
     * Reject an approval stage
     */
    public function rejectStage(
        ProjectApproval $approval,
        string $reason = '',
        ?string $attachment = null,
        ?int $userId = null
    ): ProjectApproval {
        $approval->update([
            'status' => 'rejected',
            'notes' => $reason,
            'attachment' => $attachment,
            'reviewed_at' => now(),
            'created_by' => $userId ?? auth()->id(),
        ]);

        return $approval;
    }

    /**
     * Mark stage as needing revision
     */
    public function requestRevision(
        ProjectApproval $approval,
        string $revisionNotes = '',
        ?int $userId = null
    ): ProjectApproval {
        $approval->update([
            'status' => 'need_action', // Use need_action as per controller
            'notes' => $revisionNotes,
            'reviewed_at' => now(),
            'created_by' => $userId ?? auth()->id(),
        ]);

        return $approval;
    }

    /**
     * Return project to previous stage in hierarchy
     */
    public function returnToPreviousStage(ProjectApproval $currentApproval, string $notes): ?ProjectApproval
    {
        $project = $currentApproval->project;
        $currentOrder = $currentApproval->step_order;

        if ($currentOrder <= 1) {
            // Cannot return further back than the first stage
            $project->update(['status' => 'draft']);

            return null;
        }

        $previousApproval = ProjectApproval::where('project_id', $project->id)
            ->where('step_order', $currentOrder - 1)
            ->first();

        if ($previousApproval) {
            // Re-open the previous stage
            $previousApproval->update([
                'status' => 'pending',
                'notes' => $previousApproval->notes."\n\n[إرجاع من المرحلة التالية: ".$notes.']',
                'reviewed_at' => null,
            ]);

            // Mark subsequent stages as returned instead of hard deleting to preserve audit history
            ProjectApproval::where('project_id', $project->id)
                ->where('step_order', '>=', $currentOrder)
                ->update(['status' => 'returned']);

            $project->update(['status' => 'pending_approval']);

            $this->logActivity($project, 'stage_regression', [
                'notes' => $notes,
                'from_stage_name' => $currentApproval->drop,
                'to_stage_name' => $previousApproval->drop,
            ]);

            return $previousApproval;
        }

        return null;
    }

    /**
     * Put approval on hold
     */
    public function holdApproval(
        ProjectApproval $approval,
        string $reason = '',
        ?int $userId = null
    ): ProjectApproval {
        $approval->update([
            'status' => 'on_hold',
            'notes' => $reason,
            'created_by' => $userId ?? auth()->id(),
        ]);

        return $approval;
    }

    /**
     * Reset approval to pending
     */
    public function resetApproval(ProjectApproval $approval): ProjectApproval
    {
        $approval->update([
            'status' => 'pending',
            'notes' => null,
            'attachment' => null,
            'reviewed_at' => null,
            'created_by' => null,
        ]);

        return $approval;
    }

    /**
     * Get all pending approvals for an authority (entity)
     */
    public function getPendingApprovalsForAuthority(Authority $authority): Collection
    {
        return ProjectApproval::where('entity_id', $authority->id)
            ->where('status', 'pending')
            ->with('project', 'approvalFlow')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get approval status summary for a project
     */
    public function getProjectApprovalSummary(Project $project): array
    {
        $approvals = $project->projectApprovals()->withTrashed()->with('authority', 'approvalFlow')->get();

        $summary = [
            'total_stages' => $approvals->count(),
            'approved' => $approvals->where('status', 'approved')->count(),
            'pending' => $approvals->where('status', 'pending')->count(),
            'rejected' => $approvals->where('status', 'rejected')->count(),
            'needs_revision' => $approvals->where('status', 'needs_revision')->count(),
            'on_hold' => $approvals->where('status', 'on_hold')->count(),
            'by_authority' => $this->groupApprovalsByAuthority($approvals),
        ];

        return $summary;
    }

    /**
     * Group approvals by authority with status counts
     */
    private function groupApprovalsByAuthority(Collection $approvals): array
    {
        $grouped = [];

        foreach ($approvals->groupBy('authority_id') as $authorityId => $authorityApprovals) {
            $authority = $authorityApprovals->first()->authority;

            $grouped[$authority->agency_name] = [
                'total' => $authorityApprovals->count(),
                'approved' => $authorityApprovals->where('status', 'approved')->count(),
                'pending' => $authorityApprovals->where('status', 'pending')->count(),
                'rejected' => $authorityApprovals->where('status', 'rejected')->count(),
                'stages' => $authorityApprovals->map(function ($approval) {
                    return [
                        'step' => $approval->step_order,
                        'name' => $approval->approvalFlow?->step_name ?? 'Stage '.$approval->step_order,
                        'status' => $approval->status,
                        'reviewed_at' => $approval->reviewed_at?->format('Y-m-d H:i'),
                    ];
                })->toArray(),
            ];
        }

        return $grouped;
    }

    /**
     * Check if project has any pending approvals
     */
    public function hasPendingApprovals(Project $project): bool
    {
        return $project->projectApprovals()
            ->whereIn('status', ['pending', 'needs_revision', 'on_hold'])
            ->exists();
    }

    /**
     * Check if project is fully approved
     */
    public function isFullyApproved(Project $project): bool
    {
        $totalApprovals = $project->projectApprovals()->count();

        if ($totalApprovals === 0) {
            return false;
        }

        $approvedCount = $project->projectApprovals()
            ->where('status', 'approved')
            ->count();

        return $approvedCount === $totalApprovals;
    }

    /**
     * Get next pending stage for a project from a specific authority
     */
    public function getNextPendingStage(Project $project, Authority $authority): ?ProjectApproval
    {
        return ProjectApproval::where('project_id', $project->id)
            ->where('authority_id', $authority->id)
            ->where('status', 'pending')
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Get all approvals for a project ordered by stage
     */
    public function getProjectApprovalTimeline(Project $project): Collection
    {
        return ProjectApproval::withTrashed()->where('project_id', $project->id)
            ->with('authority', 'approvalFlow', 'createdBy')
            ->orderBy('authority_id')
            ->orderBy('step_order')
            ->get();
    }

    /**
     * Get approval statistics for dashboard
     */
    public function getApprovalStatistics(): array
    {
        $totalApprovals = ProjectApproval::count();
        $pendingCount = ProjectApproval::where('status', 'pending')->count();
        $approvedCount = ProjectApproval::where('status', 'approved')->count();
        $rejectedCount = ProjectApproval::where('status', 'rejected')->count();

        return [
            'total' => $totalApprovals,
            'pending' => $pendingCount,
            'pending_percentage' => $totalApprovals > 0 ? round(($pendingCount / $totalApprovals) * 100, 2) : 0,
            'approved' => $approvedCount,
            'approved_percentage' => $totalApprovals > 0 ? round(($approvedCount / $totalApprovals) * 100, 2) : 0,
            'rejected' => $rejectedCount,
            'rejected_percentage' => $totalApprovals > 0 ? round(($rejectedCount / $totalApprovals) * 100, 2) : 0,
        ];
    }

    /**
     * Build approval workflow for display
     */
    public function buildApprovalWorkflow(Project $project): array
    {
        $approvals = $project->projectApprovals()->withTrashed()->with('authority', 'approvalFlow')->get();
        $workflow = [];

        foreach ($approvals->groupBy('authority_id') as $authorityId => $stages) {
            $authority = $stages->first()->authority;

            $stageData = [];
            foreach ($stages->sortBy('step_order') as $approval) {
                $stageData[] = [
                    'id' => $approval->id,
                    'step' => $approval->step_order,
                    'name' => $approval->approvalFlow?->step_name ?? 'Stage '.$approval->step_order,
                    'status' => $approval->status,
                    'notes' => $approval->notes,
                    'reviewed_at' => $approval->reviewed_at,
                    'reviewed_by' => $approval->createdBy?->name,
                ];
            }

            $workflow[] = [
                'authority' => $authority->agency_name,
                'authority_id' => $authority->id,
                'stages' => $stageData,
            ];
        }

        return $workflow;
    }

    /**
     * Log project movement/state change
     */
    public function logProjectMovement(
        Project $project,
        string $action,
        string $stage,
        string $notes = '',
        ?int $authorityId = null
    ): ProjectMovementLog {
        return ProjectMovementLog::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'entity' => $stage,
            'status' => $action,
            'action_required' => false,
            'notes' => $notes,
            'logged_at' => now(),
        ]);
    }

    /**
     * Auto-approve and progress to next stage
     */
    public function approveAndProgress(ProjectApproval $approval, string $notes = '', ?string $attachment = null): array
    {
        $project = $approval->project;

        // Approve current stage
        $this->approveStage($approval, $notes, $attachment, Auth::id());

        // Log the approval
        $this->logProjectMovement(
            $project,
            'approved',
            $this->getDropArabic($approval->drop),
            'Approved by: '.Auth::user()->name.'. Notes: '.$notes,
            $approval->entity_id
        );

        // Ensure project status is reset to pending if it was rolled back (e.g. admin approved without waiting for resubmit)
        if ($project->status === 'rolled_back_for_review') {
            $project->update(['status' => 'pending']);
        }

        // Create and return next stage
        $nextStage = $this->createNextApprovalStage($project, $approval->drop);

        // If this is the final stage (implementation), transition project to in_progress
        if (! $nextStage && $approval->drop === 'implementation') {
            $project->update(['status' => 'in_progress']);

            // Log the transition to in_progress
            $this->logProjectMovement(
                $project,
                'auto_progressed',
                'مرحلة التنفيذ',
                'Project status updated to In Progress after implementation phase approval',
                $approval->entity_id
            );
        }

        return [
            'success' => true,
            'current_stage' => $approval->drop,
            'current_stage_arabic' => $this->getDropArabic($approval->drop),
            'next_stage' => $nextStage ? $nextStage->drop : null,
            'next_stage_arabic' => $nextStage ? $this->getDropArabic($nextStage->drop) : 'مكتمل',
            'is_final_stage' => ! $nextStage,
            'is_execution_phase' => $approval->drop === 'committee' && $nextStage && $nextStage->drop === 'implementation', // Flag to show it entered implementation phase
            'message' => $nextStage
                ? 'تم الموافقة ودخول المرحلة التالية: '.$this->getDropArabic($nextStage->drop)
                : 'تم الموافقة على مرحلة التنفيذ - المشروع الآن قيد التنفيذ (In Progress)',
        ];
    }

    /**
     * Create the next approval stage automatically
     */
    private function createNextApprovalStage(Project $project, string $currentDrop): ?ProjectApproval
    {
        $drops = ['assembly', 'union', 'committee', 'implementation'];
        $currentIndex = array_search($currentDrop, $drops);

        if ($currentIndex === false || $currentIndex >= count($drops) - 1) {
            // Final stage reached - update project to implementation ready
            $project->update(['status' => 'approved']);

            return null;
        }

        $nextDrop = $drops[$currentIndex + 1];

        // Check if next stage already exists
        $existing = ProjectApproval::where('project_id', $project->id)
            ->where('drop', $nextDrop)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Get the appropriate authority for next stage
        $supervisingAuthority = $project->supervisingAuthorities()
            ->with('authority')
            ->skip($currentIndex + 1)
            ->first();

        $nextAuthority = $supervisingAuthority ? $supervisingAuthority->authority : $project->supervisingAuthorities()->first()->authority;

        $nextStage = ProjectApproval::create([
            'project_id' => $project->id,
            'authority_id' => $nextAuthority->id,
            'drop' => $nextDrop,
            'step_order' => $currentIndex + 2,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        // Log the automatic stage creation
        $this->logProjectMovement(
            $project,
            'auto_progressed',
            $this->getDropArabic($nextDrop),
            'Project automatically progressed to next stage after approval',
            $nextAuthority->id
        );

        return $nextStage;
    }

    /**
     * Get drop name in Arabic
     */
    private function getDropArabic(string $drop): string
    {
        $stage = Stage::where('code', $drop)->first();

        return $stage ? $stage->name_ar : $drop;
    }

    /**
     * ============= NEW STAGE SYSTEM METHODS =============
     */

    /**
     * Initialize project with first stage using new stage system
     */
    public function initializeProjectWithStages(Project $project, ?Stage $stage = null): ?ProjectApproval
    {
        try {
            // 1. Determine the origin entity ID
            // Use the project's ORIGINAL creator entity for approval chain
            $originEntityId = $project->creator_entity_id
                ?? $project->internal_entity_id
                ?? optional($project->createdBy)->entity_id
                ?? (auth()->check() ? auth()->user()->entity_id : null);

            // Try to get creator entity ID (if integer) or name (if string)
            if (! $originEntityId && $project->created_by_entity) {
                if (is_numeric($project->created_by_entity)) {
                    $originEntityId = (int) $project->created_by_entity;
                } else {
                    $entity = InternalEntity::where('name', trim($project->created_by_entity))->first();

                    // Try fuzzy match if exact match fails
                    if (! $entity) {
                        $entity = InternalEntity::where('name', 'like', '%'.trim($project->created_by_entity).'%')->first();
                    }

                    $originEntityId = $entity ? $entity->id : null;
                }
            }

            if (! $originEntityId) {
                Log::warning('Could not determine origin entity for project initialization', ['project_id' => $project->id]);

                // If we can't find an entity, we can't start a hierarchical chain
                return null;
            }

            // 2. Generate dynamic stages from the origin entity (usually current department/entity)
            // EntityHierarchyService::generateApprovalStagesFromSelectedEntity now starts from the PARENT
            $entityHierarchyService = app(EntityHierarchyService::class);
            $dynamicStages = $entityHierarchyService->generateApprovalStagesFromSelectedEntity($originEntityId);

            if (empty($dynamicStages)) {
                Log::warning('No dynamic stages found for entity hierarchy', ['origin_entity_id' => $originEntityId]);

                return null;
            }

            // The first dynamic stage is now the parent of the origin entity
            $firstStage = $dynamicStages[0];

            // Get pending status
            $pendingStatus = StageStatus::where('code', 'pending')
                ->where('is_active', true)
                ->first();
            $pendingStatusId = $pendingStatus ? $pendingStatus->id : null;

            // 3. Create the initial project approval record
            $approval = ProjectApproval::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'drop' => $firstStage['code'],
                ],
                [
                    'entity_id' => $firstStage['entity_id'],
                    'status' => 'pending',
                    'step_order' => $firstStage['order'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            // 4. Update the project itself with current stage information
            $project->update([
                'status' => 'pending_approval',
                'current_stage' => $firstStage['code'],
                'current_stage_order' => $firstStage['order'],
                'updated_at' => Carbon::now(),
            ]);

            Log::info('Project initialized with dynamic first stage (parent entity)', [
                'project_id' => $project->id,
                'origin_entity_id' => $originEntityId,
                'first_stage_code' => $firstStage['code'],
                'first_entity_id' => $firstStage['entity_id'],
            ]);

            return $approval;
        } catch (\Exception $e) {
            Log::error('Failed to initialize project with stages', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Approve stage and progress to next using stage flows
     */
    public function approveStageAndProgress(ProjectApproval $approval, string $notes = '', ?string $attachment = null): array
    {
        try {
            DB::beginTransaction();

            $project = $approval->project;
            $currentStage = $approval->stage;

            if (! $currentStage) {
                throw new \Exception('Current stage not found for approval');
            }

            // Update approval to approved status
            $approvedStatus = StageStatus::where('code', 'approved')
                ->where('is_active', true)
                ->firstOrFail();

            $approval->update([
                'stage_status_id' => $approvedStatus->id,
                'status' => 'approved',
                'notes' => $notes,
                'attachment' => $attachment,
                'reviewed_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Log the approval
            $this->logProjectMovement(
                $project,
                'approved',
                $currentStage->name_ar,
                'Approved by: '.Auth::user()->name.'. Notes: '.$notes,
                $approval->entity_id
            );

            // Find next stage using StageFlow
            $nextFlow = StageFlow::where('from_stage_id', $currentStage->id)
                ->where('trigger_status', 'approved')
                ->where('is_active', true)
                ->first();

            $nextStage = null;
            if ($nextFlow) {
                $nextStage = $nextFlow->toStage;

                // Auto-create next stage if configured
                if ($nextFlow->shouldAutoCreateNext()) {
                    $nextApproval = $this->createNextStageApproval($project, $nextStage, $approval->entity_id);

                    if ($nextApproval) {
                        $this->logProjectMovement(
                            $project,
                            'auto_progressed',
                            $nextStage->name_ar,
                            'Project automatically progressed to next stage',
                            $approval->entity_id
                        );
                    }
                }
            }

            // Check if this is the final stage
            $isFinalStage = ! $nextStage;
            if ($isFinalStage && $currentStage->code === 'implementation') {
                $project->update(['status' => 'in_progress']);
                $this->logProjectMovement(
                    $project,
                    'auto_progressed',
                    $currentStage->name_ar,
                    'Project moved to in_progress status after final stage approval',
                    $approval->entity_id
                );
            }

            DB::commit();

            return [
                'success' => true,
                'current_stage_id' => $currentStage->id,
                'current_stage_code' => $currentStage->code,
                'current_stage_name' => $currentStage->name_ar,
                'next_stage_id' => $nextStage ? $nextStage->id : null,
                'next_stage_code' => $nextStage ? $nextStage->code : null,
                'next_stage_name' => $nextStage ? $nextStage->name_ar : 'مكتمل',
                'is_final_stage' => $isFinalStage,
                'message' => $nextStage
                    ? 'تم الموافقة ودخول المرحلة التالية: '.$nextStage->name_ar
                    : 'تم الموافقة على المرحلة النهائية - المشروع الآن قيد التنفيذ',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve and progress stage', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create next stage approval
     */
    private function createNextStageApproval(Project $project, Stage $nextStage, int $currentAuthorityId): ?ProjectApproval
    {
        // Check if approval already exists for next stage
        $existing = ProjectApproval::where('project_id', $project->id)
            ->where('stage_id', $nextStage->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Get supervisory authority for next stage
        $supervisingAuthority = $project->supervisingAuthorities()
            ->with('authority')
            ->first();

        $authority = $supervisingAuthority ? $supervisingAuthority->authority : Authority::find($currentAuthorityId);

        if (! $authority) {
            Log::warning('No authority found for creating next stage', [
                'project_id' => $project->id,
                'next_stage_id' => $nextStage->id,
            ]);

            return null;
        }

        $pendingStatus = StageStatus::where('code', 'pending')
            ->where('is_active', true)
            ->first();

        return ProjectApproval::create([
            'project_id' => $project->id,
            'authority_id' => $authority->id,
            'stage_id' => $nextStage->id,
            'stage_status_id' => $pendingStatus->id,
            'drop' => $nextStage->code,
            'step_order' => $nextStage->order,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Return stage for revision using stage flows
     */
    public function returnStageForRevision(ProjectApproval $approval, string $revisionNotes = ''): array
    {
        try {
            DB::beginTransaction();

            $project = $approval->project;
            $currentStage = $approval->stage;

            if (! $currentStage) {
                throw new \Exception('Current stage not found');
            }

            // Update current approval to needs_revision status
            $revisionsStatus = StageStatus::where('code', 'needs_revision')
                ->where('is_active', true)
                ->firstOrFail();

            $approval->update([
                'stage_status_id' => $revisionsStatus->id,
                'status' => 'needs_revision',
                'notes' => $approval->notes."\n\n[Returned for Revision: ".$revisionNotes.']',
                'reviewed_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Find previous stage using incoming flows
            $previousFlow = StageFlow::where('to_stage_id', $currentStage->id)
                ->where('is_active', true)
                ->first();

            $previousStage = null;
            if ($previousFlow) {
                $previousStage = $previousFlow->fromStage;

                // Find previous approval and reset it to needs_revision
                $previousApproval = ProjectApproval::where('project_id', $project->id)
                    ->where('stage_id', $previousStage->id)
                    ->first();

                if ($previousApproval) {
                    $previousApproval->update([
                        'stage_status_id' => $revisionsStatus->id,
                        'status' => 'needs_revision',
                        'notes' => $previousApproval->notes."\n\n[Revision requested from: ".$currentStage->name_ar.']',
                        'updated_at' => now(),
                    ]);
                }

                // Delete all subsequent stages
                $this->deleteSubsequentStagesFromFlow($project, $currentStage);
            }

            // Update project status
            $project->update(['status' => 'rolled_back_for_review']);

            // Log the revision request
            $this->logProjectMovement(
                $project,
                'returned_for_revision',
                $currentStage->name_ar,
                'Project returned for revision. Reason: '.$revisionNotes,
                $approval->entity_id
            );

            DB::commit();

            return [
                'success' => true,
                'current_stage_id' => $currentStage->id,
                'current_stage_name' => $currentStage->name_ar,
                'previous_stage_id' => $previousStage ? $previousStage->id : null,
                'previous_stage_name' => $previousStage ? $previousStage->name_ar : null,
                'message' => 'تم إرجاع المشروع للمراجعة والتعديل',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to return stage for revision', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject stage
     */
    public function rejectStageWithReason(ProjectApproval $approval, string $reason = ''): array
    {
        try {
            DB::beginTransaction();

            $project = $approval->project;
            $currentStage = $approval->stage;

            if (! $currentStage) {
                throw new \Exception('Current stage not found');
            }

            $rejectedStatus = StageStatus::where('code', 'rejected')
                ->where('is_active', true)
                ->firstOrFail();

            $approval->update([
                'stage_status_id' => $rejectedStatus->id,
                'status' => 'rejected',
                'notes' => $reason,
                'reviewed_at' => now(),
                'created_by' => Auth::id(),
            ]);

            $project->update(['status' => 'rejected']);

            // Log the rejection
            $this->logProjectMovement(
                $project,
                'rejected',
                $currentStage->name_ar,
                'Project rejected at stage. Reason: '.$reason,
                $approval->entity_id
            );

            DB::commit();

            return [
                'success' => true,
                'stage_id' => $currentStage->id,
                'stage_name' => $currentStage->name_ar,
                'message' => 'تم رفض المشروع ويمكن إعادة التقديم بعد التعديلات',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject stage', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete all subsequent stages from current stage using stage flows
     */
    private function deleteSubsequentStagesFromFlow(Project $project, Stage $currentStage): void
    {
        $subsequentStages = $currentStage->outgoingFlows()
            ->pluck('to_stage_id')
            ->toArray();

        if (! empty($subsequentStages)) {
            ProjectApproval::where('project_id', $project->id)
                ->whereIn('stage_id', $subsequentStages)
                ->update(['status' => 'returned']);

            // Recursively update further stages
            foreach ($subsequentStages as $stageId) {
                $nextStage = Stage::find($stageId);
                if ($nextStage) {
                    $this->deleteSubsequentStagesFromFlow($project, $nextStage);
                }
            }
        }
    }

    /**
     * Get approval workflow using new stage system
     */
    public function getApprovalWorkflowByStages(Project $project): array
    {
        $approvals = $project->projectApprovals()
            ->with('stage', 'stageStatus', 'authority', 'createdBy')
            ->whereNotNull('stage_id')
            ->orderBy('created_at')
            ->get();

        return [
            'total_approvals' => $approvals->count(),
            'approvals' => $approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'stage_id' => $approval->stage_id,
                    'stage_code' => $approval->stage?->code,
                    'stage_name' => $approval->stage?->name_ar,
                    'status_id' => $approval->stage_status_id,
                    'status_code' => $approval->stageStatus?->code,
                    'status_name' => $approval->stageStatus?->name_ar,
                    'authority' => $approval->entity?->name,
                    'reviewer' => $approval->createdBy?->name,
                    'notes' => $approval->notes,
                    'reviewed_at' => $approval->reviewed_at?->format('Y-m-d H:i:s'),
                    'created_at' => $approval->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
            'stage_sequence' => $this->buildStageSequence($project),
        ];
    }

    /**
     * Build stage sequence for project
     */
    public function buildStageSequence(Project $project): array
    {
        $stages = Stage::where('is_active', true)
            ->orderBy('order')
            ->get();

        return $stages->map(function ($stage) use ($project) {
            $approval = ProjectApproval::where('project_id', $project->id)
                ->where('stage_id', $stage->id)
                ->first();

            return [
                'stage_id' => $stage->id,
                'stage_code' => $stage->code,
                'stage_name' => $stage->name_ar,
                'order' => $stage->order,
                'approval_id' => $approval?->id,
                'approval_status' => $approval?->stageStatus?->code ?? 'not_started',
                'approval_status_name' => $approval?->stageStatus?->name_ar ?? 'لم يتم البدء',
                'is_completed' => $approval?->stageStatus?->code === 'approved',
            ];
        })->toArray();
    }

    /**
     * Get list of all available stages
     */
    public function getAllStages(bool $activeOnly = true): Collection
    {
        $query = Stage::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Get list of all available stage statuses
     */
    public function getAllStageStatuses(bool $activeOnly = true): Collection
    {
        $query = StageStatus::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Get stage flow details
     */
    public function getStageFlowDetails(Stage $fromStage, ?Stage $toStage = null): Collection
    {
        $query = StageFlow::where('from_stage_id', $fromStage->id)
            ->where('is_active', true)
            ->with('toStage');

        if ($toStage) {
            $query->where('to_stage_id', $toStage->id);
        }

        return $query->get();
    }

    /**
     * ============= ENHANCED ENTITY-BASED WORKFLOW METHODS =============
     */

    /**
     * Build entity approval chain from user's entity to root
     */
    public function buildEntityApprovalChain(Project $project, User $user): Collection
    {
        $userEntity = $user->getApprovalEntity();

        return $userEntity->getApprovalChainToRoot();
    }

    /**
     * Create approval stages from entity chain
     */
    public function createApprovalStagesFromEntityChain(Project $project, Collection $entityChain): Collection
    {
        $approvals = collect();
        $stepOrder = 1;

        foreach ($entityChain as $entity) {
            $approval = ProjectApproval::create([
                'project_id' => $project->id,
                'authority_id' => $entity->id,
                'drop' => 'entity_'.$entity->id,
                'step_order' => $stepOrder,
                'status' => ($stepOrder === 1) ? 'pending' : 'not_started',
                'created_by' => auth()->id(),
            ]);

            $approvals->push($approval);
            $stepOrder++;
        }

        // Log initialization
        $this->logActivity($project, 'initiated', [
            'notes' => 'Approval workflow initialized with '.$entityChain->count().' stages',
            'from_stage_name' => 'Draft',
            'to_stage_name' => $entityChain->first()->name,
            'to_stage_order' => 1,
            'metadata' => [
                'entity_chain' => $entityChain->pluck('name', 'id')->toArray(),
            ],
        ]);

        return $approvals;
    }

    /**
     * Handle Approved Status - Progress to next stage without requiring comments
     */
    public function handleApprovedStatus(ProjectApproval $approval, ?string $notes = null, ?string $attachment = null): void
    {
        DB::transaction(function () use ($approval, $notes, $attachment) {
            // Mark as approved
            $approval->update([
                'status' => 'approved',
                'notes' => $notes ?? 'Approved',
                'attachment' => $attachment,
                'reviewed_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity($approval->project, 'approved', [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'from_stage_order' => $approval->step_order,
                'notes' => $notes ?? 'Approved without comments',
            ]);

            // Progress to next stage
            $this->progressToNextEntityStage($approval);
        });
    }

    /**
     * Handle Financial & Technical Review Status
     */
    public function handleFinancialTechnicalReview(ProjectApproval $approval): void
    {
        DB::transaction(function () use ($approval) {
            // Update status
            $approval->update([
                'status' => 'financial_technical_review',
                'financial_review_completed' => false,
                'technical_review_completed' => false,
            ]);

            // Log activity
            $this->logActivity($approval->project, 'sent_for_review', [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'from_stage_order' => $approval->step_order,
                'notes' => 'Sent for simultaneous financial and technical review',
                'metadata' => [
                    'review_type' => 'dual',
                    'stage_id' => $approval->id,
                ],
            ]);

            // TODO: Notify financial and technical reviewers
            // This would integrate with your notification system
        });
    }

    /**
     * Handle Requires Action Status - Return to previous stage with mandatory reason
     */
    public function handleRequiresAction(ProjectApproval $approval, string $reason, ?string $attachment = null): void
    {
        if (empty($reason)) {
            throw new \Exception('Reason is mandatory for Requires Action status');
        }

        DB::transaction(function () use ($approval, $reason, $attachment) {
            // Mark current stage
            $approval->update([
                'status' => 'requires_action',
                'required_action' => $reason,
                'attachment' => $attachment,
                'reviewed_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity($approval->project, 'requires_action', [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'from_stage_order' => $approval->step_order,
                'action_details' => $reason,
                'notes' => 'Returned to previous stage for required action',
            ]);

            // Return to previous stage
            $this->returnToPreviousEntityStage($approval);
        });
    }

    /**
     * Handle Rejected Status - Return to previous stage with mandatory reason
     */
    public function handleRejected(ProjectApproval $approval, string $reason, ?string $attachment = null): void
    {
        if (empty($reason)) {
            throw new \Exception('Reason is mandatory for Rejected status');
        }

        DB::transaction(function () use ($approval, $reason, $attachment) {
            // Mark as rejected
            $approval->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'attachment' => $attachment,
                'reviewed_at' => now(),
                'created_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity($approval->project, 'rejected', [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'from_stage_order' => $approval->step_order,
                'action_details' => $reason,
                'notes' => 'Project rejected and returned to previous stage',
            ]);

            // Return to previous stage
            $this->returnToPreviousEntityStage($approval);
        });
    }

    /**
     * Handle Resubmit Status - Return to stage that requested action
     */
    public function handleResubmit(ProjectApproval $approval, ?string $notes = null, ?string $attachment = null): void
    {
        DB::transaction(function () use ($approval, $notes, $attachment) {
            // Get the stage that returned it (the one that set requires_action or rejected)
            $returningStage = $this->getReturningStage($approval);

            // Update approval to pending
            $approval->update([
                'status' => 'pending',
                'notes' => $notes,
                'attachment' => $attachment,
                'resubmitted_at' => now(),
                'resubmitted_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity($approval->project, 'resubmitted', [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'to_stage_name' => $returningStage ? $this->getEntityName($returningStage->entity_id) : 'Next Stage',
                'notes' => $notes ?? 'Resubmitted for review',
                'metadata' => [
                    'resubmitted_to_stage' => $returningStage?->id,
                ],
            ]);
        });
    }

    /**
     * Progress to next entity stage in hierarchy
     */
    private function progressToNextEntityStage(ProjectApproval $currentApproval): ?ProjectApproval
    {
        $project = $currentApproval->project;

        // Check if there's a next stage already created
        $nextApproval = ProjectApproval::where('project_id', $project->id)
            ->where('step_order', $currentApproval->step_order + 1)
            ->first();

        if ($nextApproval) {
            // Activate the next stage
            $nextApproval->update(['status' => 'pending']);

            $this->logActivity($project, 'approved', [
                'from_stage_name' => $this->getEntityName($currentApproval->entity_id),
                'from_stage_order' => $currentApproval->step_order,
                'to_stage_name' => $this->getEntityName($nextApproval->entity_id),
                'to_stage_order' => $nextApproval->step_order,
                'notes' => 'Progressed to next stage',
            ]);

            return $nextApproval;
        }

        // No more stages - project is fully approved
        $project->update(['status' => 'approved']);

        $this->logActivity($project, 'completed', [
            'from_stage_name' => $this->getEntityName($currentApproval->entity_id),
            'notes' => 'All approval stages completed',
        ]);

        return null;
    }

    /**
     * Return to previous entity stage
     */
    private function returnToPreviousEntityStage(ProjectApproval $currentApproval): ?ProjectApproval
    {
        $project = $currentApproval->project;

        if ($currentApproval->step_order <= 1) {
            // Cannot return further back
            $project->update(['status' => 'draft']);

            return null;
        }

        // Find previous stage
        $previousApproval = ProjectApproval::where('project_id', $project->id)
            ->where('step_order', $currentApproval->step_order - 1)
            ->first();

        if ($previousApproval) {
            // Reopen previous stage
            $previousApproval->update([
                'status' => 'pending',
                'reviewed_at' => null,
            ]);

            // Delete current and future stages
            ProjectApproval::where('project_id', $project->id)
                ->where('step_order', '>=', $currentApproval->step_order)
                ->delete();

            $project->update(['status' => 'pending_approval']);

            $this->logActivity($project, 'returned_to_previous', [
                'from_stage_name' => $this->getEntityName($currentApproval->entity_id),
                'to_stage_name' => $this->getEntityName($previousApproval->entity_id),
                'notes' => 'Returned to previous stage',
            ]);

            return $previousApproval;
        }

        return null;
    }

    /**
     * Get the stage that requested action (for resubmit)
     */
    private function getReturningStage(ProjectApproval $approval): ?ProjectApproval
    {
        // Find the next stage that has requires_action or rejected status
        return ProjectApproval::where('project_id', $approval->project_id)
            ->where('step_order', '>', $approval->step_order)
            ->whereIn('status', ['requires_action', 'rejected'])
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Get entity name by ID
     */
    private function getEntityName(int $entityId): string
    {
        $entity = InternalEntity::find($entityId);

        return $entity ? $entity->name : 'Unknown Entity';
    }

    /**
     * Complete financial or technical review
     */
    public function completeReview(ProjectApproval $approval, string $reviewType, ?string $notes = null, ?string $attachment = null): bool
    {
        if (! in_array($reviewType, ['financial', 'technical'])) {
            throw new \Exception('Invalid review type. Must be "financial" or "technical"');
        }

        DB::transaction(function () use ($approval, $reviewType, $notes, $attachment) {
            $approval->update([
                "{$reviewType}_review_completed" => true,
                "{$reviewType}_review_completed_at" => now(),
                "{$reviewType}_review_user_id" => auth()->id(),
                "{$reviewType}_review_notes" => $notes,
            ]);

            if ($attachment) {
                $approval->update(['attachment' => $attachment]);
            }

            $this->logActivity($approval->project, "{$reviewType}_review_completed", [
                'from_stage_name' => $this->getEntityName($approval->entity_id),
                'notes' => ucfirst($reviewType).' review completed',
                'action_details' => $notes,
            ]);
        });

        // Check if both reviews are completed
        $approval->refresh();
        if ($approval->financial_review_completed && $approval->technical_review_completed) {
            // Both completed - progress to next stage
            $this->progressToNextEntityStage($approval);

            return true;
        }

        return false;
    }
}
