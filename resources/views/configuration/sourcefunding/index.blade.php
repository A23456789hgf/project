@extends('layouts.app')

@section('title', 'مصادر التمويل')

@section('content')
    @include('configuration.shared_styles')

    <div class="container">
        <div class="main-card">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0" style="color: #001f3f;">
                        <i class="fas fa-money-bill-wave text-primary me-2"></i> مصادر التمويل
                    </h2>
                    <div class="title-line"></div>
                </div>

                <div class="d-flex gap-2">
                    @can('funding-sources.create')
                        <a href="{{ route('funding-sources.create') }}"
                            class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-funding-sources-create">
                            <i class="fas fa-plus me-1"></i> إضافة مصدر جديد
                        </a>
                    @endcan
                </div>
            </div>

            

            <div class="mb-4 p-3 bg-light rounded-3">
                <form action="{{ route('funding-sources.index') }}" method="GET" class="row g-3 align-items-end">
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
                                <a href="{{ request()->fullUrlWithQuery(['sort_field' => 'name', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}"
                                    class="text-dark text-decoration-none">
                                    <i class="fas fa-sort me-1"></i> الاسم
                                </a>
                            </th>
                            <th style="width: 120px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sources as $index => $source)
                            <tr>
                                <td class="text-muted fw-bold">{{ $index + $sources->firstItem() }}</td>
                                <td class="text-name">{{ $source->name }}</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @can('funding-sources.edit')
                                            <a href="{{ route('funding-sources.edit', $source->id) }}"
                                                class="btn-action btn btn-sm btn-outline-warning auth-perm-funding-sources-edit"
                                                title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('funding-sources.delete')
                                            <form action="{{ route('funding-sources.destroy', $source->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="btn-action btn btn-sm btn-outline-danger auth-perm-funding-sources-delete">
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

            @if($sources->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $sources->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection