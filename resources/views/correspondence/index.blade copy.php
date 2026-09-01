@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h1 class="h3 mb-0 text-white">
                        <i class="fas fa-exchange-alt me-2"></i>لوحة متابعة المراسلات والإحالات
                    </h1>
                    @can('create', App\Models\Correspondence::class)
                    <a href="{{ route('correspondence.create') }}" class="btn btn-light btn-sm auth-perm-correspondence-create">
                        <i class="fas fa-plus me-1"></i>مراسلة جديدة
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <!-- Error/Success Messages -->
                    
                    
                    

                    <!-- KPI Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">إجمالي المواضيع</h6>
                                            <h3 class="mb-0 fw-bold">{{ $statistics['total_topics'] ?? 0 }}</h3>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">إحالات مكتملة</h6>
                                            <h3 class="mb-0 fw-bold">{{ $statistics['completed_referrals'] ?? 0 }}</h3>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                            <i class="fas fa-check-double fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">إحالات قيد الانتظار</h6>
                                            <h3 class="mb-0 fw-bold">{{ $statistics['pending_referrals'] ?? 0 }}</h3>
                                        </div>
                                        <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs mb-3" id="correspondenceTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') != 'sent' && request('filter') != 'received' && request('filter') != 'referred' && request('filter') != 'overdue' ? 'active' : '' }}" 
                               href="{{ route('correspondence.index') }}">
                                <i class="fas fa-inbox me-1"></i>الكل
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') == 'sent' ? 'active' : '' }}" 
                               href="{{ route('correspondence.index', ['filter' => 'sent']) }}">
                                <i class="fas fa-paper-plane me-1"></i>المرسلة
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') == 'received' ? 'active' : '' }}" 
                               href="{{ route('correspondence.index', ['filter' => 'received']) }}">
                                <i class="fas fa-download me-1"></i>الواردة
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') == 'referred' ? 'active' : '' }}" 
                               href="{{ route('correspondence.index', ['filter' => 'referred']) }}">
                                <i class="fas fa-share me-1"></i>المحالة
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ request('filter') == 'overdue' ? 'active' : '' }}" 
                               href="{{ route('correspondence.index', ['filter' => 'overdue']) }}">
                                <i class="fas fa-exclamation-triangle me-1"></i>المتأخرة
                            </a>
                        </li>
                    </ul>

                    <!-- Search and Filters Dashboard -->
                    <div class="card bg-light border-0 mb-4 shadow-sm">
                        <div class="card-body p-3">
                            <form action="{{ route('correspondence.index') }}" method="GET" id="referralDashboardFilterForm">
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                                
                                <div class="row g-3">
                                    <!-- Search Text -->
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" name="search" class="form-control border-start-0 ps-0" 
                                                   placeholder="بحث في الموضوع، الرقم، أو الجهة..." 
                                                   value="{{ request('search') }}">
                                        </div>
                                    </div>

                                    <!-- Department Filter -->
                                    <div class="col-md-2">
                                        <select name="referred_to_department" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">جميع الأقسام</option>
                                            @foreach($subDepartments as $dept)
                                                <option value="{{ $dept->id }}" {{ request('referred_to_department') == $dept->id ? 'selected' : '' }}>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Status Filter -->
                                    <div class="col-md-2">
                                        <select name="referral_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">جميع الحالات</option>
                                            <option value="pending" {{ request('referral_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                            <option value="accepted" {{ request('referral_status') == 'accepted' ? 'selected' : '' }}>مقبولة</option>
                                            <option value="completed" {{ request('referral_status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                            <option value="rejected" {{ request('referral_status') == 'rejected' ? 'selected' : '' }}>مرفوضة</option>
                                        </select>
                                    </div>

                                    <!-- Date Range Filter -->
                                    <div class="col-md-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">من</span>
                                            <input type="date" name="date_from" class="form-control" 
                                                   value="{{ request('date_from') }}" onchange="this.form.submit()">
                                            <span class="input-group-text bg-white">إلى</span>
                                            <input type="date" name="date_to" class="form-control" 
                                                   value="{{ request('date_to') }}" onchange="this.form.submit()">
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="col-md-2 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                            <i class="fas fa-filter me-1"></i>تصفية
                                        </button>
                                        <a href="{{ route('correspondence.index', ['filter' => request('filter')]) }}" 
                                           class="btn btn-outline-secondary btn-sm flex-fill" 
                                           title="إعادة ضبط الفلاتر">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Correspondences Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>رقم المراسلة</th>
                                    <th>الموضوع</th>
                                    <th>من / إلى</th>
                                    <th>المحال إليه</th>
                                    <th>التاريخ</th>
                                    <th>حالة الإحالة</th>
                                    <th>الأولوية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($correspondences as $correspondence)
                                    <tr class="{{ $correspondence->is_overdue ? 'table-danger' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $correspondence->correspondence_number }}</span>
                                            @if($correspondence->confidential)
                                                <span class="badge bg-dark ms-1">سري</span>
                                            @endif
                                        </td>
                                        <td>
                                             <strong>{{ Str::limit($correspondence->subject, 50) }}</strong>
                                             @if($correspondence->project_id)
                                                 <br>
                                                 <small class="text-primary fw-bold">
                                                     <i class="fas fa-project-diagram me-1"></i>
                                                     {{ $correspondence->project->project_name }}
                                                 </small>
                                             @endif
                                         </td>
                                        <td>
                                            <div class="mb-1">
                                                <i class="fas fa-paper-plane text-success me-1" title="المرسل"></i>
                                                {{ $correspondence->senderEntity?->name ?? 'غير محدد' }}
                                            </div>
                                            <div>
                                                <i class="fas fa-download text-primary me-1" title="المستلم"></i>
                                                {{ $correspondence->recipientEntity?->name ?? 'غير محدد' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($correspondence->latestReferral)
                                                <div class="d-flex align-items-center">
                                                    @if($correspondence->latestReferral->referred_to_type == 'person')
                                                        <i class="fas fa-user text-muted me-2" title="شخص"></i>
                                                        <span>{{ $correspondence->latestReferral->referred_to_name ?? 'غير محدد' }}</span>
                                                    @else
                                                        <i class="fas fa-building text-muted me-2" title="قسم"></i>
                                                        <span>{{ $correspondence->latestReferral->referredToEntity?->name ?? 'غير محدد' }}</span>
                                                    @endif
                                                </div>
                                                @if($correspondence->referrals()->count() > 1)
                                                    <small class="text-info d-block mt-1">
                                                        <i class="fas fa-history me-1"></i> +{{ $correspondence->referrals()->count() - 1 }} إحالات أخرى
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted small">لم يتم الإحالة</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $correspondence->formatted_created_at }}
                                            <br>
                                            <small class="text-muted">{{ $correspondence->days_since_creation }} يوم</small>
                                        </td>
                                        <td>
                                            @if($correspondence->latestReferral)
                                                <span class="badge bg-{{ $correspondence->latestReferral->referral_status_color }}">
                                                    {{ $correspondence->latestReferral->referral_status_label }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">لا يوجد</span>
                                            @endif
                                            @if($correspondence->is_overdue)
                                                <br><span class="badge bg-danger mt-1">متأخرة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $correspondence->priority == 'urgent' ? 'danger' : ($correspondence->priority == 'high' ? 'warning' : 'success') }}">
                                                {{ $correspondence->priority_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('view', $correspondence)
                                                <a href="{{ route('correspondence.show', $correspondence->id) }}" 
                                                   class="btn btn-sm btn-primary auth-perm-correspondence-view" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <a href="{{ route('correspondence.show', $correspondence->id) }}#movement-table"
                                               class="btn btn-sm btn-info text-white btn-movement-log auth-perm-correspondence-view" 
                                               data-id="{{ $correspondence->id }}"
                                               data-number="{{ $correspondence->correspondence_number }}"
                                               title="حركة المراسلة">
                                                    <i class="fas fa-history"></i>
                                                </a>

                                                @if($correspondence->latestReferral)
                                                <button type="button" 
                                                        class="btn btn-sm btn-success text-white btn-update-referral" 
                                                        data-id="{{ $correspondence->latestReferral->id }}"
                                                        data-status="{{ $correspondence->latestReferral->referral_status }}"
                                                        data-notes="{{ $correspondence->latestReferral->referral_notes }}"
                                                        title="تحديث حالة الإحالة">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                
                                                @can('forward', $correspondence)
                                                @if($correspondence->status !== 'closed')
                                                <button type="button" 
                                                        class="btn btn-sm btn-info text-white auth-perm-correspondence-forward" 
                                                        title="توجيه داخلي"
                                                        onclick="openForwardModal({{ $correspondence->id }}, '{{ $correspondence->correspondence_number }}')">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                
                                                @can('update', $correspondence)
                                                    <a href="{{ route('correspondence.edit', $correspondence->id) }}" 
                                                       class="btn btn-sm btn-warning auth-perm-correspondence-edit" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('delete', $correspondence)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger auth-perm-correspondence-delete" 
                                                            title="حذف"
                                                            onclick="confirmDelete({{ $correspondence->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                 @empty 
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد مراسلات</p>
                                            @can('create', App\Models\Correspondence::class)
                                            <a href="{{ route('correspondence.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>إنشاء مراسلة جديدة
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            عرض {{ $correspondences->firstItem() ?? 0 }} إلى {{ $correspondences->lastItem() ?? 0 }} من أصل {{ $correspondences->total() }} مراسلة
                        </div>
                        <div>
                            {{ $correspondences->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف هذه المراسلة؟</p>
                <p class="text-danger"><small>لا يمكن التراجع عن هذا الإجراء.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
    }
</style>
@endpush

@section('modals')

<!-- Referral Status Update Modal -->
<div class="modal fade" id="updateReferralModal" tabindex="-1" aria-labelledby="updateReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="updateReferralModalLabel">
                    <i class="fas fa-sync-alt me-2"></i>تحديث حالة الإحالة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateReferralForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">حالة الإحالة <span class="text-danger">*</span></label>
                        <select name="referral_status" id="referral_status_select" class="form-select" required>
                            <option value="pending">قيد الانتظار</option>
                            <option value="accepted">مقبولة</option>
                            <option value="completed">مكتملة</option>
                            <option value="rejected">مرفوضة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="referral_notes" class="form-label">ملاحظات التحديث</label>
                        <textarea name="referral_notes" id="referral_notes_textarea" rows="4" class="form-control" placeholder="اكتب أي ملاحظات تتعلق بتغيير الحالة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">حفظ التغييرات</button>
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
        $('.select2-modal').select2({
            dropdownParent: $('#forwardModal'),
            placeholder: 'اختر الأقسام...',
            language: "ar",
            dir: "rtl"
        });
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

        // Fetch movement log
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
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 150px;">التاريخ</th>
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
                                <small class="text-muted"><i class="fas fa-user me-1"></i>${move.user_name || 'سيستم'}</small>
                            </td>
                            <td>
                                <span class="badge bg-${move.action_color || 'secondary'} px-2 py-1">
                                    <i class="${move.action_icon} me-1"></i>${move.action_label}
                                </span>
                            </td>
                            <td style="max-width: 400px; white-space: normal;">
                                <div class="mb-1">${move.action_description}</div>
                                ${move.action_details && (Object.keys(move.action_details).length > 0) ? `<small class="text-muted d-block border-top mt-1 pt-1 italic text-truncate">${JSON.stringify(move.action_details)}</small>` : ''}
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

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

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