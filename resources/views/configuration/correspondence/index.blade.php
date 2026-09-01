@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>المراسلات بين الجهات</h5>
                    @can('correspondence.create')
                    <a href="{{ route('correspondence.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>مراسلة جديدة
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <!-- Error/Success Messages -->
                    
                    
                    

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">المراسلات الواردة</h6>
                                            <h4 class="mb-0">{{ $statistics['total_received'] ?? 0 }}</h4>
                                        </div>
                                        <i class="fas fa-download fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-warning text-dark">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">قيد الانتظار</h6>
                                            <h4 class="mb-0">{{ $statistics['pending'] ?? 0 }}</h4>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">متأخرة</h6>
                                            <h4 class="mb-0">{{ $statistics['overdue'] ?? 0 }}</h4>
                                        </div>
                                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="card bg-success text-white">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">تم الرد</h6>
                                            <h4 class="mb-0">{{ $statistics['replied'] ?? 0 }}</h4>
                                        </div>
                                        <i class="fas fa-reply fa-2x opacity-50"></i>
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

                    <!-- Search and Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form action="{{ route('correspondence.index') }}" method="GET" class="d-flex">
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                                <input type="text" name="search" class="form-control me-2" 
                                       placeholder="بحث برقم المراسلة أو الموضوع أو الجهة..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4">
                                    <form action="{{ route('correspondence.index') }}" method="GET" id="statusForm">
                                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                        <select name="status" class="form-select" onchange="this.form.submit()">
                                            <option value="">جميع الحالات</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار / بانتظار الرد</option>
                                            <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>تم الرد</option>
                                            <option value="referred" {{ request('status') == 'referred' ? 'selected' : '' }}>تم الإحالة</option>
                                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>تم الإرجاع</option>
                                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                                        </select>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <form action="{{ route('correspondence.index') }}" method="GET" id="priorityForm">
                                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <select name="priority" class="form-select" onchange="this.form.submit()">
                                            <option value="">جميع الأولويات</option>
                                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادية</option>
                                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجلة</option>
                                        </select>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <form action="{{ route('correspondence.index') }}" method="GET" id="dateForm">
                                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        <input type="hidden" name="priority" value="{{ request('priority') }}">
                                        <input type="date" name="date_from" class="form-control" 
                                               placeholder="من تاريخ" 
                                               value="{{ request('date_from') }}"
                                               onchange="this.form.submit()">
                                    </form>
                                </div>
                            </div>
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
                                    <th>من</th>
                                    <th>إلى</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الأولوية</th>
                                    <th>مرفقات</th>
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
                                        </td>
                                        <td>
                                            {{ $correspondence->senderEntity->name }}
                                            <br>
                                            <small class="text-muted">{{ $correspondence->senderUser->name ?? 'غير محدد' }}</small>
                                        </td>
                                        <td>{{ $correspondence->recipientEntity->name }}</td>
                                        <td>
                                            {{ $correspondence->formatted_created_at }}
                                            <br>
                                            <small class="text-muted">{{ $correspondence->days_since_creation }} يوم</small>
                                        </td>
                                        <td>
                                            {{-- الحالة للعرض فقط - تتحديث تلقائياً عند إجراء أي عملية --}}
                                            {{-- Status is read-only - updates automatically with any action --}}
                                            <span class="badge bg-{{ $correspondence->status_color }}">
                                                {{ $correspondence->status_label }}
                                            </span>
                                            @if($correspondence->is_overdue)
                                                <br><span class="badge bg-danger mt-1">متأخرة</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $correspondence->priority == 'urgent' ? 'danger' : ($correspondence->priority == 'high' ? 'warning' : 'success') }}">
                                                {{ $correspondence->priority_label }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($correspondence->attachment_count > 0)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-paperclip"></i> {{ $correspondence->attachment_count }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('correspondence.view')
                                                <a href="{{ route('correspondence.show', $correspondence->id) }}" 
                                                   class="btn btn-sm btn-primary" title="عرض التفاصيل">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <a href="{{ route('correspondence.show', $correspondence->id) }}#movement-table"
                                               class="btn btn-sm btn-info text-white btn-movement-log" 
                                               data-id="{{ $correspondence->id }}"
                                               data-number="{{ $correspondence->correspondence_number }}"
                                               title="حركة المراسلة">
                                                <i class="fas fa-history"></i>
                                            </a>
                                                
                                                @if($correspondence->status !== 'closed' && $correspondence->recipient_entity_id == auth()->user()->entity_id)
                                                <button type="button" 
                                                        class="btn btn-sm btn-info text-white" 
                                                        title="توجيه داخلي"
                                                        onclick="openForwardModal({{ $correspondence->id }}, '{{ $correspondence->correspondence_number }}')">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                                @endif
                                                @endcan
                                                
                                                @can('correspondence.edit')
                                                @if($correspondence->canBeEdited())
                                                    <a href="{{ route('correspondence.edit', $correspondence->id) }}" 
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @endcan
                                                
                                                @can('correspondence.delete')
                                                @if($correspondence->canBeDeleted())
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="حذف"
                                                            onclick="confirmDelete({{ $correspondence->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد مراسلات</p>
                                            @can('correspondence.create')
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
                <form id="deleteForm" method="POST" action="" onsubmit="return confirmAction(this, 'هل أنت متأكد من عملية الحذف؟')">
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
<!-- Internal Forwarding Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="forwardModalLabel">
                    <i class="fas fa-forward me-2"></i>توجيه المراسلة: <span id="forward_number_display"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="forwardForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الأقسام الموجه إليها <span class="text-danger">*</span></label>
                        <select name="to_entity_ids[]" id="index_to_entity_ids" class="form-select select2-modal" multiple required style="width: 100%">
                            @foreach($subDepartments as $subDept)
                                <option value="{{ $subDept->id }}">{{ $subDept->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">يمكنك اختيار أكثر من قسم داخلي لتوجيه المراسلة إليهم.</small>
                    </div>
                    <div class="mb-3">
                        <label for="general_notes" class="form-label">ملاحظات أو تعليمات التوجيه</label>
                        <textarea name="general_notes" id="general_notes" rows="4" class="form-control" placeholder="اكتب التعليمات للأقسام الموجه إليها..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال التوجيه</button>
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
});
</script>
@endpush