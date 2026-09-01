@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i> بحث متقدم في المراسلات</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('correspondence.search') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">رقم المراسلة</label>
                        <input type="text" name="correspondence_number" class="form-control" value="{{ request('correspondence_number') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الموضوع</label>
                        <input type="text" name="subject" class="form-control" value="{{ request('subject') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">من الجهة</label>
                        <select name="sender_entity_id" class="form-select">
                            <option value="">-- الكل --</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity->id }}" {{ request('sender_entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">إلى الجهة</label>
                        <select name="recipient_entity_id" class="form-select">
                            <option value="">-- الكل --</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity->id }}" {{ request('recipient_entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">-- الكل --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>تم الرد</option>
                            <option value="referred" {{ request('status') == 'referred' ? 'selected' : '' }}>تم الإحالة</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">الأولوية</label>
                        <select name="priority" class="form-select">
                            <option value="">-- الكل --</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>عادية</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>عاجلة</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('correspondence.search') }}" class="btn btn-secondary">إعادة تعيين</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search me-1"></i> ابحث الآن
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($results))
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">نتائج البحث ({{ $results->total() }})</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>الموضوع</th>
                                <th>من</th>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $item)
                                <tr>
                                    <td>{{ $item->correspondence_number }}</td>
                                    <td>{{ $item->subject }}</td>
                                    <td>{{ $item->senderEntity->name }}</td>
                                    <td>{{ $item->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status_color }}">{{ $item->status_label }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('correspondence.show', $item->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد نتائج تطابق بحثك.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $results->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
