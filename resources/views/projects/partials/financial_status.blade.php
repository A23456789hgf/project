@php
    $fin = $financialStatus ?? [
        'budget' => 0,
        'actual_expense' => 0,
        'actual_income' => 0,
        'remaining' => 0,
        'execution_percentage' => 0,
        'linked_expenses' => [],
        'unlinked_expenses' => [],
        'gl_entries' => [],
    ];
@endphp

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-chart-line me-2"></i>الوضع المالي للمشروع (ERPNext)
        </h5>
        <div class="d-flex align-items-center gap-2">
            @if(isset($project))
            <a href="{{ route('projects.print-financial', $project->id) }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-print me-1"></i> طباعة ماليّة المشروع
            </a>
            @endif
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                نسبة الصرف: {{ $fin['execution_percentage'] }}%
            </span>
        </div>
    </div>
    <div class="card-body p-4">

        <!-- البطاقات الإحصائية (KPI Cards) -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold mb-1">
                        <i class="fas fa-wallet text-indigo-500 me-1"></i>الميزانية المعتمدة
                    </div>
                    <div class="fs-5 fw-bold text-dark font-monospace">
                        {{ number_format($fin['budget'], 2) }} <small class="fs-6 text-muted">ر.ي</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold mb-1">
                        <i class="fas fa-file-invoice-dollar text-rose-500 me-1"></i>المصروف الفعلي
                    </div>
                    <div class="fs-5 fw-bold text-rose-600 font-monospace">
                        {{ number_format($fin['actual_expense'], 2) }} <small class="fs-6 text-muted">ر.ي</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold mb-1">
                        <i class="fas fa-piggy-bank text-emerald-500 me-1"></i>المتبقي من الميزانية
                    </div>
                    <div class="fs-5 fw-bold {{ $fin['remaining'] >= 0 ? 'text-emerald-600' : 'text-danger' }} font-monospace">
                        {{ number_format($fin['remaining'], 2) }} <small class="fs-6 text-muted">ر.ي</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded-4 border">
                    <div class="text-muted small fw-bold mb-1">
                        <i class="fas fa-percentage text-amber-500 me-1"></i>نسبة الإنجاز المالي
                    </div>
                    <div class="fs-5 fw-bold text-slate-800 font-monospace">
                        {{ $fin['execution_percentage'] }}%
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar {{ $fin['execution_percentage'] > 100 ? 'bg-danger' : 'bg-success' }}" 
                             role="progressbar" 
                             style="width: {{ min(100, $fin['execution_percentage']) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- المصروفات حسب البنود المالية (Financial Items Breakdown) -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="border rounded-4 p-3 bg-white h-100">
                    <h6 class="fw-bold text-slate-700 mb-3 border-bottom pb-2">
                        <i class="fas fa-link text-indigo-500 me-2"></i>المصروفات الفعلية حسب البند المالي (Linked)
                    </h6>
                    @if(!empty($fin['linked_expenses']))
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>البند المالي</th>
                                        <th class="text-end">المبلغ الفعلي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fin['linked_expenses'] as $itemName => $amount)
                                        <tr>
                                            <td class="fw-bold text-slate-700">{{ $itemName }}</td>
                                            <td class="text-end fw-bold text-indigo-600 font-monospace">{{ number_format($amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-3 small">لا توجد مصروفات مرتبطة ببند مالي محدد حالياً.</div>
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-4 p-3 bg-white h-100">
                    <h6 class="fw-bold text-slate-700 mb-3 border-bottom pb-2">
                        <i class="fas fa-unlink text-amber-500 me-2"></i>مصروفات غير مرتبطة ببند مالي (Unlinked)
                    </h6>
                    @if(!empty($fin['unlinked_expenses']))
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>البند/الوصف</th>
                                        <th class="text-end">المبلغ الفعلي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fin['unlinked_expenses'] as $itemName => $amount)
                                        <tr>
                                            <td class="text-slate-600">{{ $itemName }}</td>
                                            <td class="text-end fw-bold text-amber-600 font-monospace">{{ number_format($amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted text-center py-3 small">جميع المصروفات مرتبطة ببند مالي.</div>
                    @endif
                </div>
            </div>
        </div>

        @if(!empty($fin['has_budget_mismatch']))
            <div class="alert alert-warning border border-warning-subtle rounded-3 mb-4 p-3 d-flex align-items-start gap-3">
                <i class="fas fa-exclamation-triangle fs-4 text-warning flex-shrink-0 mt-1"></i>
                <div class="small">
                    <strong class="d-block mb-1 fs-6 text-dark">تنبيه محاسبي بشأن مصدر الميزانية المعتمدة:</strong>
                    الميزانية الكلية المعتمدة للمشروع هي <strong class="text-primary font-monospace">{{ number_format($fin['budget'], 2) }} ر.ي</strong> (المسجلة في اعتماد تكلفة المشروع). بينما إجمالي التقديرات المسجلة لسطور الأنشطة في قاعدة البيانات هو <strong class="text-secondary font-monospace">{{ number_format($fin['activities_total_budget'], 2) }} ر.ي</strong> (يتضمن مسودات وسجلات سابقة). يتم اعتماد <strong class="text-primary">{{ number_format($fin['budget'], 2) }} ر.ي</strong> رسمياً كميزانية وحيدة للمشروع.
                </div>
            </div>
        @endif

        <!-- ميزانية الأنشطة والإجراءات التنفيذية (Executive Activities & Actions Breakdown) -->
        <h6 class="fw-bold text-slate-700 mb-3">
            <i class="fas fa-tasks text-primary me-2"></i>تفاصيل الميزانية المعتمدة للأنشطة والإجراءات التنفيذية
        </h6>
        @if(!empty($fin['activity_breakdown']))
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>النشاط التنفيذي</th>
                            <th>الإجراء التنفيذي</th>
                            <th>البند المالي</th>
                            <th class="text-center">الكمية</th>
                            <th class="text-end">تكلفة الوحدة</th>
                            <th class="text-end">الميزانية المعتمدة (ر.ي)</th>
                            <th class="text-center">حالة الربط في ERPNext</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fin['activity_breakdown'] as $idx => $act)
                            <tr>
                                <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-slate-800">{{ $act['activity_name'] }}</td>
                                <td>{{ $act['action_name'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $act['financial_item_name'] }}</span></td>
                                <td class="text-center font-monospace">{{ number_format($act['quantity'], 2) }}</td>
                                <td class="text-end font-monospace">{{ number_format($act['unit_cost'], 2) }}</td>
                                <td class="text-end fw-bold text-primary font-monospace">{{ number_format($act['approved_budget'], 2) }}</td>
                                <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle">غير مرتبط بـ ERPNext GL (Unlinked)</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-muted text-center py-3 small border rounded-3 mb-4">لا توجد أنشطة وإجراءات تنفيذية مسجلة لهذا المشروع.</div>
        @endif

        <!-- جدول مطابقة ميزانية البنود مع المصروف الفعلي (Item Budget Reconciliation) -->
        @if(!empty($fin['item_reconciliation']))
            <h6 class="fw-bold text-slate-700 mb-3">
                <i class="fas fa-balance-scale text-indigo-600 me-2"></i>جدول مطابقة ميزانية البنود المالية مع المصروف الفعلي (Item Budget Reconciliation)
            </h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>البند المالي</th>
                            <th class="text-end">الميزانية المخصصة للأنشطة (ر.ي)</th>
                            <th class="text-end">المصروف الفعلي المرتبط (Linked)</th>
                            <th class="text-end">الفارق / المتبقي (ر.ي)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fin['item_reconciliation'] as $rec)
                            <tr>
                                <td class="fw-bold text-slate-700">{{ $rec['item_name'] }}</td>
                                <td class="text-end fw-bold text-primary font-monospace">{{ number_format($rec['approved_budget'], 2) }}</td>
                                <td class="text-end fw-bold text-indigo-600 font-monospace">{{ number_format($rec['linked_expense'], 2) }}</td>
                                <td class="text-end fw-bold {{ $rec['variance'] >= 0 ? 'text-emerald-600' : 'text-danger' }} font-monospace">
                                    {{ number_format($rec['variance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- التنبيه الخاص بالأنشطة والإجراءات -->
        <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-center gap-3">
            <i class="fas fa-info-circle fs-4 text-info flex-shrink-0"></i>
            <div class="small">
                <strong>تنبيه فني بشأن ربط الأنشطة والإجراءات:</strong> 
                يتم عرض ميزانية الأنشطة والإجراءات التقديرية المسجلة في النظام. أما المصروف الفعلي الوارد من نظام ERPNext فهو غير مرتبط بمعرف Activity/Procedure لعدم توفره في قيود اليومية، ولذلك يُعرض تحت قسم (Unlinked).
            </div>
        </div>

        <!-- جدول العمليات المالية الخاصة بالمشروع (Project GL Entries) -->
        <h6 class="fw-bold text-slate-700 mb-3">
            <i class="fas fa-list text-primary me-2"></i>قيود اليومية الخاصة بالمشروع من ERPNext
        </h6>
        @if(!empty($fin['gl_entries']))
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم السند</th>
                            <th>الحساب</th>
                            <th>بند النفقة</th>
                            <th class="text-end">مدين</th>
                            <th class="text-end">دائن</th>
                            <th>البيان</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fin['gl_entries'] as $gl)
                            <tr>
                                <td>{{ $gl['posting_date'] ?? '-' }}</td>
                                <td>{{ $gl['voucher_no'] ?? '-' }}</td>
                                <td>{{ $gl['account'] ?? '-' }}</td>
                                <td>{{ $gl['claim_expense_type'] ?? '-' }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($gl['debit'] ?? 0, 2) }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($gl['credit'] ?? 0, 2) }}</td>
                                <td>{{ $gl['remarks'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-muted text-center py-3 small border rounded-3">لا توجد قيود مالية مسجلة لهذا المشروع في ERPNext حتى الآن.</div>
        @endif

    </div>
</div>
