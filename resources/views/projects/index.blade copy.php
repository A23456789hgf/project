@extends('layouts.app')
@section('title', 'إدارة المشاريع')
@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><i class="fas fa-project-diagram"></i> إدارة المشاريع</h2>
        </div>

        <!-- أزرار الإضافة والتصدير -->
        <div class="mb-3 d-flex align-items-center flex-wrap gap-2">

            <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">

                @can('create', App\Models\Project::class)
                    <a href="{{ route('projects.create') }}" class="btn btn-success btn-sm auth-perm-projects-create">
                        <i class="fas fa-plus"></i> إنشاء مشروع
                    </a>
                @endcan

                @if(
                        Gate::check('import', App\Models\Project::class) ||
                        Gate::check('exportExcel', App\Models\Project::class) ||
                        Gate::check('exportComprehensive', App\Models\Project::class) ||
                        Gate::check('exportPdf', App\Models\Project::class) ||
                        Gate::check('exportPivot', App\Models\Project::class)
                    )
                    <div class="dropdown">

                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs"></i> العمليات
                        </button>

                        <ul class="dropdown-menu">

                            @can('import', App\Models\Project::class)
                                <li>
                                    <a class="dropdown-item auth-perm-projects-import" href="{{ route('projects.import') }}">
                                        استيراد مشاريع
                                    </a>
                                </li>
                            @endcan

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            @can('exportExcel', App\Models\Project::class)
                                <li>
                                    <button class="dropdown-item auth-perm-projects-export" onclick="handleExport('excel')">
                                        Excel
                                    </button>
                                </li>
                            @endcan

                            @can('exportComprehensive', App\Models\Project::class)
                                <li>
                                    <button class="dropdown-item auth-perm-projects-export" onclick="handleExport('comprehensive')">
                                        شامل
                                    </button>
                                </li>
                            @endcan

                            @can('exportPdf', App\Models\Project::class)
                                <li>
                                    <button class="dropdown-item auth-perm-projects-export" onclick="handleExport('pdf')">
                                        PDF
                                    </button>
                                </li>
                            @endcan

                            @can('exportPivot', App\Models\Project::class)
                                <li>
                                    <button class="dropdown-item auth-perm-projects-export" onclick="handleExport('pivot')">
                                        Pivot
                                    </button>
                                </li>
                            @endcan

                        </ul>
                    </div>
                @endif

            </div>

        </div>

        <!-- رسالة النجاح -->
        

        <!-- نموذج البحث والفرز -->
        <div class="card mb-3 compact-card">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0"><i class="fas fa-filter"></i> البحث والفرز</h6>
            </div>
            <div class="card-body py-2">
                <form action="{{ route('projects.index') }}" method="GET" class="row g-2">
                    <!-- البحث باسم المشروع -->
                    <div class="col-md-3">
                        <label for="search" class="form-label small">البحث باسم المشروع</label>
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            value="{{ request('search') }}" placeholder="أدخل اسم المشروع...">
                    </div>

                    <!-- فرز بالبرنامج -->
                    <div class="col-md-2">
                        <label for="program" class="form-label small">البرنامج</label>
                        <select class="form-select form-select-sm" id="program" name="program">
                            <option value="">جميع البرامج</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- فرز بالمجال الرئيسي -->
                    <div class="col-md-2">
                        <label for="domain" class="form-label small">المجال الرئيسي</label>
                        <select class="form-select form-select-sm" id="domain" name="domain">
                            <option value="">جميع المجالات</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" {{ request('domain') == $domain->id ? 'selected' : '' }}>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- فرز بالمجال الفرعي -->
                    <div class="col-md-2">
                        <label for="subdomain" class="form-label small">المجال الفرعي</label>
                        <select class="form-select form-select-sm" id="subdomain" name="subdomain">
                            <option value="">جميع المجالات الفرعية</option>
                            @foreach($subdomains as $subdomain)
                                <option value="{{ $subdomain->id }}" {{ request('subdomain') == $subdomain->id ? 'selected' : '' }}>
                                    {{ $subdomain->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- فرز بالتدخلات -->
                    <div class="col-md-2">
                        <label for="intervention" class="form-label small">التدخلات</label>
                        <select class="form-select form-select-sm" id="intervention" name="intervention">
                            <option value="">جميع التدخلات</option>
                            @foreach($interventions as $intervention)
                                <option value="{{ $intervention->id }}" {{ request('intervention') == $intervention->id ? 'selected' : '' }}>
                                    {{ $intervention->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="col-md-12 mt-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-redo"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        <!-- جدول المشاريع -->
        <div class="table-wrapper compact-table">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="3%" class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <input type="checkbox" id="select-all-projects" class="form-check-input"
                                        style="margin: 0; transform: scale(0.85);">
                                    <button type="button" id="bulk-delete-btn"
                                        class="btn btn-link text-danger p-0 border-0 d-none" title="حذف المحدد">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </th>
                            <th width="2%">#</th>
                            <th width="7%">رقم المشروع</th>
                            <th width="12%">اسم المشروع</th>
                            <th width="8%">البرنامج</th>
                            <th width="8%">المجال</th>
                            <th width="8%">المجال الفرعي</th>
                            <th width="10%">المرحلة</th>
                            <th width="8%">الحالة</th>
                            <th width="8%">المنشئ</th>
                            <th width="8%">الجهة</th>
                            <th width="15%">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="selected_projects[]" value="{{ $project->id }}"
                                        class="form-check-input project-checkbox" style="transform: scale(0.8);">
                                </td>
                                <td class="small-text">{{ $loop->iteration }}</td>

                                <!-- رقم المشروع -->
                                <td>
                                    <span class="project-number small-text">
                                        {{ $project->form_number ?? '-' }}
                                    </span>
                                </td>

                                <!-- بيانات المشروع -->
                                <td class="small-text">
                                    {{ \Illuminate\Support\Str::limit($project->project_name ?: ($project->status === 'draft' ? 'مسودة غير معنونة' : 'مشروع غير معنون'), 20) }}
                                </td>
                                <td class="small-text">
                                    {{ \Illuminate\Support\Str::limit(optional($project->program)->name ?? '-', 15) }}
                                </td>
                                <td class="small-text">
                                    {{ \Illuminate\Support\Str::limit(optional($project->domain)->name ?? '-', 15) }}
                                </td>
                                <td class="small-text">
                                    {{ \Illuminate\Support\Str::limit(optional($project->subdomain)->name ?? '-', 15) }}
                                </td>

                                <!-- المرحلة -->
                                @php
                                    $currentStage = $project->current_stage_name;
                                    $stageStatus = null; // Can be expanded later if needed

                                    // Check for financial/technical review icons
                                    $reviewIcons = [];
                                    if ($project->projectApprovals) {
                                        foreach ($project->projectApprovals as $approval) {
                                            if ($approval->status === 'reviewed_completed') {
                                                if (!in_array('reviewed_completed', $reviewIcons)) {
                                                    $reviewIcons[] = 'reviewed_completed';
                                                }
                                            } elseif ($approval->status === 'financial_review') {
                                                if ($approval->financial_review_completed && !in_array('financial_done', $reviewIcons)) {
                                                    $reviewIcons[] = 'financial_done';
                                                } elseif ($approval->financial_review_notes && !in_array('financial', $reviewIcons)) {
                                                    $reviewIcons[] = 'financial';
                                                }

                                                if ($approval->technical_review_completed && !in_array('technical_done', $reviewIcons)) {
                                                    $reviewIcons[] = 'technical_done';
                                                } elseif ($approval->technical_review_notes && !in_array('technical', $reviewIcons)) {
                                                    $reviewIcons[] = 'technical';
                                                }
                                            }
                                        }
                                    }

                                    // Approval status style map
                                    $approvalStatusMap = [
                                        'pending' => ['label' => 'قيد المراجعة', 'class' => 'bg-warning text-dark'],
                                        'approved' => ['label' => 'معتمد', 'class' => 'bg-success'],
                                        'rejected' => ['label' => 'مرفوض', 'class' => 'bg-danger'],
                                        'need_action' => ['label' => 'يحتاج إجراء', 'class' => 'bg-warning text-dark'],
                                        'rolled_back_for_review' => ['label' => 'أُعيد للمراجعة', 'class' => 'bg-warning text-dark'],
                                    ];
                                    $approvalBadge = $approvalStatusMap[$project->approval_status] ?? null;
                                @endphp
                                <td class="text-center" style="vertical-align: middle;">

                                    @if($project->status === 'draft')
                                        {{-- ===== DRAFT: always show clear draft label ===== --}}
                                        <span class="badge bg-secondary small-text" style="font-size:.75rem; letter-spacing:.3px;">
                                            <i class="fas fa-file-alt me-1"></i>مسودة
                                        </span>


                                    @else
                                        {{-- ===== ACTIVE STAGE ===== --}}
                                        @if($currentStage)
                                            @php
                                                $displayStage = $currentStage;
                                            @endphp
                                            <span class="badge bg-info small-text"
                                                style="font-size:.75rem; max-width:180px; white-space:normal; line-height:1.3;">
                                                <i class="fas fa-layer-group me-1"></i>{{ $displayStage }}
                                            </span>
                                        @else
                                            {{-- Fallback: show the raw status in a neutral pill --}}
                                            @php
                                                $statusLabels = [
                                                    'pending' => 'قيد الانتظار',
                                                    'pending_approval' => 'قيد الموافقة',
                                                    'internally_approved' => 'معتمد داخلياً',
                                                    'final' => 'نهائي',
                                                    'in_progress' => 'قيد التنفيذ',
                                                    'completed' => 'مكتمل',
                                                    'rolled_back_for_review' => 'أُعيد للمراجعة',
                                                ];
                                                $statusLabel = $statusLabels[$project->status] ?? $project->status;
                                            @endphp
                                            <span class="badge bg-secondary small-text" style="font-size:.73rem;">
                                                <i class="fas fa-circle-notch me-1"></i>{{ $statusLabel }}
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <!-- الحالة -->
                                <td class="text-center" style="vertical-align: middle;">
                                    @if($project->status === 'draft')
                                        @if(!$project->isDraftComplete())
                                            <span class="badge bg-light text-muted small-text"
                                                style="font-size:.68rem; border:1px solid #ccc;">
                                                <i class="fas fa-exclamation-circle text-warning"></i> غير مكتملة
                                            </span>
                                        @else
                                            <span class="badge bg-light text-success small-text"
                                                style="font-size:.68rem; border:1px solid #ccc;">
                                                <i class="fas fa-check-circle"></i> جاهزة
                                            </span>
                                        @endif
                                    @else
                                        {{-- Execution status and progress --}}
                                        @if($project->status === 'in_execution')
                                            <div class="d-flex flex-column align-items-center" style="min-width: 100px;">
                                                <span class="badge bg-success small-text mb-1" style="font-size:.68rem;">
                                                    <i class="fas fa-play-circle"></i> قيد التنفيذ
                                                </span>
                                                <div class="progress w-100" style="height: 5px; border-radius: 10px; background-color: #e9ecef;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                                        role="progressbar" 
                                                        style="width: {{ $project->execution_progress }}%;" 
                                                        aria-valuenow="{{ $project->execution_progress }}" 
                                                        aria-valuemin="0" 
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-success mt-1" style="font-size: 0.65rem; font-weight: bold;">
                                                    {{ $project->execution_progress }}%
                                                </small>
                                            </div>
                                        @endif

                                        {{-- Approval status badge --}}
                                        @if($approvalBadge && $project->status !== 'in_execution')
                                            <span class="badge {{ $approvalBadge['class'] }} small-text" style="font-size:.68rem;">
                                                {{ $approvalBadge['label'] }}
                                            </span>
                                        @endif

                                        {{-- Financial / Technical review icons --}}
                                        @foreach($reviewIcons as $reviewIcon)
                                            @if($reviewIcon === 'reviewed_completed')
                                                <span class="badge bg-success small-text ms-1">
                                                    <i class="fas fa-check-double"></i> تمت المراجعة
                                                </span>
                                            @elseif($reviewIcon === 'financial_done')
                                                <span class="badge bg-success small-text ms-1">
                                                    <i class="fas fa-check"></i> المالية مكتملة
                                                </span>
                                            @elseif($reviewIcon === 'financial')
                                                <span class="badge small-text ms-1" style="background-color: #17a2b8;">
                                                    <i class="fas fa-dollar-sign"></i> مراجعة مالية
                                                </span>
                                            @elseif($reviewIcon === 'technical_done')
                                                <span class="badge bg-success small-text ms-1">
                                                    <i class="fas fa-check"></i> الفنية مكتملة
                                                </span>
                                            @elseif($reviewIcon === 'technical')
                                                <span class="badge small-text ms-1" style="background-color: #6f42c1;">
                                                    <i class="fas fa-tools"></i> مراجعة فنية
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>

                                <!-- المنشئ -->
                                <td class="small-text text-center">{{ optional($project->createdBy)->name ?? '-' }}</td>

                                <td class="small-text text-center">
                                    {{ \Illuminate\Support\Str::limit($project->creator_entity_name, 25) }}
                                </td>
                                <!-- العمليات -->
                                <td class="table-actions">
                                    @php
                                        $isDraft = $project->status === 'draft';
                                        $isFinal = $project->status === 'final';
                                        $isInProgress = in_array($project->status, ['in_progress', 'implementation', 'in_execution']);
                                        $isAssemblyApproval = $project->status === 'assembly_approval';
                                        $isUnionStage = $project->status === 'union_stage';
                                        $isCommitteeStage = $project->status === 'committee_stage';
                                        $isPendingApproval = $project->approval_status === 'pending';
                                        $isDraftComplete = $project->isDraftComplete();
                                        $isDraftIncomplete = !$isDraftComplete;
                                        $isInternallyApproved = $project->status === 'internally_approved';
                                    @endphp

                                    <div class="btn-group btn-group-sm flex-wrap" role="group" style="gap: 1px;">
                                        <!-- ===== DRAFT PROJECTS ===== -->
                                        @if($isDraft)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Confirm Draft (Only for complete) -->
                                            @if($isDraftComplete)
                                                @can('update', $project)
                                                    <form action="{{ route('projects.finalize', $project->id) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success auth-perm-projects-finalize"
                                                            title="تأكيد المسودة وبدء سير العمل"
                                                            onclick="return confirmAction(this, 'هل تريد تأكيد هذه المسودة وبدء سير العمل؟')">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif

                                            <!-- Resume Draft (Only for incomplete) -->
                                            @if($isDraftIncomplete)
                                                @can('resume', $project)
                                                    <a href="{{ route('projects.draft.resume', $project->id) }}"
                                                        class="btn btn-primary auth-perm-projects-resume" title="استئناف المسودة">
                                                        <i class="fas fa-play-circle"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            <!-- Edit Data (Only for complete) -->
                                            @if($isDraftComplete)
                                                @can('update', $project)
                                                    <a href="{{ route('projects.edit', $project->id) }}"
                                                        class="btn btn-warning auth-perm-projects-edit" title="تعديل البيانات">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            <!-- Draft Approval (Internal Review) -->
                                            <!-- @can('review', $project)
                                                                                                            <a href="{{ route('projects.review', $project->id) }}" class="btn btn-success"
                                                                                                                title="مراجعة واعتماد المسودة داخلياً">
                                                                                                                <i class="fas fa-check-circle"></i>
                                                                                                            </a>
                                                                                                        @endcan -->

                                            <!-- Delete Draft -->
                                            @can('delete', $project)
                                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger auth-perm-projects-delete"
                                                        title="حذف المسودة"
                                                        onclick="return confirmAction(this, 'هل أنت متأكد من حذف هذه المسودة؟')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            <!-- ===== INTERNALLY APPROVED ===== -->
                                        @elseif($isInternallyApproved)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Convert to Draft -->
                                            @can('update', $project)
                                                <form action="{{ route('projects.revert-draft', $project->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning auth-perm-projects-revert"
                                                        title="الرجوع إلى مسودة"
                                                        onclick="return confirmAction(this, 'هل تريد تحويل هذا المشروع إلى مسودة؟ سيمكنك من التعديل عليه مرة أخرى.')">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            <!-- ===== ASSEMBLY APPROVAL STAGE ===== -->
                                        @elseif($isAssemblyApproval)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Approve current stage (Assembly) -->
                                            @can('approve', $project)
                                                <button type="button" class="btn btn-success auth-perm-projects-approve" title="اعتماد مرحلة الجمعية"
                                                    data-bs-toggle="modal" data-bs-target="#stageModal{{ $project->id }}_assembly">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            @endcan

                                            <!-- Convert any draft for editing -->
                                            @if($isPendingApproval)
                                                @can('update', $project)
                                                    <form action="{{ route('projects.revert-draft', $project->id) }}" method="POST"
                                                        style="display:inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning auth-perm-projects-revert" title="الرجوع إلى مسودة"
                                                            onclick="return confirmAction(this, 'هل تريد تحويل هذا المشروع إلى مسودة؟ سيمكنك من التعديل عليه مرة أخرى.')">
                                                            <i class="fas fa-undo-alt"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif

                                            <!-- ===== UNION STAGE ===== -->
                                        @elseif($isUnionStage)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Approve current stage (Union) -->
                                            @can('approve', $project)
                                                <button type="button" class="btn btn-success auth-perm-projects-approve" title="اعتماد مرحلة الاتحاد"
                                                    data-bs-toggle="modal" data-bs-target="#stageModal{{ $project->id }}_union">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            @endcan
                                            <!-- Convert to Draft -->
                                            @can('update', $project)
                                                <form action="{{ route('projects.revert-draft', $project->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning auth-perm-projects-revert"
                                                        title="الرجوع إلى مسودة"
                                                        onclick="return confirmAction(this, 'هل تريد تحويل هذا المشروع إلى مسودة؟ سيمكنك من التعديل عليه مرة أخرى.')">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            <!-- ===== COMMITTEE STAGE ===== -->
                                        @elseif($isCommitteeStage)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Approve current stage (Committee) -->
                                            @can('approve', $project)
                                                <button type="button" class="btn btn-success auth-perm-projects-approve" title="اعتماد مرحلة اللجنة"
                                                    data-bs-toggle="modal" data-bs-target="#stageModal{{ $project->id }}_committee">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            @endcan
                                            <!-- Convert to Draft -->
                                            @can('update', $project)
                                                <form action="{{ route('projects.revert-draft', $project->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning auth-perm-projects-revert"
                                                        title="الرجوع إلى مسودة"
                                                        onclick="return confirmAction(this, 'هل تريد تحويل هذا المشروع إلى مسودة؟ سيمكنك من التعديل عليه مرة أخرى.')">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            <!-- ===== IMPLEMENTATION STAGE ===== -->
                                        @elseif($isInProgress)
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Project Implementation -->
                                            @if($isInProgress)
                                                @can('execute', $project)
                                                    <a href="{{ route('projects.execution', $project->id) }}" class="btn btn-success auth-perm-projects-execute"
                                                        title="تنفيذ المشروع">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                @endcan
                                            @endif

                                            <!-- View Timeline -->
                                            @can('viewSchedule', $project)
                                                <a href="{{ route('projects.schedule', $project->id) }}" class="btn btn-info auth-perm-projects-schedule"
                                                    title="عرض الجدول الزمني">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </a>
                                            @endcan

                                            <!-- ===== FINAL / OTHER STAGES ===== -->
                                        @else
                                            <!-- View Details -->
                                            @can('view', $project)
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="btn btn-info auth-perm-projects-view-details" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            <!-- Submit for Approval (Final status only) -->
                                            @if($isFinal && $isPendingApproval)
                                                @can('update', $project)
                                                    <button class="btn btn-primary auth-perm-projects-edit" title="إرسال للموافقة" data-bs-toggle="modal"
                                                        data-bs-target="#submitApprovalModal{{ $project->id }}">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        @endif

                                        <!-- ===== FINANCIAL & TECHNICAL REVIEW BUTTONS ===== -->
                                        @if($project->status === 'financial_review')
                                            @can('reviewFinancial', $project)
                                                <a href="{{ route('projects.review.financial', $project->id) }}" class="btn btn-info auth-perm-reviews-financial"
                                                    title="المراجعة المالية">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </a>
                                            @endcan

                                            @can('reviewTechnical', $project)
                                                <a href="{{ route('projects.review.technical', $project->id) }}" class="btn btn-primary auth-perm-reviews-technical"
                                                    title="المراجعة الفنية">
                                                    <i class="fas fa-tools"></i>
                                                </a>
                                            @endcan
                                        @endif

                                        <!-- dropdown التصدير والمزيد -->
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="moreActionsDropdown{{ $project->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end"
                                                aria-labelledby="moreActionsDropdown{{ $project->id }}">
                                                @can('print', $project)
                                                    <a class="dropdown-item auth-perm-projects-print" href="{{ route('projects.print', $project->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-print text-success me-1"></i>
                                                        <span>طباعة</span>
                                                    </a>
                                                @endcan

                                                @can('export', $project)
                                                    <div class="dropdown-divider"></div>
                                                    <h6 class="dropdown-header">التصدير</h6>
                                                    <a class="dropdown-item auth-perm-projects-export"
                                                        href="{{ route('projects.export-excel-single', $project->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-excel text-success me-1"></i>
                                                        <span>تصدير Excel</span>
                                                    </a>
                                                    <a class="dropdown-item auth-perm-projects-export"
                                                        href="{{ route('projects.export-excel-comprehensive', $project->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-export text-success me-1"></i>
                                                        <span>تصدير شامل Excel</span>
                                                    </a>
                                                    <a class="dropdown-item auth-perm-projects-export" href="{{ route('projects.export-pdf', $project->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-pdf text-danger me-1"></i>
                                                        <span>تصدير PDF</span>
                                                    </a>
                                                @endcan

                                                @can('view', $project)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item auth-perm-projects-view-details" href="{{ route('projects.card.show', $project->id) }}">
                                                        <i class="fas fa-id-card text-info me-1"></i>
                                                        <span>بطاقة المشروع</span>
                                                    </a>
                                                @endcan

                                                @can('viewWorkflow', $project)
                                                    <a class="dropdown-item auth-perm-stages-view"
                                                        href="{{ route('projects.approval.show', $project->id) }}">
                                                        <i class="fas fa-tasks text-info me-1"></i>
                                                        <span>سير العمل</span>
                                                    </a>
                                                @endcan

                                                @can('delete', $project)
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                                                        style="display:inline;" class="delete-project-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger auth-perm-projects-delete" title="حذف المشروع"
                                                            onclick="return confirmAction(this, 'هل أنت متأكد من حذف هذا المشروع؟')">
                                                            <i class="fas fa-trash me-1"></i>
                                                            <span>حذف</span>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted small-text">
                                    <p class="mb-0">لا توجد مشاريع</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stage Approval Modals -->
        @foreach($projects as $project)
            @php
                $stageTypes = ['assembly', 'union', 'committee', 'implementation'];
                $stageNames = [
                    'assembly' => 'موافقة الجمعية',
                    'union' => 'موافقة الاتحاد',
                    'committee' => 'موافقة اللجنة',
                    'implementation' => 'مرحلة التنفيذ',
                ];
                $stageIcons = [
                    'assembly' => 'fas fa-users-cog',
                    'union' => 'fas fa-handshake',
                    'committee' => 'fas fa-gavel',
                    'implementation' => 'fas fa-rocket',
                ];
            @endphp
            @foreach($stageTypes as $stageType)
                <div class="modal fade" id="stageModal{{ $project->id }}_{{ $stageType }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white py-2">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="{{ $stageIcons[$stageType] }} fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="modal-title mb-0">{{ $stageNames[$stageType] }}</h6>
                                        <small class="opacity-75">مرحلة الموافقة الحالية</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-2">
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p class="mb-1 small"><strong>اسم المشروع:</strong><br><span
                                                class="text-muted">{{ $project->project_name }}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 small"><strong>رقم النموذج:</strong><br><span
                                                class="text-muted">{{ $project->form_number }}</span></p>
                                    </div>
                                </div>

                                <ul class="nav nav-tabs nav-tabs-sm" id="stageTab{{ $project->id }}_{{ $stageType }}"
                                    role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1" id="approve-tab-{{ $project->id }}-{{ $stageType }}"
                                            data-bs-toggle="tab" data-bs-target="#approve-{{ $project->id }}-{{ $stageType }}"
                                            type="button" role="tab">
                                            <i class="fas fa-check-circle text-success"></i> موافقة
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1" id="action-tab-{{ $project->id }}-{{ $stageType }}"
                                            data-bs-toggle="tab" data-bs-target="#action-{{ $project->id }}-{{ $stageType }}"
                                            type="button" role="tab">
                                            <i class="fas fa-exclamation-triangle text-warning"></i> طلب إجراء
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1" id="reject-tab-{{ $project->id }}-{{ $stageType }}"
                                            data-bs-toggle="tab" data-bs-target="#reject-{{ $project->id }}-{{ $stageType }}"
                                            type="button" role="tab">
                                            <i class="fas fa-times-circle text-danger"></i> رفض
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content mt-2" id="stageTabContent{{ $project->id }}_{{ $stageType }}">
                                    <!-- Approve Tab -->
                                    <div class="tab-pane fade show active" id="approve-{{ $project->id }}-{{ $stageType }}"
                                        role="tabpanel">
                                        <form action="{{ route('projects.approval.approve', $project) }}" method="POST"
                                            class="stage-form">
                                            @csrf
                                            <input type="hidden" name="stage_type" value="{{ $stageType }}">
                                            <div class="form-group mb-2">
                                                <label for="approve-notes-{{ $project->id }}-{{ $stageType }}"
                                                    class="form-label small">ملاحظات (اختياري)</label>
                                                <textarea class="form-control form-control-sm"
                                                    id="approve-notes-{{ $project->id }}-{{ $stageType }}" name="notes" rows="2"
                                                    placeholder="أضف أي ملاحظات..."></textarea>
                                            </div>
                                            <div class="alert alert-success py-1 small" role="alert">
                                                <i class="fas fa-info-circle"></i> سيتم الموافقة على هذه المرحلة والانتقال للمرحلة
                                                التالية.
                                            </div>
                                            <div class="d-grid gap-1">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check-circle"></i> الموافقة
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Request Action Tab -->
                                    <div class="tab-pane fade" id="action-{{ $project->id }}-{{ $stageType }}" role="tabpanel">
                                        <form action="{{ route('projects.approval.requestAction', $project) }}" method="POST"
                                            class="stage-form">
                                            @csrf
                                            <input type="hidden" name="stage_type" value="{{ $stageType }}">
                                            <div class="form-group mb-2">
                                                <label for="action-required-{{ $project->id }}-{{ $stageType }}"
                                                    class="form-label small">الإجراء المطلوب <span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm"
                                                    id="action-required-{{ $project->id }}-{{ $stageType }}" name="action_required"
                                                    rows="2" placeholder="اشرح الإجراء المطلوب..." required></textarea>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="action-notes-{{ $project->id }}-{{ $stageType }}"
                                                    class="form-label small">ملاحظات (اختياري)</label>
                                                <textarea class="form-control form-control-sm"
                                                    id="action-notes-{{ $project->id }}-{{ $stageType }}" name="notes" rows="2"
                                                    placeholder="أضف أي ملاحظات إضافية..."></textarea>
                                            </div>
                                            <div class="alert alert-warning py-1 small" role="alert">
                                                <i class="fas fa-info-circle"></i> سيتم إرسال طلب الإجراء إلى مطور المشروع.
                                            </div>
                                            <div class="d-grid gap-1">
                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-exclamation-triangle"></i> طلب إجراء
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Reject Tab -->
                                    <div class="tab-pane fade" id="reject-{{ $project->id }}-{{ $stageType }}" role="tabpanel">
                                        <form action="{{ route('projects.approval.reject', $project) }}" method="POST"
                                            class="stage-form">
                                            @csrf
                                            <input type="hidden" name="stage_type" value="{{ $stageType }}">
                                            <div class="form-group mb-2">
                                                <label for="reject-reason-{{ $project->id }}-{{ $stageType }}"
                                                    class="form-label small">سبب الرفض <span class="text-danger">*</span></label>
                                                <textarea class="form-control form-control-sm"
                                                    id="reject-reason-{{ $project->id }}-{{ $stageType }}" name="reason" rows="2"
                                                    placeholder="يرجى تقديم سبب مفصل..." required></textarea>
                                            </div>
                                            <div class="alert alert-danger py-1 small" role="alert">
                                                <i class="fas fa-info-circle"></i> سيتم رفض هذه المرحلة وإعادة المشروع إلى مطوره.
                                            </div>
                                            <div class="d-grid gap-1">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times-circle"></i> رفض
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light py-1">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        <!-- Submit for Approval Modals -->
        @foreach($projects as $project)
            @if($project->status === 'final' && $project->approval_status === 'pending')
                <div class="modal fade" id="submitApprovalModal{{ $project->id }}" tabindex="-1" role="dialog"
                    aria-labelledby="submitApprovalModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h6 class="modal-title">إرسال للموافقة</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('projects.approval.submit', $project) }}" method="POST">
                                @csrf
                                <div class="modal-body py-2">
                                    <p class="small mb-1"><strong>اسم المشروع:</strong> {{ $project->project_name }}</p>
                                    <p class="small mb-2"><strong>رقم النموذج:</strong> {{ $project->form_number }}</p>
                                    <div class="alert alert-info py-1 small">
                                        <i class="fas fa-info-circle"></i> بعد الإرسال، لن تتمكن من تعديل بيانات المشروع إلا أثناء
                                        مراحل المراجعة الفنية والمالية.
                                    </div>
                                </div>
                                <div class="modal-footer py-1">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-primary btn-sm">إرسال للموافقة</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="pagination-container custom-pagination-sm">
                @if(method_exists($projects, 'hasPages') && $projects->hasPages())
                    {{ $projects->links('pagination::bootstrap-5') }}
                @endif
            </div>

            <!-- اختيار عدد السجلات المعروضة -->
            <div class="d-flex align-items-center">
                <label for="recordsPerPage" class="me-2 small-text">عرض:</label>
                <select class="form-select form-select-sm small-text" id="recordsPerPage" style="width: auto;">
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 سجل</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 سجل</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 سجل</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500 سجل</option>
                </select>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('recordsPerPage').addEventListener('change', function () {
                const recordsPerPage = this.value;
                window.location.href = updateQueryStringParameter(window.location.href, 'per_page', recordsPerPage);
            });

            function updateQueryStringParameter(uri, key, value) {
                const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                const separator = uri.indexOf('?') !== -1 ? "&" : "?";
                if (uri.match(re)) {
                    return uri.replace(re, '$1' + key + "=" + value + '$2');
                } else {
                    return uri + separator + key + "=" + value;
                }
            }
        });

        // دالة التنقل بين المراحل
        function navigateToStage(projectId, stageId) {
            window.location.href = `/projects/${projectId}/approval/stage/${stageId}`;
        }
    </script>

    <style>
        .compact-card .card-body {
            padding: 0.75rem;
        }

        .compact-table .table {
            font-size: 13px !important;
            white-space: nowrap;
        }

        .compact-table .table th,
        .compact-table .table td {
            padding: 0.5rem 0.6rem !important;
            vertical-align: middle;
        }

        .table-actions .btn-group {
            flex-wrap: nowrap;
            gap: 2px;
        }

        .table-actions .btn {
            padding: 0.25rem 0.4rem;
            font-size: 11px;
            min-width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-number {
            font-family: monospace;
            font-weight: bold;
            font-size: 13px;
        }

        .table-wrapper {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background: #fff;
            margin-bottom: 1.5rem;
        }

        .dropdown-toggle::after {
            margin-right: 0.25em;
            margin-left: 0;
            font-size: 12px;
        }

        .small-text {
            font-size: 12px !important;
        }

        .badge {
            font-size: 11px !important;
            padding: 0.4em 0.6em !important;
        }

        .dropdown-menu {
            font-size: 14px !important;
            min-width: 170px;
            padding: 0.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            padding: 0.4rem 1rem !important;
        }

        /* Modal styles */
        .modal-content {
            font-size: 15px;
            border-radius: 10px;
        }

        .modal-header {
            padding: 1rem 1.25rem;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-footer {
            padding: 1rem 1.25rem;
        }

        .nav-tabs-sm .nav-link {
            padding: 0.6rem 1.2rem;
            font-size: 14px;
        }

        .form-control-sm {
            font-size: 14px;
            padding: 0.5rem 0.75rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            font-size: 14px;
            border-radius: 6px;
        }

        .btn-sm {
            padding: 0.45rem 1rem;
            font-size: 14px;
        }

        /* Ensure icons are also comfortable */
        .fas,
        .far,
        .fab {
            font-size: 1em;
        }

        .custom-pagination-sm .pagination {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .custom-pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
        }
    </style>

@endsection

@section('scripts')
    <script src="{{ asset('js/projects-bulk-actions.js') }}"></script>
    <script>
        $(document).ready(function () {
            // Select All Checkbox functionality
            $('#select-all-projects').change(function () {
                var isChecked = $(this).prop('checked');
                $('.project-checkbox').prop('checked', isChecked);
            });

            // If all individual checkboxes are checked, check the "Select All" checkbox
            $(document).on('change', '.project-checkbox', function () {
                var allChecked = $('.project-checkbox:checked').length === $('.project-checkbox').length;
                $('#select-all-projects').prop('checked', allChecked);
            });
        });

        function handleExport(type) {
            const selectedIds = [];
            $('.project-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            const exportType = selectedIds.length > 0 ? 'selected' : 'all';
            let url = '';
            let params = new URLSearchParams();

            if (type === 'excel') {
                url = '{{ route("projects.export-excel") }}';
            } else if (type === 'comprehensive') {
                url = '{{ route("projects.export-excel-comprehensive-all") }}';
            } else if (type === 'pdf') {
                url = '{{ route("projects.export-pdf-all") }}';
            } else if (type === 'pivot') {
                url = '{{ route("projects.export-pivot-all") }}';
            }

            params.append('export_type', exportType);

            if (exportType === 'selected') {
                params.append('selected_projects', JSON.stringify(selectedIds));
            } else {
                // If exporting all, include current URL filters
                const urlParams = new URLSearchParams(window.location.search);
                ['program_id', 'domain_id', 'subdomain_id', 'status', 'search'].forEach(param => {
                    if (urlParams.has(param)) {
                        params.append(param, urlParams.get(param));
                    }
                });
            }

            window.location.href = url + '?' + params.toString();
        }
    </script>
@endsection