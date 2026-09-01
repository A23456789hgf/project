@extends('layouts.app')

@section('styles')
<style>
    /* Wizard Steps Modern Design */
    .wizard-steps {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 3rem;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 25px;
        left: 15%;
        right: 15%;
        height: 4px;
        background: #e9ecef;
        z-index: 0;
        border-radius: 10px;
    }

    .wizard-step {
        position: relative;
        z-index: 1;
        cursor: pointer;
        text-align: center;
        width: 200px;
        transition: transform 0.3s ease;
    }

    .wizard-step:hover {
        transform: translateY(-5px);
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: white;
        border: 4px solid #e9ecef;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-weight: 800;
        font-size: 1.2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .step-label {
        font-size: 0.95rem;
        color: #6c757d;
        font-weight: 700;
        transition: all 0.3s;
    }

    .wizard-step.active .step-number {
        background: var(--primary);
        border-color: var(--primary-light);
        color: white;
        box-shadow: 0 0 0 5px rgba(13, 110, 253, 0.15);
        transform: scale(1.1);
    }

    .wizard-step.completed .step-number {
        background: #198754;
        border-color: #198754;
        color: white;
    }

    .wizard-step.active .step-label { color: var(--primary); }
    .wizard-step.completed .step-label { color: #198754; }

    /* Step Content Animation */
    .step-content {
        display: none;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .step-content.active {
        display: block;
        animation: slideUpFade 0.5s forwards;
    }

    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Premium Design System */
    :root {
        --premium-slate: #1e293b;
        --premium-indigo: #4f46e5;
        --premium-emerald: #10b981;
        --premium-amber: #f59e0b;
        --premium-rose: #e11d48;
        --premium-glass: rgba(255, 255, 255, 0.7);
        --premium-shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --premium-shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --premium-shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    body { font-family: 'Outfit', 'Inter', 'Cairo', sans-serif; }

    .content-wrapper { background: #f1f5f9; min-height: 100vh; }

    /* Elegant Container */
    .premium-container {
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: var(--premium-shadow-lg);
        overflow: hidden;
        margin-bottom: 2.5rem;
        transition: transform 0.3s ease;
    }

    .premium-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        padding: 1.5rem 2rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* Floating Row Design */
    .elegant-table {
        width: 100%;
        min-width: 800px;
        border-collapse: separate;
        border-spacing: 0 12px;
        padding: 0 1.5rem 1.5rem 1.5rem;
    }

    .elegant-table thead th {
        padding: 1rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border: none;
        text-align: right;
    }

    .elegant-table tr.floating-row {
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .elegant-table tr.floating-row td {
        padding: 1.25rem 1rem;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        color: #334155;
    }

    .elegant-table tr.floating-row td:first-child {
        border-right: 1px solid #f1f5f9;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .elegant-table tr.floating-row td:last-child {
        border-left: 1px solid #f1f5f9;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .elegant-table tr.floating-row:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 12px 20px -8px rgba(0,0,0,0.15);
        z-index: 10;
    }

    .elegant-table tr.floating-row:hover td {
        border-color: rgba(79, 70, 229, 0.2);
        background: rgba(79, 70, 229, 0.01);
    }

    /* Status Badges */
    .badge-premium {
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        border-radius: 50px;
        font-size: 0.75rem;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
    }

    .badge-premium-blue { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .badge-premium-green { background: #f0fdf4; color: #15803d; border-color: #dcfce7; }
    .badge-premium-amber { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
    .badge-premium-rose { background: #fff1f2; color: #be123c; border-color: #ffe4e6; }

    /* Nested Premium */
    .premium-nested-container {
        padding: 1rem 2rem;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    .premium-nested-item {
        background: white;
        margin-bottom: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: border-color 0.2s;
    }

    .premium-nested-item:hover {
        border-color: var(--premium-indigo);
    }

    /* Animations */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-premium {
        animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    .highlight-section {
        animation: highlightPulse 3s ease-out;
    }

    @keyframes highlightPulse {
        0% { background-color: rgba(79, 70, 229, 0.1); box-shadow: 0 0 0 10px rgba(79, 70, 229, 0.1); }
        50% { background-color: rgba(79, 70, 229, 0.05); box-shadow: 0 0 0 5px rgba(79, 70, 229, 0.05); }
        100% { background-color: transparent; box-shadow: none; }
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-fluid py-5">
        
        <!-- Page Header -->
        <div class="page-header mb-5 animate-fade-in">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('project-requests.index') }}" class="text-muted">طلبات المشاريع</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">تفاصيل الطلب</li>
                        </ol>
                    </nav>
                    <h1 class="h3 fw-bold text-dark mb-1">
                        <i class="fas fa-file-alt me-2 text-primary"></i> عرض تفاصيل طلب المشروع
                    </h1>
                    <p class="text-muted small mb-0"> رقم الطلب: {{ $projectRequest->request_number ?? '#' }}</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                        @if($projectRequest->status === 'approved' && !$projectRequest->project_id)
                            @can('project-requests.transfer')
                            <form action="{{ route('project-requests.transfer', $projectRequest->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success shadow-sm fw-bold" onclick="return confirmAction(this, 'هل تريد تحويل هذا الطلب إلى مشروع رسمي؟')">
                                    <i class="fas fa-exchange-alt me-2"></i>تحويل لمشروع رسمي
                                </button>
                            </form>
                            @endcan
                        @endif

                        @if($projectRequest->status === 'transferred' && $projectRequest->project_id)
                            <a href="{{ route('projects.show', $projectRequest->project_id) }}" class="btn btn-outline-success shadow-sm fw-bold">
                                <i class="fas fa-check-circle me-2"></i>عرض المشروع المحول: {{ $projectRequest->assigned_project_number }}
                            </a>
                        @endif

                        @can('project-requests.print')
                        <a href="{{ route('project-requests.print', $projectRequest->id) }}" target="_blank" class="btn btn-dark shadow-sm">
                            <i class="fas fa-print me-2"></i>طباعة الطلب
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="wizard-steps animate-up">
            <div class="wizard-step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">البيانات الأساسية</div>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">التفاصيل الفنية والمالية</div>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">المراجعة والاعتماد</div>
            </div>
        </div>

        <!-- Alerts -->
        

        <!-- Approval Workflow Status Card -->
        <!-- <div id="approvalPhasesSection" class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden animate-up delay-1">
            <div class="card-body p-0">
                <div class="bg-light p-4 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="fas fa-tasks fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">مسار الموافقات</h5>
                            <p class="text-muted small mb-0">تتبع حالة المشروع عبر مراحل الاعتماد المختلفة</p>
                        </div>
                    </div>
                </div> -->
               
 

            <!-- Project Tracking Information -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-user-clock me-2"></i> بيانات التسجيل
                    </h5>
                    <div class="qr-code-badge bg-light p-2 rounded-3 border">
                        <img src="{{ $qrCodeBase64 }}" alt="Project QR" style="width: 80px; height: 80px;">
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-primary h-100">
                                <div class="info-label">مقدم الطلب</div>
                                <div class="info-value fw-bold">{{ optional($projectRequest->createdBy)->name ?? 'غير محدد' }}</div>
                                <div class="small text-muted mt-1">{{ $projectRequest->creator_entity_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-info h-100">
                                <div class="info-label">تاريخ التقديم</div>
                                <div class="info-value dir-ltr">{{ $projectRequest->created_at ? $projectRequest->created_at->format('Y-m-d H:i') : '-' }}</div>
                            </div>
                        </div>
                        @if($projectRequest->updated_by_user_id)
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning h-100">
                                <div class="info-label">آخر تعديل</div>
                                <div class="info-value fw-bold">{{ optional($projectRequest->updatedBy)->name ?? 'غير محدد' }}</div>
                                <div class="small text-muted mt-1">{{ $projectRequest->updated_at ? $projectRequest->updated_at->format('Y-m-d H:i') : '-' }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 1: البيانات الأساسية والأهداف والنتائج -->
            <div class="step-content active" id="step-1">
                
                <!-- Project Basic Data -->
                <div id="basic-data-section" class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i> البيانات الأساسية للطلب
                        </h5>
                        @if($projectRequest->status === 'approved')
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                <i class="fas fa-check-circle me-1"></i> معتمد
                            </span>
                        @elseif($projectRequest->status === 'transferred')
                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                <i class="fas fa-exchange-alt me-1"></i> تم التحويل لمشروع
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                <i class="fas fa-clock me-1"></i> {{ $projectRequest->status_label }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="info-label d-block">اسم المشروع المقترح</label>
                                    <div class="fs-5 fw-bold text-dark">{{ $projectRequest->project_name ?? 'غير محدد' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">البرنامج</div>
                                <div class="info-value">{{ optional($projectRequest->program)->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">المجال الرئيسي</div>
                                <div class="info-value">{{ optional($projectRequest->domain)->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">المجال الفرعي</div>
                                <div class="info-value">{{ optional($projectRequest->subdomain)->name ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">التدخل</div>
                                <div class="info-value">{{ optional($projectRequest->intervention)->name ?? '-' }}</div>
                            </div>
                            <div class="col-12"><hr class="text-muted opacity-25"></div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">الأولوية</div>
                                <div class="info-value">
                                    @if(optional($projectRequest->priority)->priority)
                                        <span class="badge bg-secondary">{{ $projectRequest->priority->priority }}</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">الموجه الرئيسي</div>
                                <div class="info-value fw-bold">{{ optional($projectRequest->mainRouter)->main_router ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">الموجه الفرعي</div>
                                <div class="info-value fw-bold">{{ optional($projectRequest->subRouter)->sub_router ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">الفئة المستهدفة</div>
                                <div class="info-value fw-bold">{{ optional($projectRequest->targetCategory)->name ?? '-' }}</div>
                            </div>
                            <div class="col-12"><hr class="text-muted opacity-25"></div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">تاريخ البداية المتوقع (م)</div>
                                <div class="info-value font-monospace">{{ $projectRequest->start_date_gregorian ? \Carbon\Carbon::parse($projectRequest->start_date_gregorian)->format('Y-m-d') : '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">تاريخ النهاية المتوقع (م)</div>
                                <div class="info-value font-monospace">{{ $projectRequest->end_date_gregorian ? \Carbon\Carbon::parse($projectRequest->end_date_gregorian)->format('Y-m-d') : '-' }}</div>
                            </div>
                             <div class="col-md-6 col-lg-3">
                                <div class="info-label">تاريخ البداية المتوقع (هـ)</div>
                                <div class="info-value">{{ $projectRequest->start_date_hijri ?? '-' }}</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <div class="info-label">تاريخ النهاية المتوقع (هـ)</div>
                                <div class="info-value">{{ $projectRequest->end_date_hijri ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Locations -->
                @if($projectRequest->locations && $projectRequest->locations->count() > 0)
                <div id="locations-section" class="premium-container animate-premium delay-1">
                    <div class="premium-header">
                        <div class="bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="fas fa-map-marker-alt text-white"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">مواقع المشروع</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="elegant-table">
                            <thead>
                                <tr>
                                    <th width="80">#</th>
                                    <th>المحافظة</th>
                                    <th>المديرية</th>
                                    <th>المنطقة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectRequest->locations as $location)
                                <tr class="floating-row">
                                    <td class="fw-bold text-muted ps-4">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ optional($location->governorate)->name ?? 'جميع المحافظات' }}</td>
                                    <td>{{ optional($location->directorate)->name ?? 'جميع المديريات' }}</td>
                                    <td class="text-muted">{{ optional($location->subArea)->name ?? $location->area ?? 'جميع المناطق/العزل' }} / {{ optional($location->village)->name ?? 'جميع القرى/الحارات' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted opacity-50">
                                        <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                        <p>لا توجد مواقع مسجلة</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Project Details -->
                <div id="details-section" class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-file-alt me-2"></i> تفاصيل المشروع
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if($projectRequest->detail)
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="info-label mb-0 me-3">هل المشروع جزء من الخطة؟</div>
                                        <div>
                                            @if($projectRequest->detail->is_part_of_plan)
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fas fa-check me-1"></i> نعم</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="fas fa-times me-1"></i> لا</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light p-3 rounded-3">
                                        <div class="info-label text-primary mb-2">ملخص المشروع</div>
                                        <p class="mb-0 text-dark" style="line-height: 1.6;">{{ $projectRequest->detail->project_summary ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-label mb-2">المشكلة ومبررات التدخل</div>
                                    <p class="mb-0 text-muted">{{ $projectRequest->detail->problem_and_justification ?? '-' }}</p>
                                </div>
                                <div class="col-12"><hr class="text-muted opacity-25"></div>
                                <div class="col-md-6">
                                    <div class="info-label mb-2">مكونات المشروع</div>
                                    <p class="mb-0 text-muted">{{ $projectRequest->detail->project_components ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label mb-2">الأثر المتوقع</div>
                                    <p class="mb-0 text-muted">{{ $projectRequest->detail->expected_impact ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                <p>لا توجد تفاصيل إضافية مسجلة</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 4. Overall Objective -->
                <div class="content-section">
                    <div class="section-header">
                        <i class="fas fa-target"></i>
                        الهدف الرئيسي
                    </div>

                    @if($projectRequest->mainObjectives && $projectRequest->mainObjectives->count() > 0)
                        <div class="info-grid">
                            @foreach($projectRequest->mainObjectives as $objective)
                                <div class="info-item">
                                    <div class="info-label">الهدف الرئيسي</div>
                                    <div class="info-value">{{ $objective->objective ?? 'غير محدد' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-data">
                            <i class="fas fa-inbox me-2"></i>لا توجد أهداف رئيسية
                        </div>
                    @endif
                </div>

                <!-- 5. Specific Objectives, Results, and Outputs -->
                <div class="content-section">
                    <div class="section-header">
                        <i class="fas fa-list-check"></i>
                        الأهداف الخاصة والنتائج والمخرجات
                    </div>

                    @if($projectRequest->specialObjectives && $projectRequest->specialObjectives->count() > 0)
                        @foreach($projectRequest->specialObjectives as $objective)
                            <div class="subsection-title">
                                <i class="fas fa-angle-left ms-2"></i>الهدف الخاص: {{ $objective->objective ?? 'غير محدد' }}
                            </div>

                            <!-- Results -->
                            @if($objective->results && $objective->results->count() > 0)
                                <div class="mt-4 mb-3 d-flex align-items-center gap-2 text-indigo-600 fw-bold">
                                    <div class="bg-indigo-50 p-1 rounded">
                                        <i class="fas fa-check-double small"></i>
                                    </div>
                                    <span>النتائج المستهدفة:</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="elegant-table">
                                        <thead>
                                            <tr>
                                                <th width="60">#</th>
                                                <th width="250">النتيجة</th>
                                                <th>الوصف التفصيلي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($objective->results as $result)
                                            <tr class="floating-row">
                                                <td class="ps-3 text-slate-400 fw-bold">{{ $loop->iteration }}</td>
                                                <td class="fw-bold text-slate-800">{{ $result->result ?? 'غير محدد' }}</td>
                                                <td class="text-slate-500 small">{{ $result->description ?? 'بدون وصف' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-slate-400">لا توجد نتائج</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                 <!-- Outputs -->
                                 @foreach($objective->results as $result)
                                     @if($result->outputs && $result->outputs->count() > 0)
                                         <div class="mt-4 mb-3 d-flex align-items-center gap-2 text-emerald-600 fw-bold ms-4">
                                             <div class="bg-emerald-50 p-1 rounded">
                                                 <i class="fas fa-arrow-left small"></i>
                                             </div>
                                             <span>المخرجات لـ: {{ $result->result ?? 'غير محدد' }}</span>
                                         </div>
                                         <div class="table-responsive ms-4">
                                             <table class="elegant-table">
                                                 <thead>
                                                     <tr>
                                                         <th width="60">#</th>
                                                         <th>المخرج</th>
                                                         <th>الوصف</th>
                                                         <th width="150" class="text-center">الكمية المتوقعة</th>
                                                     </tr>
                                                 </thead>
                                                 <tbody>
                                                     @forelse($result->outputs as $output)
                                                     <tr class="floating-row">
                                                         <td class="ps-3 text-slate-400 fw-bold">{{ $loop->iteration }}</td>
                                                         <td class="fw-bold text-slate-800">{{ $output->output ?? 'غير محدد' }}</td>
                                                         <td class="text-slate-500 small">{{ $output->description ?? 'بدون وصف' }}</td>
                                                         <td class="text-center">
                                                            <span class="badge-premium badge-premium-blue">{{ $output->expected_quantity ?? '-' }}</span>
                                                         </td>
                                                     </tr>
                                                     @empty
                                                     <tr>
                                                         <td colspan="4" class="text-center py-4 text-slate-400">لا توجد مخرجات</td>
                                                     </tr>
                                                     @endforelse
                                                 </tbody>
                                             </table>
                                         </div>
                                     @endif
                                 @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fas fa-inbox me-2"></i>لا توجد أهداف خاصة
                        </div>
                    @endif
                </div>
            </div>

            <!-- Step 2: المخاطر والجهات والأنشطة والتكاليف -->
            <div class="step-content" id="step-2">
                <!-- 1. Risks and Stakeholders -->
                <div id="technical-details-section" class="content-section">
                    <div class="section-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        المخاطر والجهات ذات الصلة
                    </div>

                    <!-- Risks -->
                    @if($projectRequest->risks && $projectRequest->risks->count() > 0)
                        <div class="subsection-title mb-4 mt-4 fw-bold text-slate-700">
                            <i class="fas fa-exclamation-triangle ms-2 text-rose-500"></i>المخاطر المحتملة واستراتيجيات التخفيف
                        </div>
                        <div class="table-responsive">
                            <table class="elegant-table">
                                <thead>
                                    <tr>
                                        <th width="60">#</th>
                                        <th>نوع الخطر</th>
                                        <th width="150" class="text-center">مستوى الخطورة</th>
                                        <th>الحلول المقترحة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($projectRequest->risks as $risk)
                                    <tr class="floating-row">
                                        <td class="ps-3 text-slate-400 fw-bold">{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-slate-800">{{ $risk->risk ?? 'غير محدد' }}</td>
                                        <td class="text-center">
                                            @php
                                                $premiumBadge = match($risk->risk_rate) {
                                                    'high' => 'badge-premium-rose',
                                                    'medium' => 'badge-premium-amber',
                                                    'low' => 'badge-premium-green',
                                                    default => 'badge-premium-blue'
                                                };
                                                $riskIcon = match($risk->risk_rate) {
                                                    'high' => 'fa-fire',
                                                    'medium' => 'fa-exclamation-circle',
                                                    'low' => 'fa-check-circle',
                                                    default => 'fa-question-circle'
                                                };
                                            @endphp
                                            <span class="badge-premium {{ $premiumBadge }}">
                                                <i class="fas {{ $riskIcon }}"></i>
                                                {{ $risk->risk_rate ?? 'غير محدد' }}
                                            </span>
                                        </td>
                                        <td class="small text-slate-500">{{ $risk->proposed_solution ?? 'بدون حل' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-slate-400">لا توجد مخاطر مسجلة</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Supervising Authorities -->
                    @php
                        $supervisingAuthorities = $projectRequest->supervisingAuthorities()->with(['authority', 'parent'])->get();
                    @endphp

                    @if($supervisingAuthorities->count() > 0)
                        <div class="subsection-title mb-4 fw-bold text-slate-700">
                            <i class="fas fa-building ms-2 text-indigo-500"></i>الجهات المشرفة
                        </div>
                        <div class="table-responsive">
                            <table class="elegant-table">
                                <thead>
                                    <tr>
                                        <th width="80">#</th>
                                        <th>اسم الجهة</th>
                                        <th width="150" class="text-center">النوع</th>
                                        <th>الجهة التابعة لها</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supervisingAuthorities as $authority)
                                    <tr class="floating-row">
                                        <td class="text-center fw-bold text-slate-400">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                                                    <i class="fas fa-eye small"></i>
                                                </div>
                                                <span class="fw-bold text-slate-700">{{ $authority->authority->agency_name ?? 'غير محدد' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($authority->authority_type == 'internal')
                                                <span class="badge-premium badge-premium-blue">
                                                    <i class="fas fa-home"></i>داخلية
                                                </span>
                                            @else
                                                <span class="badge-premium badge-premium-green">
                                                    <i class="fas fa-external-link-alt"></i>خارجية
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($authority->parent && $authority->parent->agency_name)
                                                <div class="d-flex align-items-center gap-2 text-slate-500 small">
                                                    <i class="fas fa-level-up-alt fa-rotate-90 opacity-40"></i>
                                                    <span>{{ $authority->parent->agency_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-slate-300 fst-italic">تبعية مباشرة</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Implementing Entities -->
                    @if($projectRequest->implementingEntities && $projectRequest->implementingEntities->count() > 0)
                        <div class="subsection-title mb-4 mt-5 fw-bold text-slate-700">
                            <i class="fas fa-cogs ms-2 text-indigo-500"></i>الجهات المنفذة
                        </div>
                        <div class="table-responsive">
                            <table class="elegant-table">
                                <thead>
                                    <tr>
                                        <th width="80">#</th>
                                        <th>الجهة</th>
                                        <th width="150" class="text-center">النوع</th>
                                        <th>الجهة الرئيسية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projectRequest->implementingEntities as $entity)
                                    <tr class="floating-row">
                                        <td class="text-center fw-bold text-slate-400">{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-slate-700">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-building text-slate-300"></i>
                                                {{ $entity->authority->agency_name ?? 'غير محدد' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($entity->entity_type === 'internal')
                                                <span class="badge-premium badge-premium-blue">داخلية</span>
                                            @else
                                                <span class="badge-premium badge-premium-green">خارجية</span>
                                            @endif
                                        </td>
                                        <td class="text-slate-500 small">{{ $entity->parent->agency_name ?? 'لا يوجد' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Participating & Beneficiary Entities Combined Premium -->
                    @if(($projectRequest->participatingEntities && $projectRequest->participatingEntities->count() > 0) || ($projectRequest->beneficiaryEntities && $projectRequest->beneficiaryEntities->count() > 0))
                        <div class="subsection-title mb-4 mt-5 fw-bold text-slate-700">
                            <i class="fas fa-users-cog ms-2 text-indigo-500"></i>الجهات المشاركة والمستفيدة
                        </div>
                        <div class="table-responsive">
                            <table class="elegant-table">
                                <thead>
                                    <tr>
                                        <th width="80">#</th>
                                        <th width="150" class="text-center">الدور / النوع</th>
                                        <th>الجهة</th>
                                        <th>الجهة الأم / التبعية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1; @endphp
                                    @foreach($projectRequest->participatingEntities as $entity)
                                    <tr class="floating-row">
                                        <td class="text-center fw-bold text-slate-400">{{ $index++ }}</td>
                                        <td class="text-center">
                                            <span class="badge-premium badge-premium-amber">
                                                <i class="fas fa-handshake"></i> مشاركة
                                            </span>
                                        </td>
                                        <td class="fw-bold text-slate-700">{{ optional($entity->authority)->agency_name ?? 'غير محدد' }}</td>
                                        <td class="text-slate-500 small">{{ optional($entity->parent)->agency_name ?? 'تبعية رئيسية' }}</td>
                                    </tr>
                                    @endforeach
                                    @foreach($projectRequest->beneficiaryEntities as $entity)
                                    <tr class="floating-row">
                                        <td class="text-center fw-bold text-slate-400">{{ $index++ }}</td>
                                        <td class="text-center">
                                            <span class="badge-premium badge-premium-rose">
                                                <i class="fas fa-hand-holding-heart"></i> مستفيدة
                                            </span>
                                        </td>
                                        <td class="fw-bold text-slate-700">{{ optional($entity->authority)->agency_name ?? 'غير محدد' }}</td>
                                        <td class="text-slate-500 small">{{ optional($entity->parent)->agency_name ?? 'تبعية رئيسية' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 2. Preliminary Activities and Costs -->
                @if($projectRequest->preliminaryActivities && $projectRequest->preliminaryActivities->count() > 0)
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-play-circle me-2"></i> الأنشطة التمهيدية
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="prelimActivitiesAccordion">
                            @foreach($projectRequest->preliminaryActivities as $actIndex => $activity)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="prelimHeading{{ $actIndex }}">
                                        <button class="accordion-button {{ $actIndex !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#prelimCollapse{{ $actIndex }}" aria-expanded="{{ $actIndex === 0 ? 'true' : 'false' }}">
                                            <div class="d-flex align-items-center w-100 justify-content-between pe-3">
                                                <div>
                                                    <span class="badge bg-secondary rounded-pill me-2">{{ $loop->iteration }}</span>
                                                    <span class="fw-bold text-dark">{{ $activity->name ?? 'نشاط تمهيدي' }}</span>
                                                </div>
                                                <span class="badge bg-light text-dark border">الوزن: {{ $activity->weight ?? '0' }}%</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="prelimCollapse{{ $actIndex }}" class="accordion-collapse collapse {{ $actIndex === 0 ? 'show' : '' }}" aria-labelledby="prelimHeading{{ $actIndex }}" data-bs-parent="#prelimActivitiesAccordion">
                                        <div class="accordion-body bg-light">
                                            <!-- Procedures and Costs Premium -->
                                            @if($activity->procedures && $activity->procedures->count() > 0)
                                                <div class="table-responsive mt-3">
                                                    <table class="elegant-table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>اسم الإجراء</th>
                                                                <th width="80" class="text-center">الأهمية</th>
                                                                <th>وسائل التحقق</th>
                                                                <th width="100" class="text-center">المدة</th>
                                                                <th width="140" class="text-end">ميزانية الإجراء</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($activity->procedures as $procedure)
                                                            <tr class="floating-row">
                                                                <td class="fw-bold text-slate-700 ps-4">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <i class="fas fa-chevron-left x-small text-slate-300"></i>
                                                                        {{ $procedure->procedure_name ?? '-' }}
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge-premium badge-premium-blue">{{ $procedure->weight ?? '-' }}%</span>
                                                                </td>
                                                                <td class="small text-slate-500">{{ $procedure->verification_means ?? '-' }}</td>
                                                                <td class="text-center small text-slate-600 font-monospace">{{ $procedure->duration_days ?? '-' }} ي</td>
                                                                <td class="text-end fw-bold text-emerald-600 font-monospace pe-3">
                                                                    {{ $procedure->costs && $procedure->costs->sum('total') ? number_format($procedure->costs->sum('total'), 2) : '0.00' }}
                                                                </td>
                                                            </tr>
                                                            <!-- Premium Nested Costs -->
                                                            @if($procedure->costs && $procedure->costs->count() > 0)
                                                                <tr>
                                                                    <td colspan="5" class="py-0 px-4">
                                                                        <div class="premium-nested-container">
                                                                            @foreach($procedure->costs as $cost)
                                                                            <div class="premium-nested-item">
                                                                                <div class="d-flex align-items-center gap-3">
                                                                                    <div class="text-indigo-400 opacity-50"><i class="fas fa-coins x-small"></i></div>
                                                                                    <span class="fw-medium text-slate-600">{{ $cost->financialItem->name ?? 'بند' }}</span>
                                                                                    <span class="badge border text-slate-400 rounded-pill x-small px-2">{{ $cost->unit->unit_name ?? '-' }}</span>
                                                                                </div>
                                                                                <div class="d-flex align-items-center gap-4">
                                                                                    <span class="text-slate-400 x-small font-monospace">{{ number_format($cost->amount ?? 0, 2) }} × {{ $cost->quantity ?? 0 }}</span>
                                                                                    <span class="fw-bold text-indigo-600 font-monospace">{{ number_format($cost->total ?? 0, 2) }}</span>
                                                                                </div>
                                                                            </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-muted small fst-italic text-center py-2">لا توجد إجراءات مسجلة</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Financial Summary Footer -->
                    @if($projectRequest->preliminaryFinancialSummaries && $projectRequest->preliminaryFinancialSummaries->count() > 0)
                    <div class="card-footer bg-light p-3">
                        <h6 class="fw-bold text-dark mb-2 small text-uppercase">ملخص مالي (تمهيدي)</h6>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($projectRequest->preliminaryFinancialSummaries as $summary)
                            <div class="badge bg-white text-dark border p-2 shadow-sm rounded-3 d-flex align-items-center">
                                <span class="text-muted me-2">{{ $summary->item ?? '-' }}:</span>
                                <span class="fw-bold font-monospace text-primary">{{ number_format($summary->amount ?? 0, 2) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- 3. Executive Activities and Costs -->
                @if($projectRequest->executiveActivities && $projectRequest->executiveActivities->count() > 0)
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-cogs me-2"></i> الأنشطة التنفيذية
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="execActivitiesAccordion">
                            @foreach($projectRequest->executiveActivities as $actIndex => $activity)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="execHeading{{ $actIndex }}">
                                        <button class="accordion-button {{ $actIndex !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#execCollapse{{ $actIndex }}" aria-expanded="{{ $actIndex === 0 ? 'true' : 'false' }}">
                                            <div class="d-flex align-items-center w-100 justify-content-between pe-3">
                                                <div>
                                                    <span class="badge bg-success bg-opacity-75 rounded-pill me-2">{{ $loop->iteration }}</span>
                                                    <span class="fw-bold text-dark">{{ $activity->name ?? 'نشاط تنفيذي' }}</span>
                                                </div>
                                                <span class="badge bg-light text-dark border">الوزن: {{ $activity->weight ?? '0' }}%</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="execCollapse{{ $actIndex }}" class="accordion-collapse collapse {{ $actIndex === 0 ? 'show' : '' }}" aria-labelledby="execHeading{{ $actIndex }}" data-bs-parent="#execActivitiesAccordion">
                                        <div class="accordion-body bg-light">
                                            <!-- Activity Meta -->
                                            <div class="row g-2 mb-3 small text-muted">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-box-open me-2"></i> المخرج: {{ optional($activity->resultOutput)->output ?? (is_string($activity->output) ? $activity->output : null) ?? '-' }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-exclamation-circle me-2"></i> الخطر: {{ optional($activity->projectRisk)->risk ?? (is_string($activity->risk) ? $activity->risk : null) ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Executive Actions and Costs Premium -->
                                            @if($activity->actions && $activity->actions->count() > 0)
                                                <div class="table-responsive mt-3">
                                                    <table class="elegant-table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>اسم الإجراء التنفيذي</th>
                                                                <th width="80" class="text-center">الأهمية</th>
                                                                <th>وسائل التحقق</th>
                                                                <th width="100" class="text-center">المدة</th>
                                                                <th width="140" class="text-end">ميزانية الإجراء</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($activity->actions as $action)
                                                            <tr class="floating-row">
                                                                <td class="fw-bold text-slate-700 ps-4">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <i class="fas fa-play x-small text-indigo-300"></i>
                                                                        {{ $action->action_name ?? '-' }}
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge-premium badge-premium-blue">{{ $action->weight ?? '-' }}%</span>
                                                                </td>
                                                                <td class="small text-slate-500">{{ $action->verification_means ?? '-' }}</td>
                                                                <td class="text-center small text-slate-600 font-monospace">{{ $action->duration_days ?? '-' }} ي</td>
                                                                <td class="text-end fw-bold text-emerald-600 font-monospace pe-3">
                                                                    {{ $action->costs && $action->costs->sum('total') ? number_format($action->costs->sum('total'), 2) : '0.00' }}
                                                                </td>
                                                            </tr>
                                                            <!-- Premium Nested Costs -->
                                                            @if($action->costs && $action->costs->count() > 0)
                                                                <tr>
                                                                    <td colspan="5" class="py-0 px-4">
                                                                        <div class="premium-nested-container">
                                                                            @foreach($action->costs as $cost)
                                                                            <div class="premium-nested-item">
                                                                                <div class="d-flex align-items-center gap-3">
                                                                                    <div class="text-indigo-400 opacity-50"><i class="fas fa-coins x-small"></i></div>
                                                                                    <span class="fw-medium text-slate-600">{{ $cost->financialItem->name ?? 'بند' }}</span>
                                                                                    <span class="badge border text-slate-400 rounded-pill x-small px-2">{{ $cost->unit->unit_name ?? '-' }}</span>
                                                                                </div>
                                                                                <div class="d-flex align-items-center gap-4">
                                                                                    <span class="text-slate-400 x-small font-monospace">{{ number_format($cost->amount ?? 0, 2) }} × {{ $cost->quantity ?? 0 }}</span>
                                                                                    <span class="fw-bold text-indigo-600 font-monospace">{{ number_format($cost->total ?? 0, 2) }}</span>
                                                                                </div>
                                                                            </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-muted small fst-italic text-center py-2">لا توجد إجراءات مسجلة</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                     <!-- Financial Summary Footer -->
                    @if($projectRequest->executiveFinancialSummaries && $projectRequest->executiveFinancialSummaries->count() > 0)
                    <div class="card-footer bg-light p-3">
                        <h6 class="fw-bold text-dark mb-2 small text-uppercase">ملخص مالي (تنفيذي)</h6>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($projectRequest->executiveFinancialSummaries as $summary)
                            <div class="badge bg-white text-dark border p-2 shadow-sm rounded-3 d-flex align-items-center">
                                <span class="text-muted me-2">{{ $summary->item ?? '-' }}:</span>
                                <span class="fw-bold font-monospace text-primary">{{ number_format($summary->amount ?? 0, 2) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- 4. Financing and Costs -->
                @if($projectRequest->financings && $projectRequest->financings->count() > 0)
                <div class="modern-table-card animate-up delay-3">
                    <div class="glass-header d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h5 class="mb-0 fw-bold text-dark">مصادر التمويل والتكاليف المتوقعة</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">المصدر</th>
                                    <th>الجهة</th>
                                    <th class="text-center">النوع</th>
                                    <th class="text-end">المبلغ</th>
                                    <th class="text-center">النسبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projectRequest->financings as $financing)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ optional($financing->fundingSource)->name ?? '-' }}</td>
                                    <td>{{ optional($financing->entity)->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border">{{ optional($financing->financingType)->name ?? '-' }}</span>
                                    </td>
                                    <td class="text-end font-monospace text-success fw-bold">{{ number_format($financing->financing_amount ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; width: 60px; border-radius: 10px; background: #edf2f7;">
                                                <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $financing->financing_percentage ?? 0 }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ number_format($financing->financing_percentage ?? 0, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Total Cost Summary -->
                     @if($projectRequest->cost)
                    <div class="card-footer bg-light p-4">
                        <div class="row g-4 text-center">
                            <div class="col-md-4">
                                <div class="text-muted small mb-1">تكاليف التحضير</div>
                                <div class="h5 mb-0 fw-bold">{{ number_format($projectRequest->cost->preparatory_cost ?? 0, 2) }}</div>
                            </div>
                            <div class="col-md-4 border-start border-end">
                                <div class="text-muted small mb-1">تكاليف التنفيذ</div>
                                <div class="h5 mb-0 fw-bold">{{ number_format($projectRequest->cost->execution_cost ?? 0, 2) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-primary small mb-1 fw-bold">إجمالي التكاليف</div>
                                <div class="h4 mb-0 fw-bold text-primary">{{ number_format($projectRequest->cost->total_cost ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>


            <!-- Step 3: المراجعة والموافقة / مرحلة التنفيذ -->

                @if(isset($projectRequest->project) && $projectRequest->project)
                    @php
                        $hasPendingApprovals = $projectRequest->project->projectApprovals()
                            ->where('status', 'pending')
                            ->exists();
                        
                        // Check if any approval is under financial/technical review
                        $hasFinancialReview = $projectRequest->project->projectApprovals()
                            ->where('status', 'financial_review')
                            ->exists();
                    @endphp
                @endif
                <!-- Step 3: المراجعة والاعتماد -->
            <div class="step-content" id="step-3">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-clipboard-check me-2"></i> المراجعة والاعتماد
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4 animate-fade-in">
                            <div class="d-flex align-items-start">
                                <div class="bg-info text-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; flex-shrink: 0;">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="alert-heading fw-bold mb-2">حالة طلب المشروع</h4>
                                    <p class="mb-3">هذا الطلب حالياً في وضع: <strong>{{ $projectRequest->status_label }}</strong>.</p>
                                    
                                    @if($projectRequest->status === 'draft')
                                        <p>يرجى إرسال الطلب للمراجعة والاعتماد بعد التأكد من كافة البيانات.</p>
                                    @elseif($projectRequest->status === 'pending_approval' || $projectRequest->status === 'submitted')
                                        <p>الطلب بانتظار مراجعة المسؤولين للموافقة عليه وتحويله إلى مشروع رسمي.</p>
                                    @elseif($projectRequest->status === 'approved')
                                        <p class="text-success fw-bold">تمت الموافقة على الطلب! يمكنك الآن تحويله إلى مشروع رسمي من خلال الزر في أعلى الصفحة.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Approval Form Integration -->
                        @include('project-requests.partials.approval-form')

                        <!-- Activity History -->
                        <div class="mt-5">
                            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                <i class="fas fa-history text-secondary me-2"></i> سجل أنشطة الطلب
                            </h5>
                            <div class="bg-light rounded-3 p-3">
                                @include('project-requests.partials.activity-history', ['request' => $projectRequest])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="wizard-navigation d-flex gap-2 justify-content-between mt-4">
                <button type="button" class="btn btn-secondary btn-lg btn-navigation btn-prev px-4" id="prev-btn" style="display: none;">
                    <i class="fas fa-arrow-right me-2"></i> السابق
                </button>
                
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-primary btn-lg btn-navigation btn-next px-4" id="next-btn">
                        التالي <i class="fas fa-arrow-left ms-2"></i>
                    </button>
                    
                    @can('execution.view')
                    @if($projectRequest->project_id)
                        <a href="{{ route('projects.execution', $projectRequest->project_id) }}" class="btn btn-success btn-lg btn-navigation px-4" id="execute-btn" style="display: none;">
                            <i class="fas fa-play me-2"></i> تنفيذ المشروع
                        </a>
                        <a href="{{ route('projects.schedule', $projectRequest->project_id) }}" class="btn btn-info btn-lg btn-navigation text-white px-4" id="schedule-btn" style="display: none;">
                            <i class="fas fa-calendar-alt me-2"></i> الجدول الزمني
                        </a>
                    @endif
                    @endcan
                </div>
            </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Project Request View script loaded');
        
        const steps = document.querySelectorAll('.wizard-step');
        const stepContents = document.querySelectorAll('.step-content');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const transferForm = document.getElementById('transferForm');
        
        let currentStep = 1;
        const totalSteps = 3;

        function updateWizard(shouldScroll = true) {
            // Update steps classes
            steps.forEach((step, index) => {
                const stepNumber = parseInt(step.dataset.step);
                step.classList.remove('active', 'completed');
                
                if (stepNumber < currentStep) {
                    step.classList.add('completed');
                } else if (stepNumber === currentStep) {
                    step.classList.add('active');
                }
            });

            // Update content visibility
            stepContents.forEach(content => {
                content.classList.remove('active');
            });
            
            const currentContent = document.getElementById(`step-${currentStep}`);
            if (currentContent) {
                currentContent.classList.add('active');
            }

            // Update navigation buttons
            if (currentStep === 1) {
                if(prevBtn) prevBtn.style.display = 'none';
                if(nextBtn) nextBtn.style.display = 'inline-flex';
            } else if (currentStep === totalSteps) {
                if(prevBtn) prevBtn.style.display = 'inline-flex';
                if(nextBtn) nextBtn.style.display = 'none';
            } else {
                if(prevBtn) prevBtn.style.display = 'inline-flex';
                if(nextBtn) nextBtn.style.display = 'inline-flex';
            }

            if (shouldScroll) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Event Listeners
        if(nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateWizard();
                }
            });
        }

        if(prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    updateWizard();
                }
            });
        }

        steps.forEach(step => {
            step.addEventListener('click', function() {
                currentStep = parseInt(this.dataset.step);
                updateWizard();
            });
        });

        // Deep linking
        const urlParams = new URLSearchParams(window.location.search);
        const stepParam = urlParams.get('step');
        if (stepParam && !isNaN(stepParam) && stepParam >= 1 && stepParam <= totalSteps) {
            currentStep = parseInt(stepParam);
        }

        updateWizard(false);
    });
</script>
@endsection
