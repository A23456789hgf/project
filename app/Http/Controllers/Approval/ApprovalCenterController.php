<?php

namespace App\Http\Controllers\Approval;

use App\Enums\ApprovalPhase;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\RespondConsultationRequest;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\ProjectReferral;
use App\Services\ApprovalService;
use App\Services\EntityHierarchyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalCenterController extends Controller
{
    protected ApprovalService $approvalService;

    protected EntityHierarchyService $entityHierarchyService;

    public function __construct(
        ApprovalService $approvalService,
        EntityHierarchyService $entityHierarchyService
    ) {
        $this->approvalService = $approvalService;
        $this->entityHierarchyService = $entityHierarchyService;
    }

    /**
     * Display the standalone Approval Center index page with dynamic tabs, statistics, and filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userEntityId = (int) ($user?->entity_id ?? 0);
        $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

        // Base accessible projects query for the user
        $baseProjectsQuery = Project::query()
            ->with([
                'createdBy',
                'creatorEntity',
                'cost',
                'projectApprovals' => function ($q) {
                    $q->with(['entity', 'reviewedByUser'])->orderBy('step_order');
                },
            ]);

        $allowedEntityIds = [];
        if (! $isAdmin && $userEntityId) {
            $allowedEntityIds = array_unique(array_merge([$userEntityId], InternalEntity::getAllChildrenIds($userEntityId)));
        }

        // 1. Resolve Requested Tab / Sub-Filter
        $requestedTab = $request->get('tab', 'my_action');
        if (in_array($requestedTab, ['referrals_incoming', 'referrals_sent', 'referrals_completed'], true)) {
            return redirect()->route('consultations.index', ['tab' => $requestedTab]);
        }

        $validTabs = ['my_action', 'technical', 'financial', 'stages', 'waiting_others', 'completed', 'rejected', 'returned'];
        $activeTab = in_array($requestedTab, $validTabs, true) ? $requestedTab : 'my_action';

        // 2. Base Task Inbox Query: ONLY records requiring action NOW from current user
        // (Active + Pending + User Authorized + Action Required)
        $inboxQuery = ProjectApproval::query()
            ->with([
                'project.creatorEntity',
                'project.createdBy',
                'project.cost',
                'entity',
                'reviewedByUser',
                'technicalReviewer',
                'financialReviewer',
            ])
            ->where('is_active', true)
            ->where(function ($q) use ($user, $userEntityId, $allowedEntityIds, $isAdmin) {
                // Scenario A: Active pending approval step assigned to user's entity
                $q->where(function ($sub) use ($userEntityId, $allowedEntityIds, $isAdmin) {
                    $sub->where('status', 'pending')
                        ->whereHas('project', function ($pq) {
                            $pq->where('status', ProjectStatus::PendingApproval->value);
                        });
                    if (! $isAdmin) {
                        if ($userEntityId) {
                            $sub->whereIn('entity_id', $allowedEntityIds);
                        } else {
                            $sub->whereRaw('1 = 0');
                        }

                        if ($user) {
                            $sub->where(function ($assignQ) use ($user) {
                                $assignQ->where(function ($tq) use ($user) {
                                    $tq->where('phase', 'technical_review')
                                        ->where('technical_reviewer_id', $user->id);
                                })->orWhere(function ($fq) use ($user) {
                                    $fq->where('phase', 'financial_review')
                                        ->where('financial_reviewer_id', $user->id);
                                })->orWhere(function ($oq) use ($user) {
                                    $oq->whereNotIn('phase', ['technical_review', 'financial_review'])
                                        ->where('assigned_user_id', $user->id);
                                });
                            });
                        } else {
                            $sub->whereRaw('1 = 0');
                        }
                    }
                })
                // Scenario B: Project rolled back for review where user's entity is creator entity needing resubmission
                    ->orWhere(function ($sub) use ($user, $allowedEntityIds, $isAdmin) {
                        $sub->whereHas('project', function ($pq) use ($user, $allowedEntityIds, $isAdmin) {
                            $pq->where('status', 'rolled_back_for_review');
                            if (! $isAdmin) {
                                $pq->where(function ($cpq) use ($user, $allowedEntityIds) {
                                    $cpq->whereIn('creator_entity_id', $allowedEntityIds)
                                        ->orWhere('created_by_user_id', $user->id);
                                });
                            }
                        });
                    });
            });

        // 3. Live Task Counters (Counts ONLY tasks requiring action from user NOW)
        $myActionCount = $isAdmin ? (clone $inboxQuery)->count() : (clone $inboxQuery)->get()->filter(function ($rec) {
            return $rec->is_active && $this->approvalService->canUserActOnStep(Auth::user(), $rec);
        })->count();
        $technicalCount = $isAdmin ? (clone $inboxQuery)->where('phase', 'technical_review')->count() : (clone $inboxQuery)->where('phase', 'technical_review')->get()->filter(function ($rec) {
            return $rec->is_active && $this->approvalService->canUserActOnStep(Auth::user(), $rec);
        })->count();
        $financialCount = $isAdmin ? (clone $inboxQuery)->where('phase', 'financial_review')->count() : (clone $inboxQuery)->where('phase', 'financial_review')->get()->filter(function ($rec) {
            return $rec->is_active && $this->approvalService->canUserActOnStep(Auth::user(), $rec);
        })->count();
        $stagesCount = $isAdmin ? (clone $inboxQuery)->whereNotIn('phase', ['technical_review', 'financial_review'])->count() : (clone $inboxQuery)->whereNotIn('phase', ['technical_review', 'financial_review'])->get()->filter(function ($rec) {
            return $rec->is_active && $this->approvalService->canUserActOnStep(Auth::user(), $rec);
        })->count();

        // Compatibility fallback counts for legacy test assertions
        $waitingOthersCount = 0;
        $completedCount = 0;
        $rejectedCount = 0;
        $returnedCount = 0;

        // 4. Resolve Records for Display
        $approvalsQuery = clone $inboxQuery;

        $defaultRelations = [
            'project.creatorEntity',
            'project.createdBy',
            'project.cost',
            'entity',
            'reviewedByUser',
            'technicalReviewer',
            'financialReviewer',
        ];

        if ($activeTab === 'technical') {
            $approvalsQuery->where('phase', 'technical_review');
        } elseif ($activeTab === 'financial') {
            $approvalsQuery->where('phase', 'financial_review');
        } elseif ($activeTab === 'stages') {
            $approvalsQuery->whereNotIn('phase', ['technical_review', 'financial_review']);
        } elseif ($activeTab === 'waiting_others') {
            // Fallback for legacy test calls
            $approvalsQuery = ProjectApproval::query()
                ->with($defaultRelations)
                ->where(function ($q) use ($userEntityId) {
                    $q->where('status', 'locked')
                        ->orWhere(function ($sq) use ($userEntityId) {
                            $sq->where('is_active', true)->where('entity_id', '!=', $userEntityId);
                        });
                })->whereHas('project', function ($pq) {
                    $pq->where('status', ProjectStatus::PendingApproval->value);
                });
            $waitingOthersCount = (clone $approvalsQuery)->count();
        } elseif ($activeTab === 'completed') {
            // Fallback for legacy test calls (completed archive is officially in projects.index)
            $approvalsQuery = ProjectApproval::query()
                ->with($defaultRelations)
                ->where(function ($q) {
                    $q->where('status', 'approved')
                        ->orWhereHas('project', function ($pq) {
                            $pq->whereIn('status', [ProjectStatus::InExecution->value, 'in_progress', 'completed']);
                        });
                });
            $completedCount = (clone $approvalsQuery)->count();
        } elseif ($activeTab === 'rejected') {
            $approvalsQuery = ProjectApproval::query()
                ->with($defaultRelations)
                ->where('status', 'rejected');
            $rejectedCount = (clone $approvalsQuery)->count();
        } elseif ($activeTab === 'returned') {
            $approvalsQuery = ProjectApproval::query()
                ->with($defaultRelations)
                ->whereHas('project', function ($pq) {
                    $pq->where('status', 'rolled_back_for_review');
                });
            $returnedCount = (clone $approvalsQuery)->count();
        }

        // 4. Dynamic Filters
        if ($request->filled('search')) {
            $search = trim($request->search);
            $approvalsQuery->where(function ($q) use ($search) {
                $q->whereHas('project', function ($pq) use ($search) {
                    $pq->where('project_name', 'like', "%{$search}%")
                        ->orWhere('form_number', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('entity_id')) {
            $entityId = (int) $request->entity_id;
            $approvalsQuery->where('entity_id', $entityId);
        }

        if ($request->filled('phase')) {
            $phase = $request->phase;
            $approvalsQuery->where('phase', $phase);
        }

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            if ($statusFilter === 'active') {
                $approvalsQuery->where('is_active', true);
            } elseif ($statusFilter === 'pending') {
                $approvalsQuery->where('status', 'pending');
            } elseif ($statusFilter === 'approved') {
                $approvalsQuery->where('status', 'approved');
            } elseif ($statusFilter === 'rejected') {
                $approvalsQuery->where('status', 'rejected');
            } elseif (in_array($statusFilter, ['need_action', 'returned'], true)) {
                $approvalsQuery->whereIn('status', ['need_action', 'requires_action']);
            } elseif ($statusFilter === 'locked') {
                $approvalsQuery->where('status', 'locked');
            }
        }

        if ($request->filled('date_from')) {
            $approvalsQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $approvalsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $approvalRecords = $approvalsQuery->orderBy('updated_at', 'desc')->paginate(12)->withQueryString();

        // Compute can_user_act for each record
        foreach ($approvalRecords as $record) {
            $record->can_user_act = $record->is_active && $user && $this->approvalService->canUserActOnStep($user, $record);
        }

        // Filter out records the user cannot act on (except admin)
        if (! $isAdmin) {
            $filtered = $approvalRecords->getCollection()->filter(function ($rec) {
                return $rec->can_user_act;
            });
            $approvalRecords->setCollection($filtered);
        }

        // Seamless representation for projects in execution/completed without explicit approval rows
        if ($activeTab === 'completed') {
            $standaloneProjects = (clone $baseProjectsQuery)
                ->whereIn('status', [ProjectStatus::InExecution->value, 'in_progress', 'completed'])
                ->whereDoesntHave('projectApprovals')
                ->get();

            if ($standaloneProjects->isNotEmpty()) {
                $virtualRecords = collect();
                foreach ($standaloneProjects as $standaloneProject) {
                    $virtualApproval = new ProjectApproval([
                        'project_id' => $standaloneProject->id,
                        'entity_id' => $standaloneProject->creator_entity_id,
                        'step_order' => 1,
                        'status' => 'approved',
                        'is_completed' => true,
                        'is_active' => false,
                        'phase' => ApprovalPhase::StageApproval->value,
                        'created_at' => $standaloneProject->created_at,
                        'updated_at' => $standaloneProject->updated_at,
                        'reviewed_at' => $standaloneProject->updated_at,
                    ]);
                    $virtualApproval->setRelation('project', $standaloneProject);
                    $virtualApproval->setRelation('entity', $standaloneProject->creatorEntity);
                    $virtualApproval->setRelation('reviewedByUser', $standaloneProject->createdBy);
                    $virtualRecords->push($virtualApproval);
                }

                $mergedCollection = $approvalRecords->getCollection()->concat($virtualRecords);
                $approvalRecords->setCollection($mergedCollection);
            }
        }

        // Removed obsolete manual filtering; new logic applied earlier.

        $projects = $approvalRecords;

        // 5. Dynamic Filter Datasets
        $entities = InternalEntity::withoutGlobalScopes()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $phases = ApprovalPhase::cases();

        return view('approvals.index', compact(
            'projects',
            'approvalRecords',
            'myActionCount',
            'technicalCount',
            'financialCount',
            'stagesCount',
            'waitingOthersCount',
            'completedCount',
            'rejectedCount',
            'returnedCount',
            'activeTab',
            'entities',
            'phases'
        ));
    }

    /**
     * Backward-compatible delegation for referral details
     */
    public function showReferral(ProjectReferral $referral)
    {
        return app(ConsultationCenterController::class)->show($referral);
    }

    /**
     * Backward-compatible delegation for referral respond
     */
    public function respondReferral(RespondConsultationRequest $request, ProjectReferral $referral)
    {
        return app(ConsultationCenterController::class)->respond($request, $referral);
    }

    /**
     * Display the standalone Approval Center show page for a single project.
     */
    public function show(Project $project)
    {
        $user = Auth::user();

        // Eager load all necessary relationships for dynamic tracker, data accordion, and history
        $project->load([
            'createdBy.entity',
            'creatorEntity',
            'cost',
            'executiveActionCosts',
            'financings.fundingSource',
            'projectApprovals' => function ($q) {
                $q->with(['entity', 'reviewedByUser'])->orderBy('step_order');
            },
            'activityHistory' => function ($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            },
            'referrals' => function ($q) {
                $q->with(['referringEntity', 'referredEntity', 'referringUser', 'respondingUser'])
                    ->orderBy('created_at', 'desc');
            },
            'documents',
        ]);

        $activeStep = $this->approvalService->getActiveStep($project);
        $canActOnActiveStep = false;

        if ($activeStep && $user) {
            $canActOnActiveStep = $this->approvalService->canUserActOnStep($user, $activeStep);
        }

        $approvalChain = $project->projectApprovals;

        $entitiesList = InternalEntity::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('id', '!=', $user?->entity_id ?? 0)
            ->orderBy('name')
            ->get();

        return view('approvals.show', compact(
            'project',
            'activeStep',
            'canActOnActiveStep',
            'entitiesList',
            'approvalChain'
        ));
    }

    /**
     * Store attachment file
     */
    private function storeAttachment($file, ?Project $project = null): string
    {
        $fileName = time().'_'.uniqid().'_'.preg_replace('/[^\w\.\-]/', '_', $file->getClientOriginalName());
        $folder = $project ? "projects/{$project->id}/referral_attachments" : 'referrals/attachments';

        return $file->storeAs($folder, $fileName, 'public');
    }
}
