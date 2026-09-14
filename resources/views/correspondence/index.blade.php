@extends('layouts.app')

@section('styles')
<style>
    /* ─── إعادة ضبط العرض الكامل ─── */
    .correspondence-index {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    .correspondence-index .container,
    .correspondence-index .container-fluid {
        max-width: 100% !important;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    /* ─── تصغير الخطوط العامة ─── */
    .correspondence-index,
    .correspondence-index .form-control,
    .correspondence-index .form-select,
    .correspondence-index .btn,
    .correspondence-index .dropdown-item,
    .correspondence-index .badge,
    .correspondence-index .small {
        font-size: 0.72rem !important;
    }
    .correspondence-index h6 { font-size: 0.8rem !important; }
    .correspondence-index .form-label {
        font-size: 0.65rem !important;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }

    /* ─── كروت الإحصائيات المصغرة ─── */
    .kpi-card-artistic {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card-artistic:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .kpi-card-artistic .kpi-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 0.1rem;
    }
    .kpi-card-artistic .kpi-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .kpi-card-artistic .kpi-icon-wrap {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ─── الفلاتر ─── */
    .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.6rem;
        margin-bottom: 0.5rem;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        font-size: 0.7rem !important;
        padding: 0.3rem 0.5rem;
        height: 30px;
        border-radius: 6px;
    }
    .filter-card .row.g-2 { --bs-gutter-y: 0.35rem; }

    /* ─── التبويبات الفنية ─── */
    .nav-tabs-artistic {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .nav-tabs-artistic .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #64748b;
        font-size: 0.72rem !important;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    .nav-tabs-artistic .nav-link:hover {
        color: #3b82f6;
        border-bottom-color: #93c5fd;
    }
    .nav-tabs-artistic .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: transparent;
    }

    /* ─── الجدول بعرض الشاشة ─── */
    .corr-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .corr-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .corr-table thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        color: #334155;
        font-weight: 700;
        font-size: 0.68rem;
        padding: 0.4rem 0.3rem;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
        vertical-align: middle;
    }
    .corr-table tbody td {
        padding: 0.35rem 0.3rem;
        font-size: 0.7rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .corr-table tbody tr:hover {
        background: #f8fafc;
    }

    /* ─── منع التفاف النص والتمرير الأفقي ─── */
    .no-wrap {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .text-truncate-custom {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ─── البادجات الفنية الصغيرة ─── */
    .badge-artistic {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem 0.5rem;
        border-radius: 50rem;
        font-size: 0.65rem !important;
        font-weight: 600;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1.2;
    }
    .badge-artistic.badge-role {
        background: #ede9fe;
        color: #5b21b6;
        border-color: #c4b5fd;
    }
    .badge-artistic.badge-active {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .badge-artistic.badge-disabled {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .badge-artistic.badge-warning {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .badge-artistic.badge-info {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #7dd3fc;
    }

    /* ─── أزرار الإجراءات الصغيرة الفنية ─── */
    .btn-action-artistic {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.65rem;
        padding: 0;
        vertical-align: middle;
        text-decoration: none;
    }
    .btn-action-artistic:hover {
        transform: translateY(-1px);
        filter: brightness(1.1);
        color: white;
    }
    .btn-action-artistic.btn-view { background: #3b82f6; color: white; }
    .btn-action-artistic.btn-log { background: #8b5cf6; color: white; }
    .btn-action-artistic.btn-edit { background: #f59e0b; color: white; }
    .btn-action-artistic.btn-enable { background: #10b981; color: white; }
    .btn-action-artistic.btn-delete { background: #ef4444; color: white; }

    /* ─── أزرار الهيدر ─── */
    .header-btn {
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-weight: 600;
        font-size: 0.72rem !important;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: none;
        white-space: nowrap;
        text-decoration: none;
    }
    .header-btn.btn-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .header-btn.btn-primary-gradient:hover {
        color: white;
        filter: brightness(1.08);
    }
    .header-btn.btn-outline-gradient {
        background: white;
        border: 1px solid #e2e8f0;
        color: #475569;
    }
    .header-btn.btn-outline-gradient:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    /* ─── الحالة الفارغة ─── */
    .empty-state { padding: 2rem 1rem; text-align: center; }
    .empty-state .empty-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    /* ─── أحجام الأعمدة المحددة ─── */
    .corr-table th:nth-child(1), .corr-table td:nth-child(1) { width: 35px; }
    .corr-table th:nth-child(2), .corr-table td:nth-child(2) { width: 95px; }
    .corr-table th:nth-child(3), .corr-table td:nth-child(3) { width: auto; min-width: 140px; }
    .corr-table th:nth-child(4), .corr-table td:nth-child(4) { width: 110px; }
    .corr-table th:nth-child(5), .corr-table td:nth-child(5) { width: 100px; }
    .corr-table th:nth-child(6), .corr-table td:nth-child(6) { width: 80px; }
    .corr-table th:nth-child(7), .corr-table td:nth-child(7) { width: 75px; }
    .corr-table th:nth-child(8), .corr-table td:nth-child(8) { width: 65px; }
    .corr-table th:nth-child(9), .corr-table td:nth-child(9) { width: 145px; }

    /* ─── التجاوب مع الشاشات ─── */
    @media (max-width: 1200px) {
        .hide-xl { display: none !important; }
        .corr-table th:nth-child(5), .corr-table td:nth-child(5) { display: none; }
    }
    @media (max-width: 992px) {
        .hide-lg { display: none !important; }
        .corr-table th:nth-child(4), .corr-table td:nth-child(4) { display: none; }
    }
    @media (max-width: 768px) {
        .corr-table th:nth-child(8), .corr-table td:nth-child(8) { display: none; }
    }

    /* ─── منع التمرير الأفقي على مستوى الصفحة ─── */
    html, body {
        overflow-x: hidden;
        max-width: 100vw;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
    }
</style>
@endsection

@section('content')
<div class="correspondence-index">
    <x-index-page title="لوحة متابعة المراسلات والإحالات" icon="mail">
        <x-slot name="headerActions">
            @can('create', App\Models\Correspondence::class)
                <a href="{{ route('correspondence.create') }}" class="header-btn btn-primary-gradient auth-perm-correspondence-create">
                    <x-icon name="plus" size="12" /> مراسلة جديدة
                </a>
            @endcan
        </x-slot>

        {{-- بطاقات الإحصائيات الفنية السريعة --}}
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-2 col-lg-2">
                <div class="kpi-card-artistic">
                    <div>
                        <div class="kpi-label">المراسلات</div>
                        <div class="kpi-value text-primary">{{ number_format($statistics['total_correspondences'] ?? 0) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-primary bg-opacity-10 text-primary">
                        <x-icon name="envelope" size="16" />
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 col-lg-2">
                <div class="kpi-card-artistic">
                    <div>
                        <div class="kpi-label">المشاريع</div>
                        <div class="kpi-value text-info">{{ number_format($statistics['total_projects'] ?? 0) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-info bg-opacity-10 text-info">
                        <x-icon name="project-diagram" size="16" />
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 col-lg-2">
                <div class="kpi-card-artistic">
                    <div>
                        <div class="kpi-label">قيد الانتظار</div>
                        <div class="kpi-value text-warning">{{ number_format($statistics['pending_referrals'] ?? 0) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-warning bg-opacity-10 text-warning">
                        <x-icon name="share-square" size="16" />
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <div class="kpi-card-artistic">
                    <div>
                        <div class="kpi-label">إحالات مكتملة</div>
                        <div class="kpi-value text-success">{{ number_format($statistics['completed_referrals'] ?? 0) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-success bg-opacity-10 text-success">
                        <x-icon name="check-double" size="16" />
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3 col-lg-3">
                <div class="kpi-card-artistic">
                    <div>
                        <div class="kpi-label">إحالات متأخرة</div>
                        <div class="kpi-value text-danger">{{ number_format($statistics['overdue'] ?? 0) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-danger bg-opacity-10 text-danger">
                        <x-icon name="clock" size="16" />
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="filters">
            {{-- التبويبات الفنية --}}
            <ul class="nav nav-tabs-artistic" id="correspondenceTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ !request()->has('has_project') ? 'active' : '' }}" 
                       href="{{ route('correspondence.index', array_merge(request()->except('has_project', 'page'), ['has_project' => null])) }}">
                         <x-icon name="inbox" size="11" class="me-1" />الكل
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('has_project') == 'yes' ? 'active' : '' }}" 
                       href="{{ route('correspondence.index', array_merge(request()->except('has_project', 'page'), ['has_project' => 'yes'])) }}">
                         <x-icon name="project-diagram" size="11" class="me-1" />مرتبطة بمشاريع
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('has_project') == 'no' ? 'active' : '' }}" 
                       href="{{ route('correspondence.index', array_merge(request()->except('has_project', 'page'), ['has_project' => 'no'])) }}">
                         <x-icon name="file-alt" size="11" class="me-1" />مواضيع عامة
                    </a>
                </li>
            </ul>

            <form action="{{ route('correspondence.index') }}" method="GET" id="referralDashboardFilterForm">
                <input type="hidden" name="filter" value="{{ request('filter') }}">
                <input type="hidden" name="has_project" value="{{ request('has_project') }}">
                
                <div class="filter-card">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label for="search" class="form-label">بحث</label>
                            <input type="text" name="search" id="search" class="form-control auto-filter" 
                                   placeholder="الموضوع، الرقم، الجهة..." 
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="referred_to_department" class="form-label">القسم</label>
                            <select name="referred_to_department" id="referred_to_department" class="form-select auto-filter" onchange="this.form.submit()">
                                <option value="">جميع الأقسام</option>
                                @foreach($subDepartments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('referred_to_department') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="referral_status" class="form-label">حالة الإحالة</label>
                            <select name="referral_status" id="referral_status" class="form-select auto-filter" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="pending" {{ request('referral_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="accepted" {{ request('referral_status') == 'accepted' ? 'selected' : '' }}>مقبولة</option>
                                <option value="completed" {{ request('referral_status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="rejected" {{ request('referral_status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="date_from" class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" id="date_from" class="form-control auto-filter" 
                                   value="{{ request('date_from') }}" onchange="this.form.submit()">
                        </div>

                        <div class="col-6 col-md-2">
                            <label for="date_to" class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" id="date_to" class="form-control auto-filter" 
                                   value="{{ request('date_to') }}" onchange="this.form.submit()">
                        </div>

                        <div class="col-12 col-md-auto d-flex align-items-end">
                            <a href="{{ route('correspondence.index', request()->only(['has_project'])) }}" 
                               class="header-btn btn-outline-gradient" 
                               title="إعادة ضبط الفلاتر">
                                 <x-icon name="refresh-ccw" size="12" />
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot>

        <x-slot name="table">
            <div class="corr-table-wrapper">
                <table class="table corr-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center no-wrap">#</th>
                            <th class="text-center no-wrap">رقم المراسلة</th>
                            <th>الموضوع</th>
                            <th class="no-wrap hide-lg">من / إلى</th>
                            <th class="no-wrap hide-xl">المحال إليه</th>
                            <th class="text-center no-wrap">التاريخ</th>
                            <th class="text-center no-wrap">الحالة</th>
                            <th class="text-center no-wrap hide-md">الأولوية</th>
                            <th class="text-center no-wrap">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($correspondences as $correspondence)
                            <tr class="{{ $correspondence->is_overdue ? 'table-danger' : '' }}">
                                <td class="text-center no-wrap">
                                    <span class="text-muted fw-bold">{{ $loop->iteration + ($correspondences->currentPage() - 1) * $correspondences->perPage() }}</span>
                                </td>
                                <td class="text-center no-wrap">
                                    <span class="badge-artistic badge-role">{{ $correspondence->correspondence_number }}</span>
                                    @if($correspondence->confidential)
                                        <span class="badge-artistic badge-disabled ms-1" style="padding: 0.1rem 0.3rem;">سري</span>
                                    @endif
                                </td>
                                <td>
                                     <div class="fw-bold text-dark text-truncate-custom d-block" title="{{ $correspondence->subject }}">
                                         {{ $correspondence->subject }}
                                     </div>
                                     @if($correspondence->project_id)
                                         <small class="text-primary d-block text-truncate-custom">
                                             <x-icon name="project-diagram" size="9" class="me-1" />
                                             {{ $correspondence->project->project_name }}
                                         </small>
                                     @endif
                                </td>
                                <td class="hide-lg">
                                    <div class="text-truncate-custom" style="font-size: 0.65rem;">
                                        <span class="text-success fw-bold">من:</span> {{ $correspondence->senderEntity?->name ?? 'غير محدد' }}
                                    </div>
                                    <div class="text-truncate-custom text-muted" style="font-size: 0.65rem;">
                                        <span class="text-primary fw-bold">إلى:</span> {{ $correspondence->recipientEntity?->name ?? 'غير محدد' }}
                                    </div>
                                </td>
                                <td class="hide-xl">
                                    @if($correspondence->latestReferral)
                                        <div class="text-truncate-custom" style="font-size: 0.68rem;">
                                            @if($correspondence->latestReferral->referred_to_type == 'person')
                                                <x-icon name="user" size="9" class="text-muted me-1" />
                                                <span>{{ $correspondence->latestReferral->referred_to_name ?? 'غير محدد' }}</span>
                                            @else
                                                <x-icon name="building" size="9" class="text-muted me-1" />
                                                <span>{{ $correspondence->latestReferral->referredToEntity?->name ?? 'غير محدد' }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.68rem;">لم يتم الإحالة</span>
                                    @endif
                                </td>
                                <td class="text-center no-wrap">
                                    <div class="d-flex flex-column align-items-center" style="line-height: 1.1;">
                                        <span class="text-dark">{{ $correspondence->created_at->format('Y-m-d') }}</span>
                                        <small class="text-muted" style="font-size: 0.6rem;">{{ $correspondence->days_since_creation }} يوم</small>
                                    </div>
                                </td>
                                <td class="text-center no-wrap">
                                    @if($correspondence->latestReferral)
                                        @php
                                            $statusClass = match($correspondence->latestReferral->referral_status) {
                                                'completed' => 'badge-active',
                                                'pending', 'accepted' => 'badge-role',
                                                'rejected' => 'badge-disabled',
                                                default => 'badge-info'
                                            };
                                        @endphp
                                        <span class="badge-artistic {{ $statusClass }}">
                                            {{ $correspondence->latestReferral->referral_status_label }}
                                        </span>
                                    @else
                                        <span class="badge-artistic badge-info">لا يوجد</span>
                                    @endif
                                </td>
                                <td class="text-center no-wrap hide-md">
                                    @php
                                        $priorityClass = match($correspondence->priority) {
                                            'urgent' => 'badge-disabled',
                                            'high' => 'badge-warning',
                                            'normal', 'low' => 'badge-active',
                                            default => 'badge-role'
                                        };
                                    @endphp
                                    <span class="badge-artistic {{ $priorityClass }}">
                                        {{ $correspondence->priority_label }}
                                    </span>
                                </td>
                                <td class="text-center no-wrap">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @can('view', $correspondence)
                                            <a href="{{ route('correspondence.show', $correspondence->id) }}" 
                                               class="btn-action-artistic btn-view auth-perm-correspondence-view" title="عرض التفاصيل">
                                                 <x-icon name="eye" size="10" />
                                            </a>
                                            
                                            <a href="{{ route('correspondence.show', $correspondence->id) }}#movement-table"
                                               class="btn-action-artistic btn-log btn-movement-log auth-perm-correspondence-view" 
                                               data-id="{{ $correspondence->id }}"
                                               data-number="{{ $correspondence->correspondence_number }}"
                                               title="حركة المراسلة">
                                                 <x-icon name="clock" size="10" />
                                            </a>

                                            @if($correspondence->latestReferral)
                                                <button type="button" 
                                                        class="btn-action-artistic btn-enable btn-update-referral" 
                                                        data-id="{{ $correspondence->latestReferral->id }}"
                                                        data-status="{{ $correspondence->latestReferral->referral_status }}"
                                                        data-notes="{{ $correspondence->latestReferral->referral_notes }}"
                                                        title="تحديث حالة الإحالة">
                                                     <x-icon name="check-circle" size="10" />
                                                </button>
                                            @endif
                                        @endcan
                                        
                                        @can('forward', $correspondence)
                                            @if($correspondence->status !== 'closed')
                                                <button type="button" 
                                                        class="btn-action-artistic btn-log auth-perm-correspondence-forward" 
                                                        title="توجيه داخلي"
                                                        onclick="openForwardModal({{ $correspondence->id }}, '{{ $correspondence->correspondence_number }}')">
                                                     <x-icon name="forward" size="10" />
                                                </button>
                                            @endif
                                        @endcan
                                        
                                        @can('update', $correspondence)
                                            <a href="{{ route('correspondence.edit', $correspondence->id) }}" 
                                               class="btn-action-artistic btn-edit auth-perm-correspondence-edit" title="تعديل">
                                                 <x-icon name="edit-2" size="10" />
                                            </a>
                                        @endcan
                                        
                                        @can('delete', $correspondence)
                                            <button type="button" 
                                                    class="btn-action-artistic btn-delete auth-perm-correspondence-delete" 
                                                    title="حذف"
                                                    onclick="confirmDelete({{ $correspondence->id }})">
                                                 <x-icon name="trash-2" size="10" />
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty 
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <x-icon name="inbox" size="24" class="text-muted" />
                                        </div>
                                        <h6 class="text-dark fw-bold mb-1">لا توجد مراسلات حالياً</h6>
                                        <p class="text-muted small mb-3">لم يتم العثور على أي مراسلات تطابق معايير البحث.</p>
                                        @can('create', App\Models\Correspondence::class)
                                            <a href="{{ route('correspondence.create') }}" class="header-btn btn-primary-gradient d-inline-flex">
                                                <x-icon name="plus" size="12" /> إنشاء مراسلة جديدة
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>

        <x-slot name="pagination">
            @if(method_exists($correspondences, 'hasPages') && $correspondences->hasPages())
                <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
                    <div class="text-muted small">
                        عرض <strong>{{ $correspondences->firstItem() ?? 0 }}</strong> إلى <strong>{{ $correspondences->lastItem() ?? 0 }}</strong>
                        من <strong>{{ $correspondences->total() }}</strong> مراسلة
                    </div>
                    <div>
                        {{ $correspondences->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </x-slot>
    </x-index-page>
</div>

<!-- Forward Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-start border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold" id="forwardModalLabel">توجيه المراسلة رقم (<span id="forward_number_display"></span>)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="forwardForm" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label required">توجيه إلى (الأقسام)</label>
                        <select name="referred_to_departments[]" class="select2-modal form-select" multiple="multiple" style="width: 100%;" required>
                            @foreach($subDepartments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">ملاحظات التوجيه</label>
                        <textarea name="referral_notes" rows="3" class="form-control form-control-sm" placeholder="اكتب ملاحظات أو توجيهات هنا..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-end bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="header-btn btn-primary-gradient px-4">حفظ وتوجيه</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Movement Log Modal -->
<div class="modal fade" id="movementLogModal" tabindex="-1" aria-labelledby="movementLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-start border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold">حركة المراسلة رقم (<span id="modalCorrNumber"></span>)</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-0" id="movementLogContent">
                <!-- Log content loaded dynamically via AJAX -->
            </div>
            <div class="modal-footer border-0 bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-danger" id="deleteModalLabel">تأكيد الحذف</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center py-4">
                <x-icon name="exclamation-circle" size="40" class="text-warning mb-3" />
                <p class="mb-1 fw-bold text-dark">هل أنت متأكد من حذف هذه المراسلة؟</p>
                <p class="text-danger small mb-0">لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" action="" onsubmit="return confirmAction(this, 'هل أنت متأكد من عملية الحذف؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Referral Status Update Modal -->
<div class="modal fade" id="updateReferralModal" tabindex="-1" aria-labelledby="updateReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-start border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold" id="updateReferralModalLabel">تحديث حالة الإحالة</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateReferralForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label required">حالة الإحالة</label>
                        <select name="referral_status" id="referral_status_select" class="form-select form-select-sm" required>
                            <option value="pending">قيد الانتظار</option>
                            <option value="accepted">مقبولة</option>
                            <option value="completed">مكتملة</option>
                            <option value="rejected">مرفوضة</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="referral_notes" class="form-label">ملاحظات التحديث</label>
                        <textarea name="referral_notes" id="referral_notes_textarea" rows="3" class="form-control form-control-sm" placeholder="اكتب أي ملاحظات تتعلق بتغيير الحالة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-end bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="header-btn btn-primary-gradient px-4">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/ar.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('.select2-modal').length) {
            $('.select2-modal').select2({
                dropdownParent: $('#forwardModal'),
                placeholder: 'اختر الأقسام...',
                language: "ar",
                dir: "rtl"
            });
        }
    });

    $('.btn-movement-log').on('click', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const number = $(this).data('number');
        
        $('#modalCorrNumber').text(number);
        $('#movementLogContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p class="mt-2 text-muted">جاري تحميل سجل الحركة...</p>
            </div>
        `);
        $('#movementLogModal').modal('show');

        $.ajax({
            url: `/correspondence/${id}/movement-log`,
            method: 'GET',
            success: function(data) {
                if (!data || data.length === 0) {
                    $('#movementLogContent').html(`
                        <div class="alert alert-info m-3 text-center">
                            لا يوجد سجل حركة لهذه المراسلة بعد.
                        </div>
                    `);
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.7rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 130px;">التاريخ</th>
                                    <th>الجهة / المستخدم</th>
                                    <th>العملية / الحالة</th>
                                    <th>التفاصيل والملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.forEach(move => {
                    const actionDate = new Date(move.action_date);
                    const formattedDate = actionDate.toLocaleDateString('ar-YE', { year: 'numeric', month: '2-digit', day: '2-digit' });
                    const formattedTime = actionDate.toLocaleTimeString('ar-YE', { hour: '2-digit', minute: '2-digit' });

                    html += `
                        <tr>
                            <td>
                                <strong>${formattedDate}</strong><br>
                                <small class="text-muted">${formattedTime}</small>
                            </td>
                            <td>
                                <strong>${move.from_entity || 'غير محدد'}</strong><br>
                                <small class="text-muted"><i class="fas fa-user me-1"></i>${move.user_name || 'النظام'}</small>
                            </td>
                            <td>
                                <span class="badge bg-${move.action_color || 'secondary'} px-2 py-1" style="font-size: 0.65rem;">
                                    <i class="${move.action_icon} me-1"></i>${move.action_label}
                                </span>
                            </td>
                            <td style="max-width: 350px; white-space: normal;">
                                <div class="mb-1">${move.action_description}</div>
                                ${move.action_details && (Object.keys(move.action_details).length > 0) ? `<small class="text-muted d-block border-top mt-1 pt-1 fst-italic text-truncate">${JSON.stringify(move.action_details)}</small>` : ''}
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                $('#movementLogContent').html(html);
            },
            error: function(xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'فشل في تحميل سجل الحركة';
                $('#movementLogContent').html(`
                    <div class="alert alert-danger m-3 text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>${error}
                    </div>
                `);
            }
        });
    });

    function openForwardModal(id, number) {
        $('#forward_number_display').text(number);
        $('#forwardForm').attr('action', `/correspondence/${id}/forward`);
        $('#forwardModal').modal('show');
    }

    function confirmDelete(correspondenceId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/correspondence/${correspondenceId}`;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Referral Update Modal Logic
        $('.btn-update-referral').on('click', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            const notes = $(this).data('notes');

            $('#referral_status_select').val(status);
            $('#referral_notes_textarea').val(notes);
            $('#updateReferralForm').attr('action', `/correspondence/referrals/${id}/update-status`);
            
            const modal = new bootstrap.Modal(document.getElementById('updateReferralModal'));
            modal.show();
        });
    });
</script>
@endpush