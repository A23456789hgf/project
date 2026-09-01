@extends('layouts.app')

@section('content')
    <x-index-page title="إدارة المراحل" icon="sitemap">
        <x-slot name="headerActions">
            @can('stages.create')
                <a href="{{ route('stages.create') }}" class="btn btn-primary auth-perm-stages-create">
                    <x-icon name="plus" size="14" /> إضافة مرحلة جديدة
                </a>
            @endcan
        </x-slot>

                    <!-- ملخص المسارات -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-project-diagram"></i> مسارات اعتماد المشاريع
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach([
                                            'جمعية → اتحاد → لجنة → تنفيذ',
                                            'اتحاد → لجنة → تنفيذ',
                                            'وحدة → لجنة → تنفيذ',
                                            'إدارة عامة → قطاع → لجنة → تنفيذ',
                                            'قطاع → لجنة → تنفيذ',
                                            'مكتب مديرية → مكتب محافظة → لجنة → تنفيذ'
                                        ] as $index => $path)
                                            <div class="col-md-4 mb-2">
                                                <div class="p-2 border rounded bg-light d-flex align-items-center">
                                                    <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                                    <small class="text-dark">{{ $path }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

        <x-slot name="filters">
                                    <form method="GET" action="{{ route('stages.index') }}" class="row g-3">
                                        <div class="col-md-3">
                                            <input type="text" name="search" class="form-control" 
                                                   placeholder="بحث بالاسم أو الكود..." 
                                                   value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <select name="type" class="form-control">
                                                <option value="">جميع الأنواع</option>
                                                @foreach([
                                                    'association' => 'جمعية',
                                                    'union' => 'اتحاد',
                                                    'unit' => 'وحدة',
                                                    'general_admin' => 'إدارة عامة',
                                                    'sector' => 'قطاع',
                                                    'directorate_office' => 'مكتب مديرية',
                                                    'governorate_office' => 'مكتب محافظة',
                                                    'committee' => 'لجنة',
                                                    'execution' => 'تنفيذ'
                                                ] as $value => $label)
                                                    <option value="{{ $value }}" 
                                                            {{ request('type') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="status" class="form-control">
                                                <option value="">جميع الحالات</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> بحث
                                                </button>
                                                <a href="{{ route('stages.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i> إعادة تعيين
                                                </a>
                                            </div>
                                        </div>
                                    </form>
        </x-slot>

        <x-slot name="table">
                        <table class="table table-hover table-striped align-middle table-compact mb-0">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th width="80" class="text-center">الترتيب</th>
                                    <th>المرحلة</th>
                                    <th width="100" class="text-center">الكود</th>
                                    <th width="120" class="text-center">النوع</th>
                                    <th width="150" class="text-center">المرحلة الرئيسية</th>
                                    <th width="150" class="text-center">المسار التالي</th>
                                    <th width="100" class="text-center">الحالة</th>
                                    <th width="120" class="text-center">النظامية</th>
                                    <th width="180" class="text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stages->sortBy('order') as $index => $stage)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-circle p-2">
                                                {{ $stage->order }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @switch($stage->type)
                                                        @case('association')
                                                            <i class="fas fa-users text-primary fa-lg"></i>
                                                            @break
                                                        @case('union')
                                                            <i class="fas fa-handshake text-success fa-lg"></i>
                                                            @break
                                                        @case('committee')
                                                            <i class="fas fa-clipboard-check text-warning fa-lg"></i>
                                                            @break
                                                        @case('execution')
                                                            <i class="fas fa-play-circle text-danger fa-lg"></i>
                                                            @break
                                                        @default
                                                            <i class="fas fa-building text-info fa-lg"></i>
                                                    @endswitch
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $stage->name_ar }}</h6>
                                                    @if($stage->name_en)
                                                        <small class="text-muted">{{ $stage->name_en }}</small>
                                                    @endif
                                                    @if($stage->description_ar)
                                                        <p class="mb-0 text-muted small">{{ Str::limit($stage->description_ar, 80) }}</p>
                                                    @endif
                                                    @if($stage->description_en)
                                                        <p class="mb-0 text-muted small">{{ Str::limit($stage->description_en, 80) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <code class="bg-light p-2 rounded">{{ $stage->code }}</code>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $typeLabels = [
                                                    'association' => ['label' => 'جمعية', 'class' => 'bg-primary'],
                                                    'union' => ['label' => 'اتحاد', 'class' => 'bg-success'],
                                                    'unit' => ['label' => 'وحدة', 'class' => 'bg-info'],
                                                    'general_admin' => ['label' => 'إدارة عامة', 'class' => 'bg-secondary'],
                                                    'sector' => ['label' => 'قطاع', 'class' => 'bg-dark'],
                                                    'directorate_office' => ['label' => 'مكتب مديرية', 'class' => 'bg-warning text-dark'],
                                                    'governorate_office' => ['label' => 'مكتب محافظة', 'class' => 'bg-warning'],
                                                    'committee' => ['label' => 'لجنة', 'class' => 'bg-danger'],
                                                    'execution' => ['label' => 'تنفيذ', 'class' => 'bg-success']
                                                ];
                                            @endphp
                                            <span class="badge {{ $typeLabels[$stage->type]['class'] ?? 'bg-secondary' }}">
                                                {{ $typeLabels[$stage->type]['label'] ?? $stage->type }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($stage->parent)
                                                <span class="badge bg-light text-dark">
                                                    {{ $stage->parent->name_ar }}
                                                </span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($stage->approval_path && count($stage->approval_path) > 0)
                                                @php
                                                    $nextStages = App\Models\Stage::whereIn('id', $stage->approval_path)->get();
                                                @endphp
                                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                                    @foreach($nextStages as $nextStage)
                                                        <span class="badge bg-light text-dark border small" 
                                                              title="{{ $nextStage->name_ar }}">
                                                            {{ $nextStage->code }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">لا يوجد</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($stage->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> نشط
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> غير نشط
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($stage->is_system)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-cog"></i> نظامي
                                                </span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @can('stages.edit')
                                                <a href="{{ route('stages.edit', $stage) }}" 
                                                   class="btn btn-action-edit btn-icon auth-perm-stages-edit" 
                                                   title="تعديل">
                                                    <x-icon name="edit-2" class="action-icon" />
                                                </a>
                                                @endcan

                                                @if(!$stage->is_system)
                                                    @can('stages.delete')
                                                    <form action="{{ route('stages.destroy', $stage) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف هذه المرحلة؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-action-delete btn-icon auth-perm-stages-delete" 
                                                                title="حذف">
                                                            <x-icon name="trash-2" class="action-icon" />
                                                        </button>
                                                    </form>
                                                    @endcan
                                                @endif

                                                @can('stages.approval-path')
                                                <a href="{{ route('stages.show-approval-path', $stage) }}" 
                                                   class="btn btn-action-view btn-icon auth-perm-stages-approval-path" 
                                                   title="عرض المسار الكامل">
                                                    <x-icon name="route" class="action-icon" />
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <h5>لا توجد مراحل مضافة</h5>
                                                <p>ابدأ بإضافة مرحلة جديدة لتحديد مسارات الاعتماد</p>
                                                @can('stages.create')
                                                <a href="{{ route('stages.create') }}" class="btn btn-primary auth-perm-stages-create">
                                                    <i class="fas fa-plus"></i> إضافة مرحلة جديدة
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
        </x-slot>

        <x-slot name="footer">
                    <!-- الإحصاءات -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <h4 class="text-primary">{{ $stages->count() }}</h4>
                                                <small class="text-muted">إجمالي المراحل</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <h4 class="text-success">{{ $stages->where('is_active', true)->count() }}</h4>
                                                <small class="text-muted">مراحل نشطة</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <h4 class="text-warning">{{ $stages->where('is_system', true)->count() }}</h4>
                                                <small class="text-muted">مراحل نظامية</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-3">
                                                <h4 class="text-info">{{ $stages->whereNotNull('approval_path')->count() }}</h4>
                                                <small class="text-muted">مراحل ذات مسار</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- التذييل التوضيحي -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading">معلومة هامة:</h6>
                                        <p class="mb-2">
                                            كل مرحلة تمثل جهة أو مستوى إداري في النظام. يتم ربط كل مستخدم بمرحلة محددة عند إنشائه، ويتم تحديد مسار اعتماد المشروع بناءً على المرحلة المرتبطة بالمستخدم الذي قام بإنشائه.
                                        </p>
                                        <p class="mb-0">
                                            المراحل النظامية لا يمكن حذفها لأنها جزء أساسي من منطق عمل النظام.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </x-slot>

        <x-slot name="cardFooter">
                <div class="card-footer bg-light border-0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <small class="text-muted">دليل الألوان:</small>
                                <span class="badge bg-primary">جمعية</span>
                                <span class="badge bg-success">اتحاد</span>
                                <span class="badge bg-warning">لجنة</span>
                                <span class="badge bg-danger">تنفيذ</span>
                                <span class="badge bg-info">وحدة</span>
                                <span class="badge bg-secondary">إدارية</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                تم التحديث: {{ now()->format('Y/m/d h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
        </x-slot>
    </x-index-page>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.3s ease;
    }
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,.125);
    }
    .badge {
        font-weight: 500;
        font-size: 0.85em;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // إضافة زر لنسخ الكود
        const codeElements = document.querySelectorAll('code');
        codeElements.forEach(code => {
            code.style.cursor = 'pointer';
            code.title = 'انقر للنسخ';
            code.addEventListener('click', function() {
                const text = this.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    const originalText = this.innerText;
                    this.innerText = 'تم النسخ!';
                    this.classList.add('bg-success', 'text-white');
                    setTimeout(() => {
                        this.innerText = originalText;
                        this.classList.remove('bg-success', 'text-white');
                    }, 1500);
                });
            });
        });
    });
</script>
@endpush