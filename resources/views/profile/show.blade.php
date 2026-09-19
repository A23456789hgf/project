@extends('layouts.app')

@section('title', 'الملف الشخصي')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="page-title">الملف الشخصي</h1>
        <p class="page-subtitle">عرض المعلومات الشخصية وتغيير كلمة المرور</p>
    </div>

    <div class="row">
        <!-- معلومات المستخدم -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-primary">
                        <i class="fas fa-user-circle me-2"></i> المعلومات الشخصية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">الاسم الكامل</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">{{ $user->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">اسم المستخدم</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">{{ $user->username }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">رقم الهاتف</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">{{ $user->phone ?? 'غير متوفر' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">الدور / الصلاحية</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">
                            <span class="badge bg-primary px-3">{{ $user->role->name ?? 'غير محدد' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">الجهة / القسم</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">{{ $user->entity->name ?? 'غير محدد' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">نوع المستخدم</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">
                            {{ $user->organization_type === 'external' ? 'خارجي (جهة خارجية)' : 'داخلي (الوزارة / الجهات التابعة)' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">مسؤولية الموافقات</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">
                            {{ $user->responsibility?->label() ?? 'بدون مسؤولية' }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">النطاق الإداري</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">
                            {{ $user->internalEntity?->name ?? 'غير محدد' }}
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-muted small mb-1">النطاق الجغرافي</label>
                        <div class="form-control-plaintext border-bottom pb-2 fw-bold">
                            @php
                                $totalGovs = \App\Models\Governorate::where('is_active', 1)->count();
                            @endphp
                            @if($user->geographicScopes && $user->geographicScopes->count() > 0)
                                @if($user->geographicScopes->contains('governorate_id', 'all') || $user->geographicScopes->count() >= 15 || $user->geographicScopes->count() == $totalGovs)
                                    <span>كافة المحافظات</span>
                                @else
                                    <ul class="list-unstyled mb-0">
                                    @foreach($user->geographicScopes as $scope)
                                        <li>
                                            {{ $scope->governorate?->name ?? 'غير محدد' }}
                                            @if($scope->directorate_id && $scope->directorate_id !== 'all')
                                                - {{ $scope->directorate?->name }}
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                @endif
                            @else
                                <span class="text-muted">غير محدد</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- تغيير كلمة المرور -->
        @can('profile.edit')
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-primary">
                        <i class="fas fa-key me-2"></i> تغيير كلمة المرور
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> تحديث كلمة المرور
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- التوقيع الإلكتروني -->
    <div class="row">
        @can('profile.edit')
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-primary mb-0">
                        <i class="fas fa-signature me-2"></i> التوقيع الإلكتروني
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.openSignatureModal()">
                        <i class="fas fa-pen me-1"></i> {{ $user->hasSignature() ? 'تحديث التوقيع' : 'إعداد التوقيع' }}
                    </button>
                </div>
                <div class="card-body text-center">
                    @if($user->hasSignature())
                        <div class="signature-display p-3 border rounded bg-light d-inline-block">
                            <img id="current-signature-img" src="{{ asset('storage/' . $user->signature_path) }}" alt="التوقيع الإلكتروني" style="max-height: 150px; display: block;">
                        </div>
                    @else
                        <div class="signature-display p-3 border rounded bg-light d-inline-block" style="min-width: 200px;">
                            <img id="current-signature-img" src="" alt="التوقيع الإلكتروني" style="max-height: 150px; display: none;">
                            <div class="text-muted p-3" id="no-signature-placeholder">
                                <i class="fas fa-file-signature fa-3x mb-2 opacity-25"></i>
                                <p class="mb-0">لم تقم بإعداد التوقيع الإلكتروني بعد.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- صف المهام والتكليفات -->
    @can('profile.assignments.view')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-primary mb-0">
                        <i class="fas fa-tasks me-2"></i> مهامي وتكليفاتي المباشرة
                    </h5>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" onclick="loadProfileAssignments()">
                        <i class="fas fa-sync-alt me-1"></i> تحديث
                    </button>
                </div>
                <div class="card-body">
                    <!-- Dashboard Alerts Container inside Profile -->
                    <div id="profileDashboardAlerts"></div>

                    <!-- Filters -->
                    <div class="mb-4">
                        <div class="nav nav-pills bg-light p-1 rounded-3 d-inline-flex gap-2" id="profileAssignmentFilters" role="tablist">
                            <button class="nav-link active py-1 px-3 border-0 rounded-2 position-relative" data-filter="all" style="font-size: 0.85rem;">
                                الكل <span class="badge bg-secondary ms-1 font-num" id="profile-count-all">0</span>
                            </button>
                            <button class="nav-link py-1 px-3 border-0 rounded-2 text-danger position-relative" data-filter="overdue" style="font-size: 0.85rem;">
                                متأخرة <span class="badge bg-danger text-white ms-1 font-num" id="profile-count-overdue">0</span>
                            </button>
                            <button class="nav-link py-1 px-3 border-0 rounded-2 text-warning position-relative" data-filter="nearing" style="font-size: 0.85rem;">
                                وشيكة <span class="badge bg-warning text-dark ms-1 font-num" id="profile-count-nearing">0</span>
                            </button>
                            <button class="nav-link py-1 px-3 border-0 rounded-2 text-primary position-relative" data-filter="current" style="font-size: 0.85rem;">
                                جارية <span class="badge bg-primary text-white ms-1 font-num" id="profile-count-current">0</span>
                            </button>
                        </div>
                    </div>

                    <!-- Assignments Grid -->
                    <div class="row g-3" id="profileAssignmentsList">
                        <div class="text-center py-5 text-muted col-12" id="profileAssignmentsEmpty">
                            <i class="fas fa-tasks fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">لا توجد تكليفات حالياً</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

@push('scripts')
@can('profile.assignments.view')
<script>
    let profileAssignments = [];
    let profileCurrentFilter = 'all';

    function loadProfileAssignments() {
        const container = $('#profileAssignmentsList');
        container.html('<div class="text-center py-5 col-12"><div class="spinner-border text-primary" role="status"></div></div>');
        
        $.ajax({
            url: '{{ route("assignments.my") }}',
            method: 'GET',
            success: function (data) {
                profileAssignments = data;
                updateProfileAssignmentCounts();
                renderProfileFilteredAssignments();
                renderProfileDashboardAlerts();
            },
            error: function () {
                container.html('<div class="text-center py-5 text-danger col-12"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><p>فشل تحميل التكليفات</p></div>');
            }
        });
    }

    function updateProfileAssignmentCounts() {
        const counts = { all: 0, overdue: 0, nearing: 0, current: 0 };
        profileAssignments.forEach(item => {
            counts.all++;
            if (item.category && counts[item.category] !== undefined) {
                counts[item.category]++;
            }
        });
        $('#profile-count-all').text(counts.all);
        $('#profile-count-overdue').text(counts.overdue);
        $('#profile-count-nearing').text(counts.nearing);
        $('#profile-count-current').text(counts.current);
    }

    function renderProfileFilteredAssignments() {
        const container = $('#profileAssignmentsList');
        const filtered = profileAssignments.filter(item => profileCurrentFilter === 'all' || item.category === profileCurrentFilter);
        
        if (!filtered.length) {
            container.html(`
                <div class="text-center py-5 text-muted col-12" id="profileAssignmentsEmpty">
                    <i class="fas fa-tasks fa-2x mb-2 opacity-25"></i>
                    <p class="mb-0">لا توجد تكليفات في هذا القسم حالياً</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        filtered.forEach(function (item) {
            let badgeCls = 'bg-primary-soft text-primary';
            let borderCls = 'border-primary';
            let timeText = 'بدون تاريخ استحقاق';
            let iconCls = 'fa-tag';
            
            if (item.days_remaining !== null) {
                if (item.days_remaining < 0) {
                    badgeCls = 'bg-danger-soft text-danger';
                    borderCls = 'border-danger';
                    iconCls = 'fa-exclamation-circle';
                    timeText = `متأخر بـ ${Math.abs(item.days_remaining)} يوم`;
                } else if (item.days_remaining === 0) {
                    badgeCls = 'bg-warning-soft text-warning';
                    borderCls = 'border-warning';
                    iconCls = 'fa-clock';
                    timeText = 'يستحق اليوم';
                } else if (item.days_remaining <= 3) {
                    badgeCls = 'bg-warning-soft text-warning';
                    borderCls = 'border-warning';
                    iconCls = 'fa-clock';
                    timeText = `متبقي ${item.days_remaining} يوم (وشيك)`;
                } else {
                    badgeCls = 'bg-success-soft text-success';
                    borderCls = 'border-success';
                    iconCls = 'fa-hourglass-half';
                    timeText = `متبقي ${item.days_remaining} يوم`;
                }
            }
            
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 border-0 border-start border-4 ${borderCls} shadow-sm" style="transition: transform 0.2s; border-radius: 8px;">
                        <div class="card-body p-3 d-flex flex-column h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge ${badgeCls} px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="fas ${iconCls} me-1"></i>${timeText}
                                </span>
                                <span class="text-muted small" style="font-size: 0.7rem; font-weight: 600;">
                                    <i class="fas fa-tag me-1"></i>${item.type_label}
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem; line-height: 1.4;">${item.task_name}</h6>
                            <p class="text-muted mb-2 small" style="font-size: 0.8rem;">
                                <i class="fas fa-project-diagram me-1"></i>${item.project_name}
                            </p>
                            ${item.notes ? `<div class="bg-light p-2 rounded small text-secondary mb-2 flex-grow-1" style="font-size: 0.75rem; border-right: 2px solid #cbd5e1;"><strong>ملاحظة:</strong> ${item.notes}</div>` : ''}
                            <div class="text-start mt-auto pt-2">
                                <a href="${item.view_url || (item.is_standalone_task ? `/tasks/${item.task_id}` : `/projects/${item.project_id}/execution`)}" class="btn btn-sm btn-link text-primary p-0" style="text-decoration: none; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-external-link-alt me-1"></i>${item.is_standalone_task ? 'عرض تفاصيل المهمة' : 'انتقال للتنفيذ'}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }

    function renderProfileDashboardAlerts() {
        const alertsContainer = $('#profileDashboardAlerts');
        const overdueTasks = profileAssignments.filter(item => item.category === 'overdue');
        const nearingTasks = profileAssignments.filter(item => item.category === 'nearing');
        
        let alertsHtml = '';
        
        if (overdueTasks.length > 0) {
            alertsHtml += `
                <div class="alert alert-danger mb-4 d-flex align-items-center justify-content-between shadow-sm border-start border-4 border-danger" role="alert" style="border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <div class="text-start">
                            <h6 class="alert-heading fw-bold mb-1 text-danger">تنبيه: لديك مهام متأخرة!</h6>
                            <p class="mb-0 small text-dark">لديك <strong>${overdueTasks.length}</strong> مهمة متأخرة تجاوزت تاريخ الاستحقاق. يرجى إنجازها في أقرب وقت.</p>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-danger px-3 rounded-pill" onclick="triggerProfileFilter('overdue')">عرض المهام</button>
                </div>
            `;
        } else if (nearingTasks.length > 0) {
            alertsHtml += `
                <div class="alert alert-warning mb-4 d-flex align-items-center justify-content-between shadow-sm border-start border-4 border-warning" role="alert" style="border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning text-dark rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                            <i class="fas fa-bell fa-lg"></i>
                        </div>
                        <div class="text-start">
                            <h6 class="alert-heading fw-bold mb-1 text-warning">تنبيه: مهام تقترب من موعد الاستحقاق!</h6>
                            <p class="mb-0 small text-dark">لديك <strong>${nearingTasks.length}</strong> مهمة مستحقة للانتهاء خلال الـ 3 أيام القادمة.</p>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-warning px-3 rounded-pill text-dark" onclick="triggerProfileFilter('nearing')">عرض المهام</button>
                </div>
            `;
        }
        
        alertsContainer.html(alertsHtml);
    }

    function triggerProfileFilter(filter) {
        profileCurrentFilter = filter;
        $('#profileAssignmentFilters button').removeClass('active');
        $(`#profileAssignmentFilters button[data-filter="${filter}"]`).addClass('active');
        renderProfileFilteredAssignments();
        
        // Scroll assignments card into view if needed
        const el = document.getElementById('profileAssignmentFilters');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Register filter button click handlers
    $(document).on('click', '#profileAssignmentFilters button', function() {
        profileCurrentFilter = $(this).data('filter');
        $('#profileAssignmentFilters button').removeClass('active');
        $(this).addClass('active');
        renderProfileFilteredAssignments();
    });

    $(document).ready(function () {
        loadProfileAssignments();
    });
</script>
@endcan
@endpush
@endsection
