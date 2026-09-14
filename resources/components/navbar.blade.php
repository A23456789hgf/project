<!-- ربط ملف التنسيقات الخارجي -->
<link rel="stylesheet" href="{{ asset('css/admin-layout.css') }}">
<!-- تأكد من تحميل Font Awesome إذا لم يكن محملاً في الـ layout الرئيسي -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/phpflasher-custom.css') }}">
<!-- ========================================== -->
<!-- 1. الشريط العلوي (Premium Navbar) -->
<!-- ========================================== -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-premium">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <!-- القسم الأيمن: زر القائمة والشعار -->
        <div class="d-flex align-items-center gap-3">
            <button class="nav-icon-btn d-lg-none" id="sidebarToggle" aria-label="فتح القائمة">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand-premium" href="{{ route('dashboard') }}">
                <img src="{{ asset('/images/logo.png') }}" alt="شعار الوزارة" class="navbar-logo" />
                <span class="brand-text d-none d-sm-inline">وزارة الزراعة والثروة السمكية والموارد المائية</span>
            </a>
        </div>

        <!-- القسم الأيسر: الإشعارات والملف الشخصي -->
        <div class="d-flex align-items-center gap-2">
            <!-- الإشعارات -->
            <div class="notifications-wrapper">
                @include('components.notifications')
            </div>

            <!-- الملف الشخصي -->
            <div class="dropdown">
                <button class="nav-icon-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false" data-bs-auto-close="outside">
                    <i class="fas fa-user"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-premium" aria-labelledby="userDropdown">
                    @hasPermission('profile.view')
                    <li>
                        <a class="dropdown-item dropdown-item-premium" href="{{ route('profile.show') }}">
                            <i class="fas fa-user-circle text-primary"></i>
                            <span>الملف الشخصي</span>
                        </a>
                    </li>
                    @endhasPermission

                    <li>
                        <hr class="dropdown-divider my-2" style="border-color: var(--border-light);">
                    </li>

                    <li>
                        <a class="dropdown-item dropdown-item-premium text-danger" href="javascript:void(0)"
                            onclick="confirmLogout()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>تسجيل الخروج</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- ========================================== -->
