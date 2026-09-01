@extends('layouts.app')

@section('title', 'التقرير المالي الموحد (ERPNext)')

@section('content')
<x-index-page title="التقرير المالي الموحد (ERPNext)" icon="chart-pie">
    @slot('description')
        التقرير المالي المسترد مباشرة من النظام المحاسبي ERPNext للمشاريع ومراكز التكلفة
    @endslot

    {{-- ────────────────────────── أزرار التحكم بالتقرير ────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h5 class="fw-bold mb-1 text-dark">التقرير المالي العام للمشاريع</h5>
            <p class="text-muted small mb-0">تقرير الربح والخسارة وتفاصيل الحسابات والأستاذ العام</p>
        </div>
        <div>
            @if(auth()->user()->can('reports.financial_erpnext.print') || auth()->user()->can('reports.print'))
            <button onclick="window.print();" class="btn btn-outline-primary fw-bold px-4 shadow-sm auth-perm-reports-financial_erpnext-print">
                <i class="fa-solid fa-print me-2"></i> طباعة التقرير
            </button>
            @endif
        </div>
    </div>

    {{-- ────────────────────────── منطقة الفلاتر ────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="mb-0 fw-bold text-muted"><i class="fa-solid fa-sliders me-2"></i> فلاتر النظام المالي</h6>
        </div>
        <div class="card-body py-3 px-4 border-top">
            <form id="financialFiltersForm" class="row g-3 align-items-end">
                {{-- الجهة / الشركة --}}
                <div class="col-md-3">
                    <label for="filter_company" class="form-label small fw-bold text-muted mb-1">الجهة (Company)</label>
                    @if($isAdmin)
                        <select id="filter_company" name="company" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->erpnext_id ?: $company->name }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <select id="filter_company" name="company" class="form-select form-select-sm" disabled>
                            @foreach($companies as $company)
                                <option value="{{ $company->erpnext_id ?: $company->name }}" selected>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="company" value="{{ $userEntityErpId ?? $userEntityName }}">
                    @endif
                </div>

                {{-- المشروع --}}
                <div class="col-md-3">
                    <label for="filter_project" class="form-label small fw-bold text-muted mb-1">المشروع</label>
                    <select id="filter_project" name="project" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->erpnext_project_id }}">{{ $p->project_name }} ({{ $p->erpnext_project_id }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- نوع التصفية --}}
                <div class="col-md-2">
                    <label for="filter_based_on" class="form-label small fw-bold text-muted mb-1">تصفية بناءً على</label>
                    <select id="filter_based_on" name="filter_based_on" class="form-select form-select-sm">
                        <option value="Fiscal Year">السنة المالية</option>
                        <option value="Date Range">فترة زمنية</option>
                    </select>
                </div>

                {{-- السنة المالية --}}
                <div class="col-md-2" id="fiscal_year_wrapper">
                    <label for="filter_fiscal_year" class="form-label small fw-bold text-muted mb-1">السنة المالية</label>
                    <select id="filter_fiscal_year" name="from_fiscal_year" class="form-select form-select-sm">
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                {{-- التكرار --}}
                <div class="col-md-2" id="periodicity_wrapper">
                    <label for="filter_periodicity" class="form-label small fw-bold text-muted mb-1">التكرار</label>
                    <select id="filter_periodicity" name="periodicity" class="form-select form-select-sm">
                        <option value="Yearly" selected>سنوي</option>
                        <option value="Half-Yearly">نصف سنوي</option>
                        <option value="Quarterly">ربع سنوي</option>
                        <option value="Monthly">شهري</option>
                    </select>
                </div>

                {{-- تاريخ البداية --}}
                <div class="col-md-2 d-none" id="start_date_wrapper">
                    <label for="filter_start_date" class="form-label small fw-bold text-muted mb-1">من تاريخ</label>
                    <input type="date" id="filter_start_date" name="period_start_date" class="form-control form-control-sm" value="2026-01-01">
                </div>

                {{-- تاريخ النهاية --}}
                <div class="col-md-2 d-none" id="end_date_wrapper">
                    <label for="filter_end_date" class="form-label small fw-bold text-muted mb-1">إلى تاريخ</label>
                    <input type="date" id="filter_end_date" name="period_end_date" class="form-control form-control-sm" value="2026-12-31">
                </div>

                {{-- أزرار --}}
                <div class="col-auto d-flex gap-2 ms-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> عرض التقرير
                    </button>
                    <button type="button" id="btnResetFilters" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fa-solid fa-rotate-right me-1"></i> إعادة تعيين
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ────────────────────────── الخلاصة المالية (P&L Summary) ────────────────────────── --}}
    <div class="row g-3 mb-4" id="plSummarySection">
        {{-- Income --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="opacity-75 small mb-2" id="income_label">إجمالي الإيرادات</h6>
                            <h3 id="income_val" class="fw-bold mb-0">0 ر.ي</h3>
                        </div>
                        <div class="bg-white-50 p-3 rounded-circle">
                            <i class="fa-solid fa-arrow-trend-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Expenses --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="opacity-75 small mb-2" id="expense_label">إجمالي المصروفات</h6>
                            <h3 id="expense_val" class="fw-bold mb-0">0 ر.ي</h3>
                        </div>
                        <div class="bg-white-50 p-3 rounded-circle">
                            <i class="fa-solid fa-arrow-trend-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Net Profit --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white" id="profit_card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="opacity-75 small mb-2" id="profit_label">صافي الربح / الفارق</h6>
                            <h3 id="profit_val" class="fw-bold mb-0">0 ر.ي</h3>
                        </div>
                        <div class="bg-white-50 p-3 rounded-circle">
                            <i class="fa-solid fa-scale-balanced fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────── تبويبات عرض التقرير التفصيلي ────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white p-0 border-bottom">
            <ul class="nav nav-tabs nav-fill border-0" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold border-0 border-bottom" id="pl-statement-tab" data-bs-toggle="tab" data-bs-target="#pl-statement-panel" type="button" role="tab" aria-controls="pl-statement-panel" aria-selected="true">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> بيان الأرباح والخسائر الشامل (P&L Statement)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold border-0 border-bottom" id="pl-tab" data-bs-toggle="tab" data-bs-target="#pl-panel" type="button" role="tab" aria-controls="pl-panel" aria-selected="false">
                        <i class="fa-solid fa-chart-pie me-2"></i> تقرير الأرباح والخسائر بالتفصيل
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold border-0 border-bottom" id="gl-tab" data-bs-toggle="tab" data-bs-target="#gl-panel" type="button" role="tab" aria-controls="gl-panel" aria-selected="false">
                        <i class="fa-solid fa-list-check me-2"></i> دفتر الأستاذ العام (General Ledger)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="reportTabsContent">
                {{-- 1. بيان الأرباح والخسائر الشامل (P&L Statement) --}}
                <div class="tab-pane fade show active p-4" id="pl-statement-panel" role="tabpanel" aria-labelledby="pl-statement-tab">
                    <div id="plsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">جاري تحميل بيان الأرباح والخسائر...</div>
                    </div>
                    <div id="plsEmpty" class="d-none text-center py-5 text-muted">
                        لا توجد بيانات مالية في هذه الفترة.
                    </div>
                    <div id="plsTableWrapper" class="table-responsive d-none">
                        <table class="table table-bordered align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الحساب</th>
                                    <th>العملة</th>
                                    <th class="text-end">القيمة</th>
                                </tr>
                            </thead>
                            <tbody id="plsTableBody">
                                {{-- JS elements --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. تبويب الأرباح والخسائر بالتفصيل --}}
                <div class="tab-pane fade p-4" id="pl-panel" role="tabpanel" aria-labelledby="pl-tab">
                    <div id="plLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">جاري تحميل البيانات المالية...</div>
                    </div>
                    <div id="plEmpty" class="d-none text-center py-5 text-muted">
                        لا توجد تفاصيل للحسابات في هذه الفترة.
                    </div>
                    <div id="plTableWrapper" class="table-responsive d-none">
                        <table class="table table-bordered align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الحساب</th>
                                    <th>نوع المصروف</th>
                                    <th>العملة</th>
                                    <th class="text-end">القيمة</th>
                                </tr>
                            </thead>
                            <tbody id="plTableBody">
                                {{-- JS elements --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. تبويب دفتر الأستاذ العام --}}
                <div class="tab-pane fade p-4" id="gl-panel" role="tabpanel" aria-labelledby="gl-tab">
                    <div id="glLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">جاري جلب قيود الأستاذ العام...</div>
                    </div>
                    <div id="glEmpty" class="d-none text-center py-5 text-muted">
                        لا توجد قيود أستاذ عام تطابق الفلاتر.
                    </div>
                    <div id="glTableWrapper" class="table-responsive d-none">
                        <table class="table table-hover table-bordered align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>تاريخ القيد</th>
                                    <th>رقم السند (Voucher)</th>
                                    <th>الحساب</th>
                                    <th>نوع المصروف</th>
                                    <th>المشروع</th>
                                    <th class="text-end">مدين (Debit)</th>
                                    <th class="text-end">دائن (Credit)</th>
                                    <th>البيان (Remarks)</th>
                                </tr>
                            </thead>
                            <tbody id="glTableBody">
                                {{-- JS elements --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-index-page>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    const API_URL = '{{ route("projects.reports.financial_erpnext_data") }}';

    // التبديل بين تصفية السنة المالية ونطاق التاريخ
    document.getElementById('filter_based_on').addEventListener('change', function () {
        const val = this.value;
        if (val === 'Fiscal Year') {
            document.getElementById('fiscal_year_wrapper').classList.remove('d-none');
            document.getElementById('periodicity_wrapper').classList.remove('d-none');
            document.getElementById('start_date_wrapper').classList.add('d-none');
            document.getElementById('end_date_wrapper').classList.add('d-none');
        } else {
            document.getElementById('fiscal_year_wrapper').classList.add('d-none');
            document.getElementById('periodicity_wrapper').classList.add('d-none');
            document.getElementById('start_date_wrapper').classList.remove('d-none');
            document.getElementById('end_date_wrapper').classList.remove('d-none');
        }
    });

    function collectFilters() {
        const form = document.getElementById('financialFiltersForm');
        const data = new FormData(form);
        const filters = {};
        for (const [key, value] of data.entries()) {
            if (value && value.trim() !== '') {
                filters[key] = value.trim();
            }
        }
        
        // إذا كان نوع الفلتر سنة مالية، نقوم بحساب تواريخ البداية والنهاية تلقائياً للسنة المحددة
        if (filters.filter_based_on === 'Fiscal Year') {
            const year = filters.from_fiscal_year || '2026';
            filters.period_start_date = `${year}-01-01`;
            filters.period_end_date = `${year}-12-31`;
            filters.to_fiscal_year = year;
        }
        return filters;
    }

    function fetchFinancialReport() {
        const filters = collectFilters();
        const params = new URLSearchParams(filters);

        // إظهار اللودينق
        document.getElementById('plsLoading').classList.remove('d-none');
        document.getElementById('plsTableWrapper').classList.add('d-none');
        document.getElementById('plsEmpty').classList.add('d-none');

        document.getElementById('plLoading').classList.remove('d-none');
        document.getElementById('plTableWrapper').classList.add('d-none');
        document.getElementById('plEmpty').classList.add('d-none');
        
        document.getElementById('glLoading').classList.remove('d-none');
        document.getElementById('glTableWrapper').classList.add('d-none');
        document.getElementById('glEmpty').classList.add('d-none');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`${API_URL}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(json => {
            if (!json.success) {
                alert(json.message || 'فشل تحميل البيانات المالية.');
                return;
            }
            renderPLSummary(json.pl_summary);
            renderPLStatement(json.pl_statement);
            renderPLExpenses(json.pl_expenses);
            renderGLReport(json.gl_report);
        })
        .catch(err => {
            console.error('Financial report error:', err);
            alert('حدث خطأ في تحميل التقرير المالي من ERPNext.');
        });
    }

    // 1.5 رسم بيان الأرباح والخسائر الشامل (P&L Statement)
    function renderPLStatement(plData) {
        document.getElementById('plsLoading').classList.add('d-none');
        const items = plData.result || plData.message?.result || plData || [];
        if (!items.length) {
            document.getElementById('plsEmpty').classList.remove('d-none');
            return;
        }

        document.getElementById('plsTableWrapper').classList.remove('d-none');
        const tbody = document.getElementById('plsTableBody');

        tbody.innerHTML = items.map(row => {
            if (!row.account && !row.account_name) return '';
            
            const indentStyle = row.indent ? `style="padding-right: ${row.indent * 20}px;"` : '';
            const isTotal = row.account && (row.account.includes('Total') || row.account.includes('Profit') || row.account.includes('Opening'));
            const rowClass = isTotal ? 'fw-bold table-light' : '';
            
            const keys = Object.keys(row);
            let val = row.total || 0;
            keys.forEach(k => {
                if (k.startsWith('dec_') || k.match(/^\d{4}$/)) {
                    val = row[k];
                }
            });

            return `
            <tr class="${rowClass}">
                <td ${indentStyle}>${escapeHtml(row.account_name || row.account || '-')}</td>
                <td>${escapeHtml(row.currency || 'YER')}</td>
                <td class="text-end fw-bold">${Number(val || 0).toLocaleString()}</td>
            </tr>`;
        }).join('');
    }

    // 1. رسم بطاقات الخلاصة المالية
    function renderPLSummary(summary) {
        let income = 0;
        let expense = 0;
        let profit = 0;
        let currency = 'ر.ي';

        if (Array.isArray(summary)) {
            summary.forEach(item => {
                const label = item.label ? item.label.toLowerCase() : '';
                if (label.includes('income')) {
                    income = item.value;
                    currency = item.currency || 'ر.ي';
                } else if (label.includes('expense')) {
                    expense = item.value;
                } else if (label.includes('profit')) {
                    profit = item.value;
                }
            });
        }

        document.getElementById('income_val').textContent = Number(income).toLocaleString() + ' ' + currency;
        document.getElementById('expense_val').textContent = Number(expense).toLocaleString() + ' ' + currency;
        document.getElementById('profit_val').textContent = Number(profit).toLocaleString() + ' ' + currency;

        // تعديل كرت الربح حسب القيمة
        const profitCard = document.getElementById('profit_card');
        if (profit >= 0) {
            profitCard.className = 'card border-0 shadow-sm rounded-3 bg-primary text-white';
        } else {
            profitCard.className = 'card border-0 shadow-sm rounded-3 bg-warning text-white';
        }
    }

    // 2. رسم جدول الربح والخسارة بالتفصيل
    function renderPLExpenses(plData) {
        document.getElementById('plLoading').classList.add('d-none');
        const items = plData.result || [];
        if (!items.length) {
            document.getElementById('plEmpty').classList.remove('d-none');
            return;
        }

        document.getElementById('plTableWrapper').classList.remove('d-none');
        const tbody = document.getElementById('plTableBody');

        tbody.innerHTML = items.map(row => {
            // تجاهل الحقول الفارغة تماماً
            if (!row.account && !row.account_name) return '';
            
            const indentStyle = row.indent ? `style="padding-right: ${row.indent * 20}px;"` : '';
            const isTotal = row.account && (row.account.includes('Total') || row.account.includes('Profit'));
            const rowClass = isTotal ? 'fw-bold table-light' : '';
            
            // البحث عن قيمة الحساب للسنة (مثل dec_2026 أو total)
            const keys = Object.keys(row);
            let val = row.total || 0;
            // إذا كانت هناك مفاتيح سنوات أخرى، نأخذ القيمة منها
            keys.forEach(k => {
                if (k.startsWith('dec_') || k.match(/^\d{4}$/)) {
                    val = row[k];
                }
            });

            return `
            <tr class="${rowClass}">
                <td ${indentStyle}>${escapeHtml(row.account_name || row.account || '-')}</td>
                <td>${escapeHtml(row.claim_expense_type || '-')}</td>
                <td>${escapeHtml(row.currency || '-')}</td>
                <td class="text-end fw-bold">${Number(val || 0).toLocaleString()}</td>
            </tr>`;
        }).join('');
    }

    // 3. رسم دفتر الأستاذ العام
    function renderGLReport(glData) {
        document.getElementById('glLoading').classList.add('d-none');
        const items = glData.result || [];
        if (!items.length) {
            document.getElementById('glEmpty').classList.remove('d-none');
            return;
        }

        document.getElementById('glTableWrapper').classList.remove('d-none');
        const tbody = document.getElementById('glTableBody');

        tbody.innerHTML = items.map(row => {
            const isTotal = row.account && (row.account.includes('Total') || row.account.includes('Closing') || row.account.includes('Opening'));
            const rowClass = isTotal ? 'fw-bold table-light' : '';

            return `
            <tr class="${rowClass}">
                <td>${escapeHtml(row.posting_date || '-')}</td>
                <td>${escapeHtml(row.voucher_no || '-')}</td>
                <td>${escapeHtml(row.account || '-')}</td>
                <td>${escapeHtml(row.claim_expense_type || '-')}</td>
                <td>${escapeHtml(row.project || '-')}</td>
                <td class="text-end text-danger">${row.debit ? Number(row.debit).toLocaleString() : '-'}</td>
                <td class="text-end text-success">${row.credit ? Number(row.credit).toLocaleString() : '-'}</td>
                <td class="text-wrap" style="max-width: 250px;">${escapeHtml(row.remarks || '-')}</td>
            </tr>`;
        }).join('');
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    document.getElementById('financialFiltersForm').addEventListener('submit', function (e) {
        e.preventDefault();
        fetchFinancialReport();
    });

    document.getElementById('btnResetFilters').addEventListener('click', function () {
        document.getElementById('financialFiltersForm').reset();
        document.getElementById('filter_based_on').dispatchEvent(new Event('change'));
        fetchFinancialReport();
    });

    // تحميل التقرير عند فتح الصفحة
    document.addEventListener('DOMContentLoaded', fetchFinancialReport);

})();
</script>
@endsection
