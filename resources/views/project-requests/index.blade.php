@extends('layouts.app')
@section('title', 'إدارة طلبات المشاريع')
@section('content')
@include('configuration.shared_styles')

    <x-index-page title="إدارة طلبات المشاريع" icon="file-text">
        
        <x-slot name="stats">
            <div class="row g-3 w-100">
        <div class="col-md-2">
            <div class="card bg-navy text-white text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
                    <div class="small opacity-75">إجمالي الطلبات</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0">{{ $stats['draft'] }}</h4>
                    <div class="small opacity-75">مسودات</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['submitted'] + $stats['pending_approval'] }}</h4>
                    <div class="small opacity-75 text-dark">تحت المراجعة</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0">{{ $stats['approved'] }}</h4>
                    <div class="small opacity-75">معتمدة</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0">{{ $stats['transferred'] }}</h4>
                    <div class="small opacity-75">مُحولة لمشاريع</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white text-center border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <h4 class="fw-bold mb-0">{{ $stats['rejected'] }}</h4>
                    <div class="small opacity-75">مرفوضة</div>
                </div>
            </div>
        </div>
            </div>
        </x-slot>
        
        <x-slot name="headerActions">
            @can('project-requests.create')
            <a href="{{ route('project-requests.create') }}" class="btn btn-primary auth-perm-project-requests-create">
                <x-icon name="plus" size="14" /> تقديم طلب مشروع 
            </a>
            @endcan
            
            @can('project-requests.export')
            <div class="dropdown">
                <button class="btn btn-gold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-icon name="settings" size="14" class="me-1" /> العمليات
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="javascript:void(0)" onclick="handleExport('excel')">
                            <x-icon name="file" class="text-success me-2" /> تصدير Excel
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
        </x-slot>

        <x-slot name="filters">
            <form action="{{ route('project-requests.index') }}" method="GET" class="row g-2">
                <div class="col-md-3">
                    <label for="search" class="form-label small">البحث</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="رقم الطلب أو اسم المشروع...">
                </div>

                <div class="col-md-3">
                    <label for="program_id" class="form-label small">البرنامج</label>
                    <select class="form-select form-select-sm" id="program_id" name="program_id">
                        <option value="">جميع البرامج</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="domain_id" class="form-label small">المجال الرئيسي</label>
                    <select class="form-select form-select-sm" id="domain_id" name="domain_id">
                        <option value="">جميع المجالات</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" {{ request('domain_id') == $domain->id ? 'selected' : '' }}>{{ $domain->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label small">الحالة</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>مرسل</option>
                        <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>قيد الموافقة</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                        <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>مُحول لمشروع</option>
                    </select>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> بحث
                        </button>
                        <a href="{{ route('project-requests.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> إزالة الفلاتر
                        </a>
                    </div>
                </div>
            </form>
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="1%" class="text-center">#</th>
                        <th width="10%">رقم الطلب</th>
                        <th width="22%">اسم المشروع المقترح</th>
                        <th width="8%">الحالة</th>
                        <th width="12%">المرحلة الحالية</th>
                        <th width="8%">حالة المرحلة</th>
                        <th width="10%">تاريخ التقديم</th>
                        <th width="10%">المنشئ</th>
                        <th width="15%">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectRequests as $request)
                    <tr>
                        <td class="text-center">{{ $loop->iteration + ($projectRequests->currentPage() - 1) * $projectRequests->perPage() }}</td>
                        <td>
                            <span class="project-number small-text">
                                {{ $request->request_number ?? '-' }}
                            </span>
                        </td>
                        <td class="small-text">
                            <strong>{{ \Illuminate\Support\Str::limit($request->project_name ?? 'غير محدد', 50) }}</strong>
                            @if($request->status === 'transferred' && $request->project_id)
                                <div class="mt-1">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> محول لمشروع: 
                                        <a href="{{ route('projects.show', $request->project_id) }}" class="fw-bold">
                                            {{ $request->assigned_project_number }}
                                        </a>
                                    </small>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $request->status_color }} small-text">
                                {{ $request->status_label }}
                            </span>
                        </td>
                        <td class="small-text text-center">
                            @if($request->current_stage)
                                @php
                                    $stageName = $request->current_stage;
                                    
                                    // Handle numeric stage order values
                                    $stageOrder = [
                                        1 => 'assembly',
                                        2 => 'union',
                                        3 => 'committee',
                                        4 => 'implementation',
                                    ];
                                    
                                    if (is_numeric($stageName) && isset($stageOrder[$stageName])) {
                                        $stageName = $stageOrder[$stageName];
                                    }
                                    
                                    // Handle entity stages
                                    if (str_starts_with($stageName, 'entity_')) {
                                        $entityId = str_replace('entity_', '', $stageName);
                                        $entity = \App\Models\InternalEntity::find($entityId);
                                        $stageName = $entity->name ?? $stageName;
                                    } else {
                                        // Map stage codes to Arabic names
                                        $stageNames = [
                                            'assembly' => 'موافقة الجمعية',
                                            'union' => 'موافقة الاتحاد',
                                            'committee' => 'موافقة اللجنة',
                                            'implementation' => 'مرحلة التنفيذ',
                                        ];
                                        $stageName = $stageNames[$stageName] ?? $stageName;
                                    }
                                @endphp
                                <span class="text-primary fw-bold">{{ $stageName }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $approvalStatus = $request->approval_status;
                                $statusMap = [
                                    'pending' => ['label' => 'في الانتظار', 'class' => 'bg-warning text-dark'],
                                    'approved' => ['label' => 'موافق', 'class' => 'bg-success'],
                                    'rejected' => ['label' => 'مرفوض', 'class' => 'bg-danger'],
                                    'need_action' => ['label' => 'يحتاج إجراء', 'class' => 'bg-info'],
                                ];
                                $stageInfo = $statusMap[$approvalStatus] ?? null;
                            @endphp
                            @if($stageInfo)
                                <span class="badge {{ $stageInfo['class'] }} small-text">{{ $stageInfo['label'] }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="small-text text-center">
                            {{ $request->created_at ? $request->created_at->format('Y-m-d') : '-' }}
                        </td>
                        <td class="small-text">
                            <div class="fw-bold">{{ \Illuminate\Support\Str::limit(optional($request->createdBy)->name ?? '-', 20) }}</div>
                            <div class="text-muted x-small">{{ $request->creator_entity_name }}</div>
                        </td>
                        <td class="table-actions">
                            <div class="btn-group" role="group">
                                @can('project-requests.show')
                                <a href="{{ route('project-requests.show', $request->id) }}" class="btn btn-action-view btn-icon" title="عرض التفاصيل">
                                    <x-icon name="eye" class="action-icon" />
                                </a>
                                @endcan

                                @php
                                    $isDraft = $request->status === 'draft';
                                    $isIncomplete = $isDraft && ($request->last_saved_step ?? 0) < 7;
                                @endphp

                                @if($isIncomplete)
                                    @can('project-requests.edit')
                                    <a href="{{ route('project-requests.draft.resume', $request->id) }}" class="btn btn-action-log btn-icon" title="استئناف المسودة">
                                        <x-icon name="play" class="action-icon" />
                                    </a>
                                    @endcan
                                @endif

                                @if(($request->status === 'draft' && !$isIncomplete) || $request->status === 'pending' || auth()->user()->hasRole('admin'))
                                    @can('project-requests.edit')
                                    <a href="{{ route('project-requests.edit', $request->id) }}" class="btn btn-action-edit btn-icon" title="تعديل">
                                        <x-icon name="edit-2" class="action-icon" />
                                    </a>
                                    @endcan
                                @endif

                                @if($request->status === 'approved' && !$request->project_id)
                                    @can('project-requests.transfer')
                                    <form action="{{ route('project-requests.transfer', $request->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-action-enable btn-icon" title="تحويل لمشروع رسمي" onclick="return confirmAction(this, 'هل تريد تحويل هذا الطلب إلى مشروع رسمي؟ سيتم إصدار رقم PRO جديد.')">
                                            <x-icon name="exchange-alt" class="action-icon" />
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                @if($request->status === 'draft' || $request->status === 'pending' || auth()->user()->hasRole('admin'))
                                    @can('project-requests.delete')
                                    <form action="{{ route('project-requests.destroy', $request->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-action-delete btn-icon" title="حذف" onclick="return confirmAction(this, 'هل أنت متأكد من حذف هذا الطلب؟')">
                                            <x-icon name="trash-2" class="action-icon" />
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                                
                                @can('project-requests.print')
                                <a href="{{ route('project-requests.print', $request->id) }}" target="_blank" class="btn btn-action-log btn-icon" title="طباعة">
                                    <x-icon name="print" class="action-icon" />
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4 small-text">
                            لا توجد طلبات مشاريع مطابقة للبحث
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>

        <x-slot name="pagination">
            <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
                <div>
                    {{ $projectRequests->appends(request()->query())->links() }}
                </div>
                
                <div class="d-flex align-items-center">
                    <label for="per_page" class="me-2 small-text">عرض:</label>
                    <form action="{{ URL::current() }}" method="GET" id="perPageForm">
                        @foreach(request()->except('per_page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <select class="form-select form-select-sm small-text" name="per_page" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 سجل</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 سجل</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 سجل</option>
                        </select>
                    </form>
                </div>
            </div>
        </x-slot>
    </x-index-page>

<style>
.compact-card .card-body {
    padding: 0.75rem;
}
.compact-table .table {
    font-size: 13px !important;
}
.compact-table .table th, 
.compact-table .table td {
    padding: 0.5rem 0.6rem !important;
    vertical-align: middle;
}
.table-actions .btn-group { 
    gap: 2px; 
}
.table-actions .btn { 
    padding: 0.25rem 0.45rem; 
    font-size: 11px; 
}
.project-number { 
    font-family: monospace; 
    font-weight: bold; 
}
.table-wrapper { 
    border-radius: 8px; 
    overflow: hidden; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
    background: #fff;
}
.small-text {
    font-size: 12px !important;
}
.badge {
    padding: 0.4em 0.6em !important;
}
.x-small {
    font-size: 10px !important;
}
</style>
@endsection
