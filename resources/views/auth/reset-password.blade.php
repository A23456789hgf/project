@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="custom-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-unlock-alt fa-3x" style="color: var(--primary-dark);"></i>
                        </div>
                        <h2 class="card-title mb-2" style="color: var(--primary-dark); font-weight: 700;">إعادة تعيين كلمة المرور</h2>
                        <p class="text-muted">أدخل كلمة المرور الجديدة</p>
                    </div>

                    

                    <form action="{{ route('auth.reset-password') }}" method="POST">
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-1"></i> البريد الإلكتروني
                            </label>
                            <input type="email" class="form-control form-control-lg"
                                   id="email" name="email" value="{{ old('email', $request->email) }}" readonly
                                   style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 15px; background: #f8f9fa;">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-key me-1"></i> كلمة المرور الجديدة
                            </label>
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   id="password" name="password" required
                                   style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 15px;">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="fas fa-key me-1"></i> تأكيد كلمة المرور الجديدة
                            </label>
                            <input type="password" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation" name="password_confirmation" required
                                   style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 15px;">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" style="height: 50px; font-weight: 600;">
                                <i class="fas fa-save me-2"></i> حفظ كلمة المرور الجديدة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');

    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('كلمة المرور غير متطابقة');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>
@endsection
