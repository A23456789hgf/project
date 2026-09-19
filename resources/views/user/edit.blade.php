@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/project-tables.css') }}">
@endsection

@section('content')
    <div class="container py-5">

        {{-- Header --}}
        <div class="mb-5 border-bottom pb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-2">تعديل بيانات المستخدم</h1>
                <p class="text-muted">تحديث بيانات المستخدم: {{ $user->name }}</p>
            </div>

            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left ms-1"></i> العودة
            </a>
        </div>

        {{-- Errors --}}
        

        <form action="{{ route('users.update', $user) }}" method="POST" class="row g-4">
            @csrf
            @method('PUT')

            {{-- ID --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">معرف المستخدم</label>
                <input type="text" class="form-control bg-light" value="{{ $user->user_id }}" disabled>
            </div>

            {{-- الاسم --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">الاسم الكامل *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" required>
            </div>

            {{-- العمل --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">العمل</label>
                <input type="text" name="work" class="form-control" value="{{ old('work', $user->work) }}">
            </div>

            {{-- الهاتف --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">رقم الهاتف *</label>
                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- الدور --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">الدور *</label>
                <select name="role_id" class="form-select select2-search" required>
                    <option value="">اختر دور</option>
                    @foreach($roles as $id => $name)
                        <option value="{{ $id }}" @selected(old('role_id', $user->role_id) == $id)>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">الجهة <span class="text-danger">*</span></label>
                <select name="entity_id" id="entity_id" class="form-select select2-search @error('entity_id') is-invalid @enderror" required>
                    <option value="">-- اختر الجهة --</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}" @selected(old('entity_id', $user->entity_id) == $entity->id)>
                            {{ $entity->name }}
                        </option>
                    @endforeach
                </select>
                @error('entity_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- حالة الحساب --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">حالة الحساب *</label>
                <select name="status" class="form-select select2-search" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $user->status) == $status)>
                            {{ $status === 'Active' ? 'نشط' : 'معطل' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- مسؤولية الموافقات --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">مسؤولية الموافقات</label>
                <select name="responsibility" class="form-select select2-search @error('responsibility') is-invalid @enderror">
                    <option value="">-- بدون مسؤولية في دورة الموافقات --</option>
                    @foreach(\App\Enums\UserResponsibilityType::options() as $val => $label)
                        <option value="{{ $val }}" @selected(old('responsibility', $user->responsibility?->value) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('responsibility') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text text-muted">يُستخدم لتحديد دور المستخدم في مراحل الموافقات (فنية / مالية / اعتماد).</div>
            </div>


            {{-- النطاق الجغرافي --}}
            <div class="col-12">
                @include('user.partials.geographic_management_table', [
                    'user' => $user,
                    'geographicScopes' => $geographicScopes
                ])
            </div>

            {{-- الأزرار --}}
            <div class="col-12 d-flex gap-3">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    حفظ التغييرات
                </button>

                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                    إلغاء
                </a>
            </div>

        </form>

    </div>
@endsection

@section('scripts')
        <script>
        $(document).ready(function () {
            $('.select2-search').select2({
                dir: "rtl",
                theme: "bootstrap-5",
                width: '100%',
                dropdownParent: $('body')
            });
        });
        </script>
@endsection