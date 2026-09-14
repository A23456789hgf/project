@extends('layouts.app')

@section('title', 'وحدة الاستشارات والإحالات')

@section('styles')
<style>
    .consultation-center-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.15);
    }
    
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.25s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-3px);
    }
    .stat-card {
        border-radius: 1rem;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .stat-card.active-tab-card {
        border-color: #3b82f6;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.2);
    }
    
    .nav-tabs-custom {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        border-radius: 0.5rem 0.5rem 0 0;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #1e293b;
        background-color: #f8fafc;
    }
    .nav-tabs-custom .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background-color: #eff6ff;
    }

    /* Consultation Record Cards */
    .consultation-record-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
        background: #ffffff;
        position: relative;
    }
    .consultation-record-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 12px 28px -5px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .consultation-record-card.card-pending {
        border-top: 4px solid #f59e0b;
    }
    .consultation-record-card.card-responded {
        border-top: 4px solid #10b981;
    }
    .consultation-record-card.card-returned {
        border-top: 4px solid #64748b;
    }
    .consultation-record-card.card-closed {
        border-top: 4px solid #1e293b;
    }

    .hover-primary:hover {
        color: #2563eb !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    
    {{-- Top Banner Header --}}
    <div class="consultation-center-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 text-white-50 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50 text-decoration-none"><i class="fas fa-home me-1"></i> الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-white-50 text-decoration-none">المشاريع</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">الاستشارات والإحالات</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-comments text-info"></i> الاستشارات والإحالات
                </h3>
                <p class="text-white-50 mb-0 small">
                    منصة متخصصة ومستقلة لإدارة طلبات الإفادة والاستشارات الفنية والتنسيقية بين الجهات دون التأثير على مسار الاعتماد الرئيسي
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('approvals.index') }}" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-semibold">
                    <i class="fas fa-clipboard-check me-1"></i> مركز المراجعة والاعتمادات
                </a>
                <a href="{{ route('projects.index') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-semibold shadow-sm">
                    <i class="fas fa-list me-1"></i> قائمة المشاريع
                </a>
            </div>
        </div>
    </div>

    {{-- Notice Banner Directing to Projects Detail / Archive --}}
    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 p-3 px-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-info d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                <i class="fas fa-info-circle fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">وحدة الاستشارات والإحالات مخصصة للمهام التي تتطلب إفادتك أو متابعتك الفنية</h6>
                <p class="mb-0 text-muted small">تفاصيل المشاريع وسجل الاستشارات التاريخي الكامل متاح دائماً داخل <a href="{{ route('projects.index') }}" class="fw-bold text-decoration-underline text-info">صفحة تفاصيل المشروع</a>.</p>
            </div>
        </div>
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
            <i class="fas fa-list me-1"></i> قائمة المشاريع
        </a>
    </div>

    {{-- Statistics Cards (5 Required Metrics) --}}
    <div class="row g-3 mb-4">
        {{-- Stat 1: Incoming --}}
        <div class="col-12 col-sm-6 col-xl">
            <a href="{{ route('consultations.index', array_merge(request()->except('tab', 'page'), ['tab' => 'incoming'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'incoming' ? 'active-tab-card bg-primary bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">الاستشارات الواردة</span>
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($incomingCount) }}</h3>
                            <small class="text-muted">الموجهة إلى جهتك</small>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-inbox fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Stat 2: Sent --}}
        <div class="col-12 col-sm-6 col-xl">
            <a href="{{ route('consultations.index', array_merge(request()->except('tab', 'page'), ['tab' => 'sent'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'sent' ? 'active-tab-card bg-info bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">الاستشارات الصادرة</span>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($sentCount) }}</h3>
                            <small class="text-muted">المرسلة من جهتك</small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-paper-plane fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Stat 3: Pending Response --}}
        <div class="col-12 col-sm-6 col-xl">
            <a href="{{ route('consultations.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ request('status') === 'pending' ? 'active-tab-card bg-warning bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">بانتظار الرد</span>
                            <h3 class="fw-bold text-warning mb-0">{{ number_format($pendingCount) }}</h3>
                            <small class="text-muted">قيد المراجعة الفنية</small>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-clock fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Stat 4: Responded --}}
        <div class="col-12 col-sm-6 col-xl">
            <a href="{{ route('consultations.index', array_merge(request()->except('status', 'page'), ['status' => 'responded'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ request('status') === 'responded' ? 'active-tab-card bg-success bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">تم الرد</span>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($respondedCount) }}</h3>
                            <small class="text-muted">تمت الإفادة الفنية</small>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-check-double fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Stat 5: Closed --}}
        <div class="col-12 col-sm-6 col-xl">
            <a href="{{ route('consultations.index', array_merge(request()->except('status', 'page'), ['status' => 'closed'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ request('status') === 'closed' ? 'active-tab-card bg-dark bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">المغلقة</span>
                            <h3 class="fw-bold text-dark mb-0">{{ number_format($closedCount) }}</h3>
                            <small class="text-muted">استشارات منتهية</small>
                        </div>
                        <div class="rounded-circle bg-dark bg-opacity-10 text-dark d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-lock fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabs Navigation (3 Required Tabs: Incoming, Sent, Completed) --}}
    <ul class="nav nav-tabs nav-tabs-custom mb-4 overflow-auto flex-nowrap text-nowrap">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'incoming' ? 'active' : '' }}" href="{{ route('consultations.index', array_merge(request()->except('tab', 'page'), ['tab' => 'incoming'])) }}">
                <i class="fas fa-inbox me-1"></i> الواردة
                <span class="badge rounded-pill {{ $activeTab === 'incoming' ? 'bg-primary' : 'bg-secondary' }} ms-1">
                    {{ $incomingCount }}
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'sent' ? 'active' : '' }}" href="{{ route('consultations.index', array_merge(request()->except('tab', 'page'), ['tab' => 'sent'])) }}">
                <i class="fas fa-paper-plane me-1"></i> الصادرة
                <span class="badge rounded-pill {{ $activeTab === 'sent' ? 'bg-info' : 'bg-secondary' }} ms-1">
                    {{ $sentCount }}
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'completed' ? 'active' : '' }}" href="{{ route('consultations.index', array_merge(request()->except('tab', 'page'), ['tab' => 'completed'])) }}">
                <i class="fas fa-check-circle me-1"></i> المكتملة
                <span class="badge rounded-pill {{ $activeTab === 'completed' ? 'bg-success' : 'bg-secondary' }} ms-1">
                    {{ $respondedCount + $closedCount }}
                </span>
            </a>
        </li>
    </ul>

    {{-- Filter and Search Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('consultations.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                
                {{-- Search --}}
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold small text-muted">البحث:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-start-0" placeholder="اسم المشروع أو الرقم أو الموضوع...">
                    </div>
                </div>

                {{-- Entity Filter --}}
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold small text-muted">الجهة المعنية:</label>
                    <select name="entity_id" class="form-select bg-light">
                        <option value="">-- جميع الجهات --</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-md-2">
                    <label class="form-label fw-bold small text-muted">الحالة:</label>
                    <select name="status" class="form-select bg-light">
                        <option value="">-- كافة الحالات --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>بانتظار الرد</option>
                        <option value="responded" {{ request('status') === 'responded' ? 'selected' : '' }}>تم الرد</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>تم الإرجاع</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>مغلقة</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold small text-muted">من تاريخ:</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control bg-light">
                </div>

                {{-- Date To --}}
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold small text-muted">إلى تاريخ:</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control bg-light">
                </div>

                {{-- Submit & Reset Buttons --}}
                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                    @if(request()->hasAny(['search', 'entity_id', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('consultations.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill px-3" title="إعادة ضبط الفلاتر">
                            <i class="fas fa-times me-1"></i> إلغاء التصفية
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Content Area: Cards View --}}
    <div class="row g-4">
        @forelse($referrals as $referral)
            @php
                $isPending = ($referral->status === 'pending');
                $isResponded = ($referral->status === 'responded');
                $isReturned = ($referral->status === 'returned');
                $isClosed = ($referral->status === 'closed');

                $cardClass = 'card-pending';
                if ($isResponded) {
                    $cardClass = 'card-responded';
                } elseif ($isReturned) {
                    $cardClass = 'card-returned';
                } elseif ($isClosed) {
                    $cardClass = 'card-closed';
                }

                // Compute wait duration
                $waitingDuration = '-';
                if ($referral->created_at) {
                    if ($referral->responded_at) {
                        $waitingDuration = $referral->created_at->diffForHumans($referral->responded_at, true);
                    } else {
                        $waitingDuration = $referral->created_at->diffForHumans(null, true);
                    }
                }
            @endphp
            <div class="col-12 col-lg-6 col-xl-4">
                <div class="card consultation-record-card h-100 shadow-sm d-flex flex-column {{ $cardClass }}">
                    <div class="card-body p-4 flex-grow-1">
                        
                        {{-- Card Header: Status Badge + Wait Duration --}}
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                @if($isPending)
                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                        <i class="fas fa-clock me-1"></i> بانتظار الرد
                                    </span>
                                @elseif($isResponded)
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fas fa-check-circle me-1"></i> تم الرد
                                    </span>
                                @elseif($isReturned)
                                    <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fas fa-undo me-1"></i> تم الإرجاع
                                    </span>
                                @elseif($isClosed)
                                    <span class="badge bg-dark px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fas fa-lock me-1"></i> مغلقة
                                    </span>
                                @endif
                            </div>

                            {{-- Waiting Duration Badge --}}
                            <div class="text-muted small fw-semibold">
                                <i class="far fa-hourglass me-1 text-secondary"></i> مدة الانتظار: <span class="text-dark fw-bold">{{ $waitingDuration }}</span>
                            </div>
                        </div>

                        {{-- Project Name & Code --}}
                        <h5 class="fw-bold text-dark mb-1">
                            <a href="{{ route('consultations.show', $referral) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $referral->project?->project_name ?? 'مشروع #'.$referral->project_id }}
                            </a>
                        </h5>
                        <div class="text-muted small mb-3">
                            <i class="fas fa-hashtag text-secondary me-1"></i>
                            <span>{{ $referral->project?->form_number ?: 'PRJ-'.$referral->project_id }}</span>
                        </div>

                        {{-- Metadata Container --}}
                        <div class="bg-light rounded-3 p-3 border mb-3 small">
                            {{-- Referring Entity --}}
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted"><i class="fas fa-share text-secondary me-1"></i> الجهة المحيلة:</span>
                                <strong class="text-dark">{{ $referral->referringEntity?->name ?? 'غير محدد' }}</strong>
                            </div>

                            {{-- Consulted Entity --}}
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted"><i class="fas fa-building text-primary me-1"></i> الجهة المستشارة:</span>
                                <span class="text-primary fw-semibold">{{ $referral->referredEntity?->name ?? 'غير محدد' }}</span>
                            </div>

                            {{-- Referral Date --}}
                            <div class="d-flex align-items-center justify-content-between border-top pt-2 mt-2">
                                <span class="text-muted"><i class="far fa-calendar-alt text-secondary me-1"></i> تاريخ الإحالة:</span>
                                <span dir="ltr" class="text-secondary">{{ $referral->created_at?->format('Y-m-d h:i A') ?? '-' }}</span>
                            </div>

                            {{-- Response Date if responded --}}
                            @if($referral->responded_at)
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span class="text-muted"><i class="fas fa-check text-success me-1"></i> تاريخ الرد:</span>
                                    <span dir="ltr" class="text-success fw-semibold">{{ $referral->responded_at->format('Y-m-d h:i A') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Subject / Referral Text preview --}}
                        @if(!empty($referral->referral_text))
                            <div class="p-2.5 bg-light rounded-3 border small text-muted mb-2">
                                <div class="fw-semibold text-secondary mb-1"><i class="fas fa-comment-dots text-primary opacity-75 me-1"></i> نص الاستشارة:</div>
                                <p class="mb-0 text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                    {{ $referral->referral_text }}
                                </p>
                            </div>
                        @endif

                    </div>

                    {{-- Card Footer with Actions --}}
                    <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i> {{ $referral->updated_at?->diffForHumans() ?? $referral->created_at?->diffForHumans() }}
                        </small>
                        
                        <div class="d-flex align-items-center gap-2">
                            {{-- View Consultation Detail Button --}}
                            <a href="{{ route('consultations.show', $referral) }}" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm">
                                <i class="fas fa-eye me-1"></i> عرض الاستشارة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px;">
                            <i class="fas fa-comments text-muted fs-2"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">لا توجد استشارات أو إحالات في هذا التبويب</h5>
                    <p class="text-muted small mb-0">لم يتم العثور على أي استشارات أو إحالات مطابقة للمعايير المحددة أو التصفية.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        @if(isset($referrals) && $referrals instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $referrals->links() }}
        @endif
    </div>

</div>
@endsection
