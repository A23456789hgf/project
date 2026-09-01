@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-sms text-primary me-2"></i> سجل رسائل SMS
                </h2>
                <div class="title-line"></div>
                <div class="text-muted small mt-1">إجمالي السجلات: <strong>{{ $logs->total() }}</strong></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('sms.manage')
                <a href="{{ route('configuration.sms.manual') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-sms-manage">
                    <i class="fas fa-paper-plane me-1"></i> إرسال رسالة يدوية
                </a>
                @endcan
                @can('sms.settings.view')
                <a href="{{ route('configuration.sms.settings') }}" class="btn btn-outline-info px-4 rounded-3 fw-bold shadow-sm auth-perm-sms-settings-view">
                    <i class="fas fa-cog me-1"></i> إعدادات الأحداث
                </a>
                @endcan
            </div>
        </div>


        <div class="mb-4 p-3 bg-light rounded-3">
            <form action="{{ route('configuration.sms.logs') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="field-label">رقم الهاتف</label>
                        <input type="text" name="phone_number" class="form-control custom-field" placeholder="07xxxxxxx" value="{{ request('phone_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">المستلم</label>
                        <input type="text" name="recipient" class="form-control custom-field" placeholder="اسم أو معرف..." value="{{ request('recipient') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">المرسل</label>
                        <input type="text" name="sender" class="form-control custom-field" placeholder="اسم أو معرف..." value="{{ request('sender') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">الحالة</label>
                        <select name="status" class="form-select custom-field">
                            <option value="">الكل</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>مرسلة</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشلت</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control custom-field" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="field-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control custom-field" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-12 text-start mt-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-navy-gold shadow-sm px-4">
                                <i class="fas fa-filter me-1"></i> تصفية
                            </button>
                            <a href="{{ route('configuration.sms.logs') }}" class="btn btn-outline-secondary shadow-sm px-3" title="إعادة تعيين">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">المستلم</th>
                        <th class="text-start">رقم الهاتف</th>
                        <th class="text-start">نص الرسالة</th>
                        <th>الحالة</th>
                        <th class="text-start">تاريخ الإرسال</th>
                        <th class="text-start">المرسل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log) 
                    <tr>
                        <td class="text-muted fw-bold">{{ $log->id }}</td>
                        <td class="text-name">{{ $log->recipient ? $log->recipient->name : 'غير محدد' }}</td>
                        <td dir="ltr" class="text-end">{{ $log->phone_number }}</td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $log->message }}">
                                {{ $log->message }}
                            </span>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check me-1"></i> مرسلة</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger rounded-pill px-3 py-2" title="{{ $log->error_message }}"><i class="fas fa-times me-1"></i> فشلت</span>
                            @else
                                <span class="badge bg-warning rounded-pill px-3 py-2 text-dark"><i class="fas fa-clock me-1"></i> قيد الانتظار</span>
                            @endif
                        </td>
                        <td dir="ltr" class="text-end">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->sender ? $log->sender->name : 'نظام (تلقائي)' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">لا يوجد سجل رسائل متاح</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div>
                {{ $logs->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection