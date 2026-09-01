@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-share-square me-2"></i> الإحالات الواردة
                    </h5>
                    <a href="{{ route('correspondence.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> رجوع للمراسلات
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>رقم المراسلة</th>
                                    <th>الموضوع</th>
                                    <th>المحيل</th>
                                    <th>نص الإحالة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($referrals as $referral)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><span class="badge bg-secondary">{{ $referral->correspondence->correspondence_number }}</span></td>
                                        <td>{{ $referral->correspondence->subject }}</td>
                                        <td>{{ $referral->referredByUser->name ?? 'نظام' }}</td>
                                        <td>{{ Str::limit($referral->referral_text, 50) }}</td>
                                        <td>{{ $referral->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $referral->status == 'pending' ? 'warning' : ($referral->status == 'completed' ? 'success' : 'info') }}">
                                                {{ $referral->status == 'pending' ? 'قيد الانتظار' : ($referral->status == 'completed' ? 'تم الإنجاز' : 'قيد المعالجة') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('correspondence.show', $referral->correspondence_id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> عرض المراسلة
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">لا يوجد إحالات واردة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $referrals->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
