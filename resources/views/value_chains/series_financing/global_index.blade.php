@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="mb-5 border-bottom pb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-2">تمويلات السلاسل</h1>
                <p class="text-muted font-light">استعراض كافة جهات التمويل المرتبطة بمختلف سلاسل القيمة</p>
            </div>

            @can('series_financing.create')
                <a href="{{ route('global-financings.create') }}" class="btn btn-primary">
                    <x-icon name="plus" size="14" />
                    إضافة جهة تمويل
                </a>
            @endcan
        </div>


        <div class="card shadow-sm border-0 rounded-4">

            {{-- 🔽 Filters --}}
            <div class="p-3 border-bottom">
                <form method="GET" class="row g-2 align-items-center">

                    {{-- نوع التمويل --}}
                    <div class="col-md-4">
                        <select name="financing_type_id" class="form-select form-select-sm">
                            <option value="">كل أنواع التمويل</option>
                            @foreach($financingTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('financing_type_id') == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- نوع الجهة --}}
                    <div class="col-md-3">
                        <select name="entity_type" class="form-select form-select-sm">
                            <option value="">كل الجهات</option>
                            <option value="internal" @selected(request('entity_type') == 'internal')>
                                داخلية
                            </option>
                            <option value="external" @selected(request('entity_type') == 'external')>
                                خارجية
                            </option>
                        </select>
                    </div>

                    {{-- الفرز --}}
                    <div class="col-md-3">
                        <select name="sort" class="form-select form-select-sm">
                            <option value="">ترتيب افتراضي</option>
                            <option value="latest" @selected(request('sort') == 'latest')>
                                الأحدث
                            </option>
                            <option value="oldest" @selected(request('sort') == 'oldest')>
                                الأقدم
                            </option>
                        </select>
                    </div>

                    {{-- أزرار --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary btn-sm w-100">
                            تصفية
                        </button>

                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                            إعادة
                        </a>
                    </div>

                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle table-compact mb-0">
                    <thead>
                        <tr>
                            <th width="80">#</th>
                            <th>سلسلة القيمة</th>
                            <th>نوع التمويل</th>
                            <th>نوع الجهة</th>
                            <th>الجهة</th>
                            <th>الجهة الأم</th>
                            <th>أضيفت بواسطة</th>
                            <th class="text-center" width="120">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financings as $index => $item)
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary small">{{ $index + 1 + ($financings->currentPage() - 1) * $financings->perPage() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('value-chains.financing.index', $item->value_chain_id) }}" class="fw-bold text-decoration-none">
                                        {{ $item->valueChain->name ?? 'ـ' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark small">{{ $item->financingType->name ?? 'ـ' }}</span>
                                </td>
                                <td>
                                    @if($item->entity_type == 'internal')
                                        <span class="badge bg-info text-dark">جهة داخلية</span>
                                    @else
                                        <span class="badge bg-secondary">جهة خارجية</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-dark small">{{ $item->entity_name }}</div>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $item->parent_entity_name }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $item->createdBy->name ?? 'ـ' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">

                                        @can('series_financing.edit')
                                            <a href="{{ route('global-financings.edit', $item->id) }}"
                                                class="btn btn-action-edit btn-icon" title="تعديل">
                                                <x-icon name="edit-2" class="action-icon" />
                                            </a>
                                        @endcan

                                        @can('series_financing.delete')
                                            <form
                                                action="{{ route('global-financings.destroy', $item->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('هل أنت متأكد من حذف جهة التمويل هذه؟')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-action-delete btn-icon" title="حذف">
                                                    <x-icon name="trash-2" class="action-icon" />
                                                </button>
                                            </form>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <div class="p-4 rounded-circle bg-light">
                                            <x-icon name="dollar-sign" size="40" class="text-muted" />
                                        </div>
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">لا توجد تمويلات مسجلة حالياً</h6>
                                            <p class="small mb-0">انقر على "إضافة جهة تمويل" لإضافة مورد مالي.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($financings->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $financings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
