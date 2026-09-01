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

    .form-panel {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        position: sticky;
        top: 20px;
    }
    .form-panel .panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--card-border);
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #fdfdfd;
        border-radius: 12px 12px 0 0;
    }
    .form-panel .panel-body { padding: 1.25rem; }

    .limit-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4em 0.9em;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .form-label { font-weight: 600; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem; }
    .form-control, .form-select { border-color: #e2e8f0; border-radius: 8px; font-size: 0.88rem; }
    .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.12); }

    .badge-method { padding: 0.35em 0.75em; border-radius: 6px; font-size: 0.72rem; font-weight: 700; }

    .empty-state { text-align: center; padding: 3.5rem 1rem; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; opacity: 0.2; margin-bottom: 1rem; }
</style>
@endsection

@section('content')
@php
    $isFull = $beneficiaries->count() >= $empowermentProject->number_of_beneficiaries;
    $methodLabels = ['monthly' => 'شهري', 'annually' => 'سنوي', 'seasonally' => 'موسمي'];
    $methodColors = ['monthly' => 'info', 'annually' => 'warning', 'seasonally' => 'success'];
@endphp

<div class="container-fluid py-4 px-4">

    {{-- Title Bar --}}
    <div class="page-title-box d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1 fw-bold text-primary">
                <i class="fas fa-users me-2"></i>إدارة سجل المستفيدين
            </h5>
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('projects.empowerment') }}">قروض التمكين</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects.empowerment.show', $empowermentProject->id) }}">
                            {{ $empowermentProject->project_name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">إدارة المستفيدين</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="limit-badge {{ $isFull ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                <i class="fas fa-users"></i>
                {{ $beneficiaries->count() }} / {{ $empowermentProject->number_of_beneficiaries }} مستفيد
            </span>
            <a href="{{ route('projects.empowerment.show', $empowermentProject->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="fas fa-chevron-right ms-1"></i> العودة للمشروع
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    
    

    <div class="row g-4">

        {{-- ===== LEFT: Beneficiaries List ===== --}}
        <div class="col-lg-8">
            <div class="main-card">
                <div class="card-header">
                    <i class="fas fa-list-ul text-primary"></i>
                    <span>المستفيدون المسجلون</span>
                    <span class="ms-auto limit-badge 
                        {{ $isFull ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }}">
                        {{ $beneficiaries->count() }} / {{ $empowermentProject->number_of_beneficiaries }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم الأول</th>
                                <th>الاسم الأوسط</th>
                                <th>الاسم الأخير</th>
                                <th>رقم الهوية</th>
                                <th>الموقع</th>
                                <th>مبلغ القرض</th>
                                <th>طريقة السداد</th>
                                <th class="text-center">الأقساط</th>
                                <th class="text-center">حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beneficiaries as $i => $ben)
                                <tr>
                                    <td class="text-muted fw-bold small">{{ $i + 1 }}</td>
                                    <td class="fw-bold">{{ $ben->first_name }}</td>
                                    <td class="text-muted">{{ $ben->middle_name ?: '—' }}</td>
                                    <td class="fw-bold">{{ $ben->last_name }}</td>
                                    <td><code class="text-dark">{{ $ben->id_number }}</code></td>
                                    <td>
                                        <small class="text-muted">
                                            {{ optional($ben->governorate)->name }}
                                            @if($ben->directorate)
                                                <span class="text-primary mx-1">/</span>{{ $ben->directorate->name }}
                                            @endif
                                            @if($ben->subArea)
                                                <br><span class="text-muted">{{ $ben->subArea->name }}</span>
                                            @endif
                                            @if($ben->village)
                                                <span class="text-muted mx-1">-</span>{{ $ben->village->name }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-success fw-bold">{{ number_format($ben->loan_amount, 2) }}</td>
                                    <td>
                                        <span class="badge-method bg-{{ $methodColors[$ben->repayment_method] ?? 'secondary' }}-subtle text-{{ $methodColors[$ben->repayment_method] ?? 'secondary' }} border border-{{ $methodColors[$ben->repayment_method] ?? 'secondary' }}-subtle">
                                            {{ $methodLabels[$ben->repayment_method] ?? $ben->repayment_method }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $ben->installments_count }}</td>
                                    <td class="text-center">
                                        @can('empowerment.beneficiaries.delete', $empowermentProject)
                                        <form action="{{ route('projects.empowerment.beneficiaries.destroy', [$empowermentProject->id, $ben->id]) }}"
                                              method="POST"
                                              onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف هذا المستفيد؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0" title="حذف">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @else
                                            <i class="fas fa-lock text-muted small" title="غير مصرح"></i>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="empty-state">
                                            <i class="fas fa-user-friends d-block"></i>
                                            <p class="mb-0">لا يوجد مستفيدون مسجلون بعد لهذا المشروع</p>
                                            <p class="small text-muted mt-1">استخدم النموذج على اليمين لإضافة مستفيد</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($beneficiaries->isNotEmpty())
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold small text-muted">متوسط القرض / المستفيد:</td>
                                <td class="fw-bold text-success">{{ number_format($avgLoan, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Add Form ===== --}}
        <div class="col-lg-4">
            <div class="form-panel">
                <div class="panel-header">
                    <i class="fas fa-user-plus text-success"></i>
                    <span>إضافة مستفيد جديد</span>
                </div>
                <div class="panel-body">
                    @can('empowerment.beneficiaries.create', $empowermentProject)
                        @if($isFull)
                            <div class="alert alert-warning border-warning-subtle rounded-3 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>تم الوصول للحد الأقصى</strong><br>
                                <small>تم تسجيل {{ $empowermentProject->number_of_beneficiaries }} مستفيد. لا يمكن الإضافة.</small>
                            </div>
                        @else
                            <form action="{{ route('projects.empowerment.beneficiaries.store', $empowermentProject->id) }}" method="POST" id="beneficiaryForm">
                                @csrf

                                {{-- === Name Fields === --}}
                                <div class="mb-3">
                                    <label class="form-label">الاسم الأول </label>
                                    <input type="text" name="first_name"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           value="{{ old('first_name') }}"
                                           placeholder="الاسم الأول">
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">اسم الأب (الأوسط)</label>
                                    <input type="text" name="middle_name"
                                           class="form-control @error('middle_name') is-invalid @enderror"
                                           value="{{ old('middle_name') }}"
                                           placeholder="اسم الأب (اختياري)">
                                    @error('middle_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">اسم العائلة (الأخير) </label>
                                    <input type="text" name="last_name"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           value="{{ old('last_name') }}"
                                           placeholder="اسم العائلة">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- === ID Number === --}}
                                <div class="mb-3">
                                    <label class="form-label">رقم الهوية الوطنية </label>
                                    <input type="text" name="id_number"
                                           class="form-control @error('id_number') is-invalid @enderror"
                                           value="{{ old('id_number') }}"
                                           placeholder="رقم الهوية">
                                    @error('id_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-3 text-muted opacity-25">

                                {{-- === Location === --}}
                                <p class="small fw-bold text-muted mb-2"><i class="fas fa-map-marker-alt me-1 text-primary"></i>معلومات الإقامة</p>

                                <div class="mb-3">
                                    <label class="form-label">المحافظة </label>
                                    <select name="governorate_id" id="ben_governorate_id"
                                            class="form-select @error('governorate_id') is-invalid @enderror">
                                        <option value="">— اختر المحافظة —</option>
                                        @foreach($governorates as $gov)
                                            <option value="{{ $gov->id }}" {{ old('governorate_id') == $gov->id ? 'selected' : '' }}>
                                                {{ $gov->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('governorate_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">المديرية </label>
                                    <select name="directorate_id" id="ben_directorate_id"
                                            class="form-select @error('directorate_id') is-invalid @enderror" disabled>
                                        <option value="">— اختر المديرية —</option>
                                    </select>
                                    @error('directorate_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">العزلة / المنطقة</label>
                                    <select name="sub_area_id" id="ben_sub_area_id" class="form-select" disabled>
                                        <option value="">— اختياري —</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">القرية / المحلة</label>
                                    <select name="village_id" id="ben_village_id" class="form-select" disabled>
                                        <option value="">— اختياري —</option>
                                    </select>
                                </div>

                                <hr class="my-3 text-muted opacity-25">

                                {{-- === Loan Amount (readonly) === --}}
                                <div class="mb-3">
                                    <label class="form-label text-success">
                                        <i class="fas fa-coins me-1"></i>مبلغ القرض للفرد (تلقائي)
                                        
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-success-subtle fw-bold text-success"
                                               value="{{ number_format($avgLoan, 2) }}" readonly>
                                        <span class="input-group-text bg-success-subtle border-success-subtle text-success fw-bold">ريال</span>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        يُحسب تلقائياً: {{ number_format($empowermentProject->total_loan_amount, 2) }} ÷ {{ $empowermentProject->number_of_beneficiaries }}
                                    </small>
                                </div>

                                {{-- === Repayment & Installments === --}}
                                <div class="row g-2 mb-4">
                                    <div class="col-7">
                                        <label class="form-label">طريقة السداد </label>
                                        <select name="repayment_method"
                                                class="form-select @error('repayment_method') is-invalid @enderror">
                                            <option value="monthly"   {{ old('repayment_method') == 'monthly'   ? 'selected' : '' }}>شهري</option>
                                            <option value="annually"  {{ old('repayment_method') == 'annually'  ? 'selected' : '' }}>سنوي</option>
                                            <option value="seasonally" {{ old('repayment_method') == 'seasonally' ? 'selected' : '' }}>موسمي</option>
                                        </select>
                                        @error('repayment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-5">
                                        <label class="form-label">عدد الأقساط </label>
                                        <input type="number" name="installments_count"
                                               class="form-control @error('installments_count') is-invalid @enderror"
                                               value="{{ old('installments_count', 12) }}" min="1">
                                        @error('installments_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm">
                                    <i class="fas fa-plus-circle me-2"></i>إضافة المستفيد
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="alert alert-light border small text-muted text-center py-4 mb-0">
                            <i class="fas fa-lock mb-2 d-block fa-lg"></i>
                            ليس لديك صلاحية لإضافة مستفيدين لهذا المشروع.
                        </div>
                    @endcan
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── Governorate → Directorate ──
    $('#ben_governorate_id').on('change', function () {
        const govId = $(this).val();
        const $dir = $('#ben_directorate_id');
        const $sub = $('#ben_sub_area_id');
        const $vil = $('#ben_village_id');

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

    // ── Directorate → Sub Area ──
    $('#ben_directorate_id').on('change', function () {
        const govId = $('#ben_governorate_id').val();
        const dirId = $(this).val();
        const $sub = $('#ben_sub_area_id');
        const $vil = $('#ben_village_id');

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

    // ── Sub Area → Village ──
    $('#ben_sub_area_id').on('change', function () {
        const govId = $('#ben_governorate_id').val();
        const dirId = $('#ben_directorate_id').val();
        const subId = $(this).val();
        const $vil = $('#ben_village_id');

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
