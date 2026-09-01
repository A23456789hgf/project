@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container-fluid py-4">
    <x-ui.card title="سجل العمليات (Audit Logs)" subtitle="تتبع وتحليل كافة العمليات المنفذة في النظام" icon="fas fa-history">
        <x-slot name="actions">
            <div class="d-flex gap-2">
                @can('audit-logs.export')
                    <x-ui.button href="{{ route('admin.audit-logs.export-excel', request()->all()) }}" variant="success" size="sm" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
                    <x-ui.button href="{{ route('admin.audit-logs.export-pdf', request()->all()) }}" variant="danger" size="sm" icon="fas fa-file-pdf">تحميل PDF</x-ui.button>
                @endcan
            </div>
        </x-slot>

        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <x-ui.select name="user_id" label="المستخدم" placeholder="كل المستخدمين" :selected="request('user_id')">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <x-ui.select name="action" label="العملية" placeholder="كل العمليات" :selected="request('action')">
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <x-ui.select name="module" label="الموديول" placeholder="كل الأقسام" :selected="request('module')">
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <label class="field-label">بحث شامل</label>
                <input type="text" name="search" class="custom-field px-3" value="{{ request('search') }}" placeholder="اسم المستخدم، جهة، وصف...">
            </div>
            <div class="col-md-2">
                <label class="field-label">عدد السجلات</label>
                <select name="per_page" class="custom-field px-3">
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 سجل</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 سجل</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500 سجل</option>
                    <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000 سجل</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-navy-gold">
                    <x-icon name="filter" class="me-1" /> تصفية النتائج
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-cancel-custom">
                    <x-icon name="undo" class="me-1" /> إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <div class="main-card p-0 overflow-hidden border-0">
        <div class="table-responsive">
            <table class="custom-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">التاريخ والوقت</th>
                        <th>المستخدم والجهة</th>
                        <th>العملية والوحدة</th>
                        <th>وصف العملية</th>
                        <th>IP / الجهاز</th>
                        <th class="pe-4">تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $log->arabic_date }}</div>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $log->user_name ?? ($log->user->name ?? 'System') }}</div>
                                <small class="text-primary">{{ $log->entity_name ?? '-' }}</small>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($log->action) {
                                        'create', 'created' => 'success',
                                        'update', 'updated' => 'info',
                                        'delete', 'deleted' => 'danger',
                                        'login' => 'primary',
                                        'export' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge rounded-pill bg-{{ $badgeClass }} px-3 py-2" style="font-size: 0.75rem;">
                                    {{ $log->translated_action }}
                                </span>
                                <div class="small mt-1 text-muted fw-bold">{{ $log->translated_module }}</div>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 300px;">
                                    {{ $log->translated_description }}
                                    @if($log->url)
                                        <div class="small text-muted mt-1">
                                            <x-icon name="link" class="me-1" />{{ Str::limit($log->url, 40) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $log->ip_address }}</div>
                                <div class="small text-muted" title="{{ $log->user_agent }}">
                                    {{ $log->translated_user_agent }}
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-action-view btn-icon" 
                                        data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}"
                                        title="التفاصيل">
                                    <x-icon name="eye" class="action-icon" />
                                </button>

                                <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content text-start border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">تفاصيل العملية #{{ $log->id }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row mb-3">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="fw-bold text-muted small mb-1">المستخدم</div>
                                                        <div class="p-2 bg-light rounded">{{ $log->user_name ?? ($log->user->name ?? 'System') }}</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="fw-bold text-muted small mb-1">الجهة</div>
                                                        <div class="p-2 bg-light rounded">{{ $log->entity_name ?? '-' }}</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="fw-bold text-muted small mb-1">نوع العملية</div>
                                                        <div class="p-2 bg-light rounded">{{ $log->translated_action }}</div>
                                                    </div>
                                                     <div class="col-md-4 mb-3">
                                                         <div class="fw-bold text-muted small mb-1">القسم (Module)</div>
                                                         <div class="p-2 bg-light rounded">{{ $log->translated_module }}</div>
                                                     </div>
                                                     <div class="col-md-4 mb-3">
                                                         <div class="fw-bold text-muted small mb-1">الجهاز / المتصفح</div>
                                                         <div class="p-2 bg-light rounded">{{ $log->translated_user_agent }}</div>
                                                     </div>
                                                    <div class="col-12 mb-3">
                                                        <div class="fw-bold text-muted small mb-1">رابط الصفحة (URL)</div>
                                                        <div class="p-2 bg-light rounded text-break">{{ $log->url }}</div>
                                                    </div>
                                                     <div class="col-12 mb-3">
                                                         <div class="fw-bold text-muted small mb-1">وصف العملية</div>
                                                         <div class="p-2 bg-light rounded">{{ $log->translated_description }}</div>
                                                     </div>
                                                </div>

                                                @if($log->old_values || $log->new_values)
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="fw-bold text-danger small mb-1">القيم السابقة</div>
                                                        <div class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;">
                                                            <pre class="small mb-0">@json($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="fw-bold text-success small mb-1">القيم الجديدة</div>
                                                        <div class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;">
                                                            <pre class="small mb-0">@json($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <x-icon name="info-circle" size="32" class="mb-3 text-muted" />
                                    <p>لا يوجد سجلات مطابقة للبحث</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection