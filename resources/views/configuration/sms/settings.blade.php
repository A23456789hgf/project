@extends('layouts.app')

@section('content')
    @include('configuration.shared_styles')

    <style>
        /* ===== الخلفية العامة ===== */
        .sms-settings-wrapper {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            padding: 30px 0;
        }

        /* ===== البطاقة الرئيسية ===== */
        .sms-main-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 31, 63, 0.08);
            border: 1px solid rgba(0, 31, 63, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        /* ===== رأس الصفحة ===== */
        .sms-page-header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #004080 100%);
            padding: 30px 35px;
            position: relative;
            overflow: hidden;
        }

        .sms-page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .sms-page-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 70%);
            border-radius: 50%;
        }

        .sms-page-header h2 {
            color: #ffffff !important;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .sms-page-header .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sms-page-header .header-icon i {
            font-size: 1.3rem;
            color: #ffffff;
        }

        .sms-page-header .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 6px;
            position: relative;
            z-index: 1;
        }

        .sms-page-header .title-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00d4ff, #0099cc);
            border-radius: 3px;
            margin-top: 10px;
            position: relative;
            z-index: 1;
        }

        /* ===== زر العودة ===== */
        .btn-back-sms {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .btn-back-sms:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        /* ===== محتوى النموذج ===== */
        .sms-form-body {
            padding: 35px;
        }

        /* ===== عنوان القسم ===== */
        .section-title-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }

        .section-title-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #001f3f, #004080);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            box-shadow: 0 4px 15px rgba(0, 31, 63, 0.2);
        }

        .section-title-icon i {
            color: #ffffff;
            font-size: 1rem;
        }

        .section-title-text h5 {
            color: #001f3f;
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 2px;
        }

        .section-title-text p {
            color: #8492a6;
            font-size: 0.82rem;
            margin-bottom: 0;
        }

        /* ===== بطاقة الحدث ===== */
        .event-card {
            background: #ffffff;
            border: 2px solid #eef1f6;
            border-radius: 16px;
            padding: 22px 25px;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #001f3f, #004080);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .event-card:hover {
            border-color: #001f3f;
            box-shadow: 0 8px 30px rgba(0, 31, 63, 0.1);
            transform: translateY(-4px);
        }

        .event-card:hover::before {
            opacity: 1;
        }

        .event-card.active-event {
            border-color: #28a745;
            background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
        }

        .event-card.active-event::before {
            background: linear-gradient(180deg, #28a745, #20c997);
            opacity: 1;
        }

        .event-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .event-icon-wrapper.icon-project {
            background: linear-gradient(135deg, #e8f4fd, #d1ecf9);
            color: #0066cc;
        }

        .event-icon-wrapper.icon-user {
            background: linear-gradient(135deg, #f0e8fd, #e1d1f9);
            color: #6f42c1;
        }

        .event-icon-wrapper.icon-default {
            background: linear-gradient(135deg, #e8f8f0, #d1f5e3);
            color: #28a745;
        }

        .event-icon-wrapper i {
            font-size: 1.2rem;
        }

        .event-info h6 {
            color: #001f3f;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .event-info .event-desc {
            color: #8492a6;
            font-size: 0.82rem;
            line-height: 1.5;
        }

        /* ===== مفتاح التبديل المخصص ===== */
        .custom-switch-wrapper {
            flex-shrink: 0;
        }

        .form-check-input.custom-sms-switch {
            width: 52px;
            height: 28px;
            cursor: pointer;
            border: 2px solid #d1d9e6;
            background-color: #e9ecef;
            transition: all 0.3s ease;
            box-shadow: none !important;
        }

        .form-check-input.custom-sms-switch:checked {
            background-color: #28a745;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15) !important;
        }

        .form-check-input.custom-sms-switch:focus {
            box-shadow: 0 0 0 3px rgba(0, 31, 63, 0.1) !important;
        }

        /* ===== شارة الحالة ===== */
        .status-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 6px;
        }

        .status-badge.active {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-badge.inactive {
            background: rgba(132, 146, 166, 0.1);
            color: #8492a6;
        }

        /* ===== زر الحفظ ===== */
        .btn-save-sms {
            background: linear-gradient(135deg, #001f3f 0%, #004080 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 14px 50px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 25px rgba(0, 31, 63, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-save-sms::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-save-sms:hover::before {
            left: 100%;
        }

        .btn-save-sms:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(0, 31, 63, 0.35);
            color: #ffffff;
        }

        .btn-save-sms:active {
            transform: translateY(-1px);
        }

        /* ===== رسالة النجاح ===== */
        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #b1dfbb;
            border-radius: 14px;
            color: #155724;
            padding: 16px 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            animation: slideDown 0.5s ease;
        }

        .alert-success-custom i {
            font-size: 1.3rem;
            margin-right: 12px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== عداد الأحداث المفعّلة ===== */
        .events-counter {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 12px 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .events-counter .count-number {
            background: linear-gradient(135deg, #001f3f, #004080);
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .events-counter .count-label {
            color: #495057;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* ===== التجاوب ===== */
        @media (max-width: 768px) {
            .sms-page-header {
                padding: 20px;
            }

            .sms-form-body {
                padding: 20px;
            }

            .event-card {
                padding: 18px;
            }

            .btn-save-sms {
                width: 100%;
                padding: 14px;
            }
        }
    </style>

    <div class="sms-settings-wrapper">
        <div class="container">
            <div class="sms-main-card">

                {{-- ===== رأس الصفحة ===== --}}
                <div class="sms-page-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="header-icon">
                                <i class="fas fa-sms"></i>
                            </div>
                            <div>
                                <h2 class="mb-0">إعدادات أحداث SMS</h2>
                                <div class="title-line"></div>
                                <p class="subtitle mb-0">تفعيل أو تعطيل إشعارات الرسائل النصية لأحداث النظام</p>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('configuration.sms.logs') }}" class="btn btn-back-sms">
                                <i class="fas fa-arrow-right me-2"></i> العودة للسجل
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ===== محتوى النموذج ===== --}}
                <div class="sms-form-body">


                    <form action="{{ route('configuration.sms.update-settings') }}" method="POST">
                        @csrf

                        {{-- عنوان القسم --}}
                        <div class="section-title-wrapper">
                            <div class="section-title-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="section-title-text">
                                <h5>الأحداث المتاحة</h5>
                                <p>اختر الأحداث التي تريد إرسال إشعارات SMS عند حدوثها</p>
                            </div>
                        </div>

                        {{-- عداد الأحداث --}}
                        <div class="events-counter">
                            <div class="count-number" id="activeEventsCount">0</div>
                            <div class="count-label">أحداث مفعّلة حالياً</div>
                        </div>

                        <div class="row g-4">

                            {{-- حدث: اعتماد مشروع جديد --}}
                            <div class="col-md-6">
                                <div class="event-card {{ $events['project_approved'] === '1' ? 'active-event' : '' }}"
                                    id="card-project-approved">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="event-icon-wrapper icon-project">
                                                <i class="fas fa-project-diagram"></i>
                                            </div>
                                            <div class="event-info">
                                                <h6 class="mb-0">اعتماد مشروع جديد</h6>
                                                <span class="event-desc">
                                                    يتم إرسال رسالة عند اعتماد مشروع جديد
                                                </span>
                                                <span
                                                    class="status-badge {{ $events['project_approved'] === '1' ? 'active' : 'inactive' }}"
                                                    id="badge-project-approved">
                                                    {{ $events['project_approved'] === '1' ? '● مفعّل' : '○ معطّل' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="custom-switch-wrapper">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input custom-sms-switch" type="checkbox"
                                                    name="sms_event_project_approved" value="1" id="switch-project-approved"
                                                    {{ $events['project_approved'] === '1' ? 'checked' : '' }}
                                                    onchange="updateEventCard('project-approved', this)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- حدث: تسجيل مستخدم جديد --}}
                            <div class="col-md-6">
                                <div class="event-card {{ $events['user_registered'] === '1' ? 'active-event' : '' }}"
                                    id="card-user-registered">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="event-icon-wrapper icon-user">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <div class="event-info">
                                                <h6 class="mb-0">تسجيل مستخدم جديد</h6>
                                                <span class="event-desc">
                                                    يتم إرسال رسالة ترحيبية عند تفعيل حساب المستخدم
                                                </span>
                                                <span
                                                    class="status-badge {{ $events['user_registered'] === '1' ? 'active' : 'inactive' }}"
                                                    id="badge-user-registered">
                                                    {{ $events['user_registered'] === '1' ? '● مفعّل' : '○ معطّل' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="custom-switch-wrapper">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input custom-sms-switch" type="checkbox"
                                                    name="sms_event_user_registered" value="1" id="switch-user-registered"
                                                    {{ $events['user_registered'] === '1' ? 'checked' : '' }}
                                                    onchange="updateEventCard('user-registered', this)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- يمكنك إضافة المزيد من الأحداث بنفس الطريقة --}}

                        </div>

                        {{-- زر الحفظ --}}
                        <div class="col-12 mt-5 text-center">
                            @can('sms.settings.update')
                            <button type="submit" class="btn btn-save-sms">
                                <i class="fas fa-save me-2"></i> حفظ الإعدادات
                            </button>
                            @endcan
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== JavaScript للتحديث التفاعلي ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            updateCounter();
        });

        function updateEventCard(eventId, checkbox) {
            const card = document.getElementById('card-' + eventId);
            const badge = document.getElementById('badge-' + eventId);

            if (checkbox.checked) {
                card.classList.add('active-event');
                badge.className = 'status-badge active';
                badge.textContent = '● مفعّل';
            } else {
                card.classList.remove('active-event');
                badge.className = 'status-badge inactive';
                badge.textContent = '○ معطّل';
            }

            updateCounter();
        }

        function updateCounter() {
            const switches = document.querySelectorAll('.custom-sms-switch');
            let count = 0;
            switches.forEach(sw => {
                if (sw.checked) count++;
            });
            document.getElementById('activeEventsCount').textContent = count;
        }
    </script>

@endsection