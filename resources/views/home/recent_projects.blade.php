                <div class="content-card-elegant h-100">
                    <div class="card-header-elegant">
                        <div class="header-title-elegant">
                            <div class="title-dot-gold"></div>
                            <h5 class="mb-0 fw-semibold">المشاريع الأخيرة</h5>
                        </div>
                        @can('projects.view')
                            <a href="{{ route('projects.index') }}" class="btn-text-elegant auth-perm-projects-view">
                                <span>عرض الأرشيف الكامل</span>
                                <i class="fas fa-arrow-left-long"></i>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body-elegant p-0">
                        @if($recentProjects && count($recentProjects) > 0)
                            <div class="table-responsive-elegant">
                                <table class="table-elegant mb-0">
                                    <thead>
                                        <tr>
                                            <th>اسم المشروع</th>
                                            <th>حالة التنفيذ</th>
                                            <th>تاريخ الإنشاء</th>
                                            <th class="text-center">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentProjects as $project)
                                            <tr>
                                                <td>
                                                    <div class="project-info-elegant">
                                                        <div class="project-avatar-elegant">
                                                            {{ mb_substr($project->project_name ?? '؟', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="project-name-elegant">
                                                                {{ $project->project_name ?? 'بدون اسم' }}
                                                            </div>
                                                            <small class="text-muted-elegant">معرف: #{{ $project->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($project->status === 'active' || $project->status === 'final')
                                                        <span
                                                            class="status-pill {{ $project->status === 'final' ? 'status-gold' : 'status-navy' }}">
                                                            <span class="status-dot"></span>
                                                            {{ $project->status === 'final' ? 'نهائي ومعتمد' : 'قيد التنفيذ' }}
                                                        </span>
                                                    @else
                                                        <span class="status-pill status-muted">
                                                            <span class="status-dot"></span>
                                                            مسودة أولية
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="date-elegant">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        {{ $project->created_at?->format('Y-m-d') ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @can('projects.view-details')
                                                        <a href="{{ route('projects.show', $project->id) }}"
                                                            class="btn-action-elegant auth-perm-projects-view-details"
                                                            title="عرض التفاصيل">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state-elegant">
                                <div class="empty-icon-elegant">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h6 class="mb-1 fw-semibold">لا توجد مشاريع مسجلة</h6>
                                <p class="text-muted-elegant mb-0 small">يمكنك البدء بإنشاء مشروعك الأول من الزر أعلاه</p>
                            </div>
                        @endif
                    </div>
                </div>
