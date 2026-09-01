@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i> المراسلات المتأخرة (أكثر من 7 أيام)
                    </h5>
                    <a href="{{ route('correspondence.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> رجوع للكل
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
                                    <th>من</th>
                                    <th>إلى</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الأولوية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($correspondences as $correspondence)
                                    <tr class="table-danger">
                                        <td>{{ $loop->iteration }}</td>
                                        <td><span class="badge bg-secondary">{{ $correspondence->correspondence_number }}</span></td>
                                        <td><strong>{{ Str::limit($correspondence->subject, 50) }}</strong></td>
                                        <td>{{ $correspondence->senderEntity->name }}</td>
                                        <td>{{ $correspondence->recipientEntity->name }}</td>
                                        <td>
                                            {{ $correspondence->formatted_created_at }}
                                            <br><small class="text-danger font-weight-bold">{{ $correspondence->days_since_creation }} يوم</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $correspondence->status_color }}">
                                                {{ $correspondence->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $correspondence->priority == 'urgent' ? 'danger' : ($correspondence->priority == 'high' ? 'warning' : 'success') }}">
                                                {{ $correspondence->priority_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('correspondence.show', $correspondence->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">لا يوجد مراسلات متأخرة حالياً.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $correspondences->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
