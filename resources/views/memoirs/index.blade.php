@extends('layouts.app')

@section('content')
    <x-index-page title="إدارة المذكرات الإدارية" icon="file-text">
        <x-slot name="headerActions">
            @can('memoirs.create')
            <a href="{{ route('memoirs.create') }}" class="btn btn-primary auth-perm-memoirs-create">
                <x-icon name="plus" size="14" /> إنشاء مذكرة
            </a>
            @endcan
        </x-slot>
        <x-slot name="filters">
                            <form action="{{ route('memoirs.index') }}" method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">بحث عام</label>
                                    <div class="input-group shadow-sm">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="بحث بالرقم، الجهة، الموضوع..." 
                                               value="{{ request('search') }}">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">من تاريخ</label>
                                    <input type="date" name="date_from" class="form-control shadow-sm" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">إلى تاريخ</label>
                                    <input type="date" name="date_to" class="form-control shadow-sm" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-dark w-100 shadow-sm">
                                        <i class="fas fa-filter me-1"></i>تصفية
                                    </button>
                                </div>
                            </form>
        </x-slot>

        <x-slot name="table">
            <table class="table table-hover table-striped align-middle table-compact mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="py-3">رقم المذكرة</th>
                                    <th class="py-3">موجهة إلى</th>
                                    <th class="py-3">الموضوع</th>
                                    <th class="py-3">التاريخ (ميلادي/هجري)</th>
                                    <th class="py-3">المشروع المرتبط</th>
                                    <th class="py-3">بواسطة</th>
                                    <th class="py-3 text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($memoirs as $memoir)
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary px-2 py-1">
                                                {{ $memoir->memoir_number }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $memoir->to }}</strong></td>
                                        <td>{{ Str::limit($memoir->subject, 60) }}</td>
                                        <td>
                                            <div class="small">
                                                <i class="far fa-calendar-alt me-1 text-muted"></i>{{ $memoir->gregorian_date }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="far fa-calendar me-1"></i>{{ $memoir->hijri_date }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($memoir->project)
                                                <div class="small fw-bold text-primary">
                                                    <i class="fas fa-project-diagram me-1 text-muted"></i>
                                                    {{ $memoir->project->project_name }}
                                                </div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $memoir->creator?->name }}</div>
                                            <div class="small text-muted">{{ $memoir->entity?->name }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm" role="group">
                                                @can('memoirs.view', $memoir)
                                                <a href="{{ route('memoirs.show', $memoir->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="طباعة"
                                                        onclick="openPrintSettings({{ $memoir->id }})">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                @endcan
                                                
                                                @can('memoirs.edit', $memoir)
                                                <a href="{{ route('memoirs.edit', $memoir->id) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('memoirs.delete', $memoir)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="حذف"
                                                        onclick="confirmDelete({{ $memoir->id }}, '{{ $memoir->memoir_number }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty 
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="opacity-50 mb-3">
                                                <i class="fas fa-file-invoice fa-4x mb-3"></i>
                                                <h5>لا توجد مذكرات حالياً</h5>
                                            </div>
                                            @can('memoirs.create')
                                            <a href="{{ route('memoirs.create') }}" class="btn btn-primary shadow-sm">
                                                <i class="fas fa-plus me-1"></i>أنشئ أول مذكرة الآن
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
            </table>
        </x-slot>

        <x-slot name="pagination">
            @if(method_exists($memoirs, 'hasPages') && $memoirs->hasPages())
                <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
                    <div class="small text-muted">
                        عرض {{ $memoirs->firstItem() ?? 0 }} إلى {{ $memoirs->lastItem() ?? 0 }} من أصل {{ $memoirs->total() }} مذكرة
                    </div>
                    <div>
                        {{ $memoirs->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </x-slot>
    </x-index-page>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-circle me-2"></i>تأكيد الحذف</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="fs-5">هل أنت متأكد من حذف المذكرة رقم:</p>
                <h4 class="text-danger mb-4" id="memoir_number_display"></h4>
                <p class="text-muted small">هذا الإجراء سيقوم بنقل المذكرة إلى سلة المحذوفات.</p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" action="" onsubmit="return confirmAction(this, 'هل أنت متأكد من عملية الحذف؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">حذف المذكرة</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('memoirs.partials.print_settings_modal')
@endsection

@push('scripts')
<script>
    function confirmDelete(id, number) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/memoirs/${id}`;
        document.getElementById('memoir_number_display').innerText = number;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>
@endpush
