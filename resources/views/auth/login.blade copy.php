<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المشاريع</title>
    <script data-auto-replace-svg="nest" defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #08214c;
            --primary-light: #1a4b8c;
            --accent-gold: #d4af37;
            --accent-gold-light: #f3d772;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            height: 100vh;
            overflow: hidden;
            margin: 0;
        }

        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* Left Side - Branding */
        .brand-section {
            flex: 1.2;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #11346e 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            overflow: hidden;
            padding: 2rem;
            z-index: 1;
        }

        .brand-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            z-index: -1;
        }

        .c-1 { width: 600px; height: 600px; top: -100px; right: -100px; }
        .c-2 { width: 400px; height: 400px; bottom: -50px; left: -50px; background: rgba(212, 175, 55, 0.05); }

        .brand-content {
            text-align: center;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }

        .logo-container {
            width: 280px;
            height: 280px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border: 4px solid var(--accent-gold);
            padding: 20px;
            transition: var(--transition);
        }

        .logo-container:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 50px rgba(212, 175, 55, 0.3);
        }

        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .ministry-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .system-subtitle {
            font-size: 1.1rem;
            color: var(--accent-gold);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Right Side - Form */
        .form-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
        }

        .login-card-modern {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            /* box-shadow: 0 10px 40px rgba(0,0,0,0.05); */ /* Optional shadow if standalone */
        }

        .form-header {
            margin-bottom: 2.5rem;
            text-align: right;
        }

        .welcome-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }
        
        .welcome-text::after {
            content: '';
            position: absolute;
            bottom: -5px;
            right: 0;
            width: 40px;
            height: 4px;
            background: var(--accent-gold);
            border-radius: 2px;
        }

        .instruction-text {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .custom-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .custom-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper > i {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: var(--transition);
        }

        #password {
            padding-left: 3rem;
        }

        .custom-form-control {
            width: 100%;
            padding: 0.8rem 3rem 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: #f8fafc;
        }

        .custom-form-control:focus {
            background: white;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(26, 75, 140, 0.1);
            outline: none;
        }

        .custom-form-control:focus + i {
            color: var(--primary-light);
        }

        .password-toggle {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            z-index: 10;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: var(--primary-dark);
        }

        .actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-dark);
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            background: white;
        }

        input[type="checkbox"]:checked + .custom-checkbox {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        input[type="checkbox"]:checked + .custom-checkbox::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            font-size: 10px;
        }

        .forgot-link {
            font-size: 0.9rem;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn i {
            margin-right: 10px; /* actually left in RTL but right logically */
            transition: transform 0.3s ease;
        }
        
        /* Fix icon margin for RTL */
        html[dir="rtl"] .submit-btn i {
            margin-right: 0;
            margin-left: 10px;
        }

        .submit-btn:hover i {
            transform: translateX(-5px);
        }

        .error-msg {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            .login-wrapper { flex-direction: column; }
            .brand-section { flex: 0 0 35%; padding: 1.5rem; min-height: 280px; }
            .logo-container { width: 160px; height: 160px; padding: 10px; margin-bottom: 1rem; }
            .ministry-title { font-size: 1.1rem; }
            .system-subtitle { font-size: 0.9rem; }
            .form-section { flex: 1; padding: 1.5rem; border-top-left-radius: 30px; border-top-right-radius: 30px; margin-top: -30px; z-index: 10; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Branding Section -->
    <div class="brand-section">
        <div class="decorative-circle c-1"></div>
        <div class="decorative-circle c-2"></div>
        
        <div class="brand-content">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="شعار الوزارة">
            </div>
            <h1 class="ministry-title">وزارة الزراعة والثروة السمكيةوالموارد المائية</h1>
            <h2 class="system-subtitle">نظام إدارة المشاريع</h2>
        
        </div>
    </div>

    <!-- Login Form Section -->
    <div class="form-section">
        <div class="login-card-modern">
            <div class="form-header">
                <h2 class="welcome-text">مرحباً بك مجدداً</h2>
                <p class="instruction-text">سجل الدخول للمتابعة إلى لوحة التحكم</p>
            </div>

            <form action="{{ route('auth.login') }}" method="POST" id="loginForm">
                @csrf
                
                <div class="custom-input-group">
                    <label for="username">اسم المستخدم</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="custom-form-control" placeholder="أدخل اسم المستخدم" value="{{ old('username') }}" required autofocus autocomplete="username">
                        <i class="fas fa-user"></i>
                    </div>
                    @error('username')
                        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="custom-input-group">
                    <label for="password">كلمة المرور</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="custom-form-control" placeholder="أدخل كلمة المرور" required autocomplete="current-password">
                        <i class="fas fa-lock input-icon-lock"></i>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="actions-row">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" hidden checked>
                        <span class="custom-checkbox"></span>
                        تذكرني
                    </label>
                    <a href="#" class="forgot-link">نسيت كلمة المرور؟</a>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <div class="spinner" id="btnSpinner"></div>
                    <span id="btnText">تسجيل الدخول</span>
                    <i class="fas fa-arrow-left" id="btnIcon"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const spinner = document.getElementById('btnSpinner');
        const icon = document.getElementById('btnIcon');

        btn.disabled = true;
        btn.style.opacity = '0.8';
        text.textContent = 'جاري التحقق...';
        spinner.style.display = 'block';
        icon.style.display = 'none';
    });
</script>

</body>
</html>