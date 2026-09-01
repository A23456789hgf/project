@extends('layouts.app')

@section('title', 'تقرير الموازنات المالية')

@section('content')
<x-index-page title="تقرير الموازنات المالية" icon="chart-pie">
    @slot('description')
        تقرير تفصيلي بموازنات الجهات والمشاريع المستوردة من ERPNext
    @endslot

    {{-- ────────────────────────── أزرار التحكم بالتقرير ────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1 text-dark">تقرير الموازنات</h5>
            <p class="text-muted small mb-0">يمكنك تصفية وعرض الموازنات المالية للجهات والمشاريع وطباعتها</p>
        </div>
        <div>
            <button onclick="window.print();" class="btn btn-outline-primary fw-bold px-4 shadow-sm">
                <i class="fa-solid fa-print me-2"></i> طباعة التقرير
            </button>
        </div>
    </div>

    {{-- ────────────────────────── بطاقات الإحصاءات للتقرير ────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="opacity-75 small mb-2">إجمالي الموازنات المعتمدة</h6>
                            <h3 id="totalBudgetSum" class="fw-bold mb-0">0 ر.ي</h3>
                        </div>
                        <div class="bg-white-50 p-3 rounded-circle">
                            <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-2">عدد الموازنات المدرجة</h6>
                            <h3 id="totalBudgetCount" class="fw-bold mb-0 text-primary">0</h3>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="fa-solid fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-2">الموازنات المعتمدة (المرحلة)</h6>
                            <h3 id="approvedBudgetCount" class="fw-bold mb-0 text-success">0</h3>
                        </div>
                        <div class="bg-success-subtle text-success p-3 rounded-circle">
                            <i class="fa-solid fa-square-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────── منطقة الفلاتر ────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="mb-0 fw-bold text-muted"><i class="fa-solid fa-sliders me-2"></i> فلاتر التقرير</h6>
        </div>
        <div class="card-body py-3 px-4 border-top">
            <form id="budgetFiltersForm" class="row g-3 align-items-end">
                {{-- الجهة / الشركة --}}
                <div class="col-md-3">
                    <label for="filter_company" class="form-label small fw-bold text-muted mb-1">الجهة</label>
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
                    <label for="filter_project" class="form-label small fw-bold text-muted mb-1">رقم / اسم المشروع</label>
                    <input type="text" id="filter_project" name="project" class="form-control form-control-sm"
                           placeholder="مثال: PROJ-0003">
                </div>

                {{-- Budget Against --}}
                <div class="col-md-2">
                    <label for="filter_budget_against" class="form-label small fw-bold text-muted mb-1">الميزانية مقابل</label>
                    <select id="filter_budget_against" name="budget_against" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="Cost Center">مركز تكلفة</option>
                        <option value="Project">مشروع</option>
                    </select>
                </div>

                {{-- السنة المالية (من) --}}
                <div class="col-md-2">
                    <label for="filter_from_fiscal_year" class="form-label small fw-bold text-muted mb-1">السنة المالية (من)</label>
                    <input type="text" id="filter_from_fiscal_year" name="from_fiscal_year"
                           class="form-control form-control-sm" placeholder="مثال: 2024">
                </div>

                {{-- السنة المالية (إلى) --}}
                <div class="col-md-2">
                    <label for="filter_to_fiscal_year" class="form-label small fw-bold text-muted mb-1">السنة المالية (إلى)</label>
                    <input type="text" id="filter_to_fiscal_year" name="to_fiscal_year"
                           class="form-control form-control-sm" placeholder="مثال: 2024">
                </div>

                {{-- الحالة --}}
                <div class="col-md-2">
                    <label for="filter_docstatus" class="form-label small fw-bold text-muted mb-1">الحالة</label>
                    <select id="filter_docstatus" name="docstatus" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="0">مسودة</option>
                        <option value="1">معتمدة</option>
                        <option value="2">ملغاة</option>
                    </select>
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

    {{-- ────────────────────────── جدول الموازنات ────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i>
                بيانات التقرير المالي للموازنة
            </h6>
            <div class="d-flex align-items-center gap-2">
                <select id="perPageSelect" class="form-select form-select-sm" style="width: 80px;">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">

            {{-- Loading Indicator --}}
            <div id="budgetLoadingWrapper" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <div class="mt-3 text-muted">جاري جلب بيانات الموازنات من ERPNext...</div>
            </div>

            {{-- Error State --}}
            <div id="budgetErrorWrapper" class="d-none text-center py-5">
                <i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-3"></i>
                <p id="budgetErrorMsg" class="text-muted mb-0">حدث خطأ في جلب البيانات.</p>
            </div>

            {{-- Empty State --}}
            <div id="budgetEmptyWrapper" class="d-none text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">لا توجد موازنات تطابق معايير البحث.</p>
            </div>

            {{-- Data Table --}}
            <div id="budgetTableWrapper" class="d-none table-responsive">
                <table class="table table-hover mb-0 align-middle text-nowrap compact-table">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="px-3 py-2">#</th>
                            <th class="px-3 py-2">اسم الموازنة</th>
                            <th class="px-3 py-2">الجهة (Company)</th>
                            <th class="px-3 py-2">Budget Against</th>
                            <th class="px-3 py-2">مركز التكلفة / المشروع</th>
                            <th class="px-3 py-2">السنة المالية</th>
                            <th class="px-3 py-2">توزيع الموازنة</th>
                            <th class="px-3 py-2">الحالة</th>
                            <th class="px-3 py-2 text-center">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody id="budgetTableBody">
                        {{-- Populated by JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div id="budgetPaginationWrapper" class="card-footer bg-white border-top d-none py-3 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <span id="budgetPaginationInfo" class="text-muted small"></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="budgetPagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- ────────────────────── Modal: تفاصيل الموازنة ────────────────────── --}}
    <div class="modal fade" id="budgetDetailModal" tabindex="-1" aria-labelledby="budgetDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-3">
                    <h6 class="modal-title mb-0" id="budgetDetailModalLabel">
                        <i class="fa-solid fa-chart-pie me-2"></i>
                        تفاصيل الموازنة
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body p-4" id="budgetDetailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2 text-muted">جاري تحميل التفاصيل...</div>
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

    const API_URL = '/api/budgets';

    let currentPage   = 1;
    let currentFilters = {};
    let perPage       = 20;

    // ─── الحالة → نص ──────────────────────────────────────────────────────────
    function docstatusLabel(status) {
        const map = { '0': 'مسودة', '1': 'معتمدة', '2': 'ملغاة' };
        const cls  = { '0': 'bg-secondary', '1': 'bg-success', '2': 'bg-danger' };
        return `<span class="badge ${cls[String(status)] || 'bg-light text-dark'}">${map[String(status)] || status}</span>`;
    }

    // ─── البحث/الفلاتر من الـ Form ────────────────────────────────────────────
    function collectFilters() {
        const form = document.getElementById('budgetFiltersForm');
        const data = new FormData(form);
        const filters = {};
        for (const [key, value] of data.entries()) {
            if (value && value.trim() !== '') {
                filters[key] = value.trim();
            }
        }
        return filters;
    }

    // ─── جلب البيانات من API ──────────────────────────────────────────────────
    function fetchBudgets(page = 1) {
        currentPage = page;

        showState('loading');

        const params = new URLSearchParams({
            ...currentFilters,
            page: page,
            per_page: perPage,
        });

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
                showError(json.message || 'حدث خطأ غير متوقع.');
                return;
            }
            const data = json.data || [];
            renderTable(data);
            renderPagination(json.pagination || {});
            
            // Calculate and set stats
            const totalCount = json.pagination?.total ?? data.length;
            document.getElementById('totalBudgetCount').textContent = totalCount;
            
            const approvedCount = data.filter(b => String(b.docstatus) === '1').length;
            document.getElementById('approvedBudgetCount').textContent = approvedCount;
            
            const totalSum = data.reduce((sum, b) => sum + (parseFloat(b.budget_amount) || 0), 0);
            document.getElementById('totalBudgetSum').textContent = totalSum.toLocaleString() + ' ر.ي';
        })
        .catch(err => {
            console.error('Budget fetch error:', err);
            showError('فشل الاتصال بالخادم. يرجى المحاولة مرة أخرى.');
        });
    }

    // ─── رسم الجدول ───────────────────────────────────────────────────────────
    function renderTable(items) {
        if (!items.length) {
            showState('empty');
            return;
        }
        showState('table');

        const tbody = document.getElementById('budgetTableBody');
        const offset = (currentPage - 1) * perPage;
        tbody.innerHTML = items.map((b, idx) => {
            const target = b.budget_against === 'Project' ? b.project : b.cost_center;
            return `
            <tr>
                <td class="px-3 py-2 text-muted small">${offset + idx + 1}</td>
                <td class="px-3 py-2">
                    <a href="#" class="fw-bold text-primary text-decoration-none btn-budget-detail"
                       data-name="${escapeHtml(b.name)}">
                       ${escapeHtml(b.name)}
                    </a>
                </td>
                <td class="px-3 py-2">${escapeHtml(b.company || '-')}</td>
                <td class="px-3 py-2">${escapeHtml(b.budget_against || '-')}</td>
                <td class="px-3 py-2">${escapeHtml(target || '-')}</td>
                <td class="px-3 py-2">${escapeHtml(b.from_fiscal_year || '')} ${b.to_fiscal_year && b.to_fiscal_year !== b.from_fiscal_year ? '→ ' + escapeHtml(b.to_fiscal_year) : ''}</td>
                <td class="px-3 py-2">${escapeHtml(b.distribution_frequency || '-')}</td>
                <td class="px-3 py-2">${docstatusLabel(b.docstatus)}</td>
                <td class="px-3 py-2 text-center">
                    <button class="btn btn-outline-primary btn-sm btn-budget-detail"
                            data-name="${escapeHtml(b.name)}"
                            title="عرض التفاصيل">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        // Attach detail listeners
        document.querySelectorAll('.btn-budget-detail').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openBudgetDetail(this.dataset.name);
            });
        });
    }

    // ─── Pagination ───────────────────────────────────────────────────────────
    function renderPagination(pagination) {
        const { total = 0, page = 1, pages = 1 } = pagination;

        document.getElementById('budgetPaginationInfo').textContent =
            `عرض ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, total)} من ${total} موازنة`;

        const paginationEl = document.getElementById('budgetPagination');
        const paginationWrapper = document.getElementById('budgetPaginationWrapper');
        paginationWrapper.classList.remove('d-none');

        let html = '';

        html += `<li class="page-item ${page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${page - 1}" aria-label="السابق">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>`;

        const range = 2;
        for (let i = Math.max(1, page - range); i <= Math.min(pages, page + range); i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        html += `<li class="page-item ${page >= pages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${page + 1}" aria-label="التالي">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>`;

        paginationEl.innerHTML = html;

        paginationEl.querySelectorAll('.page-link[data-page]').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const p = parseInt(this.dataset.page);
                if (p >= 1 && p <= pages) fetchBudgets(p);
            });
        });
    }

    // ─── Modal تفاصيل الموازنة ──────────────────────────────────────────────
    function openBudgetDetail(name) {
        const modal = new bootstrap.Modal(document.getElementById('budgetDetailModal'));
        document.getElementById('budgetDetailContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">جاري تحميل التفاصيل...</div>
            </div>`;
        modal.show();

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`${API_URL}/${encodeURIComponent(name)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(json => {
            if (!json.success) {
                document.getElementById('budgetDetailContent').innerHTML = `
                    <div class="alert alert-danger">${json.message || 'حدث خطأ في جلب البيانات.'}</div>`;
                return;
            }
            renderBudgetDetail(json.data);
        })
        .catch(() => {
            document.getElementById('budgetDetailContent').innerHTML = `
                <div class="alert alert-danger">فشل الاتصال بالخادم.</div>`;
        });
    }

    function renderBudgetDetail(b) {
        const skip = ['doctype', 'idx', 'accounts', '__onload', 'name'];
        const labelMap = {
            naming_series: 'سلسلة الترقيم', budget_against: 'نوع الموازنة (مقارنة بـ)',
            company: 'الجهة', cost_center: 'مركز التكلفة', project: 'المشروع',
            from_fiscal_year: 'السنة المالية (من)', to_fiscal_year: 'السنة المالية (إلى)',
            budget_start_date: 'تاريخ البداية', budget_end_date: 'تاريخ النهاية',
            distribution_frequency: 'تكرار التوزيع', budget_amount: 'إجمالي الموازنة',
            distribute_equally: 'توزيع بالتساوي', budget_distribution_total: 'إجمالي التوزيع الفعلي',
            owner: 'المنشئ', creation: 'تاريخ الإنشاء', modified: 'آخر تعديل',
            modified_by: 'معدّل بواسطة', docstatus: 'الحالة',
        };

        const valMap = {
            'Project': 'مشروع',
            'Cost Center': 'مركز تكلفة',
            'Monthly': 'شهري',
            'Quarterly': 'ربع سنوي',
            'Half-Yearly': 'نصف سنوي',
            'Yearly': 'سنوي'
        };

        let rows = '';
        for (const [key, value] of Object.entries(b)) {
            if (skip.includes(key) || value === null || value === '' || value === 0) continue;
            const label = labelMap[key] || key.replace(/_/g, ' ');
            let displayVal = valMap[value] || value;
            if (key === 'docstatus') displayVal = docstatusLabel(value);
            else if (typeof value === 'boolean') displayVal = value ? 'نعم' : 'لا';
            else displayVal = escapeHtml(String(displayVal));
            rows += `
            <div class="col-md-6 col-12">
                <div class="d-flex flex-column border rounded p-2 h-100">
                    <small class="text-muted mb-1">${escapeHtml(label)}</small>
                    <span class="fw-medium">${displayVal}</span>
                </div>
            </div>`;
        }

        // حسابات الموازنة (accounts child table)
        let accountsHtml = '';
        if (Array.isArray(b.accounts) && b.accounts.length) {
            accountsHtml = `
            <h6 class="fw-bold mt-4 mb-3 border-bottom pb-2"><i class="fa-solid fa-list-check me-2"></i>بنود الموازنة</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>الحساب</th>
                            <th>مبلغ الموازنة</th>
                            <th>ق1</th><th>ق2</th><th>ق3</th><th>ق4</th>
                            <th>يناير</th><th>فبراير</th><th>مارس</th>
                            <th>أبريل</th><th>مايو</th><th>يونيو</th>
                            <th>يوليو</th><th>أغسطس</th><th>سبتمبر</th>
                            <th>أكتوبر</th><th>نوفمبر</th><th>ديسمبر</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${b.accounts.map(acc => `
                        <tr>
                            <td>${escapeHtml(acc.account || '-')}</td>
                            <td>${Number(acc.budget_amount || 0).toLocaleString()}</td>
                            <td>${Number(acc.q1_amount || 0).toLocaleString()}</td>
                            <td>${Number(acc.q2_amount || 0).toLocaleString()}</td>
                            <td>${Number(acc.q3_amount || 0).toLocaleString()}</td>
                            <td>${Number(acc.q4_amount || 0).toLocaleString()}</td>
                            <td>${Number(acc.january || 0).toLocaleString()}</td>
                            <td>${Number(acc.february || 0).toLocaleString()}</td>
                            <td>${Number(acc.march || 0).toLocaleString()}</td>
                            <td>${Number(acc.april || 0).toLocaleString()}</td>
                            <td>${Number(acc.may || 0).toLocaleString()}</td>
                            <td>${Number(acc.june || 0).toLocaleString()}</td>
                            <td>${Number(acc.july || 0).toLocaleString()}</td>
                            <td>${Number(acc.august || 0).toLocaleString()}</td>
                            <td>${Number(acc.september || 0).toLocaleString()}</td>
                            <td>${Number(acc.october || 0).toLocaleString()}</td>
                            <td>${Number(acc.november || 0).toLocaleString()}</td>
                            <td>${Number(acc.december || 0).toLocaleString()}</td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        }

        document.getElementById('budgetDetailContent').innerHTML = `
            <div class="row g-3">${rows}</div>
            ${accountsHtml}`;
    }

    // ─── حالات العرض ────────────────────────────────────────────────────────
    function showState(state) {
        document.getElementById('budgetLoadingWrapper').classList.add('d-none');
        document.getElementById('budgetErrorWrapper').classList.add('d-none');
        document.getElementById('budgetEmptyWrapper').classList.add('d-none');
        document.getElementById('budgetTableWrapper').classList.add('d-none');
        document.getElementById('budgetPaginationWrapper').classList.add('d-none');

        if (state === 'loading') document.getElementById('budgetLoadingWrapper').classList.remove('d-none');
        else if (state === 'error')   document.getElementById('budgetErrorWrapper').classList.remove('d-none');
        else if (state === 'empty')   document.getElementById('budgetEmptyWrapper').classList.remove('d-none');
        else if (state === 'table')   document.getElementById('budgetTableWrapper').classList.remove('d-none');
    }

    function showError(msg) {
        document.getElementById('budgetErrorMsg').textContent = msg;
        showState('error');
    }

    // ─── Escape HTML ────────────────────────────────────────────────────────
    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    // ─── Events ─────────────────────────────────────────────────────────────
    document.getElementById('budgetFiltersForm').addEventListener('submit', function (e) {
        e.preventDefault();
        currentFilters = collectFilters();
        fetchBudgets(1);
    });

    document.getElementById('btnResetFilters').addEventListener('click', function () {
        document.getElementById('budgetFiltersForm').reset();
        @unless($isAdmin)
            // إعادة تعيين حقل الجهة المخفي للمستخدم غير الـ Admin
            document.querySelector('input[name="company"][type="hidden"]').value = '{{ $userEntityErpId ?? $userEntityName }}';
        @endunless
        currentFilters = collectFilters();
        fetchBudgets(1);
    });

    document.getElementById('perPageSelect').addEventListener('change', function () {
        perPage = parseInt(this.value);
        fetchBudgets(1);
    });

    // ─── تحميل أولي ────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        currentFilters = collectFilters();
        fetchBudgets(1);
    });

})();
</script>
@endsection
