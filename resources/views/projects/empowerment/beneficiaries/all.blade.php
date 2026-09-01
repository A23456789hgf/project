@extends('layouts.app')

@section('styles')
<style>
    :root {
        --primary-bg: #f8fafc;
        --card-border: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --accent: #2563eb;
    }
    body { background-color: var(--primary-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    .page-title-box {
        background: white;
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .main-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .main-card .card-header {
        background: #fdfdfd;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--card-border);
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table-premium { width: 100%; margin-bottom: 0; }
    .table-premium thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.875rem 1rem;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-premium tbody td {
        padding: 0.875rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-main);
        font-size: 0.88rem;
    }
    .table-premium tr:hover { background-color: #fafcff; }
    .table-premium tbody tr:last-child td { border-bottom: none; }

    .filter-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .badge-method { padding: 0.35em 0.75em; border-radius: 6px; font-size: 0.72rem; font-weight: 700; }
    .form-label { font-weight: 600; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem; }
    .form-control, .form-select { border-color: #e2e8f0; border-radius: 8px; font-size: 0.88rem; }
    .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.12); }
    
    .limit-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4em 0.9em;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    /* Modal Styling */
    .modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .modal-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 15px 15px 0 0; }
    .modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 15px 15px; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Title Bar --}}
    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="fas fa-users me-2"></i>مستفيدو القروض (التمكين)
            </h5>
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.empowerment') }}">إدارة التمكين</a></li>
                    <li class="breadcrumb-item active">كافة المستفيدين</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($selectedProject)
                <span class="limit-badge {{ $isFull ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                    <i class="fas fa-chart-pie me-1"></i>
                    السعة: {{ $currentCount ?? $beneficiaries->total() }} / {{ $selectedProject->number_of_beneficiaries }}
                </span>
            @endif
        </div>
    </div>

    {{-- Alert Messages --}}
    
    

    {{-- Filters --}}
    <div class="filter-card">
        <form action="{{ route('projects.empowerment.beneficiaries.all') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">بحث (الاسم أو رقم الهوية)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="اسم المستفيد أو رقم الهوية..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label">المشروع</label>
                <select name="project_id" id="project_id_dropdown" class="form-select">
                    <option value="">— كافة المشاريع —</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary px-3 w-100 fw-bold">
                    <i class="fas fa-filter me-1"></i> فرز النتائج
                </button>
                <a href="{{ route('projects.empowerment.beneficiaries.all') }}" class="btn btn-outline-secondary" title="إعادة ضبط">
                    <i class="fas fa-sync-alt"></i>
                </a>
                
                {{-- Permanent but JS-controlled Add Button --}}
                @can('empowerment.edit')
                    <button type="button" id="btn_add_beneficiary_immediate" class="btn btn-success px-4 fw-bold shadow-sm flex-shrink-0 @if(!$selectedProject || $isFull) d-none @endif" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                        <i class="fas fa-plus-circle me-1"></i> إضافة مستفيد جديد
                    </button>
                @endcan
            </div>
        </form>
    </div>

    {{-- Beneficiaries Table --}}
    <div class="main-card">
        <div class="card-header">
            <i class="fas fa-list-ul text-primary"></i>
            <span>سجل المستفيدين الشامل</span>
        </div>
        <div class="table-responsive">
            <table class="table table-premium">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>اسم المستفيد</th>
                        <th>رقم الهوية</th>
                        @if(!$selectedProject) <th>المشروع</th> @endif
                        <th>الموقع</th>
                        <th>مبلغ القرض</th>
                        <th>طريقة السداد</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beneficiaries as $i => $ben)
                        <tr>
                            <td class="text-center text-muted fw-bold small">{{ $beneficiaries->firstItem() + $i }}</td>
                            <td>
                                <div class="fw-bold">{{ $ben->first_name }} {{ $ben->middle_name }} {{ $ben->last_name }}</div>
                            </td>
                            <td><code class="text-dark bg-light px-2 py-1 rounded small">{{ $ben->id_number }}</code></td>
                            @if(!$selectedProject)
                            <td>
                                @can('empowerment.view', $ben->empowermentProject)
                                    <a href="{{ route('projects.empowerment.show', $ben->empowerment_project_id) }}" class="text-decoration-none">
                                        {{ $ben->empowermentProject->project_name }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $ben->empowermentProject->project_name }}</span>
                                @endcan
                            </td>
                            @endif
                            <td>
                                <small class="text-muted">
                                    {{ optional($ben->governorate)->name }} / {{ optional($ben->directorate)->name }}
                                </small>
                            </td>
                            <td class="text-success fw-bold">{{ number_format($ben->loan_amount, 2) }}</td>
                            <td>
                                @php
                                    $labels = ['monthly' => 'شهري', 'annually' => 'سنوي', 'seasonally' => 'موسمي'];
                                    $colors = ['monthly' => 'info', 'annually' => 'warning', 'seasonally' => 'success'];
                                @endphp
                                <span class="badge-method bg-{{ $colors[$ben->repayment_method] ?? 'secondary' }}-subtle text-{{ $colors[$ben->repayment_method] ?? 'secondary' }} border border-{{ $colors[$ben->repayment_method] ?? 'secondary' }}-subtle">
                                    {{ $labels[$ben->repayment_method] ?? $ben->repayment_method }}
                                </span>
                            </td>
                            <td class="text-center">
                                @can('empowerment.view', $ben->empowermentProject)
                                    <a href="{{ route('projects.empowerment.beneficiaries.index', $ben->empowerment_project_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-eye me-1"></i> التفاصيل
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-user-friends fa-3x mb-3 opacity-25"></i>
                                <p>لا يوجد سجلات مستفيدين تطابق معايير البحث</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($beneficiaries->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $beneficiaries->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ===== Modal: Add Beneficiary (Permanent DOM element) ===== --}}
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1" aria-labelledby="addBeneficiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="add_beneficiary_form" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary" id="addBeneficiaryModalLabel">
                        <i class="fas fa-user-plus me-2"></i>إضافة مستفيد: <span id="modal_project_name">{{ $selectedProject->project_name ?? '' }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Capacity Warning Alert --}}
                    <div id="modal_capacity_warning" class="alert alert-danger d-none mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        عذراً، تم الوصول للحد الأقصى لعدد المستفيدين لهذا المشروع.
                    </div>

                    <div class="row g-3">
                        {{-- Basic Info --}}
                        <div class="col-md-4">
                            <label class="form-label">الاسم الأول </label>
                            <input type="text" name="first_name" class="form-control" placeholder="مثال: أحمد">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اسم الأب</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="اختياري">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">اللقب / العائلة </label>
                            <input type="text" name="last_name" class="form-control" placeholder="مثال: الصنعاني">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهوية </label>
                            <input type="text" name="id_number" class="form-control" placeholder="رقم البطاقة الشخصية">
                        </div>
                        
                        <div class="col-12"><hr class="my-1"></div>
                        
                        {{-- Location Info --}}
                        <div class="col-md-6">
                            <label class="form-label">المحافظة </label>
                            <select name="governorate_id" id="modal_gov_id" class="form-select">
                                <option value="">— اختر المحافظة —</option>
                                @foreach($governorates as $gov)
                                    <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المديرية </label>
                            <select name="directorate_id" id="modal_dir_id" class="form-select" disabled>
                                <option value="">— اختر المديرية —</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المنطقة / العزلة</label>
                            <select name="sub_area_id" id="modal_sub_id" class="form-select" disabled>
                                <option value="">— اختياري —</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">القرية / المحلة</label>
                            <select name="village_id" id="modal_vil_id" class="form-select" disabled>
                                <option value="">— اختياري —</option>
                            </select>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Payment Info --}}
                        <div class="col-md-12 bg-light p-3 rounded-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-muted small">مبلغ القرض المستحق للفرد:</span>
                                <span class="h5 mb-0 fw-bold text-success" id="modal_loan_amount_display">
                                    {{ isset($avgLoan) ? number_format($avgLoan, 2) : '0.00' }} ريال
                                </span>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">طريقة السداد</label>
                            <select name="repayment_method" class="form-select">
                                <option value="monthly">شهري</option>
                                <option value="annually">سنوي</option>
                                <option value="seasonally">موسمي</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">عدد الأقساط</label>
                            <input type="number" name="installments_count" class="form-control" value="12" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" id="btn_save_beneficiary" class="btn btn-success px-5 fw-bold">حفظ البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const $projectSelect = $('#project_id_dropdown');
    const $btnAdd = $('#btn_add_beneficiary_immediate');
    const $modalProjectName = $('#modal_project_name');
    const $modalLoanAmount = $('#modal_loan_amount_display');
    const $modalForm = $('#add_beneficiary_form');
    const $capacityWarning = $('#modal_capacity_warning');
    const $btnSave = $('#btn_save_beneficiary');

    // ── Handle Project Selection Change ──
    $projectSelect.on('change', function() {
        const projectId = $(this).val();
        
        if (!projectId) {
            $btnAdd.addClass('d-none');
            return;
        }

        // Fetch Project Details via AJAX
        $.get(`/api/empowerment-projects/${projectId}/details`, function(data) {
            // Update Modal Content
            $modalProjectName.text(data.project_name);
            $modalLoanAmount.text(data.avg_loan_formatted + ' ريال');
            $modalForm.attr('action', `/projects/empowerment/${projectId}/beneficiaries`);
            
            // Handle Capacity
            if (data.is_full) {
                $capacityWarning.removeClass('d-none');
                $btnSave.prop('disabled', true);
                $btnAdd.addClass('btn-outline-danger').removeClass('btn-success').html('<i class="fas fa-exclamation-circle"></i> المشروع ممتلئ');
            } else {
                $capacityWarning.addClass('d-none');
                $btnSave.prop('disabled', false);
                $btnAdd.addClass('btn-success').removeClass('btn-outline-danger d-none').html('<i class="fas fa-plus-circle me-1"></i> إضافة مستفيد جديد');
            }
            
            $btnAdd.removeClass('d-none');
        }).fail(function() {
            flasher.error('حدث خطأ أثناء جلب بيانات المشروع');
        });
    });

    // ── Governorate → Directorate (Modal) ──
    $('#modal_gov_id').on('change', function () {
        const govId = $(this).val();
        const $dir = $('#modal_dir_id');
        const $sub = $('#modal_sub_id');
        const $vil = $('#modal_vil_id');

        $dir.empty().append('<option value="">— اختر المديرية —</option>').prop('disabled', true);
        $sub.empty().append('<option value="">— اختياري —</option>').prop('disabled', true);
        $vil.empty().append('<option value="">— اختياري —</option>').prop('disabled', true);

        if (govId) {
            $.get(`/api/locations/directorates/${govId}`, function (data) {
                if (data && data.length) {
                    data.forEach(item => $dir.append(`<option value="${item.id}">${item.name}</option>`));
                    $dir.prop('disabled', false);
                }
            });
        }
    });

    // ── Directorate → Sub Area (Modal) ──
    $('#modal_dir_id').on('change', function () {
        const govId = $('#modal_gov_id').val();
        const dirId = $(this).val();
        const $sub = $('#modal_sub_id');
        const $vil = $('#modal_vil_id');

        $sub.empty().append('<option value="">— اختياري —</option>').prop('disabled', true);
        $vil.empty().append('<option value="">— اختياري —</option>').prop('disabled', true);

        if (dirId) {
            $.get(`/api/locations/sub-areas/${govId}/${dirId}`, function (data) {
                if (data && data.length) {
                    data.forEach(item => $sub.append(`<option value="${item.id}">${item.name}</option>`));
                    $sub.prop('disabled', false);
                }
            });
        }
    });

    // ── Sub Area → Village (Modal) ──
    $('#modal_sub_id').on('change', function () {
        const govId = $('#modal_gov_id').val();
        const dirId = $('#modal_dir_id').val();
        const subId = $(this).val();
        const $vil = $('#modal_vil_id');

        $vil.empty().append('<option value="">— اختياري —</option>').prop('disabled', true);

        if (subId) {
            $.get(`/api/locations/villages/${govId}/${dirId}/${subId}`, function (data) {
                if (data && data.length) {
                    data.forEach(item => $vil.append(`<option value="${item.id}">${item.name}</option>`));
                    $vil.prop('disabled', false);
                }
            });
        }
    });
});
</script>
@endpush
