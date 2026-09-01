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

    /* Header Styling */
    .page-title-box {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Stats Card Styling */
    .stat-widget {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        border-right: 4px solid var(--accent);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        height: 100%;
    }
    .stat-widget .label { color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; display: block; }
    .stat-widget .value { font-size: 1.25rem; font-weight: 800; color: var(--text-main); }

    /* Main Info Card */
    .main-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .main-card .card-header {
        background: #fdfdfd;
        padding: 1.25rem;
        border-bottom: 1px solid var(--card-border);
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
    }
    .main-card .card-header i { color: var(--accent); margin-left: 10px; }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        padding: 1.5rem;
    }
    .detail-item {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.75rem;
    }
    .detail-item .label { color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .detail-item .value { color: var(--text-main); font-weight: 600; font-size: 0.95rem; }

    /* Premium Table */
    .table-premium { width: 100%; margin-bottom: 0; }
    .table-premium thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 1rem;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-premium tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-main);
        font-size: 0.9rem;
    }
    .table-premium tr:hover { background-color: #fcfcfd; }

    /* Badge Style */
    .badge-soft {
        padding: 0.5em 1em;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Sidebar Status Box */
    .status-panel {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--card-border);
        padding: 1.5rem;
        position: sticky;
        top: 20px;
    }
</style>
@endsection

@section('content')


<div class="container-fluid py-4 px-4">

    {{-- Title Bar --}}
    <div class="page-title-box d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 fw-bold text-primary">{{ $p->project_name }}</h4>
            <p class="text-muted mb-0 small">
                <i class="fas fa-hashtag me-1"></i> رقم المشروع: <strong>{{ $p->project_number }}</strong>
                <span class="mx-2">|</span>
                <i class="fas fa-calendar-day me-1"></i> تاريخ الإحالة: {{ $p->created_at->format('Y-m-d') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('projects.empowerment') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="fas fa-chevron-right ms-1"></i> العودة للقائمة
            </a>
            @if($proj)
            <a href="{{ route('projects.show', $proj->id) }}" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm">
                <i class="fas fa-eye me-1"></i> ملف المشروع الفني
            </a>
            @endif
        </div>
    </div>

    {{-- Key Stats Cards --}}
    @php
        $loanPercent = $p->total_project_cost > 0 ? ($p->total_loan_amount / $p->total_project_cost) * 100 : 0;
        $avgLoan = $p->number_of_beneficiaries > 0 ? ($p->total_loan_amount / $p->number_of_beneficiaries) : 0;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-widget" style="border-right-color: #10b981;">
                <span class="label">إجمالي مبلغ القرض</span>
                <span class="value text-success">{{ number_format($p->total_loan_amount, 2) }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget" style="border-right-color: #3b82f6;">
                <span class="label">إجمالي تكلفة المشروع</span>
                <span class="value">{{ number_format($p->total_project_cost, 2) }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget" style="border-right-color: #f59e0b;">
                <span class="label">نسبة القرض / التكلفة</span>
                <span class="value text-warning">{{ number_format($loanPercent, 1) }}%</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-widget" style="border-right-color: #8b5cf6;">
                <span class="label">إجمالي المستفيدين</span>
                <span class="value">{{ number_format($p->number_of_beneficiaries) }} مستفيد</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Right Column (Main Content) --}}
        <div class="col-lg-8">
            
            {{-- Project Identity & Timeline --}}
            <div class="main-card">
                <div class="card-header"><i class="fas fa-file-alt"></i> تفاصيل بيانات المشروع</div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="label">الجهة المنشئة / المقدمة</div>
                        <div class="value">{{ $proj ? $proj->creator_entity_name : ($p->submitting_entity ?? '—') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="label">تاريخ البداية (ميلادي / هجري)</div>
                        <div class="value">
                            {{ $p->start_date_gregorian ? $p->start_date_gregorian->format('Y-m-d') : '—' }}
                            <span class="text-muted fw-normal mx-1">|</span>
                            {{ $p->start_date_hijri ?? '—' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="label">تاريخ النهاية (ميلادي / هجري)</div>
                        <div class="value">
                            {{ $p->end_date_gregorian ? $p->end_date_gregorian->format('Y-m-d') : '—' }}
                            <span class="text-muted fw-normal mx-1">|</span>
                            {{ $p->end_date_hijri ?? '—' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="label">المجموعات المستهدفة</div>
                        <div class="value">
                            @if($proj && $proj->beneficiaryGroups->isNotEmpty())
                                @foreach($proj->beneficiaryGroups as $group)
                                    <span class="badge bg-light text-primary border me-1">{{ $group->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">غير محدد</span>
                            @endif
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="label">متوسط القرض للمستفيد الواحد</div>
                        <div class="value text-success fw-bold">{{ number_format($avgLoan, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Financing Table (Full Data Display) --}}
            <div class="main-card">
                <div class="card-header"><i class="fas fa-coins"></i> تفاصيل هيكل التمويل المعتمد</div>
                <div class="table-responsive">
                    <table class="table table-premium">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>مصدر التمويل</th>
                                <th>نوع التمويل</th>
                                <th class="text-end">المبلغ المخصص</th>
                                <th class="text-center">العملة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($proj && $proj->financings->isNotEmpty())
                                @foreach($proj->financings as $i => $fin)
                                <tr>
                                    <td class="text-muted fw-bold">{{ $i + 1 }}</td>
                                    <td>{{ optional($fin->fundingSource)->name }}</td>
                                    <td>{{ optional($fin->financingType)->name }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($fin->financing_amount, 2) }}</td>
                                    <td class="text-center small">{{ $fin->currency ?? 'ريال' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد بيانات تمويل مسجلة</td></tr>
                            @endif
                        </tbody>
                        @if($p->total_loan_amount > 0)
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">الإجمالي الكلي للقروض:</td>
                                <td class="text-end fw-bold text-success fs-6">{{ number_format($p->total_loan_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Location Table (Full Data Display) --}}
            <div class="main-card">
                <div class="card-header"><i class="fas fa-map-marked-alt"></i> النطاق الجغرافي للمشروع</div>
                <div class="table-responsive">
                    <table class="table table-premium">
                        <thead>
                            <tr>
                                <th>المحافظة</th>
                                <th>المديرية</th>
                                <th>العزلة / المنطقة</th>
                                <th>القرية / المحلة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($proj && $proj->locations->isNotEmpty())
                                @foreach($proj->locations as $loc)
                                <tr>
                                    <td class="fw-bold">{{ optional($loc->governorate)->name }}</td>
                                    <td>{{ optional($loc->directorate)->name }}</td>
                                    <td>{{ optional($loc->subArea)->name ?? '—' }}</td>
                                    <td class="text-muted small">{{ optional($loc->village)->name ?? '—' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="4" class="text-center py-4 text-muted">لا توجد بيانات مواقع مسجلة</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Left Column (Sidebar) --}}
        <div class="col-lg-4">
            
            {{-- Processing Panel --}}
            <div class="status-panel shadow-sm">
                <h6 class="fw-bold mb-3 border-bottom pb-2">معالجة القرض</h6>
                
                <div class="mb-4 text-center">
                    <div class="badge-soft bg-{{ $p->status_badge }}-subtle text-{{ $p->status_badge }} py-3 w-100 d-block border border-{{ $p->status_badge }}">
                        <i class="fas {{ $p->status_icon }} fa-lg me-2"></i>
                        <span class="fs-6">{{ $p->status_label }}</span>
                    </div>
                    @if($p->processed_at)
                    <p class="mt-2 mb-0 small text-muted">
                        تمت المعالجة في: <strong>{{ $p->processed_at->format('Y-m-d') }}</strong><br>
                        بواسطة: {{ $p->processor->name ?? 'موظف القروض' }}
                    </p>
                    @endif
                </div>

                @can('empowerment.edit', $p)
                <form id="empowerment-process-form" method="POST" action="{{ route('projects.empowerment.update-status', $p->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">تحديث الحالة:</label>
                        <select name="status" class="form-select border-2 shadow-none">
                            @foreach($statuses as $key => $s)
                                <option value="{{ $key }}" {{ $p->status === $key ? 'selected' : '' }}>
                                    {{ $s['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">إجمالي مبلغ القرض:</label>
                            <input type="number" step="0.01" name="total_loan_amount" id="form_total_loan" 
                                   class="form-control border-2 shadow-none" value="{{ $p->total_loan_amount }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">عدد المستفيدين:</label>
                            <input type="number" name="number_of_beneficiaries" id="form_beneficiary_count" 
                                   class="form-control border-2 shadow-none" value="{{ $p->number_of_beneficiaries }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-success">نصيب المستفيد الواحد (محسوب):</label>
                        <div class="input-group">
                            <input type="text" id="form_share_per_beneficiary" class="form-control border-2 shadow-none bg-light fw-bold text-success" 
                                   value="{{ number_format($avgLoan, 2) }}" readonly>
                            <span class="input-group-text bg-light border-2">ريال</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ملاحظات الموظف المختص:</label>
                        <textarea name="notes" class="form-control border-2 shadow-none" rows="3" 
                                  placeholder="أضف أي ملاحظات أو مسوغات لقرار الحالة...">{{ $p->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">
                        <i class="fas fa-save me-1"></i> حفظ وتحديث البيانات
                    </button>
                </form>
                @else
                <div class="alert alert-light border small text-muted text-center py-4 mb-0">
                    <i class="fas fa-lock mb-2 d-block fa-lg"></i>
                    ليس لديك صلاحية لتعديل حالة معالجة القرض.
                </div>
                @endcan
            </div>

            {{-- Master Details Sidebar --}}
            <div class="main-card mt-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <i class="fas fa-list-ul text-primary me-2"></i>
                    <span class="fw-bold text-dark">بيانات المشروع التفصيلية</span>
                </div>
                <div class="list-group list-group-flush">
                    @can('empowerment.beneficiaries.view', $p)
                    <a href="{{ route('projects.empowerment.beneficiaries.index', $p->id) }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0">
                        <div>
                            <i class="fas fa-users text-primary me-2"></i>
                            <span class="fw-medium text-dark">إدارة سجل المستفيدين</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill
                                {{ $beneficiaryCount >= $p->number_of_beneficiaries ? 'bg-danger' : 'bg-primary' }}">
                                {{ $beneficiaryCount }} / {{ $p->number_of_beneficiaries }}
                            </span>
                            <i class="fas fa-chevron-left text-muted small"></i>
                        </div>
                    </a>
                    @endcan
                    {{-- يمكن إضافة روابط أخرى هنا لاحقاً --}}
                </div>
            </div>


            {{-- Summary Sidebar Note --}}
            <div class="mt-4 p-3 rounded-3" style="background: #eff6ff; border: 1px solid #dbeafe;">
                <h6 class="small fw-bold text-primary mb-3">ملخص مالي سريع</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">فجوة التمويل:</span>
                    <span class="small fw-bold">{{ number_format($p->total_project_cost - $p->total_loan_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">متوسط القرض/المستفيد:</span>
                    <span class="small fw-bold text-success">{{ number_format($avgLoan, 2) }}</span>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $loanPercent }}%"></div>
                </div>
            </div>

        </div>
    </div>
</div>
@push('scripts')
<script>
$(document).ready(function() {
    const $loanInput = $('#form_total_loan');
    const $countInput = $('#form_beneficiary_count');
    const $shareDisplay = $('#form_share_per_beneficiary');

    function calculateShare() {
        const loan = parseFloat($loanInput.val()) || 0;
        const count = parseInt($countInput.val()) || 0;
        const share = count > 0 ? (loan / count) : 0;
        
        $shareDisplay.val(share.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $loanInput.on('input', calculateShare);
    $countInput.on('input', calculateShare);
});
</script>
@endpush
@endsection