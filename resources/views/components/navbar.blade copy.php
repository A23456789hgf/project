<!-- الشريط العلوي -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="menu-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand mb-0 h1" href="{{ route('dashboard') }}">
                <img src="{{ asset('/images/logo.png') }}" alt="الشعار" width="40" />
                وزارة الزراعة والثروة السمكية والموارد المائية
            </a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Notification System -->
            @can('notifications.view')
                @include('components.notifications')
            @endcan

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <button
                    class="btn btn-link dropdown-toggle d-flex align-items-center gap-2 p-1 text-decoration-none border-0"
                    style="color:#fff !important; background: rgba(255,255,255,0.08); border-radius: 999px;"
                    type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-white text-primary d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px; border-radius: 50%; font-size: 0.85rem;">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="d-none d-md-inline small" style="color:#fff;">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                            href="{{ route('profile.show') }}">
                            <i class="fas fa-user-circle text-primary"></i>
                            <span>الملف الشخصي</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger"
                            href="javascript:void(0)" onclick="confirmLogout()">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>تسجيل الخروج</span>
                        </a>
                    </li>
                </ul>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                @csrf
            </form>

            <script>
                function confirmLogout() {
                    Swal.fire({
                        title: 'هل تود تسجيل الخروج؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'نعم، خروج',
                        cancelButtonText: 'لا',
                        customClass: {
                            confirmButton: 'btn btn-danger mx-2',
                            cancelButton: 'btn btn-secondary mx-2'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                }
            </script>

        </div>
    </div>
</nav>

<!-- طبقة التعتيم للجوال -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- القائمة الجانبية -->
<div class="sidebar-container" id="sidebar">
    <div class="sidebar-header">
        <h5 class="mb-0">القائمة الجانبية</h5>
    </div>

    <div class="sidebar-content">
        <ul class="nav flex-column">
            @php
                $user = auth()->user();
            @endphp

            @can('dashboard.sidebar')
                <li class="nav-item auth-perm-dashboard-sidebar" data-sidebar-module="dashboard">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> الصفحة الرئيسية
                    </a>
                </li>
            @endcan

            @canany(['projects.sidebar', 'projects-implementation.sidebar', 'quality.sidebar', 'execution.sidebar'])
                <li class="nav-item auth-perm-projects-sidebar auth-perm-execution-sidebar auth-perm-quality-sidebar"
                    data-sidebar-module="projects">
                    @php
                        $isProjectsActive = request()->routeIs('projects.*')
                            || request()->routeIs('execution.*')
                            || request()->routeIs('projects.quality.*');
                    @endphp

                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isProjectsActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-projects" role="button"
                        aria-expanded="{{ $isProjectsActive ? 'true' : 'false' }}" aria-controls="submenu-projects">

                        <span>
                            <i class="fas fa-project-diagram"></i> مشروع
                        </span>

                        <i class="fas fa-chevron-down small"></i>
                    </a>

                    <div class="collapse ps-3 {{ $isProjectsActive ? 'show' : '' }}" id="submenu-projects">
                        <ul class="nav flex-column">
                            @can('projects.sidebar')
                                <li class="nav-item auth-perm-projects-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}"
                                        href="{{ route('projects.index') }}">
                                        قائمة المشاريع
                                    </a>
                                </li>
                            @endcan

                            @can('projects-implementation.sidebar')
                                <li class="nav-item auth-perm-projects-implementation-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.implementation.index') ? 'active' : '' }}"
                                        href="{{ route('projects.implementation.index') }}">
                                        المشاريع المنفذة
                                    </a>
                                </li>
                            @endcan

                            @can('quality.sidebar')
                                <li class="nav-item auth-perm-quality-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.quality.*') ? 'active' : '' }}" href="javascript:void(0)">
                                        <i class="fas fa-chart-line me-2"></i>جودة المشاريع
                                    </a>
                                </li>
                            @endcan

                            @can('execution.sidebar')
                                <li class="nav-item auth-perm-execution-sidebar">
                                    <a class="nav-link {{ request()->routeIs('execution.tracking') ? 'active' : '' }}" href="javascript:void(0)">
                                        <i class="fas fa-tasks me-2"></i>متابعة التنفيذ
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @canany(['referrals.sidebar', 'correspondence.sidebar', 'memoirs.sidebar'])
                <li class="nav-item auth-perm-referrals-sidebar auth-perm-correspondence-sidebar auth-perm-memoirs-sidebar"
                    data-sidebar-module="correspondence">
                    @php
                        $isReferralsActive = request()->routeIs('project-referrals.*')
                            || request()->routeIs('referrals.*')
                            || request()->routeIs('correspondence.*')
                            || request()->routeIs('memoirs.*');
                    @endphp

                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isReferralsActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-referrals" role="button"
                        aria-expanded="{{ $isReferralsActive ? 'true' : 'false' }}" aria-controls="submenu-referrals">

                        <span>
                            <i class="fas fa-exchange-alt"></i> إدارة الإحالات
                        </span>

                        <i class="fas fa-chevron-down small"></i>
                    </a>

                    <div class="collapse ps-3 {{ $isReferralsActive ? 'show' : '' }}" id="submenu-referrals">
                        <ul class="nav flex-column">
                            @can('referrals.sidebar')
                                <li class="nav-item auth-perm-referrals-sidebar">
                                    <a class="nav-link {{ request()->routeIs('project-referrals.index') ? 'active' : '' }}"
                                        href="{{ route('project-referrals.index') }}">
                                        إحالات المشاريع
                                    </a>
                                </li>
                            @endcan

                            @can('correspondence.sidebar')
                                <li class="nav-item auth-perm-correspondence-sidebar">
                                    <a class="nav-link {{ request()->routeIs('correspondence.index') ? 'active' : '' }}"
                                        href="{{ route('correspondence.index') }}">
                                        لوحة متابعة الإحالات والمراسلات
                                    </a>
                                </li>
                            @endcan

                            @can('memoirs.sidebar')
                                <li class="nav-item auth-perm-memoirs-sidebar">
                                    <a class="nav-link {{ request()->routeIs('memoirs.index') ? 'active' : '' }}"
                                        href="{{ route('memoirs.index') }}">
                                        <i class="fas fa-file-signature me-1"></i> المذكرات الإدارية
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @can('plans.sidebar')
                <li class="nav-item auth-perm-plans-sidebar" data-sidebar-module="planning">
                    @php
                        $isPlanningActive = request()->routeIs('plans.*');
                    @endphp

                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isPlanningActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-planning" role="button"
                        aria-expanded="{{ $isPlanningActive ? 'true' : 'false' }}" aria-controls="submenu-planning">
                        <span>
                            <i class="fas fa-paste"></i> إدارة التخطيط
                        </span>
                        <i class="fas fa-chevron-down small"></i>
                    </a>

                    <div class="collapse ps-3 {{ $isPlanningActive ? 'show' : '' }}" id="submenu-planning">
                        <ul class="nav flex-column">
                            @can('plans.index')
                                <li class="nav-item auth-perm-plans-sidebar">
                                    <a class="nav-link {{ request()->routeIs('plans.index') ? 'active' : '' }}"
                                        href="{{ route('plans.index') }}">
                                        الخطط والمشاريع
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcan

            @can('reports.sidebar')
                <li class="nav-item auth-perm-reports-sidebar" data-sidebar-module="reports">
                    @php
                        $isReportsActive = request()->routeIs('projects.reports.*');
                    @endphp

                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isReportsActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-reports" role="button"
                        aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}" aria-controls="submenu-reports">
                        <span>
                            <i class="fas fa-chart-pie"></i> إدارة التقارير
                        </span>
                        <i class="fas fa-chevron-down small"></i>
                    </a>

                    <div class="collapse ps-3 {{ $isReportsActive ? 'show' : '' }}" id="submenu-reports">
                        <ul class="nav flex-column">
                            @can('reports.index')
                                <li class="nav-item auth-perm-reports-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.reports.index') ? 'active' : '' }}"
                                        href="{{ route('projects.reports.index') }}">
                                        لوحة معلومات التقارير
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcan

            @can('empowerment.sidebar')
                <li class="nav-item auth-perm-empowerment-sidebar" data-sidebar-module="empowerment">
                    @php
                        $isEmpowermentActive = request()->routeIs('projects.empowerment*');
                    @endphp
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isEmpowermentActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-empowerment" role="button"
                        aria-expanded="{{ $isEmpowermentActive ? 'true' : 'false' }}" aria-controls="submenu-empowerment">
                        <span>
                            <i class="fas fa-hand-holding-usd"></i> إدارة التمكين
                        </span>
                        <i class="fas fa-chevron-down small"></i>
                    </a>

                    <div class="collapse ps-3 {{ $isEmpowermentActive ? 'show' : '' }}" id="submenu-empowerment">
                        <ul class="nav flex-column">
                            @can('empowerment.loans.view')
                                <li class="nav-item auth-perm-empowerment-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.empowerment') ? 'active' : '' }}"
                                        href="{{ route('projects.empowerment') }}">
                                        القروض والحسابات
                                    </a>
                                </li>
                            @endcan

                            @can('empowerment.beneficiaries.view')
                                <li class="nav-item auth-perm-empowerment-sidebar">
                                    <a class="nav-link {{ request()->routeIs('projects.empowerment.beneficiaries.all') ? 'active' : '' }}"
                                        href="{{ route('projects.empowerment.beneficiaries.all') }}">
                                        مستفيدو القروض
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcan

            @php
                $encodingPermissions = [
                    'programs.sidebar',
                    'domains.sidebar',
                    'governorates.sidebar',
                    'villages.sidebar',
                    'financing-types.sidebar',
                    'financing-forms.sidebar',
                    'authorities.sidebar',
                    'priorities.sidebar',
                    'beneficiaries.sidebar',
                    'configuration.sidebar',
                    'donors.sidebar'
                ];
            @endphp

            @canany($encodingPermissions)
                <li class="nav-item auth-perm-configuration-sidebar" data-sidebar-module="encoding">
                    @php
                        $encodingRoutes = ['programs.*', 'domains.*', 'subdomains.*', 'interventions.*', 'governorates.*', 'directorates.*', 'sub-areas.*', 'villages.*', 'financial-items.*', 'funding-sources.*', 'financing-types.*', 'formfinancing.*', 'subfinancing-forms.*', 'authorities.*', 'main-routers.*', 'sub-routers.*', 'priorities.*', 'units.*', 'beneficiary-groups.*'];
                        $isEncodingActive = request()->routeIs(...$encodingRoutes);
                    @endphp
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isEncodingActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-encoding" role="button"
                        aria-expanded="{{ $isEncodingActive ? 'true' : 'false' }}" aria-controls="submenu-encoding">
                        <span><i class="fas fa-cogs"></i> الترميزات</span>
                        <i class="fas fa-chevron-down small"></i>
                    </a>
                    <div class="collapse ps-3 {{ $isEncodingActive ? 'show' : '' }}" id="submenu-encoding">
                        <ul class="nav flex-column">
                            @can('programs.sidebar')
                                <li class="nav-item auth-perm-programs-sidebar">
                                    <a class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}"
                                        href="{{ route('programs.index') }}">البرامج</a>
                                </li>
                            @endcan

                            @can('domains.sidebar')
                                <li class="nav-item auth-perm-domains-sidebar">
                                    <a class="nav-link {{ request()->routeIs('domains.*') ? 'active' : '' }}"
                                        href="{{ route('domains.index') }}">المجالات</a>
                                </li>
                                <li class="nav-item auth-perm-domains-sidebar">
                                    <a class="nav-link {{ request()->routeIs('subdomains.*') ? 'active' : '' }}"
                                        href="{{ route('subdomains.index') }}">المجالات الفرعية</a>
                                </li>
                            @endcan

                            @can('configuration.sidebar')
                                <li class="nav-item auth-perm-configuration-sidebar">
                                    <a class="nav-link {{ request()->routeIs('interventions.*') ? 'active' : '' }}"
                                        href="{{ route('interventions.index') }}">أنواع التدخل</a>
                                </li>
                            @endcan

                            @can('governorates.sidebar')
                                <li class="nav-item auth-perm-governorates-sidebar">
                                    <a class="nav-link {{ request()->routeIs('governorates.*') ? 'active' : '' }}"
                                        href="{{ route('governorates.index') }}">المحافظة</a>
                                </li>
                                <li class="nav-item auth-perm-governorates-sidebar">
                                    <a class="nav-link {{ request()->routeIs('directorates.*') ? 'active' : '' }}"
                                        href="{{ route('directorates.index') }}">المديرية</a>
                                </li>
                                <li class="nav-item auth-perm-governorates-sidebar">
                                    <a class="nav-link {{ request()->routeIs('sub-areas.*') ? 'active' : '' }}"
                                        href="{{ route('sub-areas.index') }}">العزلة / المنطقة</a>
                                </li>
                            @endcan

                            @can('villages.sidebar')
                                <li class="nav-item auth-perm-villages-sidebar">
                                    <a class="nav-link {{ request()->routeIs('villages.*') ? 'active' : '' }}"
                                        href="{{ route('villages.index') }}">القرية / الحارة</a>
                                </li>
                            @endcan

                            @can('configuration.sidebar')
                                <li class="nav-item auth-perm-configuration-sidebar">
                                    <a class="nav-link {{ request()->routeIs('financial-items.*') ? 'active' : '' }}"
                                        href="{{ route('financial-items.index') }}">البنود المالية</a>
                                </li>
                            @endcan

                            @can('donors.sidebar')
                                <li class="nav-item auth-perm-donors-sidebar">
                                    <a class="nav-link {{ request()->routeIs('funding-sources.*') ? 'active' : '' }}"
                                        href="{{ route('funding-sources.index') }}">مصدر التمويل</a>
                                </li>
                            @endcan

                            @can('financing-types.sidebar')
                                <li class="nav-item auth-perm-financing-types-sidebar">
                                    <a class="nav-link {{ request()->routeIs('financing-types.*') ? 'active' : '' }}"
                                        href="{{ route('financing-types.index') }}">نوع التمويل </a>
                                </li>
                            @endcan

                            @can('financing-forms.sidebar')
                                <li class="nav-item auth-perm-financing-forms-sidebar">
                                    <a class="nav-link {{ request()->routeIs('formfinancing.*') ? 'active' : '' }}"
                                        href="{{ route('formfinancing.index') }}">اشكال التمويل </a>
                                </li>
                                <li class="nav-item auth-perm-financing-forms-sidebar">
                                    <a class="nav-link {{ request()->routeIs('subfinancing-forms.*') ? 'active' : '' }}"
                                        href="{{ route('subfinancing-forms.index') }}">اشكال التمويل الفرعية </a>
                                </li>
                            @endcan

                            @can('authorities.sidebar')
                                <li class="nav-item auth-perm-authorities-sidebar">
                                    <a class="nav-link {{ request()->routeIs('authorities.*') ? 'active' : '' }}"
                                        href="{{ route('authorities.index') }}"> الجهة </a>
                                </li>
                            @endcan

                            @can('configuration.sidebar')
                                <li class="nav-item auth-perm-configuration-sidebar">
                                    <a class="nav-link {{ request()->routeIs('internal-entities.*') ? 'active' : '' }}"
                                        href="{{ route('internal-entities.index') }}">
                                        الجهات الداخلية المنظمة
                                    </a>
                                </li>
                            @endcan

                            @can('entity_officers.view')
                                <li class="nav-item auth-perm-entity-officers-view">
                                    <a class="nav-link {{ request()->routeIs('entity-officers.*') ? 'active' : '' }}"
                                        href="{{ route('entity-officers.index') }}">
                                        مسؤولي الجهات
                                    </a>
                                </li>
                            @endcan

                            @can('entity-authorities.view')
                                <li class="nav-item auth-perm-entity-authorities-view">
                                    <a class="nav-link {{ request()->routeIs('entity-authorities.*') ? 'active' : '' }}"
                                        href="{{ route('entity-authorities.index') }}">
                                        صلاحيات الجهات
                                    </a>
                                </li>
                            @endcan

                            @can('priorities.sidebar')
                                <li class="nav-item auth-perm-priorities-sidebar">
                                    <a class="nav-link {{ request()->routeIs('priorities.*') ? 'active' : '' }}"
                                        href="{{ route('priorities.index') }}"> الأولويات </a>
                                </li>
                            @endcan

                            @can('configuration.sidebar')
                                <li class="nav-item auth-perm-configuration-sidebar">
                                    <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"
                                        href="{{ route('units.index') }}"> الوحدة </a>
                                </li>
                            @endcan

                            @can('beneficiaries.sidebar')
                                <li class="nav-item auth-perm-beneficiaries-sidebar">
                                    <a class="nav-link {{ request()->routeIs('beneficiary-groups.*') ? 'active' : '' }}"
                                        href="{{ route('beneficiary-groups.index') }}"> الفئات المستفيدة </a>
                                </li>
                            @endcan

                            @can('signatures.sidebar')
                                <li class="nav-item auth-perm-signatures-sidebar">
                                    <a class="nav-link {{ request()->routeIs('signatures.*') ? 'active' : '' }}"
                                        href="{{ route('signatures.index') }}">
                                        إدارة التواقيع
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @canany(['users.sidebar', 'roles-permissions.sidebar', 'audit-logs.sidebar'])
                <li class="nav-item auth-perm-users-sidebar auth-perm-roles-permissions-sidebar auth-perm-audit-logs-sidebar"
                    data-sidebar-module="users">
                    @php
                        $userManagementRoutes = ['users.*', 'roles.*', 'roles-permissions.*', 'admin.audit-logs.*'];
                        $isUserManagementActive = request()->routeIs(...$userManagementRoutes);
                    @endphp
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $isUserManagementActive ? 'active' : '' }}"
                        data-bs-toggle="collapse" href="#submenu-users" role="button"
                        aria-expanded="{{ $isUserManagementActive ? 'true' : 'false' }}" aria-controls="submenu-users">
                        <span><i class="fas fa-users-cog"></i> إدارة المستخدمين</span>
                        <i class="fas fa-chevron-down small"></i>
                    </a>
                    <div class="collapse ps-3 {{ $isUserManagementActive ? 'show' : '' }}" id="submenu-users">
                        <ul class="nav flex-column">
                            @can('users.sidebar')
                                <li class="nav-item auth-perm-users-sidebar">
                                    <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                        href="{{ route('users.index') }}">قائمة المستخدمين</a>
                                </li>
                            @endcan

                            @can('roles-permissions.sidebar')
                                <li class="nav-item auth-perm-roles-permissions-sidebar">
                                    <a class="nav-link {{ request()->routeIs('roles.index*') ? 'active' : '' }}"
                                        href="{{ route('roles.index') }}">إدارة الأدوار والصلاحيات </a>
                                </li>
                            @endcan

                            @can('audit-logs.sidebar')
                                <li class="nav-item auth-perm-audit-logs-sidebar">
                                    <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                                        href="{{ route('admin.audit-logs.index') }}">سجل الأنشطة</a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
        </ul>
    </div>
</div>