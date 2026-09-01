@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-route text-primary me-2"></i> الموجهات الرئيسية
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('main-routers.create')
                <a href="{{ route('main-routers.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-main-routers-create">
                    <i class="fas fa-plus me-1"></i> إضافة موجه جديد
                </a>
                @endcan
                @can('main-routers.export')
                <a href="{{ route('config.export', ['entity' => 'main-routers']) }}" class="btn btn-outline-success px-4 rounded-3 fw-bold shadow-sm auth-perm-main-routers-export">
                    <i class="fas fa-file-export me-1"></i> تصدير
                </a>
                @endcan
                @can('main-routers.import')
                <a href="{{ route('config.import', ['entity' => 'main-routers']) }}" class="btn btn-outline-info px-4 rounded-3 fw-bold shadow-sm auth-perm-main-routers-import">
                    <i class="fas fa-file-import me-1"></i> استيراد
                </a>
                @endcan
            </div>
        </div>

        

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم الموجه</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($routers as $router)                    <tr>
                        <td class="text-muted fw-bold">{{ $router->id }}</td>
                        <td class="text-name">{{ $router->main_router }}</td>
                        <td>
                            @if($router->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> نشط
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> غير نشط
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('main-routers.edit')
                                <a href="{{ route('main-routers.edit', $router->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-main-routers-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('main-routers.delete')
                                <form action="{{ route('main-routers.destroy', $router->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-main-routers-delete">
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
