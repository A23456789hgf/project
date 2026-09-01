@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-sitemap text-primary me-2"></i> الأشكال الفرعية للتمويل
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('subfinancing-forms.create')
                <a href="{{ route('subfinancing-forms.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-subfinancing-forms-create">
                    <i class="fas fa-plus me-1"></i> إضافة جديد
                </a>
                @endcan
            </div>
        </div>

        

        <div class="mb-4 p-3 bg-light rounded-3">
            <form action="{{ route('subfinancing-forms.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="field-label">البحث بالاسم</label>
                    <input type="text" name="search" class="form-control custom-field" placeholder="بحث..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="field-label">شكل التمويل الرئيسي</label>
                    <select name="financing_form_id" class="form-select custom-field">
                        <option value="">كل أشكال التمويل</option>
                        @foreach($financingForms as $form)
                        <option value="{{ $form->id }}" {{ request('financing_form_id')==$form->id?'selected':'' }}>{{ $form->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="field-label">الحالة</label>
                    <select name="is_active" class="form-select custom-field">
                        <option value="">الحالة</option>
                        <option value="1" {{ request('is_active')==='1'?'selected':'' }}>نشط</option>
                        <option value="0" {{ request('is_active')==='0'?'selected':'' }}>غير نشط</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-navy-gold shadow-sm w-100">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">الاسم</th>
                        <th class="text-start">شكل التمويل الرئيسي</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subForms as $sub)
                    <tr>
                        <td class="text-muted fw-bold">{{ $sub->id }}</td>
                        <td class="text-name">{{ $sub->name }}</td>
                        <td class="text-name">{{ $sub->financingForm->name }}</td>
                        <td>
                            @if($sub->is_active)
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
                                @can('subfinancing-forms.edit')
                                <a href="{{ route('subfinancing-forms.edit',$sub->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-subfinancing-forms-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('subfinancing-forms.delete')
                                <form action="{{ route('subfinancing-forms.destroy',$sub->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-subfinancing-forms-delete">
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

        @if($subForms->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $subForms->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
