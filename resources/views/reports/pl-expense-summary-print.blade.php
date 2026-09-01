<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>تسجيل الدخول - نظام إدارة المشاريع</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.5;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ========== قسم النموذج ========== */
        .form-section {
            flex: 1;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(0.75rem, 3vw, 2rem);
            position: relative;
            order: 1;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: -80px; left: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        .login-card-modern {
            width: 100%;
            max-width: 400px;
            padding: clamp(1.25rem, 3vw, 2rem);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: clamp(12px, 2vw, 20px);
            box-shadow: 0 15px 45px rgba(8, 33, 76, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.08);
            position: relative; z-index: 10;
            animation: fadeInUp 0.8s ease-out;
        }

        .form-header {
            margin-bottom: 1.25rem;
            text-align: right;
        }

        .welcome-text {
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.35rem;
            position: relative;
            display: inline-block;
        }

        .welcome-text::after {
            content: '';
            position: absolute;
            bottom: -6px; right: 0;
            width: 45px; height: 3px;
            background: linear-gradient(90deg, var(--accent-gold), var(--accent-gold-light));
            border-radius: 2px;
            transition: var(--transition);
        }

        .welcome-text:hover::after {
            width: 100%;
        }

        .instruction-text {
            color: var(--text-light);
            font-size: clamp(0.8rem, 2vw, 0.85rem);
            margin-top: 0.6rem;
        }

        /* ========== حقول الإدخال المُصغّرة ========== */
        .custom-input-group {
            position: relative;
            margin-bottom: 0.85rem;
        }

        .custom-input-group label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-dark);
            text-align: right;
        }

        .input-wrapper { position: relative; }

        .input-wrapper > .input-icon {
            position: absolute;
            right: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: var(--transition);
            z-index: 5;
            pointer-events: none;
            font-size: 0.85rem;
        }

        #password { padding-left: 2.5rem; }

        .custom-form-control {
            width: 100%;
            padding: 0.55rem 2.2rem 0.55rem 0.7rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: var(--transition);
            background: #f8fafc;
            font-family: inherit;
            text-align: right;
            height: 40px;
        }

        .custom-form-control:focus {
            background: white;
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
            outline: none;
        }

        .custom-form-control:focus + .input-icon,
        .custom-form-control:focus ~ .input-icon {
            color: var(--accent-gold);
        }

        .custom-form-control::placeholder {
            color: #94a3b8;
            opacity: 1;
            text-align: right;
            font-size: 0.8rem;
        }

        .password-toggle {
            position: absolute;
            left: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            z-index: 10;
            transition: var(--transition);
            padding: 0.2rem;
            border-radius: 4px;
            background: transparent;
            border: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
        }

        .password-toggle:hover {
            color: var(--primary-dark);
            background: rgba(26, 75, 140, 0.05);
        }

        /* ========== خيارات إضافية ========== */
        .actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: var(--text-dark);
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox-wrapper {
            position: relative;
            width: 16px; height: 16px;
        }

        .custom-checkbox-wrapper input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            width: 100%; height: 100%;
            z-index: 2;
        }

        .custom-checkbox {
            position: absolute;
            top: 0; right: 0;
            width: 100%; height: 100%;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            background: white;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-checkbox-wrapper input:checked + .custom-checkbox {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .custom-checkbox-wrapper input:checked + .custom-checkbox::after {
            content: '';
            width: 4px; height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-bottom: 1px;
        }

        .forgot-link {
            font-size: 0.78rem;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            padding: 0.2rem 0.4rem;
            border-radius: 5px;
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            background: rgba(26, 75, 140, 0.08);
            text-decoration: none;
        }

        /* ========== زر الإرسال ========== */
        .submit-btn {
            width: 100%;
            padding: 0.65rem;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            box-shadow: 0 4px 12px rgba(8, 33, 76, 0.25);
            position: relative;
            overflow: hidden;
            height: 42px;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0; right: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.25), transparent);
            transition: right 0.6s ease;
        }

        .submit-btn:hover::before { right: 100%; }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(8, 33, 76, 0.35);
        }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn i { transition: transform 0.3s ease; font-size: 0.85rem; }
        .submit-btn:hover i { transform: translateX(-3px); }

        .submit-btn .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
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

        /* ========== رسائل الخطأ ========== */
        .error-msg {
            color: #ef4444;
            font-size: 0.72rem;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 4px;
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        /* ========== القسم الأيسر ========== */
        .brand-section {
            flex: 1.1;
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0f2d5e 50%, #08214c 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            overflow: hidden;
            padding: clamp(0.75rem, 2vw, 1.5rem);
            z-index: 1;
            order: 2;
        }

        .brand-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23d4af37' stroke-width='0.5' opacity='0.12'%3E%3Cpath d='M30 0L60 30L30 60L0 30Z'/%3E%3Cpath d='M30 8L52 30L30 52L8 30Z'/%3E%3Ccircle cx='30' cy='30' r='12'/%3E%3C/g%3E%3C/svg%3E");
        }

        .brand-section::after {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            z-index: 0;
            animation: float 20s infinite ease-in-out;
        }

        .c-1 {
            width: clamp(250px, 45vw, 500px);
            height: clamp(250px, 45vw, 500px);
            top: clamp(-120px, -18vh, -80px);
            right: clamp(-120px, -18vw, -80px);
        }

        .c-2 {
            width: clamp(180px, 35vw, 350px);
            height: clamp(180px, 35vw, 350px);
            bottom: clamp(-80px, -12vh, -40px);
            left: clamp(-80px, -12vw, -40px);
            background: rgba(212, 175, 55, 0.06);
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-15px) scale(1.01); }
        }

        .brand-content {
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 0.5rem;
            animation: fadeInUp 0.8s ease-out;
            max-width: 100%;
        }

        .logo-container {
            width: clamp(110px, 20vw, 220px);
            height: clamp(110px, 20vw, 220px);
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.25), 0 0 0 3px rgba(212, 175, 55, 0.25);
            border: clamp(2px, 0.4vw, 4px) solid var(--accent-gold);
            padding: clamp(10px, 1.5vw, 18px);
            transition: var(--transition);
            flex-shrink: 0;
            position: relative;
        }

        .logo-container::before {
            content: '';
            position: absolute;
            top: -8px; left: -8px; right: -8px; bottom: -8px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--accent-gold), transparent, var(--accent-gold));
            opacity: 0.25;
            z-index: -1;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.25; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.03); }
        }

        .logo-container:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 20px 55px rgba(212, 175, 55, 0.4), 0 0 0 3px rgba(212, 175, 55, 0.4);
        }

        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .logo-placeholder {
            font-size: clamp(2rem, 5vw, 4rem);
            color: var(--primary-dark);
        }

        .ministry-title {
            font-size: clamp(0.95rem, 2.2vw, 1.35rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.4;
        }

        .system-subtitle {
            font-size: clamp(0.85rem, 1.8vw, 1.05rem);
            color: var(--accent-gold);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .divider {
            width: 60px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
            margin: 0.75rem auto;
        }

        /* ========== Modal استعادة كلمة المرور ========== */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(8, 33, 76, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: white;
            border: none;
            border-radius: 14px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.15);
            font-size: 0.85rem;
            width: 100%;
            max-width: 420px;
            transform: translateY(20px) scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: translateY(0) scale(1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            background: var(--bg-light);
            border-radius: 14px 14px 0 0;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.1rem;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: rgba(0,0,0,0.05);
            color: var(--text-dark);
        }

        .modal-body { padding: 1rem; }
        .modal-footer { padding: 0.6rem 1rem; border-top: 1px solid rgba(0,0,0,0.05); }

        .modal .form-control {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            height: 38px;
            width: 100%;
            font-family: inherit;
            transition: var(--transition);
        }

        .modal .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
            outline: none;
        }

        .modal .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--text-dark);
            display: block;
            text-align: right;
        }

        .modal .btn-primary {
            background: var(--primary-dark);
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            color: white;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }

        .modal .btn-primary:hover {
            background: var(--primary-light);
        }

        .modal .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .modal .alert {
            font-size: 0.78rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            display: none;
        }

        .modal .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .modal .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .modal p.text-muted {
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            color: var(--text-light);
        }

        .spinner-border {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        .spinner-border-sm {
            width: 14px; height: 14px;
            border-width: 2px;
        }

        /* ========== التجاوب ========== */
        @media (max-width: 992px) {
            .login-wrapper { flex-direction: column-reverse; }
            .brand-section {
                flex: 0 0 auto;
                min-height: clamp(160px, 28vh, 260px);
                padding: 1rem;
                order: 2;
            }
            .form-section {
                flex: 1;
                padding: 1.25rem 1rem;
                order: 1;
                min-height: auto;
            }
            .login-card-modern {
                max-width: 100%;
                padding: 1.25rem;
            }
            .decorative-circle { display: none; }
        }

        @media (max-width: 576px) {
            .brand-section { min-height: 140px; padding: 0.75rem; }
            .logo-container {
                width: 85px; height: 85px;
                margin-bottom: 0.6rem;
                padding: 8px;
            }
            .ministry-title { font-size: 0.82rem; }
            .system-subtitle { font-size: 0.75rem; }
            .form-section { padding: 1rem 0.75rem; }
            .login-card-modern {
                padding: 1.1rem;
                border-radius: 14px;
            }
            .welcome-text { font-size: 1.15rem; }
            .actions-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .forgot-link { align-self: flex-end; font-size: 0.75rem; }
            .custom-input-group label { font-size: 0.75rem; }
            .custom-form-control {
                font-size: 0.82rem;
                padding: 0.5rem 2rem 0.5rem 0.6rem;
                height: 38px;
            }
            .submit-btn {
                font-size: 0.85rem;
                height: 40px;
                padding: 0.55rem;
            }
        }

        @media (max-height: 700px) and (max-width: 576px) {
            .login-wrapper { overflow-y: auto; }
            body { overflow: auto; }
            .brand-section { min-height: 120px; }
        }

        @media (min-width: 1400px) {
            .brand-content { transform: scale(1.05); }
            .login-card-modern { max-width: 420px; }
        }

        /* Floating particles animation */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.15);
            pointer-events: none;
            animation: particleFloat 15s infinite ease-in-out;
        }

        @keyframes particleFloat {
            0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
        }
    </style>
