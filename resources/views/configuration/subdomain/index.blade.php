@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-sitemap text-primary me-2"></i> إدارة المجالات الفرعية
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('subdomains.create')
                <a href="{{ route('subdomains.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-subdomains-create">
                    <i class="fas fa-plus me-1"></i> إضافة مجال فرعي
                </a>
                @endcan

                <div class="dropdown">
                    <button class="btn btn-navy-gold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i> البيانات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        @can('subdomains.export')
                        <li><a class="dropdown-item py-2 auth-perm-subdomains-export" href="{{ route('config.export', ['entity' => 'subdomains']) }}"><i class="fas fa-download text-success me-2"></i> تصدير Excel</a></li>
                        @endcan
                        @can('subdomains.import')
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 auth-perm-subdomains-import" href="{{ route('config.import', ['entity' => 'subdomains']) }}"><i class="fas fa-upload text-info me-2"></i> استيراد ملف</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-4 p-3 bg-light rounded-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="field-label">المجال الرئيسي</label>
                    <select name="domain_id" class="form-select custom-field">
                        <option value="">جميع المجالات</option>
                        @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" {{ request('domain_id') == $domain->id ? 'selected' : '' }}>
                            {{ $domain->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="field-label">البحث</label>
                    <input type="text" name="search" class="form-control custom-field" placeholder="بحث..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
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
                        <th class="text-start">اسم المجال الفرعي</th>
                        <th>المجال الرئيسي</th>
                        <th class="text-center">الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                        @forelse ($subdomains as $subdomain)
                        <tr>
                        <td class="text-name">{{ $subdomain->name }}</td>
                        <td><span class="badge-label">{{ $subdomain->domain?->name ?? 'غير محدد' }}</span></td>
                        <td class="text-center">
                            @if($subdomain->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-3 rounded-pill"><i class="fas fa-check-circle me-1"></i> مفعل</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3 rounded-pill"><i class="fas fa-times-circle me-1"></i> معطل</span>
                            @endif
                        </td>
                        <td><span class="badge-label">{{ $subdomain->created_at->format('Y-m-d') }}</span></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('subdomains.edit')
                                <a href="{{ route('subdomains.edit', $subdomain) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-subdomains-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('subdomains.delete')
                                <form action="{{ route('subdomains.destroy', $subdomain) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-subdomains-delete" onclick="return confirmAction(this, 'هل تريد الحذف؟')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $subdomains->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection