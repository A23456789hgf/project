@php
    $isDraft = in_array($project->status, ['draft', 'completed_draft'], true) || $project->isDraft();
    $isInExecution = in_array($project->status, ['in_execution', 'in_progress'], true);
    $isRejected = ($project->status === 'rejected');
    $isRolledBack = ($project->status === 'rolled_back_for_review');

    // Fetch actual approvals from database ordered by step_order
    $approvals = $project->projectApprovals()
        ->with(['entity', 'reviewedByUser'])
        ->orderBy('step_order')
        ->get();

    // Group approvals by Entity (preserving sequence)
    $groupedEntities = collect();
    foreach ($approvals as $approval) {
        $entityId = $approval->entity_id ?? 0;
        $entityName = $approval->entity ? $approval->entity->name : ($approval->authority_name ?? 'الجهة المعنية');
        
        if (!$groupedEntities->has($entityId)) {
            $groupedEntities->put($entityId, [
                'id' => $entityId,
                'name' => $entityName,
                'is_root' => $approval->is_root ?? false,
                'steps' => collect(),
            ]);
        }
        $groupedEntities[$entityId]['steps']->push($approval);
    }

    $activeStep = $approvals->firstWhere('is_active', true);
@endphp

<div class="approval-workflow-wrapper">
    @if($isDraft)
        {{-- ========================================================================= --}}
        {{-- DRAFT STATE BANNER --}}
        {{-- ========================================================================= --}}
        <div class="card border-0 bg-light rounded-4 p-4 text-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3" style="width: 72px; height: 72px;">
                <i class="fas fa-file-edit fa-2x"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">المشروع في مرحلة المسودة (Draft)</h5>
            <p class="text-muted small mb-3 mx-auto" style="max-width: 600px;">
                المشروع حالياً قيد الإعداد لدى الجهة المنشئة (<strong>{{ $project->creator_entity_name ?? 'الجهة المنشئة' }}</strong>). لم تبدأ سلسلة الموافقات والاعتمادات الرسمية بعد.
            </p>

            @can('closeDraft', $project)
                <div class="mt-2">
                    <button type="button" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#closeDraftModal">
                        <i class="fas fa-paper-plane me-2"></i> إغلاق المسودة وإرسالها للاعتماد
                    </button>
                </div>
            @else
                <div class="badge bg-secondary bg-opacity-10 text-secondary py-2 px-3 rounded-pill d-inline-block mx-auto">
                    <i class="fas fa-lock me-1"></i> إغلاق المسودة متاح فقط لمخولي الجهة المنشئة للمشروع
                </div>
            @endcan
        </div>

    @elseif($approvals->isEmpty())
        <div class="alert alert-info border-0 rounded-4 p-4 text-center">
            <i class="fas fa-info-circle me-2"></i> لا توجد مراحل اعتماد مسجلة لهذا المشروع حالياً.
        </div>
    @else
        {{-- ========================================================================= --}}
        {{-- ROLLED BACK / RETURNED FOR REVIEW ALERT --}}
        {{-- ========================================================================= --}}
        @if($isRolledBack)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-warning text-white rounded-circle p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="fas fa-undo-alt fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-dark mb-1">تم إرجاع المشروع لاستكمال النواقص (Returned for Completion)</h5>
                        <p class="text-muted mb-2">تمت إعادة المشروع إلى الجهة المنشئة لاستكمال الملاحظات والمطلوب، وسيعود إلى نفس الخطوة بعد إعادة التقديم.</p>
                        @if($activeStep && $activeStep->notes)
                            <div class="bg-white rounded-3 p-3 border border-warning border-opacity-25 mt-2">
                                <strong class="text-dark d-block mb-1"><i class="fas fa-comment-dots text-warning me-1"></i> سبب الإرجاع / الملاحظات:</strong>
                                <span class="text-secondary">{{ $activeStep->notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- REJECTED STATE ALERT --}}
        {{-- ========================================================================= --}}
        @if($isRejected)
            @php
                $rejectedStep = $approvals->firstWhere('status', 'rejected');
            @endphp
            <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-danger text-white rounded-circle p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="fas fa-times-circle fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-danger mb-1">تم رفض المشروع (Rejected)</h5>
                        <p class="text-muted mb-2">تم رفض المشروع وإيقاف مسار الاعتماد.</p>
                        @if($rejectedStep && $rejectedStep->notes)
                            <div class="bg-white rounded-3 p-3 border border-danger border-opacity-25 mt-2">
                                <strong class="text-danger d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i> سبب الرفض:</strong>
                                <span class="text-secondary">{{ $rejectedStep->notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- DYNAMIC ENTITY & STEP HIERARCHY TIMELINE --}}
        {{-- ========================================================================= --}}
        <div class="approval-hierarchy-cards">
            @foreach($groupedEntities as $index => $entityGroup)
                @php
                    $entitySteps = $entityGroup['steps'];
                    $hasActive = $entitySteps->contains('is_active', true);
                    $allCompleted = $entitySteps->every(fn($s) => in_array($s->status, ['approved', 'completed'], true));
                    $hasRejected = $entitySteps->contains('status', 'rejected');
                    $hasNeedAction = $entitySteps->contains('status', 'need_action');

                    $entityStatusClass = $allCompleted 
                        ? 'border-success' 
                        : ($hasActive 
                            ? 'border-primary shadow-sm' 
                            : ($hasRejected 
                                ? 'border-danger' 
                                : ($hasNeedAction 
                                    ? 'border-warning' 
                                    : 'border-light-subtle')));
                    
                    $entityBgBadge = $allCompleted
                        ? 'bg-success'
                        : ($hasActive
                            ? 'bg-primary'
                            : ($hasRejected
                                ? 'bg-danger'
                                : ($hasNeedAction
                                    ? 'bg-warning text-dark'
                                    : 'bg-secondary')));
                @endphp

                <div class="card {{ $entityStatusClass }} rounded-4 mb-3 overflow-hidden border-2">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge {{ $entityBgBadge }} rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                @if($allCompleted)
                                    <i class="fas fa-check"></i>
                                @elseif($hasActive)
                                    <i class="fas fa-hourglass-half"></i>
                                @elseif($hasRejected)
                                    <i class="fas fa-times"></i>
                                @else
                                    <i class="fas fa-building"></i>
                                @endif
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">
                                    {{ $entityGroup['name'] }}
                                    @if($loop->first)
                                        <span class="badge bg-info bg-opacity-10 text-info ms-2">الجهة المنشئة</span>
                                    @elseif($entityGroup['is_root'] || $loop->last)
                                        <span class="badge bg-dark bg-opacity-10 text-dark ms-2">الجهة العليا (Root)</span>
                                    @endif
                                </h6>
                            </div>
                        </div>

                        <div>
                            @if($allCompleted)
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> تم اعتماد الجهة
                                </span>
                            @elseif($hasActive)
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill animate-pulse">
                                    <i class="fas fa-play-circle me-1"></i> المرحلة النشطة حالياً
                                </span>
                            @elseif($hasRejected)
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-2 rounded-pill">
                                    <i class="fas fa-times-circle me-1"></i> تم الرفض
                                </span>
                            @elseif($hasNeedAction)
                                <span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-3 py-2 rounded-pill">
                                    <i class="fas fa-exclamation-circle me-1"></i> بانتظار استكمال
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                    <i class="fas fa-lock me-1"></i> بانتظار وصول الدور
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2">
                            @foreach($entitySteps as $step)
                                @php
                                    $stepPhaseName = $step->getPhaseArabicName();
                                    $isStepApproved = in_array($step->status, ['approved', 'completed'], true);
                                    $isStepActive = (bool) $step->is_active;
                                    $isStepRejected = ($step->status === 'rejected');
                                    $isStepNeedAction = in_array($step->status, ['need_action', 'needs_revision'], true);
                                    $isStepLocked = ($step->status === 'locked' || (!$isStepApproved && !$isStepActive && !$isStepRejected && !$isStepNeedAction));
                                @endphp

                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 h-100 {{ $isStepActive ? 'bg-primary bg-opacity-10 border border-primary' : ($isStepApproved ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : ($isStepRejected ? 'bg-danger bg-opacity-10 border border-danger' : ($isStepNeedAction ? 'bg-warning bg-opacity-10 border border-warning' : 'bg-light border border-light-subtle opacity-75'))) }}">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="small text-muted fw-semibold">الخطوة #{{ $step->step_order }}</span>
                                            @if($isStepApproved)
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> معتمد</span>
                                            @elseif($isStepActive)
                                                <span class="badge bg-primary"><i class="fas fa-dot-circle me-1"></i> قيد المراجعة</span>
                                            @elseif($isStepRejected)
                                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i> مرفوض</span>
                                            @elseif($isStepNeedAction)
                                                <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> استكمال</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fas fa-lock me-1"></i> مقفلة</span>
                                            @endif
                                        </div>

                                        <h6 class="fw-bold mb-1 {{ $isStepActive ? 'text-primary' : ($isStepApproved ? 'text-success' : 'text-dark') }}">
                                            {{ $stepPhaseName }}
                                        </h6>

                                        @if($step->reviewed_at || $step->reviewedByUser)
                                            <div class="small text-muted mt-2 border-top pt-2">
                                                @if($step->reviewedByUser)
                                                    <div><i class="fas fa-user-check me-1"></i> {{ $step->reviewedByUser->name }}</div>
                                                @endif
                                                @if($step->reviewed_at)
                                                    <div><i class="fas fa-clock me-1"></i> {{ $step->reviewed_at->format('Y-m-d H:i') }}</div>
                                                @endif
                                                @if($step->notes)
                                                    <div class="mt-1 text-dark fst-italic"><i class="fas fa-comment me-1"></i> {{ Str::limit($step->notes, 80) }}</div>
                                                @endif
                                            </div>
                                        @elseif($isStepActive)
                                            <div class="small text-primary mt-2">
                                                <i class="fas fa-info-circle me-1"></i> بانتظار إجراء المراجع المخول
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Final Milestone: In Execution --}}
            <div class="card rounded-4 border-2 {{ $isInExecution ? 'border-success bg-success bg-opacity-10' : 'border-light-subtle bg-light opacity-75' }} p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center {{ $isInExecution ? 'bg-success text-white' : 'bg-secondary bg-opacity-25 text-muted' }}" style="width: 48px; height: 48px;">
                            <i class="fas fa-rocket fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold {{ $isInExecution ? 'text-success' : 'text-muted' }}">مرحلة التنفيذ (In Execution)</h6>
                            <small class="text-muted">المحطة النهائية بعد اعتماد كافة الجهات الهرمية</small>
                        </div>
                    </div>
                    <div>
                        @if($isInExecution)
                            <span class="badge bg-success py-2 px-3 rounded-pill fw-bold">
                                <i class="fas fa-check-double me-1"></i> تم الاعتماد النهائي والمشروع قيد التنفيذ
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-25 text-secondary py-2 px-3 rounded-pill">
                                بانتظار اكتمال سلسلة الاعتماد
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Close Draft Modal --}}
@can('closeDraft', $project)
    <div class="modal fade" id="closeDraftModal" tabindex="-1" aria-labelledby="closeDraftModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="{{ route('projects.approval.submit', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white border-0 py-3">
                        <h5 class="modal-title fw-bold" id="closeDraftModalLabel">
                            <i class="fas fa-paper-plane me-2"></i> إغلاق المسودة وإرسال المشروع للاعتماد
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-3 mb-3">
                            <i class="fas fa-info-circle me-1"></i> سيتم إغلاق المسودة وبناء سلسلة الموافقات الهرمية تلقائياً من جهتكم حتى الجهة العليا.
                        </div>
                        <div class="mb-3">
                            <label for="close_draft_notes" class="form-label fw-bold">ملاحظات الإرسال (اختياري):</label>
                            <textarea class="form-control" id="close_draft_notes" name="notes" rows="3" placeholder="أدخل أي ملاحظات ترغب بإرفاقها مع طلب الاعتماد..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="fas fa-check-circle me-1"></i> تأكيد وإرسال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