<!-- 2. طبقة التعتيم والقائمة الجانبية -->
<!-- ========================================== -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar-premium" id="sidebar">
    <div class="sidebar-header">
        <h5 class="mb-0"><i class="fas fa-th-large me-2 opacity-50"></i> القائمة الرئيسية</h5>
    </div>

    <div class="sidebar-content">
        <ul class="nav flex-column">
            @php $user = auth()->user(); @endphp

            @hasPermission('dashboard.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">الصفحة الرئيسية</span>
                    </span>
                </a>
            </li>
            @endhasPermission

            @php
                $isProjectsActive = request()->routeIs('projects.*') || request()->routeIs('financial-justifications.*');
                ob_start();
            @endphp
            @hasPermission('projects.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.index') ? 'active' : '' }}"
                    href="{{ route('projects.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">قائمة المشاريع</span></span>
                </a>
            </li>
            @endhasPermission

            @hasAnyPermission(['execution.view', 'execution.sidebar'])
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.implementation.index') ? 'active' : '' }}"
                    href="{{ route('projects.implementation.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">المشاريع المنفذة</span></span>
                </a>
            </li>
            @endhasAnyPermission

            @hasPermission('quality.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.quality.*') ? 'active' : '' }}"
                    href="{{ route('projects.quality.index') }}">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-chart-line nav-icon"></i><span class="nav-text">جودة المشاريع</span>
                    </span>
                </a>
            </li>
            @endhasPermission

            @hasAnyPermission(['execution.approve', 'execution.reject'])
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('execution.tracking') ? 'active' : '' }}"
                    href="{{ route('execution.tracking') }}">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-tasks nav-icon"></i><span class="nav-text">متابعة التنفيذ</span>
                    </span>
                </a>
            </li>
            @endhasAnyPermission
            @php $projectsMenu = trim(ob_get_clean()); @endphp

            @if(!empty($projectsMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isProjectsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-projects" role="button" aria-expanded="{{ $isProjectsActive ? 'true' : 'false' }}"
                        aria-controls="submenu-projects">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-project-diagram nav-icon"></i><span class="nav-text">إدارة المشاريع</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isProjectsActive ? 'show' : '' }}" id="submenu-projects">
                        <ul class="nav flex-column submenu-container">
                            {!! $projectsMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            @hasPermission('tasks.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('tasks.*') || request()->routeIs('projects.tasks.*') ? 'active' : '' }}"
                    href="{{ route('tasks.index') }}">
                    <span class="d-flex align-items-center w-100">
                        <i class="fas fa-clipboard-list nav-icon"></i><span class="nav-text">إدارة المهام</span>
                    </span>
                </a>
            </li>
            @endhasPermission

            @php
                $isReferralsActive = request()->routeIs('project-referrals.*') || request()->routeIs('referrals.*');
                ob_start();
            @endphp
            @hasPermission('referrals.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('project-referrals.index') ? 'active' : '' }}"
                    href="{{ route('project-referrals.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">إحالات المشاريع</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('correspondence.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('correspondence.index') ? 'active' : '' }}"
                    href="{{ route('correspondence.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">المراسلات بين
                            الجهات</span></span>
                </a>
            </li>
            @endhasPermission
            @php $referralsMenu = trim(ob_get_clean()); @endphp

            @if(!empty($referralsMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isReferralsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-referrals" role="button" aria-expanded="{{ $isReferralsActive ? 'true' : 'false' }}"
                        aria-controls="submenu-referrals">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-exchange-alt nav-icon"></i><span class="nav-text">إدارة الإحالات</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isReferralsActive ? 'show' : '' }}" id="submenu-referrals">
                        <ul class="nav flex-column submenu-container">
                            {!! $referralsMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $isPlanningActive = request()->routeIs('plans.*');
                ob_start();
            @endphp
            @hasPermission('plans.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('plans.index') ? 'active' : '' }}"
                    href="{{ route('plans.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">الخطط والمشاريع</span></span>
                </a>
            </li>
            @endhasPermission
            @php $planningMenu = trim(ob_get_clean()); @endphp

            @if(!empty($planningMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isPlanningActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-planning" role="button" aria-expanded="{{ $isPlanningActive ? 'true' : 'false' }}"
                        aria-controls="submenu-planning">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-paste nav-icon"></i><span class="nav-text">إدارة التخطيط</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isPlanningActive ? 'show' : '' }}" id="submenu-planning">
                        <ul class="nav flex-column submenu-container">
                            {!! $planningMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            {{-- @php
                $isValueChainsActive = request()->routeIs('value-chains.*') || request()->routeIs('value-chain-members.*') || request()->routeIs('global-financings.*');
                ob_start();
            @endphp
            @if($user?->hasPermission('value-chains.view'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('value-chains.index') ? 'active' : '' }}" href="{{ route('value-chains.index') }}"><span class="d-flex align-items-center w-100"><span class="nav-text">سلاسل القيمة</span></span></a></li>
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('value-chain-members.*') ? 'active' : '' }}" href="{{ route('value-chain-members.index') }}"><span class="d-flex align-items-center w-100"><span class="nav-text">أعضاء السلاسل</span></span></a></li>
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('global-financings.*') ? 'active' : '' }}" href="{{ route('global-financings.index') }}"><span class="d-flex align-items-center w-100"><span class="nav-text">تمويلات السلاسل</span></span></a></li>
            @endif
            @php $valueChainsMenu = trim(ob_get_clean()); @endphp

            @if(!empty($valueChainsMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isValueChainsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#submenu-value-chains" role="button" aria-expanded="{{ $isValueChainsActive ? 'true' : 'false' }}" aria-controls="submenu-value-chains">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-sitemap nav-icon"></i><span class="nav-text">إدارة سلاسل القيمة</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isValueChainsActive ? 'show' : '' }}" id="submenu-value-chains">
                        <ul class="nav flex-column submenu-container">
                            {!! $valueChainsMenu !!}
                        </ul>
                    </div>
                </li>
            @endif --}}

            @php
                $isReportsActive = request()->routeIs('projects.reports.*');
                ob_start();
            @endphp
            @hasPermission('reports.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.index') ? 'active' : '' }}"
                    href="{{ route('projects.reports.index') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">لوحة معلومات التقارير</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.implementation.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.implementation') ? 'active' : '' }}"
                    href="{{ route('projects.reports.implementation') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">ملخص التنفيذ الميداني</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.quality.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.quality') ? 'active' : '' }}"
                    href="{{ route('projects.reports.quality') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">مقاييس الجودة</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.financial.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.financial') ? 'active' : '' }}"
                    href="{{ route('projects.reports.financial') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">المؤشرات المالية للمشاريع</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.progress.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.progress') ? 'active' : '' }}"
                    href="{{ route('projects.reports.progress') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">تتبع الإنجاز والتقدم</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.financial_erpnext.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.financial_erpnext') ? 'active' : '' }}"
                    href="{{ route('projects.reports.financial_erpnext') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">التقرير المالي ERPNext</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.pl_expense_summary.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.pl_expense_summary') ? 'active' : '' }}"
                    href="{{ route('projects.reports.pl_expense_summary') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">ملخص الإيرادات والنفقات</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.profit_and_loss.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.profit_and_loss') ? 'active' : '' }}"
                    href="{{ route('projects.reports.profit_and_loss') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">الأرباح والخسائر</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.official_summary.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.reports.official_summary') ? 'active' : '' }}"
                    href="{{ route('projects.reports.official_summary') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">التقرير الرسمي الشامل</span></span>
                </a>
            </li>
            @endhasPermission

            @hasPermission('reports.permissions.view')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('permissions.report') ? 'active' : '' }}"
                    href="{{ route('permissions.report') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">مراجعة صلاحيات النظام</span></span>
                </a>
            </li>
            @endhasPermission
            @php $reportsMenu = trim(ob_get_clean()); @endphp

            @if(!empty($reportsMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isReportsActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-reports" role="button" aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}"
                        aria-controls="submenu-reports">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-chart-pie nav-icon"></i><span class="nav-text">إدارة التقارير</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isReportsActive ? 'show' : '' }}" id="submenu-reports">
                        <ul class="nav flex-column submenu-container">
                            {!! $reportsMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $isEmpowermentActive = request()->routeIs('projects.empowerment');
                ob_start();
            @endphp
            @hasPermission('empowerment.sidebar')
            <li class="nav-item">
                <a class="nav-link-premium {{ request()->routeIs('projects.empowerment') ? 'active' : '' }}"
                    href="{{ route('projects.empowerment') }}">
                    <span class="d-flex align-items-center w-100"><span class="nav-text">القروض والحسابات</span></span>
                </a>
            </li>
            @endhasPermission
            @php $empowermentMenu = trim(ob_get_clean()); @endphp

            @if(!empty($empowermentMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isEmpowermentActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-empowerment" role="button"
                        aria-expanded="{{ $isEmpowermentActive ? 'true' : 'false' }}" aria-controls="submenu-empowerment">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-hand-holding-usd nav-icon"></i><span class="nav-text">إدارة التمكين</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isEmpowermentActive ? 'show' : '' }}" id="submenu-empowerment">
                        <ul class="nav flex-column submenu-container">
                            {!! $empowermentMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $encodingRoutes = ['programs.*', 'domains.*', 'subdomains.*', 'interventions.*', 'governorates.*', 'directorates.*', 'sub-areas.*', 'villages.*', 'financial-items.*', 'funding-sources.*', 'financing-types.*', 'value-chain-financing-types.*', 'formfinancing.*', 'subfinancing-forms.*', 'authorities.*', 'main-routers.*', 'sub-routers.*', 'priorities.*', 'units.*', 'beneficiary-groups.*', 'internal-entities.*'];
                $isEncodingActive = request()->routeIs(...$encodingRoutes);
                ob_start();
            @endphp

            @if($user?->hasPermission('programs.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('programs.*') ? 'active' : '' }}"
                        href="{{ route('programs.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">البرامج</span></span></a></li>
            @endif
            @if($user?->hasPermission('domains.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('domains.*') ? 'active' : '' }}"
                        href="{{ route('domains.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">المجالات</span></span></a></li>
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('subdomains.*') ? 'active' : '' }}"
                        href="{{ route('subdomains.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">المجالات الفرعية</span></span></a></li>
            @endif
            @if($user?->hasPermission('configuration.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('interventions.*') ? 'active' : '' }}"
                        href="{{ route('interventions.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">أنواع التدخل</span></span></a></li>
            @endif
            @if($user?->hasPermission('governorates.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('governorates.*') ? 'active' : '' }}"
                        href="{{ route('governorates.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">المحافظة</span></span></a></li>
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('directorates.*') ? 'active' : '' }}"
                        href="{{ route('directorates.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">المديرية</span></span></a></li>
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('sub-areas.*') ? 'active' : '' }}"
                        href="{{ route('sub-areas.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">العزلة / المنطقة</span></span></a></li>
            @endif
            @if($user?->hasPermission('villages.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('villages.*') ? 'active' : '' }}"
                        href="{{ route('villages.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">القرية / الحارة</span></span></a></li>
            @endif
            @if($user?->hasPermission('configuration.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('financial-items.*') ? 'active' : '' }}"
                        href="{{ route('financial-items.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">البنود المالية</span></span></a></li>
            @endif
            @if($user?->hasPermission('donors.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('funding-sources.*') ? 'active' : '' }}"
                        href="{{ route('funding-sources.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">مصدر التمويل</span></span></a></li>
            @endif
            @if($user?->hasPermission('financing-types.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('financing-types.*') ? 'active' : '' }}"
                        href="{{ route('financing-types.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">نوع التمويل</span></span></a></li>
            @endif
            @if($user?->hasPermission('value-chain-financing-types.view'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('value-chain-financing-types.*') ? 'active' : '' }}"
                        href="{{ route('value-chain-financing-types.index') }}"><span
                            class="d-flex align-items-center w-100"><span class="nav-text">أنواع تمويل
                                السلاسل</span></span></a></li>
            @endif
            @if($user?->hasPermission('financing-forms.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('formfinancing.*') ? 'active' : '' }}"
                        href="{{ route('formfinancing.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">أشكال التمويل</span></span></a></li>
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('subfinancing-forms.*') ? 'active' : '' }}"
                        href="{{ route('subfinancing-forms.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">أشكال التمويل الفرعية</span></span></a></li>
            @endif
            @if($user?->hasPermission('authorities.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('authorities.*') ? 'active' : '' }}"
                        href="{{ route('authorities.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">الجهة</span></span></a></li>
            @endif
            @if($user?->hasPermission('configuration.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('internal-entities.*') ? 'active' : '' }}"
                        href="{{ route('internal-entities.index') }}"><span class="d-flex align-items-center w-100"><i
                                class="fas fa-sitemap nav-icon"></i><span class="nav-text">الجهات الداخلية
                                المنظمة</span></span></a></li>
            @endif
            @if($user?->hasPermission('priorities.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('priorities.*') ? 'active' : '' }}"
                        href="{{ route('priorities.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">الأولويات</span></span></a></li>
            @endif
            @if($user?->hasPermission('configuration.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('units.*') ? 'active' : '' }}"
                        href="{{ route('units.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">الوحدة</span></span></a></li>
            @endif
            @if($user?->hasPermission('beneficiaries.sidebar') || $user?->hasPermission('beneficiary-groups.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('beneficiary-groups.*') ? 'active' : '' }}"
                        href="{{ route('beneficiary-groups.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">الفئات المستفيدة</span></span></a></li>
            @endif
            @php $encodingMenu = trim(ob_get_clean()); @endphp

            @if(!empty($encodingMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isEncodingActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-encoding" role="button" aria-expanded="{{ $isEncodingActive ? 'true' : 'false' }}"
                        aria-controls="submenu-encoding">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-cogs nav-icon"></i><span class="nav-text">الترميز والإعدادات</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isEncodingActive ? 'show' : '' }}" id="submenu-encoding">
                        <ul class="nav flex-column submenu-container">
                            {!! $encodingMenu !!}
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $userManagementRoutes = ['users.*', 'roles.*', 'roles-permissions.*', 'admin.audit-logs.*'];
                $isUserManagementActive = request()->routeIs(...$userManagementRoutes);
                ob_start();
            @endphp
            @if($user?->hasPermission('users.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('users.index') ? 'active' : '' }}"
                        href="{{ route('users.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">قائمة المستخدمين</span></span></a></li>
            @endif
            @if($user?->hasPermission('roles-permissions.sidebar'))
                <li class="nav-item"><a class="nav-link-premium {{ request()->routeIs('roles.index*') ? 'active' : '' }}"
                        href="{{ route('roles.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">إدارة الأدوار والصلاحيات</span></span></a></li>
            @endif
            @if($user?->hasPermission('audit-logs.sidebar'))
                <li class="nav-item"><a
                        class="nav-link-premium {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                        href="{{ route('admin.audit-logs.index') }}"><span class="d-flex align-items-center w-100"><span
                                class="nav-text">سجل الأنشطة</span></span></a></li>
            @endif
            @php $usersMenu = trim(ob_get_clean()); @endphp

            @if(!empty($usersMenu))
                <li class="nav-item">
                    <a class="nav-link-premium {{ $isUserManagementActive ? 'active' : '' }}" data-bs-toggle="collapse"
                        href="#submenu-users" role="button" aria-expanded="{{ $isUserManagementActive ? 'true' : 'false' }}"
                        aria-controls="submenu-users">
                        <span class="d-flex align-items-center w-100">
                            <i class="fas fa-users-cog nav-icon"></i><span class="nav-text">إدارة المستخدمين</span>
                        </span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </a>
                    <div class="collapse {{ $isUserManagementActive ? 'show' : '' }}" id="submenu-users">
                        <ul class="nav flex-column submenu-container">
                            {!! $usersMenu !!}
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. السكربتات (Logic & Interactions) -->
<!-- ========================================== -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'هل تود تسجيل الخروج؟',
            text: 'سيتم إنهاء جلستك الحالية بأمان',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> نعم، خروج',
            cancelButtonText: '<i class="fas fa-times me-1"></i> إلغاء',
            customClass: {
                confirmButton: 'btn btn-primary px-4',
                cancelButton: 'btn btn-light text-dark px-4 border'
            },
            buttonsStyling: true,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');
        }

        if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
    });
</script>