<base target="_blank">
</head>

<body>

    <div class="login-wrapper">

        <!-- قسم النموذج -->
        <div class="form-section">
            <div class="login-card-modern">
                <div class="form-header">
                    <h2 class="welcome-text">مرحباً بك مجدداً</h2>
                    <p class="instruction-text">سجل الدخول للمتابعة إلى لوحة التحكم</p>
                </div>

                <form action="#" method="POST" id="loginForm" novalidate>
                    <input type="hidden" name="_token" value="demo-token-12345">

                    <div class="custom-input-group">
                        <label for="phone">رقم الهاتف</label>
                        <div class="input-wrapper">
                            <input type="text" id="phone" name="phone" class="custom-form-control"
                                placeholder="أدخل رقم الهاتف" value="" required autofocus
                                autocomplete="tel">
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="custom-input-group">
                        <label for="password">كلمة المرور</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="custom-form-control"
                                placeholder="أدخل كلمة المرور" required autocomplete="current-password" minlength="6">
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword()"
                                aria-label="إظهار/إخفاء كلمة المرور">
                                <i class="fas fa-eye" id="toggleIconEye" style="font-size: 14px;"></i>
                                <i class="fas fa-eye-slash" id="toggleIconEyeSlash"
                                    style="font-size: 14px; display: none;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="actions-row">
                        <label class="remember-me">
                            <div class="custom-checkbox-wrapper">
                                <input type="checkbox" name="remember" id="remember" checked>
                                <span class="custom-checkbox"></span>
                            </div>
                            <span>تذكرني</span>
                        </label>
                        <a href="#" class="forgot-link" onclick="openModal(event)">نسيت كلمة المرور؟</a>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <div class="spinner" id="btnSpinner"></div>
                        <span id="btnText">تسجيل الدخول</span>
                        <i class="fas fa-arrow-left" id="btnIcon" style="font-size: 14px;"></i>
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small style="font-size: 0.72rem; color: var(--text-light);">
                        تواجه مشكلة؟
                        <a href="#" class="text-decoration-none" style="color: var(--primary-light); font-weight: 600;">
                            تواصل مع الدعم الفني
                        </a>
                    </small>
                </div>
            </div>
        </div>

        <!-- قسم الهوية البصرية -->
        <div class="brand-section">
            <div class="decorative-circle c-1"></div>
            <div class="decorative-circle c-2"></div>

            <!-- Floating particles -->
            <div class="particle" style="width: 6px; height: 6px; top: 20%; left: 15%; animation-delay: 0s;"></div>
            <div class="particle" style="width: 4px; height: 4px; top: 60%; left: 80%; animation-delay: 3s;"></div>
            <div class="particle" style="width: 8px; height: 8px; top: 40%; left: 70%; animation-delay: 6s;"></div>
            <div class="particle" style="width: 5px; height: 5px; top: 80%; left: 30%; animation-delay: 9s;"></div>

            <div class="brand-content">
                <div class="logo-container">
                    <i class="fas fa-landmark logo-placeholder"></i>
                </div>
                <h1 class="ministry-title">وزارة الزراعة والثروة السمكية والموارد المائية</h1>
                <div class="divider"></div>
                <h2 class="system-subtitle">نظام إدارة وتمويل المشاريع</h2>
            </div>
        </div>

    </div>

    <!-- Modal استعادة كلمة المرور -->
    <div class="modal-overlay" id="forgotPasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">استعادة كلمة المرور</h5>
                <button type="button" class="modal-close" onclick="closeModal()" aria-label="إغلاق">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">

                <!-- خطوة 1 -->
                <div id="step-phone">
                    <p class="text-muted">أدخل رقم الهاتف المسجل لإرسال رمز التحقق.</p>
                    <div class="mb-2">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" id="reset-phone" class="form-control"
                            placeholder="مثال: 770000000" dir="ltr" style="text-align: right;">
                    </div>
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-primary" id="btn-send-otp" onclick="sendOtp()">
                            <span id="text-send-otp">إرسال رمز التحقق</span>
                            <div class="spinner-border spinner-border-sm text-light ms-2" id="spinner-send-otp" role="status"></div>
                        </button>
                    </div>
                </div>

                <!-- خطوة 2 -->
                <div id="step-otp" style="display: none;">
                    <p class="text-muted">أدخل رمز التحقق المرسل إلى هاتفك.</p>
                    <div class="mb-2">
                        <label class="form-label">رمز التحقق</label>
                        <input type="text" id="reset-otp" class="form-control text-center"
                            placeholder="6 أرقام" maxlength="6" dir="ltr"
                            style="letter-spacing: 4px; font-weight: bold; font-size: 1rem;">
                    </div>
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-primary" id="btn-verify-otp" onclick="verifyOtp()">
                            <span id="text-verify-otp">تحقق من الرمز</span>
                            <div class="spinner-border spinner-border-sm text-light ms-2" id="spinner-verify-otp" role="status"></div>
                        </button>
                    </div>
                </div>

                <!-- خطوة 3 -->
                <div id="step-new-password" style="display: none;">
                    <p class="text-muted">أدخل كلمة المرور الجديدة.</p>
                    <div class="mb-2">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" id="reset-new-password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" id="reset-confirm-password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-primary" id="btn-reset-password" onclick="resetPassword()">
                            <span id="text-reset-password">تغيير كلمة المرور</span>
                            <div class="spinner-border spinner-border-sm text-light ms-2" id="spinner-reset-password" role="status"></div>
                        </button>
                    </div>
                </div>

                <div id="reset-alert" class="alert"></div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('toggleIconEye');
            const eyeSlashIcon = document.getElementById('toggleIconEyeSlash');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'inline-block';
                eyeIcon.parentElement.setAttribute('aria-label', 'إخفاء كلمة المرور');
            } else {
                passwordField.type = 'password';
                eyeIcon.style.display = 'inline-block';
                eyeSlashIcon.style.display = 'none';
                eyeIcon.parentElement.setAttribute('aria-label', 'إظهار كلمة المرور');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const spinner = document.getElementById('btnSpinner');
            const icon = document.getElementById('btnIcon');

            if (btn.disabled) return;

            btn.disabled = true;
            btn.style.opacity = '0.85';
            text.textContent = 'جاري التحقق...';
            spinner.style.display = 'block';
            icon.style.display = 'none';

            // Simulate login
            setTimeout(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
                text.textContent = 'تسجيل الدخول';
                spinner.style.display = 'none';
                icon.style.display = 'inline-block';
                
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'error-msg';
                alertDiv.style.color = '#16a34a';
                alertDiv.innerHTML = '<i class="fas fa-check-circle" style="font-size: 12px;"></i> تم تسجيل الدخول بنجاح (عرض تجريبي)';
                this.appendChild(alertDiv);
                setTimeout(() => alertDiv.remove(), 3000);
            }, 2000);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const firstInput = document.querySelector('.custom-form-control');
            if (firstInput && window.innerWidth < 768) {
                setTimeout(() => firstInput.focus(), 300);
            }
        });

        // Modal functions
        function openModal(e) {
            e.preventDefault();
            const modal = document.getElementById('forgotPasswordModal');
            const loginPhone = document.getElementById('phone').value.trim();
            if (loginPhone) document.getElementById('reset-phone').value = loginPhone;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('forgotPasswordModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Reset form
            setTimeout(() => {
                document.getElementById('step-phone').style.display = 'block';
                document.getElementById('step-otp').style.display = 'none';
                document.getElementById('step-new-password').style.display = 'none';
                document.getElementById('reset-phone').value = '';
                document.getElementById('reset-otp').value = '';
                document.getElementById('reset-new-password').value = '';
                document.getElementById('reset-confirm-password').value = '';
                document.getElementById('reset-alert').style.display = 'none';
            }, 300);
        }

        // Close modal on overlay click
        document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        function showAlert(message, type = 'danger') {
            const alert = document.getElementById('reset-alert');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = message;
            alert.style.display = 'block';
        }

        function toggleLoading(btnId, isLoading) {
            const text = document.getElementById(`text-${btnId}`);
            const spinner = document.getElementById(`spinner-${btnId}`);
            const btn = document.getElementById(`btn-${btnId}`);
            if (isLoading) {
                btn.disabled = true;
                spinner.style.display = 'inline-block';
            } else {
                btn.disabled = false;
                spinner.style.display = 'none';
            }
        }

        function sendOtp() {
            const phone = document.getElementById('reset-phone').value.trim();
            if (!phone) { showAlert('يرجى إدخال رقم الهاتف'); return; }
            document.getElementById('reset-alert').style.display = 'none';
            toggleLoading('send-otp', true);
            
            setTimeout(() => {
                toggleLoading('send-otp', false);
                showAlert('تم إرسال رمز التحقق بنجاح (عرض تجريبي)', 'success');
                document.getElementById('step-phone').style.display = 'none';
                document.getElementById('step-otp').style.display = 'block';
                document.getElementById('reset-otp').focus();
            }, 1500);
        }

        function verifyOtp() {
            const otp = document.getElementById('reset-otp').value.trim();
            if (!otp || otp.length !== 6) { showAlert('أدخل رمز صحيح من 6 أرقام'); return; }
            document.getElementById('reset-alert').style.display = 'none';
            toggleLoading('verify-otp', true);
            
            setTimeout(() => {
                toggleLoading('verify-otp', false);
                showAlert('تم التحقق بنجاح (عرض تجريبي)', 'success');
                document.getElementById('step-otp').style.display = 'none';
                document.getElementById('step-new-password').style.display = 'block';
            }, 1500);
        }

        function resetPassword() {
            const newPassword = document.getElementById('reset-new-password').value;
            const confirmPassword = document.getElementById('reset-confirm-password').value;
            if (!newPassword || newPassword.length < 6) { showAlert('كلمة المرور 6 أحرف على الأقل'); return; }
            if (newPassword !== confirmPassword) { showAlert('تأكيد كلمة المرور غير متطابق'); return; }
            document.getElementById('reset-alert').style.display = 'none';
            toggleLoading('reset-password', true);
            
            setTimeout(() => {
                toggleLoading('reset-password', false);
                showAlert('تم تغيير كلمة المرور بنجاح. سيتم التحديث...', 'success');
                setTimeout(() => {
                    closeModal();
                }, 2000);
            }, 1500);
        }

        // Enter key support for modal inputs
        document.getElementById('reset-phone').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendOtp();
        });
        document.getElementById('reset-otp').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') verifyOtp();
        });
        document.getElementById('reset-confirm-password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') resetPassword();
        });
    </script>
</body>

</html>