@extends('layouts.app')

@section('title', 'الجهات المشرفة')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-user-shield text-primary me-2"></i> الجهات المشرفة
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('supervisors.create')
                <a href="{{ route('supervisors.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-supervisors-create">
                    <i class="fas fa-plus me-1"></i> إضافة جهة مشرفة
                </a>
                @endcan
            </div>
        </div>

        

        <div class="mb-4 p-3 bg-light rounded-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-12">
                    <label class="field-label">البحث</label>
                    <input type="text" id="search" class="form-control custom-field" placeholder="بحث في الجهات المشرفة...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-table" id="supervisorsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم الجهة المشرفة</th>
                        <th>الحالة</th>
                        <th style="width: 150px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($supervisors as $index => $item)
                    <tr>
                        <td class="text-muted fw-bold">{{ $index + 1 }}</td>
                        <td class="text-name">
                            <i class="fas fa-user-shield text-muted me-1"></i> {{ $item->name }}
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> مفعلة
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> معطلة
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                @can('supervisors.edit')
                                <a href="{{ route('supervisors.edit', $item->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-supervisors-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('supervisors.toggleStatus', $item->id) }}" method="POST" class="d-inline auth-perm-supervisors-edit">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-{{ $item->is_active ? 'warning' : 'success' }}" title="{{ $item->is_active ? 'تعطيل' : 'تفعيل' }}">
                                        <i class="fas fa-{{ $item->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                @endcan
                                @can('supervisors.delete')
                                <form action="{{ route('supervisors.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-supervisors-delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('search').addEventListener('input', e => {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(row => {
            const text = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endsection
