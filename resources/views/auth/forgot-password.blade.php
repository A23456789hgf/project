@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="custom-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-key fa-3x" style="color: var(--primary-dark);"></i>
                        </div>
                        <h2 class="card-title mb-2" style="color: var(--primary-dark); font-weight: 700;">نسيت كلمة المرور</h2>
                        <p class="text-muted">أدخل بريدك الإلكتروني لإعادة تعيين كلمة المرور</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    

                    <form action="{{ route('auth.send-reset-link') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope me-1"></i> البريد الإلكتروني
                            </label>
                            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required autofocus
                                   style="border: 2px solid #e0e0e0; border-radius: 8px; padding: 12px 15px;">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" style="height: 50px; font-weight: 600;">
                                <i class="fas fa-paper-plane me-2"></i> إرسال رابط إعادة التعيين
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">
                            <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--primary-dark);">
                                <i class="fas fa-arrow-right me-1"></i> العودة إلى تسجيل الدخول
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
