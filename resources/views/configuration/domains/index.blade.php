@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')
<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0 d-flex align-items-center" style="color: #001f3f;">
                    <x-icon name="layer-group" class="text-primary me-2" /> قائمة المجالات
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('domains.create')
                <a href="{{ route('domains.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-domains-create d-inline-flex align-items-center gap-2">
                    <x-icon name="plus" /> إضافة مجال
                </a>
                @endcan

                <div class="dropdown">
                    <button class="btn btn-navy-gold dropdown-toggle shadow-sm d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <x-icon name="database" /> البيانات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        @can('domains.export')
                        <li><a class="dropdown-item py-2 auth-perm-domains-export d-flex align-items-center gap-2" href="{{ route('config.export', ['entity' => 'domains']) }}"><x-icon name="download" class="text-success" /> تصدير Excel</a></li>
                        @endcan
                        @can('domains.import')
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 auth-perm-domains-import d-flex align-items-center gap-2" href="{{ route('config.import', ['entity' => 'domains']) }}"><x-icon name="upload" class="text-info" /> استيراد ملف</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>

        

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم المجال</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($domains as $domain)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $domain->name }}</td>
                        <td>
                            @can('domains.edit')
                            <form action="{{ route('domains.toggleStatus', $domain->id) }}" method="POST" class="d-inline auth-perm-domains-edit">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" style="border: none;">
                                    @if($domain->is_active)
                                        <span class="text-success fw-bold d-inline-flex align-items-center gap-1"><x-icon name="check-circle" /> مفعل</span>
                                    @else
                                        <span class="text-danger fw-bold d-inline-flex align-items-center gap-1"><x-icon name="times-circle" /> غير مفعل</span>
                                    @endif
                                </button>
                            </form>
                            @else
                                @if($domain->is_active)
                                    <span class="text-success fw-bold d-inline-flex align-items-center gap-1"><x-icon name="check-circle" /> مفعل</span>
                                @else
                                    <span class="text-danger fw-bold d-inline-flex align-items-center gap-1"><x-icon name="times-circle" /> غير مفعل</span>
                                @endif
                            @endcan
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('domains.edit')
                                <a href="{{ route('domains.edit', $domain->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-domains-edit" title="تعديل">
                                    <x-icon name="edit" />
                                </a>
                                @endcan
                                @can('domains.delete')
                                <form action="{{ route('domains.destroy', $domain->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-domains-delete" onclick="return confirmAction(this, 'هل تريد الحذف؟')">
                                        <x-icon name="trash-alt" />
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
