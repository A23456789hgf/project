@extends('layouts.app')

@section('content')
    @include('configuration.shared_styles')

    <div class="container">
        <div class="main-card">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0" style="color: #001f3f;">
                        <i class="fas fa-hand-holding-usd text-primary me-2"></i> أنواع التمويل
                    </h2>
                    <div class="title-line"></div>
                </div>

                <div class="d-flex gap-2">
                    @can('financing-types.create')
                        <a href="{{ route('financing-types.create') }}"
                            class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-financing-types-create">
                            <i class="fas fa-plus me-1"></i> إضافة نوع تمويل جديد
                        </a>
                    @endcan
                </div>
            </div>

            

            <div class="mb-4 p-3 bg-light rounded-3">
                <form action="{{ route('financing-types.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="field-label">البحث</label>
                        <input type="text" name="search" class="form-control custom-field" placeholder="بحث بالاسم..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-navy-gold shadow-sm w-100">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th class="text-start">
                                <a href="{{ route('financing-types.index', ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="text-dark text-decoration-none">
                                    <i class="fas fa-sort me-1"></i> نوع التمويل
                                </a>
                            </th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financingTypes as $type)
                            <tr>
                                <td class="text-muted fw-bold">{{ $type->id }}</td>
                                <td class="text-name">{{ $type->name }}</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @can('financing-types.edit')
                                            <a href="{{ route('financing-types.edit', $type) }}"
                                                class="btn-action btn btn-sm btn-outline-warning auth-perm-financing-types-edit" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('financing-types.delete')
                                            <form action="{{ route('financing-types.destroy', $type) }}" method="POST"
                                                class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-financing-types-delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($financingTypes->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $financingTypes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection