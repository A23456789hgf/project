<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\Authority;
use App\Models\Domain;
use App\Models\ExecutiveActionCost;
use App\Models\ExecutiveActivity;
use App\Models\ExecutiveActivityAction;
use App\Models\InternalEntity;
use App\Models\Intervention;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryCost;
use App\Models\PreliminaryProcedure;
use App\Models\Program;
use App\Models\Project;
use App\Models\ProjectActivityHistory;
use App\Models\ProjectApproval;
use App\Models\Subdomain;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\EntityHierarchyService;
use App\Services\FrappeAPIService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProjectApprovalController extends Controller
{
    protected ApprovalService $approvalService;

    protected EntityHierarchyService $entityHierarchyService;

    protected ProjectService $projectService;

    protected FrappeAPIService $frappeService;

    public function __construct(
        ApprovalService $approvalService,
        EntityHierarchyService $entityHierarchyService,
        ProjectService $projectService,
        FrappeAPIService $frappeService
    ) {
        $this->approvalService = $approvalService;
        $this->entityHierarchyService = $entityHierarchyService;
        $this->projectService = $projectService;
        $this->frappeService = $frappeService;
    }

    /**
     * Handle project approval submission
     */
    public function approveProject(Request $request, Project $project): JsonResponse
    {
        // Validation rules - notes are mandatory for requires_action and rejected
        $rules = [
            'drop' => 'required|string',
            'entity_id' => 'required|integer|exists:internal_entities,id',
            'status' => 'required|in:approved,need_action,rejected,resubmitted,financial_technical_review',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
        ];

        // Make notes required for need_action and rejected
        if (in_array($request->status, ['need_action', 'rejected'])) {
            $rules['notes'] = 'required|string|min:10';
        }

        $validator = Validator::make($request->all(), $rules, [
            'notes.required' => 'السبب إلزامي عند اختيار حالة "يتطلب إجراء" أو "مرفوض"',
            'notes.min' => 'يجب أن يكون السبب 10 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $drop = $request->drop;
            $entityId = $request->entity_id;
            $status = $request->status;
            $notes = $request->notes;
            $currentUserId = auth()->id();

            // Retrieve the entity and its approval chain
            $entity = InternalEntity::find($entityId);
            $approvalChain = $entity ? $entity->getApprovalChainToRoot()->pluck('id')->toArray() : [];
            // You may store $approvalChain for further processing if needed

            // Find or create the project approval
            $projectApproval = ProjectApproval::where('project_id', $project->id)
                ->where('drop', $drop)
                ->first();

            // Fallback for older records where 'drop' was stored as entity name
            if (! $projectApproval && str_starts_with($drop, 'entity_')) {
                $entityIdFromDrop = str_replace('entity_', '', $drop);
                $entity = InternalEntity::find($entityIdFromDrop);
                if ($entity) {
                    $projectApproval = ProjectApproval::where('project_id', $project->id)
                        ->where('drop', $entity->name)
                        ->first();

                    if ($projectApproval) {
                        $projectApproval->update(['drop' => $drop]);
                        Log::info('Migrated project approval drop from name to ID-based format', [
                            'approval_id' => $projectApproval->id,
                            'old_drop' => $entity->name,
                            'new_drop' => $drop,
                        ]);
                    }
                }
            }

            // If approval record doesn't exist, create it
            if (! $projectApproval) {
                Log::info('Creating new approval record for stage', [
                    'project_id' => $project->id,
                    'drop' => $drop,
                    'entity_id' => $entityId,
                ]);

                // Get the step order for this stage
                $stepOrder = $this->getStepOrder($drop, $project);

                $projectApproval = ProjectApproval::create([
                    'project_id' => $project->id,
                    'drop' => $drop,
                    'entity_id' => $entityId,
                    'status' => 'pending',
                    'step_order' => $stepOrder,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                Log::info('Created new approval record', [
                    'approval_id' => $projectApproval->id,
                    'project_id' => $project->id,
                    'drop' => $drop,
                ]);
            }

            // Handle attachment
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $this->storeAttachment($request->file('attachment'), $project);
            }

            // Handle each status using the new enhanced workflow methods
            if ($status === 'approved') {
                // Approved: Progress to next stage without requiring comments
                $this->handleApprovedStatus($projectApproval, $currentUserId, $notes, $attachmentPath);

                DB::commit();

                // Refresh the project to get the updated status after progressToNextStage
                $project->refresh();

                // Determine the next stage drop identifier for UI auto‑selection
                $nextStageDrop = $this->getNextStage($projectApproval->drop, $project);

                return response()->json([
                    'success' => true,
                    'message' => 'تم الموافقة على المشروع بنجاح والانتقال للمرحلة التالية.',
                    'approval' => $projectApproval->fresh(['entity', 'reviewedByUser']),
                    'next_drop' => $nextStageDrop,
                    'project_status' => $project->status, // 'in_execution' when all approvals done
                ], 200);

            } elseif ($status === 'financial_technical_review') {
                // Financial & Technical Review: Send to both reviewers simultaneously
                $this->handleFinancialTechnicalReview($projectApproval, $currentUserId, $notes, $attachmentPath);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'تم إرسال المشروع للمراجعة المالية والفنية بنجاح.',
                    'approval' => $projectApproval->fresh(['entity', 'reviewedByUser']),
                ], 200);

            } elseif (in_array($status, ['need_action', 'rejected'])) {
                // Requires Action / Rejected: Return to previous stage with mandatory reason
                $this->handleRequiresActionOrRejected($projectApproval, $currentUserId, $status, $notes, $attachmentPath);

                DB::commit();

                $message = $status === 'need_action'
                    ? 'تم طلب إجراء على المشروع وإرجاعه إلى المرحلة السابقة.'
                    : 'تم رفض المشروع وإرجاعه إلى المرحلة السابقة.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'approval' => $projectApproval->fresh(['entity', 'reviewedByUser']),
                    'returned_to_previous' => true,
                ], 200);

            } elseif ($status === 'resubmitted') {
                // Resubmit: Return to stage that requested action
                $this->handleResubmit($projectApproval, $currentUserId, $notes, $attachmentPath);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'تم إعادة تقديم المشروع بنجاح.',
                    'approval' => $projectApproval->fresh(['entity', 'reviewedByUser']),
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Project approval error', [
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الموافقة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Approved Status
     * - Progress to next stage without requiring notes
     * - Automatically log with username and timestamp
     * - Update project_activity_history
     */
    private function handleApprovedStatus(ProjectApproval $approval, int $userId, ?string $notes = null, ?string $attachmentPath = null): void
    {
        $user = User::find($userId);
        $userName = $user ? $user->name : 'مستخدم غير معروف';
        $timestamp = Carbon::now();

        // Update approval record
        $approval->update([
            'status' => 'approved',
            'notes' => $notes ?: "تمت الموافقة تلقائياً بواسطة {$userName}",
            'attachment' => $attachmentPath,
            'reviewed_at' => $timestamp,
            'reviewed_by' => $userId,
        ]);

        // Log activity in project_activity_history with automatic message
        $this->logActivity(
            $approval->project_id,
            $userId,
            'approval',
            'approved',
            $notes ?: 'تمت الموافقة والانتقال للمرحلة التالية تلقائياً',
            [
                'entity_id' => $approval->entity_id,
                'drop' => $approval->drop,
                'drop_arabic' => $this->getDropArabic($approval->drop),
                'stage' => $this->getStageFromDrop($approval->drop),
                'attachment' => $attachmentPath,
                'user_name' => $userName,
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                'automatic_progression' => true,
            ]
        );

        // Progress to next stage automatically
        $this->progressToNextStage($approval);
    }

    /**
     * Handle Financial & Technical Review
     * - Send to both financial and technical reviewers simultaneously
     * - No notes or attachments required during transfer
     * - Project stays in same stage until both reviews completed
     */
    private function handleFinancialTechnicalReview(ProjectApproval $approval, int $userId, ?string $notes = null, ?string $attachmentPath = null): void
    {
        $user = User::find($userId);
        $userName = $user ? $user->name : 'مستخدم غير معروف';
        $timestamp = Carbon::now();

        // Update approval record - assign both reviewers simultaneously
        $approval->update([
            'status' => 'financial_technical_review',
            'notes' => $notes ?: "تم إرسال المشروع للمراجعة المالية والفنية المتزامنة بواسطة {$userName}",
            'attachment' => $attachmentPath,
            'reviewed_at' => $timestamp,
            'reviewed_by' => $userId,
            'financial_review_status' => 'pending',
            'technical_review_status' => 'pending',
            'financial_reviewer_id' => null,
            'technical_reviewer_id' => null,
            'is_completed' => false,
        ]);

        // Also update project status to financial_technical_review
        if ($approval->project) {
            $approval->project->update([
                'status' => 'financial_technical_review',
            ]);
        }

        // Log activity in project_activity_history
        $this->logActivity(
            $approval->project_id,
            $userId,
            'approval',
            'financial_technical_review',
            $notes ?: 'تم إرسال المشروع للمراجعة المالية والفنية بشكل متزامن. المشروع سيبقى في نفس المرحلة حتى يكمل كلا المراجعين مهامهم.',
            [
                'entity_id' => $approval->entity_id,
                'drop' => $approval->drop,
                'drop_arabic' => $this->getDropArabic($approval->drop),
                'stage' => $this->getStageFromDrop($approval->drop),
                'attachment' => $attachmentPath,
                'user_name' => $userName,
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                'simultaneous_review' => true,
                'financial_status' => 'pending',
                'technical_status' => 'pending',
            ]
        );

        // Project remains in same stage - no progression until both reviews complete
        Log::info('Financial and Technical Review initiated', [
            'project_id' => $approval->project_id,
            'approval_id' => $approval->id,
            'initiated_by' => $userName,
            'stage' => $this->getStageFromDrop($approval->drop),
        ]);
    }

    /**
     * Handle Requires Action / Rejected Status
     * - Notes are mandatory (enforced in validation)
     * - Return to previous stage automatically
     * - Block progression until action is taken
     */
    private function handleRequiresActionOrRejected(ProjectApproval $approval, int $userId, string $status, string $notes, ?string $attachmentPath = null): void
    {
        $user = User::find($userId);
        $userName = $user ? $user->name : 'مستخدم غير معروف';
        $timestamp = Carbon::now();

        $statusArabic = $status === 'need_action' ? 'يحتاج إلى إجراء' : 'مرفوض';

        // Update approval record
        $approval->update([
            'status' => $status,
            'notes' => $notes,
            'attachment' => $attachmentPath,
            'reviewed_at' => $timestamp,
            'reviewed_by' => $userId,
        ]);

        // Log activity in project_activity_history with detailed information
        $this->logActivity(
            $approval->project_id,
            $userId,
            'approval',
            $status,
            $notes,
            [
                'entity_id' => $approval->entity_id,
                'drop' => $approval->drop,
                'drop_arabic' => $this->getDropArabic($approval->drop),
                'stage' => $this->getStageFromDrop($approval->drop),
                'attachment' => $attachmentPath,
                'action_required' => $status === 'need_action',
                'user_name' => $userName,
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
                'status_arabic' => $statusArabic,
                'automatic_return' => true,
            ]
        );

        // Return to previous stage automatically
        $this->returnToPreviousStage($approval);

        Log::info('Project returned to previous stage', [
            'project_id' => $approval->project_id,
            'status' => $status,
            'returned_by' => $userName,
            'reason' => $notes,
        ]);
    }

    /**
     * Handle Resubmit Status
     * - Return to the stage that requested action
     * - Optional notes and attachments
     */
    private function handleResubmit(ProjectApproval $approval, int $userId, ?string $notes = null, ?string $attachmentPath = null): void
    {
        // Find the stage that requested action
        $actionRequiredStage = $this->findActionRequiredStage($approval->project_id);

        if ($actionRequiredStage) {
            // Update the stage that requested action
            $actionRequiredStage->update([
                'status' => 'pending',
                'notes' => $notes,
                'attachment' => $attachmentPath,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);
        }

        // Update current approval
        $approval->update([
            'status' => 'resubmitted',
            'notes' => $notes,
            'attachment' => $attachmentPath,
            'reviewed_at' => Carbon::now(),
            'reviewed_by' => $userId,
        ]);

        // Sync to Empowerment Department if needed
        try {
            $this->projectService->syncProjectToEmpowermentDepartment($approval->project);
        } catch (\Exception $e) {
            Log::error('Failed to sync to empowerment on resubmit', ['error' => $e->getMessage()]);
        }

        // Log activity
        $this->logActivity(
            $approval->project_id,
            $userId,
            'approval',
            'resubmitted',
            $notes,
            [
                'entity_id' => $approval->entity_id,
                'drop' => $approval->drop,
                'drop_arabic' => $this->getDropArabic($approval->drop),
                'stage' => $this->getStageFromDrop($approval->drop),
                'resubmitted_to_stage' => $actionRequiredStage ? $actionRequiredStage->drop : null,
                'attachment' => $attachmentPath,
            ]
        );
    }

    private function createReviewTasks(ProjectApproval $approval): void
    {
        // Simultaneous reviews are managed by project_approval columns
    }

    /**
     * Show financial review page
     */
    public function showFinancialReview(Project $project)
    {
        $project->load([
            'program',
            'domain',
            'subdomain',
            'mainObjectives',
            'specialObjectives',
            'supervisingAuthorities',
            'implementingEntities',
            'participatingEntities',
            'beneficiaryEntities',
            'financings.fundingSource',
            'cost',
            'preliminaryActivities.procedures.costs.financialItem',
            'executiveActivities.actions.costs.financialItem',
            'executiveActivities.actions.assignedEntities',
            'documents',
            'activityHistory.user',
            'currentApprovalStage',
        ]);

        $approval = ProjectApproval::with(['technicalReviewer', 'financialReviewer'])
            ->where('project_id', $project->id)
            ->where('status', 'financial_technical_review')
            ->latest()
            ->first() ?? ProjectApproval::with(['technicalReviewer', 'financialReviewer'])
            ->where('project_id', $project->id)
            ->latest()
            ->first();

        return view('projects.review.financial', compact('project', 'approval'));
    }

    /**
     * Show technical review page
     */
    public function showTechnicalReview(Project $project)
    {
        $project->load([
            'program',
            'domain',
            'subdomain',
            'mainObjectives',
            'specialObjectives',
            'supervisingAuthorities',
            'implementingEntities',
            'participatingEntities',
            'beneficiaryEntities',
            'financings.fundingSource',
            'cost',
            'preliminaryActivities.procedures.costs.financialItem',
            'executiveActivities.actions.costs.financialItem',
            'executiveActivities.actions.assignedEntities',
            'documents',
            'activityHistory.user',
            'currentApprovalStage',
        ]);

        $approval = ProjectApproval::with(['technicalReviewer', 'financialReviewer'])
            ->where('project_id', $project->id)
            ->where('status', 'financial_technical_review')
            ->latest()
            ->first() ?? ProjectApproval::with(['technicalReviewer', 'financialReviewer'])
            ->where('project_id', $project->id)
            ->latest()
            ->first();

        return view('projects.review.technical', compact('project', 'approval'));
    }

    /**
     * Submit financial review
     */
    public function submitFinancialReview(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'review_notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
            'status' => 'nullable|in:approved,need_action,rejected',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            return back()->with('error', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $approval = ProjectApproval::where('project_id', $project->id)
                ->where('status', 'financial_technical_review')
                ->latest()
                ->first();

            $userId = auth()->id();
            $notes = $request->input('review_notes') ?? $request->input('notes');
            $status = $request->input('status', 'approved');
            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $attachmentPath = $this->storeAttachment($request->file('attachment'), $project);
            }

            // Update preliminary costs if submitted
            if ($request->has('preliminary_costs') && is_array($request->preliminary_costs)) {
                foreach ($request->preliminary_costs as $costId => $data) {
                    $cost = PreliminaryCost::find($costId);
                    if ($cost) {
                        $qty = (float) ($data['quantity'] ?? $cost->quantity);
                        $price = (float) ($data['unit_price'] ?? $cost->unit_price);
                        $cost->update([
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_cost' => $qty * $price,
                        ]);
                    }
                }
            }

            // Update executive costs if submitted
            if ($request->has('executive_costs') && is_array($request->executive_costs)) {
                foreach ($request->executive_costs as $costId => $data) {
                    $cost = ExecutiveActionCost::find($costId);
                    if ($cost) {
                        $qty = (float) ($data['quantity'] ?? $cost->quantity);
                        $price = (float) ($data['unit_price'] ?? $cost->unit_price);
                        $cost->update([
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total_cost' => $qty * $price,
                        ]);
                    }
                }
            }

            if ($approval) {
                // Update financial review status
                $approval->update([
                    'financial_review_status' => $status,
                    'financial_reviewer_id' => $userId,
                    'financial_reviewed_at' => Carbon::now(),
                    'financial_notes' => $notes,
                    'financial_attachment' => $attachmentPath,
                ]);

                // Check if both reviews are completed
                $bothCompleted = $this->checkBothReviewsCompleted($approval);

                if ($bothCompleted) {
                    $overallStatus = $this->determineOverallReviewStatus($approval);

                    if ($overallStatus === 'approved') {
                        $this->progressToNextStage($approval);
                    } else {
                        $this->handleReviewRejection($approval, $overallStatus);
                    }
                }
            } else {
                $bothCompleted = false;
            }

            // Log activity
            $this->logActivity(
                $project->id,
                $userId,
                'financial_review',
                $status,
                $notes ?? 'تمت المراجعة المالية بنجاح',
                [
                    'approval_id' => $approval?->id,
                    'attachment' => $attachmentPath,
                ]
            );

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تقديم المراجعة المالية بنجاح.',
                    'both_completed' => $bothCompleted,
                    'overall_status' => ($approval && $bothCompleted) ? $this->determineOverallReviewStatus($approval) : null,
                ]);
            }

            return redirect()->route('projects.index')->with('success', 'تم تقديم المراجعة المالية بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit financial review error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تقديم المراجعة المالية: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'حدث خطأ أثناء تقديم المراجعة المالية: '.$e->getMessage());
        }
    }

    /**
     * Submit technical review
     */
    public function submitTechnicalReview(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'review_notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,ppt,pptx,xls,xlsx,txt,csv|max:20480',
            'status' => 'nullable|in:approved,need_action,rejected',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            return back()->with('error', $validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $approval = ProjectApproval::where('project_id', $project->id)
                ->where('status', 'financial_technical_review')
                ->latest()
                ->first();

            $userId = auth()->id();
            $notes = $request->input('review_notes') ?? $request->input('notes');
            $status = $request->input('status', 'approved');
            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $attachmentPath = $this->storeAttachment($request->file('attachment'), $project);
            }

            // Update preliminary activities
            if ($request->has('preliminary_activities') && is_array($request->preliminary_activities)) {
                foreach ($request->preliminary_activities as $actId => $data) {
                    $activity = PreliminaryActivity::find($actId);
                    if ($activity && isset($data['activity'])) {
                        $activity->update(['activity' => $data['activity']]);
                    }
                }
            }

            // Update preliminary procedures
            if ($request->has('preliminary_procedures') && is_array($request->preliminary_procedures)) {
                foreach ($request->preliminary_procedures as $procId => $data) {
                    $procedure = PreliminaryProcedure::find($procId);
                    if ($procedure) {
                        $updateData = [];
                        if (isset($data['procedure'])) {
                            $updateData['procedure'] = $data['procedure'];
                        }
                        if (isset($data['start_date'])) {
                            $updateData['start_date'] = $data['start_date'];
                        }
                        if (isset($data['end_date'])) {
                            $updateData['end_date'] = $data['end_date'];
                        }

                        if (! empty($updateData['start_date']) && ! empty($updateData['end_date'])) {
                            $start = Carbon::parse($updateData['start_date']);
                            $end = Carbon::parse($updateData['end_date']);
                            $updateData['duration_days'] = max(0, $end->diffInDays($start));
                        }
                        $procedure->update($updateData);
                    }
                }
            }

            // Update executive activities
            if ($request->has('executive_activities') && is_array($request->executive_activities)) {
                foreach ($request->executive_activities as $actId => $data) {
                    $activity = ExecutiveActivity::find($actId);
                    if ($activity && isset($data['activity_name'])) {
                        $activity->update(['activity_name' => $data['activity_name']]);
                    }
                }
            }

            // Update executive actions
            if ($request->has('executive_actions') && is_array($request->executive_actions)) {
                foreach ($request->executive_actions as $actId => $data) {
                    $action = ExecutiveActivityAction::find($actId);
                    if ($action) {
                        $updateData = [];
                        if (isset($data['action_name'])) {
                            $updateData['action_name'] = $data['action_name'];
                        }
                        if (isset($data['start_date'])) {
                            $updateData['start_date'] = $data['start_date'];
                        }
                        if (isset($data['end_date'])) {
                            $updateData['end_date'] = $data['end_date'];
                        }
                        $action->update($updateData);

                        if (isset($data['responsible_entity'])) {
                            $assigned = $action->assignedEntities()->first();
                            if ($assigned) {
                                $assigned->update(['entity_name' => $data['responsible_entity']]);
                            }
                        }
                    }
                }
            }

            if ($approval) {
                // Update technical review status
                $approval->update([
                    'technical_review_status' => $status,
                    'technical_reviewer_id' => $userId,
                    'technical_reviewed_at' => Carbon::now(),
                    'technical_notes' => $notes,
                    'technical_attachment' => $attachmentPath,
                ]);

                // Check if both reviews are completed
                $bothCompleted = $this->checkBothReviewsCompleted($approval);

                if ($bothCompleted) {
                    $overallStatus = $this->determineOverallReviewStatus($approval);

                    if ($overallStatus === 'approved') {
                        $this->progressToNextStage($approval);
                    } else {
                        $this->handleReviewRejection($approval, $overallStatus);
                    }
                }
            } else {
                $bothCompleted = false;
            }

            // Log activity
            $this->logActivity(
                $project->id,
                $userId,
                'technical_review',
                $status,
                $notes ?? 'تمت المراجعة الفنية بنجاح',
                [
                    'approval_id' => $approval?->id,
                    'attachment' => $attachmentPath,
                ]
            );

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تقديم المراجعة الفنية بنجاح.',
                    'both_completed' => $bothCompleted,
                    'overall_status' => ($approval && $bothCompleted) ? $this->determineOverallReviewStatus($approval) : null,
                ]);
            }

            return redirect()->route('projects.index')->with('success', 'تم تقديم المراجعة الفنية بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit technical review error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تقديم المراجعة الفنية: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'حدث خطأ أثناء تقديم المراجعة الفنية: '.$e->getMessage());
        }
    }

    /**
     * Check if both financial and technical reviews are completed
     */
    private function checkBothReviewsCompleted(ProjectApproval $approval): bool
    {
        return ! in_array($approval->financial_review_status, ['pending', null])
            && ! in_array($approval->technical_review_status, ['pending', null]);
    }

    /**
     * Determine overall status based on both reviews
     */
    private function determineOverallReviewStatus(ProjectApproval $approval): string
    {
        $financialStatus = $approval->financial_review_status;
        $technicalStatus = $approval->technical_review_status;

        // If any review is rejected, overall status is rejected
        if ($financialStatus === 'rejected' || $technicalStatus === 'rejected') {
            return 'rejected';
        }

        // If any review requires action, overall status is need_action
        if ($financialStatus === 'need_action' || $technicalStatus === 'need_action') {
            return 'need_action';
        }

        // Both are approved
        if ($financialStatus === 'approved' && $technicalStatus === 'approved') {
            return 'approved';
        }

        return 'pending';
    }

    /**
     * Handle review rejection or need_action
     */
    private function handleReviewRejection(ProjectApproval $approval, string $status): void
    {
        // Update approval status
        $approval->update([
            'status' => $status,
            'reviewed_at' => Carbon::now(),
        ]);

        // Return to previous stage if needed
        if (in_array($status, ['need_action', 'rejected'])) {
            $this->returnToPreviousStage($approval);
        }

        // Log activity
        $this->logActivity(
            $approval->project_id,
            auth()->id(),
            'review_completion',
            $status,
            'تم الانتهاء من المراجعة المالية والفنية',
            [
                'approval_id' => $approval->id,
                'financial_status' => $approval->financial_review_status,
                'technical_status' => $approval->technical_review_status,
            ]
        );
    }

    /**
     * Progress to next stage in the workflow
     */
    private function progressToNextStage(ProjectApproval $currentApproval): void
    {
        $projectId = $currentApproval->project_id;

        Log::info('progressToNextStage called', [
            'project_id' => $projectId,
            'current_approval_id' => $currentApproval->id,
            'current_drop' => $currentApproval->drop,
        ]);

        // Get the next stage based on the workflow configuration
        $project = Project::find($projectId);
        $nextStage = $this->getNextStage($currentApproval->drop, $project);

        Log::info('Next stage determined', [
            'next_stage' => $nextStage,
            'has_next_stage' => $nextStage !== null,
        ]);

        if ($nextStage) {
            // Update current approval as completed
            $currentApproval->update(['is_completed' => true]);

            // Get full approval chain once to retrieve correct entity_id and step_order
            $stages = $this->getProjectApprovalChain($project);
            $nextStageData = collect($stages)->firstWhere('code', $nextStage);

            $nextEntityId = $nextStageData['entity_id'] ?? null;
            $nextStepOrder = $nextStageData['order'] ?? 1;

            // If still null, try fallback extraction for legacy compatibility
            if ($nextEntityId === null && str_starts_with($nextStage, 'entity_')) {
                $nextEntityId = (int) str_replace('entity_', '', $nextStage);
            }

            Log::info('Creating/updating next approval stage', [
                'next_stage' => $nextStage,
                'next_entity_id' => $nextEntityId,
                'next_step_order' => $nextStepOrder,
            ]);

            ProjectApproval::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'drop' => $nextStage,
                ],
                [
                    'entity_id' => $nextEntityId,
                    'status' => 'pending',
                    'step_order' => $nextStepOrder,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            // Update project status and current stage
            Log::info('Updating project current_stage and current_stage_order', [
                'old_stage' => $project->current_stage,
                'new_stage' => $nextStage,
                'old_order' => $project->current_stage_order,
                'new_order' => $nextStepOrder,
            ]);

            Project::where('id', $projectId)->update([
                'current_stage' => $nextStage,
                'current_stage_order' => $nextStepOrder,
                'updated_at' => Carbon::now(),
            ]);

            Log::info('Stage progression completed successfully', [
                'project_id' => $projectId,
                'to_stage' => $nextStage,
                'entity_id' => $nextEntityId,
            ]);

            // Sync to Empowerment Department if needed
            try {
                $this->projectService->syncProjectToEmpowermentDepartment($project);
            } catch (\Exception $e) {
                Log::error('Failed to sync to empowerment on stage progression', ['error' => $e->getMessage()]);
            }
            // Log activity
            $this->logActivity(
                $projectId,
                auth()->id(),
                'stage_progression',
                'progressed',
                'تم الانتقال إلى المرحلة التالية',
                [
                    'from_stage' => $currentApproval->drop,
                    'to_stage' => $nextStage,
                    'entity_id' => $nextEntityId,
                ]
            );
        } else {
            Log::info('No next stage - all approvals done, transitioning project to in_execution');

            // Mark current approval as completed
            $currentApproval->update(['is_completed' => true]);

            // All approvals done: move project to in_execution
            $targetProject = Project::find($projectId);
            if ($targetProject) {
                $targetProject->update([
                    'status' => 'in_execution',
                    'current_stage' => null,
                    'current_stage_order' => null,
                ]);

                // =====================================================
                // 🔹 مزامنة جميع الجهات المرتبطة بالمشروع مع ERPNext
                // قبل إرسال المشروع نفسه
                // =====================================================
                $this->syncProjectEntities($targetProject);

                // =====================================================
                // 🔹 إرسال البيانات إلى ERPNext عند دخول مرحلة التنفيذ
                // =====================================================
                $this->createProjectInErpNext($targetProject);
            }

            // Log completion of approval cycle
            $this->logActivity(
                $projectId,
                auth()->id(),
                'project_completion',
                'in_execution',
                'اكتملت جميع مراحل الاعتماد — تم تحويل المشروع لمرحلة التنفيذ',
                [
                    'final_stage' => $currentApproval->drop,
                ]
            );

            // Sync empowerment/ERPNext on transition to execution (إن وجد)
            try {
                $this->projectService->syncProjectToEmpowermentDepartment($project);
            } catch (\Exception $e) {
                Log::error('Failed to sync empowerment on project in_execution transition', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * مزامنة جميع الجهات المرتبطة بالمشروع مع ERPNext
     * (البرنامج، المجال، المجال الفرعي، التدخل، السلطات، الكيانات المشاركة، المستفيدة، مصادر التمويل، ...)
     */
    protected function syncProjectEntities(Project $project): void
    {
        // 1. البرنامج (Program)
        if ($project->program_id) {
            $program = Program::find($project->program_id);
            if ($program) {
                $this->syncGenericEntity($program, 'Program', 'name');
            }
        }

        // 2. المجال (Domain)
        if ($project->domain_id) {
            $domain = Domain::find($project->domain_id);
            if ($domain) {
                $this->syncGenericEntity($domain, 'Domain', 'name');
            }
        }

        // 3. المجال الفرعي (Subdomain)
        if ($project->subdomain_id) {
            $subdomain = Subdomain::find($project->subdomain_id);
            if ($subdomain) {
                $this->syncGenericEntity($subdomain, 'Subdomain', 'name');
            }
        }

        // 4. التدخل (Intervention)
        if ($project->intervention_id) {
            $intervention = Intervention::find($project->intervention_id);
            if ($intervention) {
                $this->syncGenericEntity($intervention, 'Intervention', 'name');
            }
        }

        // 5. السلطات المشرفة (Supervising Authorities) - علاقة many-to-many
        if (method_exists($project, 'supervisingAuthorities')) {
            foreach ($project->supervisingAuthorities as $authority) {
                try {
                    $this->frappeService->syncAuthority($authority);
                } catch (\Exception $e) {
                    Log::error('Error syncing supervising authority', [
                        'authority_id' => $authority->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 6. الكيانات المنفذة (Implementing Entities)
        if (method_exists($project, 'implementingEntities')) {
            foreach ($project->implementingEntities as $entity) {
                try {
                    $this->frappeService->syncParticipatingEntity($entity);
                } catch (\Exception $e) {
                    Log::error('Error syncing implementing entity', [
                        'entity_id' => $entity->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 7. الكيانات المشاركة (Participating Entities)
        if (method_exists($project, 'participatingEntities')) {
            foreach ($project->participatingEntities as $entity) {
                try {
                    $this->frappeService->syncParticipatingEntity($entity);
                } catch (\Exception $e) {
                    Log::error('Error syncing participating entity', [
                        'entity_id' => $entity->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 8. الكيانات المستفيدة (Beneficiary Entities)
        if (method_exists($project, 'beneficiaryEntities')) {
            foreach ($project->beneficiaryEntities as $entity) {
                try {
                    $this->frappeService->syncBeneficiaryEntity($entity);
                } catch (\Exception $e) {
                    Log::error('Error syncing beneficiary entity', [
                        'entity_id' => $entity->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 9. مصادر التمويل (Funding Sources) عبر علاقة التمويلات
        if (method_exists($project, 'financings')) {
            foreach ($project->financings as $financing) {
                if ($financing->fundingSource) {
                    try {
                        $this->frappeService->syncFundingSource($financing->fundingSource);
                    } catch (\Exception $e) {
                        Log::error('Error syncing funding source', [
                            'source_id' => $financing->fundingSource->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // 10. أي جهات داخلية أخرى مرتبطة مباشرة (إذا وجدت)
        // مثال: إذا كان هناك علاقة project->internalEntity
        // يمكنك إضافتها هنا
    }

    /**
     * دالة عامة لمزامنة أي نموذج مع ERPNext ككيان
     *
     * @param  mixed  $model
     */
    protected function syncGenericEntity($model, string $doctype, string $nameField): void
    {
        if (! $model) {
            return;
        }

        $name = $model->{$nameField} ?? $model->name ?? '';
        if (empty($name)) {
            Log::warning('Cannot sync entity: missing name field', ['model' => get_class($model), 'id' => $model->id]);

            return;
        }

        $data = [
            $nameField => $name,
            'entity_type' => $doctype,
            'entity_code' => (string) $model->id,
            'is_active' => 1,
        ];

        // يمكن إضافة حقول إضافية حسب الحاجة
        if (property_exists($model, 'description') && $model->description) {
            $data['description'] = $model->description;
        }

        try {
            $result = $this->frappeService->getOrCreateEntity($doctype, $nameField, $data);
            if ($result) {
                Log::info('Entity synced to ERPNext', ['doctype' => $doctype, 'name' => $name]);
            } else {
                Log::error('Failed to sync entity to ERPNext', ['doctype' => $doctype, 'name' => $name]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while syncing entity', [
                'doctype' => $doctype,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * إنشاء/إرسال بيانات المشروع إلى ERPNext عند دخوله مرحلة التنفيذ (تلقائياً)
     */
    private function createProjectInErpNext(Project $project): void
    {
        // في مرحلة التنفيذ نريد إرسال التحديث إلى ERPNext (PUT) حتى وإن تم إرساله مسبقاً كمسودة
        if (! empty($project->erpnext_project_id)) {
            Log::info('المشروع يمتلك معرف في ERPNext مسبقاً، سيتم تحديث بياناته لمرحلة التنفيذ.', [
                'project_id' => $project->id,
                'erpnext_project_id' => $project->erpnext_project_id,
            ]);
        }

        // 3. التحقق من البيانات المطلوبة قبل تنفيذ POST
        if (empty($project->project_name)) {
            $errorMsg = 'بيانات المشروع غير مكتملة (الاسم مفقود). لم يتم الإرسال إلى ERPNext.';
            Log::warning($errorMsg, ['project_id' => $project->id]);

            // 6. الاحتفاظ بسجل الخطأ
            $project->update([
                'sync_status' => 'failed',
                'frappe_sync_status' => 'failed',
                'sync_error' => $errorMsg,
            ]);

            return;
        }

        try {
            // 1. استدعاء دالة الإرسال (POST)
            $frappeResult = $this->frappeService->sendProjectOnExecution($project, true);

            $frappeProjectId = $frappeResult['data']['name'] ?? $frappeResult['name'] ?? null;

            // 5. حفظ حالة المزامنة للنجاح
            $project->update([
                'frappe_synced_at' => now(),
                'frappe_project_id' => $frappeProjectId,
                'frappe_project_name' => $frappeResult['data']['project_name'] ?? null,
                'frappe_sync_status' => 'success',
                'erpnext_project_id' => $frappeProjectId,
                'sync_status' => 'synced',
                'synced_to_erpnext_at' => now(),
                'execution_started_at' => now(),
                'sync_error' => null, // مسح الأخطاء السابقة إن وجدت
            ]);

            // 4. تسجيل رسالة نجاح تحتوي على معرف المشروع وحالة الإرسال
            Log::info('تم إرسال المشروع إلى ERPNext بنجاح', [
                'project_id' => $project->id,
                'frappe_project_id' => $frappeProjectId,
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            // 6. الاحتفاظ بسجل الخطأ للسماح بالتشخيص وإعادة المحاولة لاحقاً
            $project->update([
                'frappe_sync_status' => 'failed',
                'sync_status' => 'failed',
                'sync_error' => substr($e->getMessage(), 0, 1000),
            ]);

            // 4. تسجيل رسالة خطأ تحتوي على سبب الفشل وتفاصيل الخطأ
            Log::error('فشل إرسال المشروع إلى ERPNext', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'status' => 'failed',
            ]);
        }
    }

    /**
     * Return to previous stage
     */
    private function returnToPreviousStage(ProjectApproval $currentApproval): void
    {
        $projectId = $currentApproval->project_id;
        $project = Project::find($projectId);

        // Get the previous stage
        $previousStage = $this->getPreviousStage($currentApproval->drop, $project);

        if ($previousStage) {
            // Update current approval to indicate return
            $currentApproval->update([
                'returned_from_stage' => $currentApproval->drop,
                'returned_at' => Carbon::now(),
            ]);

            // Update previous approval stage to pending
            ProjectApproval::where('project_id', $projectId)
                ->where('drop', $previousStage)
                ->update([
                    'status' => 'pending',
                    'notes' => null,
                    'attachment' => null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'updated_at' => Carbon::now(),
                ]);

            // Update project status
            $prevStepOrder = $this->getStepOrder($previousStage, $project);
            Project::where('id', $projectId)->update([
                'current_stage' => $previousStage,
                'current_stage_order' => $prevStepOrder,
                'status' => 'rolled_back_for_review',
                'updated_at' => Carbon::now(),
            ]);

            // Log activity
            $this->logActivity(
                $projectId,
                auth()->id(),
                'stage_regression',
                'returned',
                'تم إرجاع المشروع إلى المرحلة السابقة',
                [
                    'from_stage' => $currentApproval->drop,
                    'to_stage' => $previousStage,
                    'reason' => $currentApproval->notes,
                ]
            );
        }
    }

    /**
     * Store attachment file and return path
     */
    private function storeAttachment($file, Project $project): string
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
        $uniqueName = Str::slug($fileName).'_'.time().'.'.$extension;

        $path = $file->storeAs(
            "projects/{$project->id}/approvals",
            $uniqueName,
            'public'
        );

        return $path;
    }

    /**
     * Log activity to project_activity_history
     */
    private function logActivity(int $projectId, int $userId, string $actionType, string $status, ?string $notes = null, array $metadata = []): void
    {
        ProjectActivityHistory::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'status' => $status,
            'notes' => $notes,
            'metadata' => json_encode($metadata),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Get the full approval chain for a project
     */
    private function getProjectApprovalChain(Project $project): array
    {
        $projectService = app(ProjectService::class);
        $stages = $projectService->getApprovalStages($project);

        return collect($stages)->map(function ($stage) {
            return [
                'code' => $stage['drop'],
                'order' => $stage['drop_order'],
                'entity_id' => $stage['entity_id'],
                'name_ar' => $stage['stage_name'],
                'name_en' => $stage['stage_name_en'] ?? $stage['stage_name'],
            ];
        })->toArray();
    }

    /**
     * Get next stage in workflow
     */
    private function getNextStage(string $currentDrop, Project $project): ?string
    {
        Log::info('getNextStage called', [
            'currentDrop' => $currentDrop,
            'project_id' => $project->id,
            'project_current_stage' => $project->current_stage,
        ]);

        $stages = $this->getProjectApprovalChain($project);

        Log::info('Approval chain retrieved', [
            'total_stages' => count($stages),
            'stages' => $stages,
        ]);

        $currentIndex = -1;

        foreach ($stages as $index => $stage) {
            if ($stage['code'] === $currentDrop) {
                $currentIndex = $index;
                Log::info('Found current stage in chain', [
                    'index' => $index,
                    'stage_code' => $stage['code'],
                    'stage_name' => $stage['stage_name'] ?? 'N/A',
                ]);
                break;
            }
        }

        if ($currentIndex !== -1 && isset($stages[$currentIndex + 1])) {
            $nextStage = $stages[$currentIndex + 1]['code'];

            // If the next stage is implementation, it means all entity approvals are done.
            // Returning null will trigger the transition to 'in_execution'.
            if ($nextStage === 'implementation') {
                Log::info('Next stage is implementation — project ready for in_execution', [
                    'currentDrop' => $currentDrop,
                ]);

                return null;
            }

            Log::info('Next stage found', [
                'next_stage_index' => $currentIndex + 1,
                'next_stage_code' => $nextStage,
                'next_stage_name' => $stages[$currentIndex + 1]['name_ar'] ?? 'N/A',
            ]);

            return $nextStage;
        }

        // No next stage found — the project has completed all entity approvals.
        // Returning null triggers the in_execution transition in progressToNextStage.
        Log::info('No next stage in approval chain — project ready for in_execution', [
            'currentDrop' => $currentDrop,
            'currentIndex' => $currentIndex,
        ]);

        return null;
    }

    /**
     * Get previous stage in workflow
     */
    private function getPreviousStage(string $currentDrop, Project $project): ?string
    {
        $stages = $this->getProjectApprovalChain($project);
        $currentIndex = -1;

        foreach ($stages as $index => $stage) {
            if ($stage['code'] === $currentDrop) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex > 0) {
            return $stages[$currentIndex - 1]['code'];
        }

        // No previous stage found — this is already the first entity stage.
        return null;
    }

    /**
     * Get step order for a stage
     */
    private function getStepOrder(string $stageCode, Project $project): int
    {
        $stages = $this->getProjectApprovalChain($project);
        foreach ($stages as $stage) {
            if ($stage['code'] === $stageCode) {
                return $stage['order'];
            }
        }

        $order = [
            'assembly' => 1,
            'union' => 2,
            'committee' => 3,
            'implementation' => 4,
        ];

        return $order[$stageCode] ?? 99;
    }

    /**
     * Get stage from drop value
     */
    private function getStageFromDrop(string $drop): string
    {
        if (str_starts_with($drop, 'entity_')) {
            $id = str_replace('entity_', '', $drop);
            $entity = InternalEntity::find($id);

            return $entity->name ?? $drop;
        }

        $stages = [
            'assembly' => 'موافقة الجمعية',
            'union' => 'موافقة الاتحاد',
            'committee' => 'موافقة اللجنة',
            'implementation' => 'مرحلة التنفيذ',
        ];

        return $stages[$drop] ?? $drop;
    }

    private function getUsersWithPermission(string $permission): Collection
    {
        // Update to correct permission slugs
        $map = [
            'review_financial' => 'approvals.financial-review',
            'review_technical' => 'approvals.technical-review',
        ];

        $slug = $map[$permission] ?? $permission;

        return User::whereHas('permissions', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->get();
    }

    /**
     * Find the stage that requested action
     */
    private function findActionRequiredStage(int $projectId): ?ProjectApproval
    {
        return ProjectApproval::where('project_id', $projectId)
            ->whereIn('status', ['need_action', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get project movement log
     */
    public function getProjectMovementLog(Project $project): JsonResponse
    {
        try {
            $approvals = ProjectApproval::withTrashed()
                ->where('project_id', $project->id)
                ->with(['entity', 'reviewedByUser'])
                ->orderBy('created_at', 'desc')
                ->get();

            $log = $approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'timestamp' => ($approval->reviewed_at ?? $approval->updated_at ?? $approval->created_at)->format('Y-m-d H:i:s'),
                    'stage' => $this->getStageFromDrop($approval->drop),
                    'authority' => $approval->entity->name ?? 'غير معروف',
                    'reviewer' => $approval->reviewedByUser->name ?? 'غير معروف',
                    'status' => $approval->status,
                    'status_arabic' => $this->getStatusArabic($approval->status),
                    'notes' => $approval->notes,
                    'attachment' => $approval->attachment ? [
                        'name' => basename($approval->attachment),
                        'url' => Storage::url($approval->attachment),
                        'size' => Storage::exists($approval->attachment) ?
                            round(Storage::size($approval->attachment) / 1024 / 1024, 2).' MB' : 'غير متوفر',
                    ] : null,
                    'step_order' => $approval->step_order,
                    'is_return' => in_array($approval->status, ['need_action', 'needs_revision', 'rejected']),
                    'requires_return_to' => $approval->requires_return_to,
                ];
            });

            $approvals_history = [];
            $rejections_history = [];
            $required_actions_history = [];

            foreach ($log as $item) {
                if ($item['status'] === 'approved' || $item['status'] === 'pending') {
                    $approvals_history[] = $item;
                } elseif ($item['status'] === 'rejected') {
                    $rejections_history[] = $item;
                } elseif (in_array($item['status'], ['need_action', 'needs_revision', 'resubmit'])) {
                    $required_actions_history[] = $item;
                }
            }

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'movement_log' => $log,
                'approvals_history' => $approvals_history,
                'rejections_history' => $rejections_history,
                'required_actions_history' => $required_actions_history,
                'project_status' => $this->getCurrentProjectStatus($project),
                'total_movements' => count($log),
            ]);

        } catch (\Exception $e) {
            Log::error('Get project movement log error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب سجل حركة المشروع: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current approval status for a project
     */
    public function getApprovalStatus(Project $project): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'status' => $this->getCurrentProjectStatus($project),
            ]);
        } catch (\Exception $e) {
            Log::error('Get approval status error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب حالة الموافقة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial data for the project
     */
    public function getFinancialData(Project $project): JsonResponse
    {
        try {
            $totalBudget = $project->total_budget ?? 0;
            $financings = $project->financings()->with(['fundingSource', 'authority'])->get();

            return response()->json([
                'success' => true,
                'total_budget' => $totalBudget,
                'financings' => $financings,
                'project_name' => $project->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Get financial data error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب البيانات المالية: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval attachments for a project
     */
    public function getApprovalAttachments(Project $project): JsonResponse
    {
        try {
            $attachments = ProjectApproval::where('project_id', $project->id)
                ->whereNotNull('attachment')
                ->with(['authority:id,agency_name', 'createdBy:id,name'])
                ->get()
                ->map(function ($approval) {
                    return [
                        'id' => $approval->id,
                        'name' => basename($approval->attachment),
                        'url' => Storage::url($approval->attachment),
                        'size' => Storage::exists($approval->attachment) ?
                            round(Storage::size($approval->attachment) / 1024 / 1024, 2).' MB' : 'غير متوفر',
                        'stage' => $this->getDropArabic($approval->drop),
                        'authority' => $approval->entity->name ?? 'غير معروف',
                        'status' => $approval->status,
                        'status_arabic' => $this->getStatusArabic($approval->status),
                        'reviewer_name' => $approval->createdBy->name ?? 'غير معروف',
                    ];
                });

            return response()->json([
                'success' => true,
                'attachments' => $attachments,
                'total_count' => $attachments->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Get approval attachments error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المرفقات.',
            ], 500);
        }
    }

    /**
     * Download approval attachment
     */
    public function downloadAttachment($projectId, $approvalId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $approval = ProjectApproval::where('project_id', $project->id)->findOrFail($approvalId);

            if (! $approval->attachment || ! Storage::exists($approval->attachment)) {
                abort(404, 'الملف غير موجود.');
            }

            return Storage::download($approval->attachment);

        } catch (\Exception $e) {
            Log::error('Download attachment error', [
                'project_id' => $projectId,
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'حدث خطأ أثناء تحميل الملف.');
        }
    }

    /**
     * View attachment directly in browser
     */
    public function viewAttachment($projectId, $approvalId)
    {
        try {
            $project = Project::findOrFail($projectId);
            $approval = ProjectApproval::where('project_id', $project->id)->findOrFail($approvalId);

            if (! $approval->attachment || ! Storage::exists($approval->attachment)) {
                abort(404, 'الملف غير موجود.');
            }

            return response()->file(Storage::path($approval->attachment));

        } catch (\Exception $e) {
            Log::error('View attachment error', [
                'project_id' => $projectId,
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'الملف غير موجود.');
        }
    }

    /**
     * Get file content for text-based files
     */
    public function getFileContent($projectId, $approvalId): JsonResponse
    {
        try {
            $project = Project::findOrFail($projectId);
            $approval = ProjectApproval::where('project_id', $project->id)->findOrFail($approvalId);

            if (! $approval->attachment || ! Storage::exists($approval->attachment)) {
                return response()->json(['success' => false, 'message' => 'الملف غير موجود.'], 404);
            }

            $content = Storage::get($approval->attachment);

            return response()->json([
                'success' => true,
                'content' => $content,
                'file_name' => basename($approval->attachment),
                'file_size' => $this->formatFileSize(Storage::size($approval->attachment)),
            ]);

        } catch (\Exception $e) {
            Log::error('Get file content error', [
                'project_id' => $projectId,
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'خطأ في قراءة ملف: '.$e->getMessage()], 500);
        }
    }

    /**
     * Get detailed audit logs for project approvals
     */
    public function getApprovalAuditLog(Project $project): JsonResponse
    {
        try {
            $approvals = ProjectApproval::withTrashed()
                ->where('project_id', $project->id)
                ->with(['authority', 'createdBy', 'stage'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($approval) {
                    return [
                        'timestamp' => ($approval->reviewed_at ?? $approval->updated_at ?? $approval->created_at)->format('Y-m-d H:i:s'),
                        'stage' => $approval->stage->name_ar ?? $this->getDropArabic($approval->drop),
                        'authority' => $approval->entity->name ?? 'غير معروف',
                        'reviewer' => $approval->createdBy->name ?? 'غير معروف',
                        'status' => $this->getStatusArabic($approval->status),
                        'notes' => $approval->notes,
                    ];
                });

            return response()->json([
                'success' => true,
                'audit_logs' => $approvals,
            ]);

        } catch (\Exception $e) {
            Log::error('Audit log error', ['project_id' => $project->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'حدث خطأ في جلب سجل التدقيق.'], 500);
        }
    }

    /**
     * Get pending approvals for current user's authority
     */
    public function getPendingApprovals(): JsonResponse
    {
        try {
            $entityId = auth()->user()->entity_id;
            if (! $entityId) {
                return response()->json(['success' => true, 'pending_approvals' => [], 'count' => 0]);
            }

            $pending = ProjectApproval::where('entity_id', $entityId)
                ->where('status', 'pending')
                ->with(['project', 'stage'])
                ->get();

            return response()->json([
                'success' => true,
                'pending_approvals' => $pending,
                'count' => $pending->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get pending approvals error', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'خطأ في جلب الموافقات المعلقة.'], 500);
        }
    }

    /**
     * Reset approval stage to pending (admin only)
     */
    public function resetApprovalStage(Request $request, $projectId, $approvalId): JsonResponse
    {
        try {
            $approval = ProjectApproval::where('project_id', $projectId)->findOrFail($approvalId);
            $approval->update([
                'status' => 'pending',
                'reviewed_at' => null,
                'notes' => 'تمت إعادة الضبط بواسطة المسؤول',
            ]);

            return response()->json(['success' => true, 'message' => 'تم إعادة تعيين المرحلة بنجاح.']);
        } catch (\Exception $e) {
            Log::error('Reset approval error', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'فشلت عملية إعادة التعيين.'], 500);
        }
    }

    /**
     * Get approval timeline for a project
     */
    public function getApprovalTimeline(Project $project): JsonResponse
    {
        try {
            $approvals = ProjectApproval::withTrashed()
                ->where('project_id', $project->id)
                ->orderBy('step_order', 'asc')
                ->get()
                ->map(function ($a) {
                    return [
                        'stage' => $this->getDropArabic($a->drop),
                        'status' => $this->getStatusArabic($a->status),
                        'date' => ($a->reviewed_at ?? $a->created_at)->format('Y-m-d'),
                    ];
                });

            return response()->json(['success' => true, 'timeline' => $approvals]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ في جلب المخطط الزمني.'], 500);
        }
    }

    /**
     * Get approval statistics for the project
     */
    public function getApprovalStatistics($projectId): JsonResponse
    {
        try {
            $counts = ProjectApproval::where('project_id', $projectId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            return response()->json([
                'success' => true,
                'statistics' => [
                    'total' => $counts->sum(),
                    'approved' => $counts['approved'] ?? 0,
                    'rejected' => $counts['rejected'] ?? 0,
                    'pending' => $counts['pending'] ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ في جلب الإحصائيات.'], 500);
        }
    }

    /**
     * Get pending approvals for current project
     */
    public function getProjectPendingApprovals(Project $project): JsonResponse
    {
        try {
            $pending = ProjectApproval::where('project_id', $project->id)
                ->where('status', 'pending')
                ->with(['authority', 'stage'])
                ->get();

            return response()->json([
                'success' => true,
                'pending_approvals' => $pending,
                'count' => $pending->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ في جلب الموافقات المعلقة للمشروع.'], 500);
        }
    }

    /**
     * Get transactions requiring action for current project
     */
    public function getProjectTransactionsRequiringAction(Project $project): JsonResponse
    {
        try {
            $requiring = ProjectApproval::where('project_id', $project->id)
                ->whereIn('status', ['need_action', 'rejected'])
                ->with(['authority', 'stage'])
                ->get();

            return response()->json([
                'success' => true,
                'transactions' => $requiring,
                'count' => $requiring->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ في جلب المعاملات المطلوبة.'], 500);
        }
    }

    /**
     * Helper method: Format file size to human readable format
     */
    private function formatFileSize($bytes): string
    {
        if ($bytes == 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($bytes, 1024);
        $floor = floor($base);

        return round(pow(1024, $base - $floor), 2).' '.$units[$floor];
    }

    /**
     * Get current project status based on approvals
     */
    private function getCurrentProjectStatus(Project $project): array
    {
        $allStages = $this->getProjectApprovalChain($project);
        $totalStages = count($allStages);

        $approvals = ProjectApproval::where('project_id', $project->id)
            ->orderBy('step_order')
            ->get();

        $currentStage = $project->current_stage ?? ($allStages[0]['code'] ?? null);
        $completedStages = 0;
        $stageDetails = [];

        foreach ($approvals as $approval) {
            $stageDetails[$approval->drop] = [
                'status' => $approval->status,
                'status_arabic' => $this->getStatusArabic($approval->status),
                'reviewed_at' => $approval->reviewed_at?->format('Y-m-d H:i:s'),
                'reviewer' => $approval->createdBy->name ?? 'غير معروف',
                'financial_review_status' => $approval->financial_review_status,
                'technical_review_status' => $approval->technical_review_status,
            ];

            if ($approval->status === 'approved' || $approval->is_completed) {
                $completedStages++;
            }
        }

        $overallStatus = $project->status ?? 'pending';
        // In case currentStage is null, use the first stage
        $nextStage = $currentStage ? $this->getNextStage($currentStage, $project) : ($allStages[0]['code'] ?? null);

        return [
            'overall_status' => $overallStatus,
            'overall_status_arabic' => $this->getOverallStatusArabic($overallStatus),
            'current_stage' => $currentStage,
            'current_stage_arabic' => $currentStage ? $this->getDropArabic($currentStage) : 'لم يبدأ',
            'completed_stages' => $completedStages,
            'total_stages' => $totalStages,
            'progress_percentage' => $totalStages > 0 ? round(($completedStages / $totalStages) * 100, 2) : 0,
            'stage_details' => $stageDetails,
            'next_stage' => $nextStage,
            'next_stage_arabic' => $nextStage ? $this->getDropArabic($nextStage) : 'اكتمال الموافقة',
        ];
    }

    /**
     * Get drop in Arabic
     */
    private function getDropArabic(string $drop): string
    {
        $drops = [
            'assembly' => 'موافقة الجمعية',
            'union' => 'موافقة الاتحاد',
            'committee' => 'موافقة اللجنة',
            'implementation' => 'مرحلة التنفيذ',
        ];

        if (isset($drops[$drop])) {
            return $drops[$drop];
        }

        if (str_starts_with($drop, 'entity_')) {
            $id = str_replace('entity_', '', $drop);
            $entity = InternalEntity::find($id);

            return $entity ? $entity->name : $drop;
        }

        return $drop;
    }

    /**
     * Get status in Arabic
     */
    private function getStatusArabic(string $status): string
    {
        $statuses = [
            'approved' => 'موافق',
            'rejected' => 'مرفوض',
            'pending' => 'قيد الانتظار',
            'need_action' => 'بحاجة إلى إجراء',
            'resubmitted' => 'تم إعادة تقديمه',
            'financial_technical_review' => 'مراجعة مالية وفنية',
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Get overall status in Arabic
     */
    private function getOverallStatusArabic(string $status): string
    {
        $statuses = [
            'completed' => 'مكتمل',
            'rejected' => 'مرفوض',
            'need_action' => 'بحاجة إلى إجراء',
            'rolled_back_for_review' => 'تم إرجاعه للمراجعة',
            'resubmitted' => 'تم إعادة تقديمه',
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'financial_technical_review' => 'قيد المراجعة المالية والفنية',
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Get approval stages for a project (for dynamic dropdown update)
     */
    public function getApprovalStages(Project $project): JsonResponse
    {
        try {
            // Get the service directly or use dependency injection
            $projectService = app(ProjectService::class);
            $approvalStages = $projectService->getApprovalStages($project);

            return response()->json([
                'success' => true,
                'approval_stages' => $approvalStages,
                'current_stage' => $project->current_stage,
            ]);
        } catch (\Exception $e) {
            Log::error('Get approval stages error', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مراحل الموافقة: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export activity history to CSV
     */
    public function exportActivityHistory(Project $project)
    {
        $activities = $project->activityHistory()->with('user')->orderBy('created_at', 'desc')->get();
        $filename = "project_{$project->id}_activity_log_".date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($activities) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['التاريخ والوقت', 'المستخدم', 'نوع الإجراء', 'المرحلة السابقة', 'المرحلة الحالية', 'الملاحظات']);

            foreach ($activities as $act) {
                fputcsv($file, [
                    $act->created_at ? $act->created_at->format('Y-m-d H:i:s') : '',
                    $act->user->name ?? 'النظام',
                    $act->getActionDescription(),
                    $act->from_stage_name ?? 'ـ',
                    $act->to_stage_name ?? 'ـ',
                    $act->notes ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
