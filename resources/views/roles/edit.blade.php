@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="row justify-content-center">
        <div class="col-12">
            <form method="POST" action="{{ route('roles.update', $role) }}" id="roleForm">
                @csrf
                @method('PUT')

                {{-- Header Bar --}}
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                    <div>
                        <h5 class="mb-1 text-dark fw-bold">
                            <i class="fas fa-edit text-primary me-2"></i>تعديل بيانات الدور: {{ $role->name }}
                        </h5>
                        <p class="text-muted small mb-0">قم بتحديث بيانات الدور وصلاحياته وحفظ التغييرات.</p>
                    </div>
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="fas fa-arrow-right ms-1"></i> العودة للقائمة
                    </a>
                </div>

                {{-- Role Basic Info Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 text-dark fw-bold">
                            <i class="fas fa-id-card text-primary me-2"></i>البيانات الأساسية للدور
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted mb-1">اسم الدور <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tag text-muted"></i></span>
                                    <input type="text" name="name"
                                           class="form-control border-start-0 @error('name') is-invalid @enderror"
                                           value="{{ old('name', $role->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($role->name === 'مدير النظام')
                                    <small class="text-info d-block mt-1"><i class="fas fa-info-circle me-1"></i> لا ينصح بتغيير اسم دور مدير النظام الأساسي لضمان ثبات الإعدادات.</small>
                                @endif
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch p-0 mt-3 d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="is_active" value="1"
                                           {{ $role->is_active ? 'checked' : '' }} {{ $role->name === 'مدير النظام' ? 'disabled' : '' }}
                                           style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                    <label class="form-check-label fw-bold text-dark small mb-0" for="is_active" style="cursor: pointer;">
                                        {{ $role->is_active ? 'الدور نشط وجاهز' : 'الدور معطل' }}
                                    </label>
                                </div>
                                @if($role->name === 'مدير النظام')
                                    <input type="hidden" name="is_active" value="1">
                                @endif
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted mb-1">وصف الدور</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                          rows="2" placeholder="وصف مختصر لمسؤوليات هذا الدور...">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Permissions Matrix --}}
                @include('roles.partials._permissions_matrix', ['submitButtonText' => 'تحديث الدور والصلاحيات'])

                {{-- Form Actions --}}
                <div class="mt-4 mb-5 text-center">
                    <a href="{{ route('roles.index') }}" class="btn btn-light border px-5 fw-semibold">
                        رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection