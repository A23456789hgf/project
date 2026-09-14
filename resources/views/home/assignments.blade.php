<div class="content-card-elegant mb-4">
    <div class="card-header-elegant">
        <div class="header-title-elegant">
            <div class="title-dot-navy"></div>
            <h5 class="mb-0 fw-semibold">التكليفات والمهام</h5>
        </div>
        <div class="d-flex gap-2">
            @canany(['task.create', 'task.createGlobal', 'execution.assign'])
                <a href="{{ route('tasks.create') }}" class="btn-icon-elegant btn-gold-elegant" title="إضافة مهمة جديدة">
                    <i class="fas fa-plus"></i>
                </a>
            @endcanany
            @can('main_modules.tasks')
                <a href="{{ route('tasks.index') }}" class="btn-icon-elegant btn-outline-navy-elegant" title="إدارة كافة المهام">
                    <i class="fas fa-tasks"></i>
                </a>
            @endcan
            <button class="btn-icon-elegant btn-navy-elegant" onclick="loadAssignments()"
                title="تحديث البيانات">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs-elegant">
        <div class="nav nav-pills flex-nowrap overflow-auto" id="assignmentFilters" role="tablist">
            <button class="nav-link-elegant active" data-filter="all">
                الكل <span class="badge-elegant bg-secondary" id="count-all">0</span>
            </button>
            <button class="nav-link-elegant" data-filter="overdue">
                متأخرة <span class="badge-elegant bg-danger" id="count-overdue">0</span>
            </button>
            <button class="nav-link-elegant" data-filter="nearing">
                وشيكة <span class="badge-elegant bg-warning text-dark" id="count-nearing">0</span>
            </button>
            <button class="nav-link-elegant" data-filter="current">
                جارية <span class="badge-elegant bg-primary" id="count-current">0</span>
            </button>
            <button class="nav-link-elegant" data-filter="completed">
                مكتملة <span class="badge-elegant bg-success" id="count-completed">0</span>
            </button>
        </div>
    </div>

    <!-- Assignments List -->
    <div class="list-container-elegant" id="assignmentsList">
        <div class="empty-state-elegant py-4">
            <div class="empty-icon-elegant small">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <p class="text-muted-elegant mb-0 small">لا توجد تكليفات أو مهام حالياً</p>
        </div>
    </div>

    @can('main_modules.tasks')
        <div class="card-footer-elegant pt-2 pb-1 border-top border-light-subtle text-center">
            <a href="{{ route('tasks.index') }}" class="btn-link-elegant text-primary fw-semibold small d-inline-flex align-items-center gap-1">
                <span>الانتقال إلى إدارة المهام الشاملة</span>
                <i class="fas fa-arrow-left fa-xs"></i>
            </a>
        </div>
    @endcan
</div>
