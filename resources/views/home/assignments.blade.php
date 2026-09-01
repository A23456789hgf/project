                <div class="content-card-elegant mb-4">
                    <div class="card-header-elegant">
                        <div class="header-title-elegant">
                            <div class="title-dot-navy"></div>
                            <h5 class="mb-0 fw-semibold">التكليفات والمتابعة</h5>
                        </div>
                        <div class="d-flex gap-2">
                            @can('assignments.create')
                                <button class="btn-icon-elegant btn-gold-elegant" data-bs-toggle="modal"
                                    data-bs-target="#assignModal" title="تكليف جديد">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            @endcan
                            <button class="btn-icon-elegant btn-navy-elegant" onclick="loadAssignments()"
                                title="تحديث البيانات">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="filter-tabs-elegant">
                        <div class="nav nav-pills" id="assignmentFilters" role="tablist">
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
                        </div>
                    </div>

                    <!-- Assignments List -->
                    <div class="list-container-elegant" id="assignmentsList">
                        <div class="empty-state-elegant py-4">
                            <div class="empty-icon-elegant small">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <p class="text-muted-elegant mb-0 small">لا توجد تكليفات حالياً</p>
                        </div>
                    </div>
                </div>
