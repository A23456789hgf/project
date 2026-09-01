<!-- Execution Records Display Table -->
@if($allExecutions && $allExecutions->count() > 0)
    <div class="mt-4">
        <h6 class="border-bottom pb-2 mb-3">
            <i class="fas fa-history me-2"></i>سجل التنفيذ 
            <span class="badge bg-info">{{ $allExecutions->count() }}</span>
        </h6>
        
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;" data-action-id="{{ $action->id }}">
            <table class="table table-sm table-bordered table-hover align-middle" id="execution_tbody_{{ $action->id }}">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th width="6%">الرقم</th>
                        <th width="8%">تاريخ البداية</th>
                        <th width="8%">تاريخ النهاية</th>
                        <th width="8%">الحالة</th>
                        <th width="10%">المبلغ الفعلي</th>
                        <th width="10%">المبلغ المنفق</th>
                        <th width="10%">المتبقي</th>
                        <th width="8%">نسبة الإنجاز</th>
                        <th width="8%">% الإنجاز</th>
                        <th width="8%">المستندات</th>
                        <th width="10%">الجودة</th>
                        <th width="6%">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allExecutions as $execution)
                        @php
                            $statusColors = [
                                'not_started' => 'secondary',
                                'in_progress' => 'warning',
                                'delayed' => 'danger',
                                'stalled' => 'dark',
                                'completed' => 'success'
                            ];
                            $statusLabels = [
                                'not_started' => 'لم يبدأ',
                                'in_progress' => 'قيد التنفيذ',
                                'delayed' => 'متأخر',
                                'stalled' => 'متوقف',
                                'completed' => 'مكتمل'
                            ];

                            // Time Quality Calculation
                            $timeQuality = ['status' => 'pending', 'label' => 'قيد التنفيذ', 'color' => 'info'];
                            if ($action->end_date_gregorian && $execution->actual_finish_date_gregorian) {
                                $plannedEnd = \Carbon\Carbon::parse($action->end_date_gregorian);
                                $actualEnd = \Carbon\Carbon::parse($execution->actual_finish_date_gregorian);
                                
                                if ($actualEnd->gt($plannedEnd)) {
                                    $timeQuality = ['status' => 'late', 'label' => 'متأخر', 'color' => 'danger'];
                                } elseif ($actualEnd->lt($plannedEnd)) {
                                    $timeQuality = ['status' => 'ahead', 'label' => 'مبكر', 'color' => 'success'];
                                } else {
                                    $timeQuality = ['status' => 'on_time', 'label' => 'في الوقت', 'color' => 'primary'];
                                }
                            }

                            // Financial Quality Calculation
                            $plannedAmount = $action->costs->sum('total');
                            $actualAmount = $execution->amount_spent;
                            $financialQuality = ['status' => 'pending', 'label' => 'قيد المراجعة', 'color' => 'info'];
                            
                            if ($plannedAmount > 0) {
                                if ($actualAmount > $plannedAmount) {
                                    $financialQuality = ['status' => 'over', 'label' => 'تجاوز', 'color' => 'danger'];
                                } elseif ($actualAmount < $plannedAmount) {
                                    $financialQuality = ['status' => 'under', 'label' => 'توفير', 'color' => 'success'];
                                } else {
                                    $financialQuality = ['status' => 'match', 'label' => 'مطابق', 'color' => 'primary'];
                                }
                            }

                            $executionActualAmount = (float) ($execution->actual_amount ?? 0);
                            $executionSpentAmount = (float) ($execution->amount_spent ?? 0);
                            $financialCompletionPercentage = $executionActualAmount > 0
                                ? ($executionSpentAmount / $executionActualAmount) * 100
                                : 0;
                            $financialCompletionBarWidth = min(max($financialCompletionPercentage, 0), 100);
                        @endphp
                        <tr id="exec-row-{{ $execution->id }}" data-action-id="{{ $action->id }}" data-actual-start-date="{{ $execution->actual_start_date_gregorian?->format('Y-m-d') }}" data-actual-end-date="{{ $execution->actual_finish_date_gregorian?->format('Y-m-d') }}">
                            <td class="text-center font-weight-bold">{{ $execution->sequence ?? '-' }}</td>
                            <td>
                                <input type="date" 
                                       class="form-control form-control-sm actual-start-gregorian" 
                                       value="{{ $execution->actual_start_date_gregorian?->format('Y-m-d') ?? '' }}"
                                       data-hijri-field="actual-start-hijri-display-{{ $execution->id }}"
                                       disabled>
                                <small class="text-muted d-block actual-start-hijri" 
                                       id="actual-start-hijri-display-{{ $execution->id }}" 
                                       style="margin-top: 2px;">{{ $execution->actual_start_date_hijri ?? '-' }}</small>
                            </td>
                            <td>
                                <input type="date" 
                                       class="form-control form-control-sm actual-finish-gregorian" 
                                       value="{{ $execution->actual_finish_date_gregorian?->format('Y-m-d') ?? '' }}"
                                       data-hijri-field="actual-finish-hijri-display-{{ $execution->id }}"
                                       disabled>
                                <small class="text-muted d-block actual-finish-hijri" 
                                       id="actual-finish-hijri-display-{{ $execution->id }}" 
                                       style="margin-top: 2px;">{{ $execution->actual_finish_date_hijri ?? '-' }}</small>
                            </td>
                            <td class="text-center">
                                <select class="form-select form-select-sm exec-status" disabled>
                                    @foreach(['not_started' => 'لم يبدأ', 'in_progress' => 'قيد التنفيذ', 'delayed' => 'متأخر', 'stalled' => 'متوقف', 'completed' => 'مكتمل'] as $key => $label)
                                        <option value="{{ $key }}" {{ $execution->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-end">
                                <input type="number" 
                                       class="form-control form-control-sm actual-amount text-end" 
                                       value="{{ $execution->actual_amount ?? 0 }}"
                                       step="0.01"
                                       disabled>
                            </td>
                            <td class="text-end">
                                <input type="number" 
                                       class="form-control form-control-sm amount-spent text-end" 
                                       value="{{ $execution->amount_spent ?? 0 }}"
                                       step="0.01"
                                       disabled>
                            </td>
                            <td class="text-end">
                                <input type="number" 
                                       class="form-control form-control-sm remaining-amount text-end" 
                                       value="{{ $execution->remaining_amount ?? 0 }}"
                                       step="0.01"
                                       disabled>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="progress w-100" style="height: 20px;">
                                        <div class="progress-bar completion-progress" role="progressbar" 
                                             style="width: {{ $financialCompletionBarWidth }}%"
                                             aria-valuenow="{{ $financialCompletionBarWidth }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 completion-percentage">
                                        {{ number_format($financialCompletionPercentage, 1) }}%
                                    </small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <input type="number" 
                                           class="form-control form-control-sm text-center completion-percentage-field" 
                                           value="{{ $execution->completion_percentage ?? 0 }}"
                                           step="0.1"
                                           min="0"
                                           max="100"
                                           disabled
                                           style="width: 70px;">
                                    <small class="text-muted mt-1">%</small>
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $hasDocuments = (is_array($execution->technical_documents) && count($execution->technical_documents) > 0) ||
                                                   (is_array($execution->financial_documents) && count($execution->financial_documents) > 0);
                                @endphp
                                @if($hasDocuments)
                                    <button class="btn btn-sm btn-outline-info" 
                                            type="button"
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#docs-row-{{ $execution->id }}"
                                            title="عرض المستندات">
                                        <i class="fas fa-file-alt me-1"></i>
                                        {{ (is_array($execution->technical_documents) ? count($execution->technical_documents) : 0) + 
                                           (is_array($execution->financial_documents) ? count($execution->financial_documents) : 0) }}
                                    </button>
                                @else
                                    <span class="badge bg-light text-muted">لا توجد</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-{{ $timeQuality['color'] }} small" style="font-size: 0.7rem;">
                                        <i class="fas fa-clock me-1"></i>{{ $timeQuality['label'] }}
                                    </span>
                                    <span class="badge bg-{{ $financialQuality['color'] }} small" style="font-size: 0.7rem;">
                                        <i class="fas fa-dollar-sign me-1"></i>{{ $financialQuality['label'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm gap-1" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary view-details-btn"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#details-row-{{ $execution->id }}"
                                            title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @php
                                        $isDelayed = $action->end_date_gregorian && $execution->actual_finish_date_gregorian && 
                                                    $execution->actual_finish_date_gregorian > $action->end_date_gregorian;
                                        $delayExplanation = $execution->delayExplanation;
                                    @endphp
                                    @if($isDelayed && !$delayExplanation)
                                        @can('execution.delay-explanation.manage')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger add-delay-justification"
                                                data-exec-id="{{ $execution->id }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#delay-row-{{ $execution->id }}"
                                                title="إضافة شرح التأخير">
                                            <i class="fas fa-plus me-1"></i><i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                        @endcan
                                    @elseif($delayExplanation)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#delay-row-{{ $execution->id }}"
                                                title="عرض شرح التأخير">
                                            <i class="fas fa-hourglass-end"></i>
                                        </button>
                                    @endif
                                    @can('execution.edit')
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning edit-execution-row"
                                            data-exec-id="{{ $execution->id }}"
                                            data-exec-type="executive"
                                            title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan
                                    @can('execution.delete')
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-execution-row"
                                            data-exec-id="{{ $execution->id }}"
                                            data-exec-type="executive"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Details Row (Collapse) -->
                        <tr class="collapse" id="details-row-{{ $execution->id }}">
                            <td colspan="11">
                                <div class="p-3 bg-light-subtle rounded">
                                    {{-- Include Approval Section --}}
                                    @include('projects.partials.execution-approval', [
                                        'execution' => $execution,
                                        'project' => $project,
                                        'executionType' => 'executive'
                                    ])
                                    
                                    <hr class="my-3">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-info-circle me-1"></i>ملخص البيانات
                                            </h6>
                                            <dl class="row small">
                                                <dt class="col-sm-6">المبلغ الفعلي:</dt>
                                                <dd class="col-sm-6">{{ number_format($execution->actual_amount ?? 0, 2) }} ﷼</dd>
                                                
                                                <dt class="col-sm-6">المبلغ المنفق:</dt>
                                                <dd class="col-sm-6">{{ number_format($execution->amount_spent ?? 0, 2) }} ﷼</dd>
                                                
                                                <dt class="col-sm-6">المتبقي:</dt>
                                                <dd class="col-sm-6">{{ number_format($execution->remaining_amount ?? 0, 2) }} ﷼</dd>
                                                
                                                <dt class="col-sm-6">نسبة الإنجاز (المالية):</dt>
                                                <dd class="col-sm-6">{{ number_format($financialCompletionPercentage, 1) }}%</dd>
                                                
                                                <dt class="col-sm-6">نسبة الإنجاز (المدخلة):</dt>
                                                <dd class="col-sm-6">
                                                    <strong>{{ number_format($execution->completion_percentage ?? 0, 1) }}%</strong>
                                                </dd>
                                                
                                                <dt class="col-sm-6">حالة الإجراء:</dt>
                                                <dd class="col-sm-6">
                                                    <span class="badge bg-{{ $statusColors[$execution->status] ?? 'secondary' }}">
                                                        {{ $statusLabels[$execution->status] ?? $execution->status }}
                                                    </span>
                                                </dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-primary mb-2">
                                                <i class="fas fa-calendar me-1"></i>التواريخ
                                            </h6>
                                            <dl class="row small">
                                                <dt class="col-sm-6">البداية (ميلادي):</dt>
                                                <dd class="col-sm-6">{{ $execution->actual_start_date_gregorian?->format('Y-m-d') ?? '-' }}</dd>
                                                
                                                <dt class="col-sm-6">البداية (هجري):</dt>
                                                <dd class="col-sm-6">{{ $execution->actual_start_date_hijri ?? '-' }}</dd>
                                                
                                                <dt class="col-sm-6">النهاية (ميلادي):</dt>
                                                <dd class="col-sm-6">{{ $execution->actual_finish_date_gregorian?->format('Y-m-d') ?? '-' }}</dd>
                                                
                                                <dt class="col-sm-6">النهاية (هجري):</dt>
                                                <dd class="col-sm-6">{{ $execution->actual_finish_date_hijri ?? '-' }}</dd>
                                            </dl>
                                        </div>
                                    </div>
                                    
                                    @if($execution->notes)
                                        <div class="mt-3">
                                            <h6 class="text-info">
                                                <i class="fas fa-sticky-note me-1"></i>الملاحظات
                                            </h6>
                                            <p class="text-muted">{{ $execution->notes }}</p>
                                        </div>
                                    @endif
                                    
                                    @php
                                        $delayExplanation = $execution->delayExplanation;
                                        $isDelayed = $action->end_date_gregorian && $execution->actual_finish_date_gregorian && 
                                                    $execution->actual_finish_date_gregorian > $action->end_date_gregorian;
                                    @endphp
                                    @if($isDelayed || $delayExplanation)
                                        <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                                            <h6 class="text-warning mb-2">
                                                <i class="fas fa-exclamation-triangle me-1"></i>شرح التأخير
                                            </h6>
                                            @if($delayExplanation)
                                                <div class="mb-3">
                                                    <p class="text-muted"><strong>الشرح:</strong></p>
                                                    <p class="bg-white p-2 rounded">{{ $delayExplanation->explanation }}</p>
                                                    <div class="mt-2">
                                                        <strong>حالة الموافقة:</strong>
                                                        <span class="badge bg-{{ $delayExplanation->approval_status === 'approved' ? 'success' : ($delayExplanation->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ $delayExplanation->approval_status === 'approved' ? 'موافق عليه' : ($delayExplanation->approval_status === 'rejected' ? 'مرفوض' : 'قيد الانتظار') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                @if(is_array($delayExplanation->attachments) && count($delayExplanation->attachments) > 0)
                                                    <div class="mt-3 mb-3">
                                                        <strong>المرفقات:</strong>
                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                            @foreach($delayExplanation->attachments as $attachment)
                                                                <a href="{{ Storage::url($attachment) }}" 
                                                                   class="btn btn-sm btn-outline-info"
                                                                   target="_blank"
                                                                   title="تحميل">
                                                                    <i class="fas fa-download me-1"></i>{{ basename($attachment) }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if($delayExplanation->reviewer_notes)
                                                    <div class="mt-3 p-2 bg-white rounded border">
                                                        <strong>ملاحظات المراجع:</strong>
                                                        <p class="text-muted mb-0 mt-2">{{ $delayExplanation->reviewer_notes }}</p>
                                                    </div>
                                                @endif
                                                
                                                @can('execution.delay-explanation.approve')
                                                @if($delayExplanation->approval_status === 'pending')
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <strong class="small d-block mb-2">ملاحظات المراجعة:</strong>
                                                        <textarea class="form-control form-control-sm mb-2 reviewer-notes-input" 
                                                                  data-explanation-id="{{ $delayExplanation->id }}"
                                                                  rows="2" 
                                                                  placeholder="أضف ملاحظاتك (اختياري)..."></textarea>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-success approve-delay-inline"
                                                                    data-explanation-id="{{ $delayExplanation->id }}">
                                                                <i class="fas fa-check me-1"></i>موافقة
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger reject-delay-inline"
                                                                    data-explanation-id="{{ $delayExplanation->id }}">
                                                                <i class="fas fa-times me-1"></i>رفض
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                                @endcan
                                            @else
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="text-muted small mb-0">لم يتم إدخال شرح التأخير حتى الآن</p>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning add-delay-explanation-inline"
                                                            data-exec-id="{{ $execution->id }}"
                                                            title="إضافة شرح التأخير">
                                                        <i class="fas fa-plus me-1"></i>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Documents Row (Collapse) -->
                        @php
                            $hasDocuments = (is_array($execution->technical_documents) && count($execution->technical_documents) > 0) ||
                                           (is_array($execution->financial_documents) && count($execution->financial_documents) > 0);
                        @endphp
                        @if($hasDocuments)
                            <tr class="collapse" id="docs-row-{{ $execution->id }}">
                                <td colspan="11">
                                    <div class="p-3 bg-light-subtle rounded">
                                        <!-- Technical Documents -->
                                        @if(is_array($execution->technical_documents) && count($execution->technical_documents) > 0)
                                            <div class="mb-3">
                                                <h6 class="text-primary mb-2">
                                                    <i class="fas fa-file-pdf me-1"></i>المستندات التقنية
                                                </h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($execution->technical_documents as $doc)
                                                        <div class="card border-primary" style="max-width: 150px;">
                                                            <div class="card-body text-center p-2">
                                                                <i class="fas fa-file-alt text-primary fa-2x mb-2 d-block"></i>
                                                                <small class="text-truncate d-block" title="{{ $doc }}">{{ basename($doc) }}</small>
                                                                <button type="button" 
                                                                        class="btn btn-xs btn-outline-primary mt-2 view-doc"
                                                                        data-doc-path="{{ $doc }}"
                                                                        data-doc-name="{{ basename($doc) }}"
                                                                        data-execution-id="{{ $execution->id }}"
                                                                        data-exec-type="executive">
                                                                    <i class="fas fa-eye me-1"></i>عرض
                                                                </button>
                                                                @can('execution.delete')
                                                                <button type="button" 
                                                                        class="btn btn-xs btn-outline-danger mt-2 delete-doc"
                                                                        data-doc-path="{{ $doc }}"
                                                                        data-delete-url="{{ route('projects.execution.delete-technical', [$project, $execution->id]) }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Financial Documents -->
                                        @if(is_array($execution->financial_documents) && count($execution->financial_documents) > 0)
                                            <div>
                                                <h6 class="text-success mb-2">
                                                    <i class="fas fa-receipt me-1"></i>المستندات المالية
                                                </h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($execution->financial_documents as $doc)
                                                        <div class="card border-success" style="max-width: 150px;">
                                                            <div class="card-body text-center p-2">
                                                                <i class="fas fa-file-invoice text-success fa-2x mb-2 d-block"></i>
                                                                <small class="text-truncate d-block" title="{{ $doc }}">{{ basename($doc) }}</small>
                                                                <button type="button" 
                                                                        class="btn btn-xs btn-outline-success mt-2 view-doc"
                                                                        data-doc-path="{{ $doc }}"
                                                                        data-doc-name="{{ basename($doc) }}"
                                                                        data-execution-id="{{ $execution->id }}"
                                                                        data-exec-type="executive">
                                                                    <i class="fas fa-eye me-1"></i>عرض
                                                                </button>
                                                                @can('execution.delete')
                                                                <button type="button" 
                                                                        class="btn btn-xs btn-outline-danger mt-2 delete-doc"
                                                                        data-doc-path="{{ $doc }}"
                                                                        data-delete-url="{{ route('projects.execution.delete-financial', [$project, $execution->id]) }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Completion Summary -->
        @php
            $totalActualAmount = $allExecutions->sum('actual_amount');
            $totalSpentAmount = $allExecutions->sum('amount_spent');
            $totalRemainingAmount = $allExecutions->sum('remaining_amount');
            $totalCompletionPercentage = $allExecutions->count() > 0 ? ($allExecutions->sum('completion_percentage') / $allExecutions->count()) : 0;
            $financialCompletionPercentage = $totalActualAmount > 0 ? ($totalSpentAmount / $totalActualAmount) * 100 : 0;
        @endphp
        <div class="mt-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3">
                                <i class="fas fa-coins me-2"></i>الملخص المالي
                            </h6>
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">إجمالي المبلغ الفعلي</small>
                                    <h5 class="text-dark mt-1">{{ number_format($totalActualAmount, 2) }}</h5>
                                    <small class="text-muted">﷼</small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">إجمالي المنصرف</small>
                                    <h5 class="text-success mt-1">{{ number_format($totalSpentAmount, 2) }}</h5>
                                    <small class="text-muted">﷼</small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">إجمالي المتبقي</small>
                                    <h5 class="text-warning mt-1">{{ number_format($totalRemainingAmount, 2) }}</h5>
                                    <small class="text-muted">﷼</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-success mb-3">
                                <i class="fas fa-chart-pie me-2"></i>نسب الإنجاز
                            </h6>
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">متوسط نسبة الإنجاز المدخلة</small>
                                    <h5 class="text-info mt-1">{{ number_format($totalCompletionPercentage, 1) }}%</h5>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">نسبة الإنجاز المالية</small>
                                    <h5 class="text-info mt-1">{{ number_format($financialCompletionPercentage, 1) }}%</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-light border border-secondary my-3">
        <div class="text-center py-3">
            <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
            <p class="text-muted mb-0">لا توجد سجلات تنفيذ مسجلة حتى الآن</p>
            <small class="text-muted">قم بإضافة بيانات التنفيذ باستخدام النموذج أعلاه</small>
        </div>
    </div>
@endif

<style>
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
