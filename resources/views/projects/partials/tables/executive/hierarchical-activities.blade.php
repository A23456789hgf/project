<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-tasks"></i>الأنشطة التنفيذية - عرض هرمي موسع
    </div>
    <div class="project-table-wrapper">
        <table class="project-table hierarchical-table" id="executiveActivitiesTable">
            <thead>
                <tr class="header-row">
                    <th width="40" class="text-center"></th>
                    <th>النشاط</th>
                    <th width="120">الوزن (%)</th>
                    <th width="130">المخرج</th>
                    <th width="150">التكاليف</th>
                    <th width="150">الإجراءات</th>
                </tr>
                <tr><td colspan="6"><div class="d-flex gap-3 align-items-center px-3 py-2"><span>المجموع:</span><span id="executiveActivitiesWeightDisplay" class="badge bg-secondary">0.00%</span><span id="executiveActivitiesWeightStatus" class="badge bg-warning" style="display: none;">غير موازن</span></div></td></tr>
            </thead>
            <tbody id="executive-activities-container">
                @if(isset($project) && $project->executiveActivities && $project->executiveActivities->count() > 0)
                    @foreach($project->executiveActivities as $activityIndex => $activity)
                        @include('projects.partials.tables.executive.hierarchical-activity', [
                            'activityIndex' => $activityIndex,
                            'activity' => $activity,
                            'projectOutputs' => isset($project->resultOutputs) ? $project->resultOutputs : collect(),
                            'projectRisks' => isset($project->risks) ? $project->risks : collect()
                        ])
                    @endforeach
                @else
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><br>لا توجد أنشطة تنفيذية</td></tr>
                @endif
            </tbody>
        </table>
        <div class="p-3"><button type="button" class="btn btn-primary" id="add-executive-activity-btn"><i class="fas fa-plus"></i>إضافة نشاط</button></div>
    </div>
</div>

<style>
.hierarchical-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.hierarchical-table th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 0.75rem; text-align: right; font-weight: 600; }
.hierarchical-table .level-1 { background: #fff; }
.hierarchical-table .level-1 td { padding: 0.75rem; border-bottom: 1px solid #dee2e6; }
.hierarchical-table .level-2 td { padding: 0.6rem 0.75rem; background: #f9f9f9; border-bottom: 1px solid #e9ecef; }
.hierarchical-table .level-3 td { padding: 0.6rem 0.75rem; background: #f5f7fa; border-bottom: 1px solid #e9ecef; }
.hierarchical-table .level-4 td { padding: 0; background: #fff; border-bottom: 1px solid #e9ecef; }
.hierarchical-table .level-5 td { padding: 0; background: #fff; border-bottom: 1px solid #e9ecef; }
.expand-btn { color: #666; transition: transform 0.2s; }
.expand-btn.expanded .expand-icon { transform: rotate(-90deg); }
.ps-4 { padding-left: 2rem !important; }
.ps-5 { padding-left: 3rem !important; }
.ps-6 { padding-left: 4rem !important; }
.ps-7 { padding-left: 5rem !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('executiveActivitiesTable');
    if (!table) return;
    
    table.addEventListener('click', e => {
        const btn = e.target.closest('.expand-btn');
        if (!btn) return;
        
        const row = btn.closest('tr');
        const isExpanded = row.dataset.expanded === 'true';
        const actIdx = row.dataset.activityIndex;
        const actIdx2 = row.dataset.actionIndex;
        
        if (row.classList.contains('level-1')) {
            table.querySelectorAll(`.level-2[data-activity-index="${actIdx}"]`).forEach(r => 
                r.style.display = isExpanded ? 'none' : 'table-row');
        } else if (row.classList.contains('level-2')) {
            const level3and4 = table.querySelectorAll(`.level-3[data-activity-index="${actIdx}"][data-action-index="${actIdx2}"], .level-4[data-activity-index="${actIdx}"][data-action-index="${actIdx2}"], .level-5[data-activity-index="${actIdx}"][data-action-index="${actIdx2}"]`);
            level3and4.forEach(r => r.style.display = isExpanded ? 'none' : 'table-row');
        } else if (row.classList.contains('level-3')) {
            table.querySelectorAll(`.level-5[data-activity-index="${actIdx}"][data-action-index="${actIdx2}"]`).forEach(r => 
                r.style.display = isExpanded ? 'none' : 'table-row');
        }
        
        btn.classList.toggle('expanded');
        row.dataset.expanded = !isExpanded;
    });
});
</script>
