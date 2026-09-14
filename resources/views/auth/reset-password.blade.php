<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>استعادة كلمة المرور - نظام إدارة المشاريع</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* نفس التصميم السابق مع بعض التعديلات البسيطة */
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            line-height: 1.5;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            margin: 0;
            padding: 0;
        }

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
            top: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card-modern {
            width: 100%;
            max-width: 420px;
            padding: clamp(1.25rem, 3vw, 2rem);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: clamp(12px, 2vw, 20px);
            box-shadow: 0 15px 45px rgba(8, 33, 76, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.08);
            position: relative;
            z-index: 10;
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
            bottom: -6px;
            right: 0;
            width: 45px;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-gold), var(--accent-gold-light));
            border-radius: 2px;
            transition: var(--transition);
        }

        .instruction-text {
            color: var(--text-light);
            font-size: clamp(0.8rem, 2vw, 0.85rem);
            margin-top: 0.6rem;
            margin-bottom: 0;
        }

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

        .input-wrapper {
            position: relative;
        }

        .input-wrapper>.input-icon {
            position: absolute;
            right: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: var(--transition);
            z-index: 5;
            pointer-events: none;
            font-size: 0.9rem;
        }

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

        .custom-form-control:focus+.input-icon {
            color: var(--accent-gold);
        }

        .custom-form-control::placeholder {
            color: #94a3b8;
            opacity: 1;
            text-align: right;
            font-size: 0.8rem;
        }

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
            min-height: 42px;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.25), transparent);
            transition: right 0.6s ease;
        }

        .submit-btn:hover::before {
            right: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(8, 33, 76, 0.35);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            font-size: 0.78rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.78rem;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            padding: 0.3rem 0.5rem;
            border-radius: 5px;
            min-height: 32px;
        }

        .back-link:hover {
            color: var(--primary-dark);
            background: rgba(26, 75, 140, 0.08);
            text-decoration: none;
        }

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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23d4af37' stroke-width='0.5' opacity='0.12'%3E%3Cpath d='M30 0L60 30L30 60L0 30Z'/%3E%3Cpath d='M30 8L52 30L30 52L8 30Z'/%3E%3Ccircle cx='30' cy='30' r='12'/%3E%3C/g%3E%3C/svg%3E");
        }

        .brand-section::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.08) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
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
            0%,
            100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-15px) scale(1.01);
            }
        }

        .brand-content {
            text-align: center;
            position: relative;
            z-index: 2;
            padding: 0.5rem;
            animation: fadeInUp 0.8s ease-out;
            max-width: 100%;
        }

        .brand-content h1,
        .brand-content h2,
        .brand-content p {
            margin-top: 0;
            margin-bottom: 0.5rem;
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
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--accent-gold), transparent, var(--accent-gold));
            opacity: 0.25;
            z-index: -1;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,
            100% {
                opacity: 0.25;
                transform: scale(1);
            }
            50% {
                opacity: 0.4;
                transform: scale(1.03);
            }
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
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
            margin: 0.75rem auto;
        }

        /* التجاوب (نفس التجاوب السابق) */
        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column-reverse;
            }
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
                padding: 1.5rem;
            }
            .decorative-circle {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .brand-section {
                min-height: 180px;
                padding: 0.8rem;
            }
            .logo-container {
                width: 95px;
                height: 95px;
                margin-bottom: 0.75rem;
                padding: 10px;
            }
            .ministry-title {
                font-size: 0.9rem;
            }
            .system-subtitle {
                font-size: 0.8rem;
            }
            .login-card-modern {
                padding: 1.25rem;
            }
            .form-section {
                padding: 1rem;
            }
        }

        @media (max-width: 576px) {
            .brand-section {
                min-height: 140px;
                padding: 0.6rem;
            }
            .logo-container {
                width: 75px;
                height: 75px;
                margin-bottom: 0.5rem;
                padding: 8px;
            }
            .ministry-title {
                font-size: 0.78rem;
            }
            .system-subtitle {
                font-size: 0.7rem;
            }
            .form-section {
                padding: 0.75rem;
            }
            .login-card-modern {
                padding: 1.1rem;
                border-radius: 14px;
                box-shadow: 0 10px 30px rgba(8, 33, 76, 0.06);
            }
            .welcome-text {
                font-size: 1.15rem;
            }
            .instruction-text {
                font-size: 0.78rem;
            }
            .custom-form-control {
                font-size: 16px !important;
                height: 38px;
                padding: 0.45rem 2rem 0.45rem 0.6rem;
            }
            .custom-input-group label {
                font-size: 0.75rem;
            }
            .submit-btn {
                font-size: 0.85rem;
                height: 40px;
                min-height: 40px;
                padding: 0.5rem;
            }
            .divider {
                width: 40px;
                margin: 0.5rem auto;
            }
            .back-link {
                font-size: 0.75rem;
                min-height: 40px;
            }
        }

        @media (max-width: 400px) {
            .brand-section {
                min-height: 100px;
                padding: 0.4rem;
            }
            .logo-container {
                width: 60px;
                height: 60px;
                margin-bottom: 0.3rem;
                padding: 6px;
                border-width: 2px;
            }
            .ministry-title {
                font-size: 0.65rem;
            }
            .system-subtitle {
                font-size: 0.6rem;
            }
            .login-card-modern {
                padding: 0.8rem;
                border-radius: 12px;
            }
            .welcome-text {
                font-size: 1rem;
            }
            .instruction-text {
                font-size: 0.7rem;
                margin-top: 0.4rem;
            }
            .custom-input-group {
                margin-bottom: 0.6rem;
            }
            .custom-form-control {
                font-size: 16px !important;
                height: 36px;
                padding: 0.4rem 1.8rem 0.4rem 0.5rem;
            }
            .submit-btn {
                font-size: 0.8rem;
                height: 38px;
                min-height: 38px;
                border-radius: 8px;
            }
            .form-header {
                margin-bottom: 0.8rem;
            }
            .divider {
                width: 30px;
            }
        }

        @media (max-height: 650px) and (max-width: 576px) {
            .login-wrapper {
                min-height: 100dvh;
            }
            .brand-section {
                min-height: 90px;
                padding: 0.3rem;
            }
            .logo-container {
                width: 55px;
                height: 55px;
                margin-bottom: 0.2rem;
                padding: 5px;
            }
            .ministry-title {
                font-size: 0.6rem;
                margin-bottom: 0.1rem;
            }
            .system-subtitle {
                font-size: 0.55rem;
            }
            .divider {
                margin: 0.2rem auto;
                width: 25px;
                height: 1.5px;
            }
            .form-section {
                padding: 0.4rem;
            }
            .login-card-modern {
                padding: 0.6rem;
                border-radius: 10px;
            }
            .form-header {
                margin-bottom: 0.5rem;
            }
            .welcome-text {
                font-size: 0.9rem;
            }
            .welcome-text::after {
                bottom: -3px;
                height: 2px;
                width: 30px;
            }
            .instruction-text {
                font-size: 0.65rem;
                margin-top: 0.2rem;
            }
            .custom-input-group {
                margin-bottom: 0.4rem;
            }
            .custom-input-group label {
                font-size: 0.65rem;
                margin-bottom: 0.1rem;
            }
            .custom-form-control {
                height: 32px;
                padding: 0.2rem 1.5rem 0.2rem 0.4rem;
                font-size: 14px !important;
                border-radius: 6px;
            }
            .submit-btn {
                height: 34px;
                min-height: 34px;
                font-size: 0.75rem;
                border-radius: 6px;
                padding: 0.3rem;
            }
            .back-link {
                font-size: 0.65rem;
                min-height: 30px;
                padding: 0.2rem;
            }
        }

        @media (min-width: 1400px) {
            .brand-content {
                transform: scale(1.05);
            }
            .login-card-modern {
                max-width: 440px;
                padding: 2.2rem;
            }
            .form-section {
                padding: 2rem;
            }
            .brand-section {
                flex: 1.2;
            }
        }

        @media (min-width: 2000px) {
            .login-card-modern {
                max-width: 500px;
                padding: 2.5rem;
            }
            .brand-section {
                flex: 1.5;
            }
            .logo-container {
                width: 280px;
                height: 280px;
            }
            .ministry-title {
                font-size: 1.8rem;
            }
            .system-subtitle {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">

        <!-- قسم النموذج -->
        <div class="form-section">
            <div class="login-card-modern">
                <div class="form-header">
                    <h2 class="welcome-text">استعادة كلمة المرور</h2>
                    <p class="instruction-text">أدخل رقم هاتفك لإعادة تعيين كلمة المرور</p>
                </div>

                <!-- خطوة 1: إدخال رقم الهاتف -->
                <div id="step-phone">
                    <div class="custom-input-group">
                        <label for="reset-phone">رقم الهاتف</label>
                        <div class="input-wrapper">
                            <input type="text" id="reset-phone" class="custom-form-control"
                                placeholder="أدخل رقم الهاتف" dir="ltr" inputmode="numeric">
                            <i class="fas fa-phone input-icon"></i>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="submit-btn" id="btn-send-otp">
                            <span id="text-send-otp">إرسال رمز التحقق</span>
                            <div class="spinner" id="spinner-send-otp" style="display: none;"></div>
                        </button>
                    </div>
                </div>

                <!-- خطوة 2: إدخال الرمز -->
                <div id="step-otp" style="display: none;">
                    <p class="instruction-text" style="margin-bottom: 0.75rem;">أدخل رمز التحقق المرسل إلى هاتفك.</p>
                    <div class="custom-input-group">
                        <label for="reset-otp">رمز التحقق</label>
                        <div class="input-wrapper">
                            <input type="text" id="reset-otp" class="custom-form-control text-center"
                                placeholder="6 أرقام" maxlength="6" dir="ltr"
                                style="letter-spacing: 4px; font-weight: bold; font-size: 1rem;" inputmode="numeric">
                            <i class="fas fa-shield-alt input-icon"></i>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="submit-btn" id="btn-verify-otp">
                            <span id="text-verify-otp">تحقق من الرمز</span>
                            <div class="spinner" id="spinner-verify-otp" style="display: none;"></div>
                        </button>
                    </div>
                </div>

                <!-- خطوة 3: كلمة مرور جديدة -->
                <div id="step-new-password" style="display: none;">
                    <p class="instruction-text" style="margin-bottom: 0.75rem;">أدخل كلمة المرور الجديدة.</p>
                    <div class="custom-input-group">
                        <label for="reset-new-password">كلمة المرور الجديدة</label>
                        <div class="input-wrapper">
                            <input type="password" id="reset-new-password" class="custom-form-control" placeholder="••••••••">
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    <div class="custom-input-group">
                        <label for="reset-confirm-password">تأكيد كلمة المرور</label>
                        <div class="input-wrapper">
                            <input type="password" id="reset-confirm-password" class="custom-form-control" placeholder="••••••••">
                            <i class="fas fa-check-circle input-icon"></i>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="submit-btn" id="btn-reset-password">
                            <span id="text-reset-password">تغيير كلمة المرور</span>
                            <div class="spinner" id="spinner-reset-password" style="display: none;"></div>
                        </button>
                    </div>
                </div>

                <!-- رسائل التنبيه -->
                <div id="reset-alert" class="alert mt-3" style="display: none;"></div>

                <!-- رابط الرجوع إلى تسجيل الدخول -->
                <div class="text-center mt-3">
                    <a href="login.html" class="back-link">
                        <i class="fas fa-arrow-right"></i>
                        العودة إلى تسجيل الدخول
                    </a>
                </div>
            </div>
        </div>

        <!-- قسم الهوية -->
        <div class="brand-section">
            <div class="decorative-circle c-1"></div>
            <div class="decorative-circle c-2"></div>
            <div class="brand-content">
                <div class="logo-container">
                    <img src="images/logo.png" alt="شعار الوزارة">
                </div>
                <h1 class="ministry-title">وزارة الزراعة والثروة السمكية والموارد المائية</h1>
                <div class="divider"></div>
                <h2 class="system-subtitle">نظام إدارة وتمويل المشاريع</h2>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnSendOtp = document.getElementById('btn-send-otp');
            const btnVerifyOtp = document.getElementById('btn-verify-otp');
            const btnResetPassword = document.getElementById('btn-reset-password');
            const resetAlert = document.getElementById('reset-alert');

            function showAlert(message, type = 'danger') {
                resetAlert.className = `alert alert-${type}`;
                resetAlert.innerHTML = message;
                resetAlert.style.display = 'block';
            }

            function toggleLoading(btnId, isLoading) {
                const text = document.getElementById(`text-${btnId}`);
                const spinner = document.getElementById(`spinner-${btnId}`);
                const btn = document.getElementById(`btn-${btnId}`);
                if (isLoading) {
                    btn.disabled = true;
                    spinner.style.display = 'block';
                    text.textContent = 'جاري...';
                } else {
                    btn.disabled = false;
                    spinner.style.display = 'none';
                    // استعادة النص الأصلي (سيتم ضبطه حسب السياق)
                    if (btnId === 'send-otp') text.textContent = 'إرسال رمز التحقق';
                    else if (btnId === 'verify-otp') text.textContent = 'تحقق من الرمز';
                    else if (btnId === 'reset-password') text.textContent = 'تغيير كلمة المرور';
                }
            }

            // محاكاة إرسال الرمز
            btnSendOtp.addEventListener('click', function() {
                const phone = document.getElementById('reset-phone').value.trim();
                if (!phone) { showAlert('يرجى إدخال رقم الهاتف'); return; }
                resetAlert.style.display = 'none';
                toggleLoading('send-otp', true);
                setTimeout(() => {
                    toggleLoading('send-otp', false);
                    showAlert('تم إرسال رمز التحقق: 123456 (محاكاة)', 'success');
                    document.getElementById('step-phone').style.display = 'none';
                    document.getElementById('step-otp').style.display = 'block';
                }, 1500);
            });

            // محاكاة التحقق من الرمز
            btnVerifyOtp.addEventListener('click', function() {
                const otp = document.getElementById('reset-otp').value.trim();
                if (!otp || otp.length !== 6) { showAlert('أدخل رمز صحيح من 6 أرقام'); return; }
                resetAlert.style.display = 'none';
                toggleLoading('verify-otp', true);
                setTimeout(() => {
                    toggleLoading('verify-otp', false);
                    if (otp === '123456') {
                        showAlert('تم التحقق بنجاح', 'success');
                        document.getElementById('step-otp').style.display = 'none';
                        document.getElementById('step-new-password').style.display = 'block';
                    } else {
                        showAlert('رمز غير صحيح. استخدم 123456 للمحاكاة.');
                    }
                }, 1500);
            });

            // محاكاة تغيير كلمة المرور
            btnResetPassword.addEventListener('click', function() {
                const newPassword = document.getElementById('reset-new-password').value;
                const confirmPassword = document.getElementById('reset-confirm-password').value;
                if (!newPassword || newPassword.length < 6) { showAlert('كلمة المرور 6 أحرف على الأقل');
                    return; }
                if (newPassword !== confirmPassword) { showAlert('تأكيد كلمة المرور غير متطابق'); return; }
                resetAlert.style.display = 'none';
                toggleLoading('reset-password', true);
                setTimeout(() => {
                    toggleLoading('reset-password', false);
                    showAlert('تم تغيير كلمة المرور بنجاح (محاكاة). سيتم توجيهك إلى صفحة تسجيل الدخول.', 'success');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                }, 1500);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>