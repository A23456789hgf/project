<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Services\ProjectExportService;
use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\PreliminaryProcedureExecution;
use App\Models\Project;
use App\Models\ProjectExecution;
use App\Models\ProjectQuality;
use App\Services\ScopeFilterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    protected $projectService;

    protected $scopeFilter;

    public function __construct(ProjectService $projectService, ScopeFilterService $scopeFilter)
    {
        $this->projectService = $projectService;
        $this->scopeFilter = $scopeFilter;

        $this->middleware('permission:reports.view')->only(['index']);
        $this->middleware('permission:reports.implementation.view')->only(['implementation']);
        $this->middleware('permission:reports.quality.view')->only(['quality']);
        $this->middleware('permission:reports.financial.view')->only(['financial']);
        $this->middleware('permission:reports.progress.view')->only(['progress']);
        $this->middleware('permission:reports.status.view')->only(['status']);
        $this->middleware('permission:reports.overview.view')->only(['overview']);
        $this->middleware('permission:reports.print')->only(['printIndex', 'printGenerate', 'downloadOfficialReport']);
    }

    /**
     * الحصول على استعلام المشاريع مع تطبيق فلترة النطاقات الموحدة وفلاتر الطلب.
     */
    private function getFilteredProjectsQuery(?Request $request = null, bool $enforceDefaultEntity = true)
    {
        $query = Project::query();

        return $this->scopeFilter->applyProjectFilters($query, $request, $enforceDefaultEntity);
    }

    /**
     * استخراج إحصائيات مركز البيانات الموحد
     */
    private function getIndexStats(Request $request): array
    {
        $projectsQuery = $this->getFilteredProjectsQuery($request);
        $totalProjects = (clone $projectsQuery)->count();

        // Projects by status for the bar chart
        $projectsByStatus = (clone $projectsQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusMap = [
            'active' => 'نشط',
            'in_execution' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'delayed' => 'متأخر',
            'on_hold' => 'معلق',
            'draft' => 'مسودة',
            'approved' => 'معتمد',
        ];

        $formattedStatus = [];
        foreach ($statusMap as $key => $label) {
            $formattedStatus[$label] = $projectsByStatus[$key] ?? 0;
        }

        $completedProjectsCount = $projectsByStatus['completed'] ?? 0;
        $delayedProjectsCount = $projectsByStatus['delayed'] ?? 0;
        $onHoldProjectsCount = $projectsByStatus['on_hold'] ?? 0;

        // Last 6 months labels
        $months = [];
        $progressSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('F');

            $avgProgress = ProjectExecution::where('approval_status', 'approved')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))
                ->avg('completion_percentage') ?? 0;

            $progressSeries[] = round((float) $avgProgress, 1);
        }

        // Total budget with filter
        $totalBudget = (clone $projectsQuery)
            ->join('project_costs', 'projects.id', '=', 'project_costs.project_id')
            ->sum('project_costs.total_cost');

        // Total spent with filter
        $totalSpentQuery = PreliminaryProcedureExecution::where('approval_status', 'approved')
            ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))
            ->sum('actual_amount')
            + ProjectExecution::where('approval_status', 'approved')
                ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))
                ->sum('actual_amount');

        $stats = [
            'total_projects' => $totalProjects,
            'completed_projects' => $completedProjectsCount,
            'delayed_projects' => $delayedProjectsCount,
            'on_hold_projects' => $onHoldProjectsCount,
            'projects_by_status' => $formattedStatus,
            'months' => $months,
            'progress_series' => $progressSeries,
            'implementation' => [
                'preliminary' => PreliminaryProcedureExecution::whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
                'executive' => ProjectExecution::whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
                'pending_approval' => PreliminaryProcedureExecution::where('approval_status', 'pending')
                    ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count()
                    + ProjectExecution::where('approval_status', 'pending')
                        ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
            ],
            'quality' => [
                'total_records' => ProjectQuality::whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
                'negative' => ProjectQuality::where('quality_status', 'negative')
                    ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
                'positive' => ProjectQuality::where('quality_status', 'positive')
                    ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request))->count(),
            ],
            'financial' => [
                'total_budget' => (float) $totalBudget,
                'total_spent' => (float) $totalSpentQuery,
            ],
        ];

        $stats['financial']['remaining'] = $stats['financial']['total_budget'] - $stats['financial']['total_spent'];
        $stats['financial']['utilization_percentage'] = $stats['financial']['total_budget'] > 0
            ? ($stats['financial']['total_spent'] / $stats['financial']['total_budget']) * 100
            : 0;

        return $stats;
    }

    /**
     * لوحة تقارير مركز البيانات الموحد (Overview Dashboard)
     */
    public function index(Request $request)
    {
        $stats = $this->getIndexStats($request);
        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        if ($request->has('print') || $request->input('export') === 'print') {
            return view('projects.reports.print_index', array_merge(compact('stats'), $dropdownData));
        }

        return view('projects.reports.index', array_merge(compact('stats'), $dropdownData));
    }

    /**
     * Display status report
     */
    public function status(Request $request)
    {
        return $this->progress($request);
    }

    /**
     * Display overview
     */
    public function overview()
    {
        return redirect()->route('projects.reports.index');
    }

    /**
     * Display implementation summary report
     */
    public function implementation(Request $request)
    {
        $projectId = $request->get('project_id');
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');

        // Base preliminary query with scope filter
        $preliminaryQuery = PreliminaryProcedureExecution::with(['project:id,project_name,form_number', 'procedure:id,procedure_name', 'createdBy:id,name'])
            ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request));

        if ($status !== 'all' && ! empty($status)) {
            $preliminaryQuery->where('approval_status', $status);
        }

        // Base executive query with scope filter
        $executiveQuery = ProjectExecution::with(['project:id,project_name,form_number', 'action:id,action', 'createdBy:id,name'])
            ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request));

        if ($status !== 'all' && ! empty($status)) {
            $executiveQuery->where('approval_status', $status);
        }

        // Compute aggregate stats
        $prelimStats = (clone $preliminaryQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN approval_status = 'approved' THEN actual_amount ELSE 0 END) as total_spent
            ")->first();

        $execStats = (clone $executiveQuery)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN approval_status = 'approved' THEN actual_amount ELSE 0 END) as total_spent,
                AVG(CASE WHEN approval_status = 'approved' THEN completion_percentage ELSE NULL END) as avg_completion
            ")->first();

        $stats = [
            'preliminary' => [
                'total' => (int) ($prelimStats->total ?? 0),
                'approved' => (int) ($prelimStats->approved ?? 0),
                'pending' => (int) ($prelimStats->pending ?? 0),
                'rejected' => (int) ($prelimStats->rejected ?? 0),
                'total_spent' => (float) ($prelimStats->total_spent ?? 0),
            ],
            'executive' => [
                'total' => (int) ($execStats->total ?? 0),
                'approved' => (int) ($execStats->approved ?? 0),
                'pending' => (int) ($execStats->pending ?? 0),
                'rejected' => (int) ($execStats->rejected ?? 0),
                'total_spent' => (float) ($execStats->total_spent ?? 0),
                'avg_completion' => (float) ($execStats->avg_completion ?? 0),
            ],
            'preliminary_count' => (int) ($prelimStats->total ?? 0),
            'executive_count' => (int) ($execStats->total ?? 0),
            'pending_approval' => (int) ($prelimStats->pending ?? 0) + (int) ($execStats->pending ?? 0),
            'total_records' => (int) ($prelimStats->total ?? 0) + (int) ($execStats->total ?? 0),
        ];

        // Fetch execution records safely with limit when type is all
        $preliminaryExecutions = collect();
        $executiveExecutions = collect();

        if ($type === 'all' || $type === 'preliminary' || empty($type)) {
            $preliminaryExecutions = (clone $preliminaryQuery)->orderBy('created_at', 'desc')->take(100)->get();
        }
        if ($type === 'all' || $type === 'executive' || empty($type)) {
            $executiveExecutions = (clone $executiveQuery)->orderBy('created_at', 'desc')->take(100)->get();
        }

        // Build unified data
        $implementationData = collect();
        foreach ($preliminaryExecutions as $exec) {
            $implementationData->push([
                'type' => 'preliminary',
                'project_name' => $exec->project->project_name ?? 'N/A',
                'project_code' => $exec->project->form_number ?? 'N/A',
                'activity_name' => $exec->procedure->procedure_name ?? 'N/A',
                'execution_date' => $exec->actual_start_date_gregorian?->format('Y-m-d') ?? '-',
                'amount_spent' => $exec->actual_amount,
                'completion_percentage' => 100,
                'approval_status' => $exec->approval_status,
                'approved_by' => $exec->createdBy->name ?? null,
                'created_at' => $exec->created_at,
            ]);
        }
        foreach ($executiveExecutions as $exec) {
            $implementationData->push([
                'type' => 'executive',
                'project_name' => $exec->project->project_name ?? 'N/A',
                'project_code' => $exec->project->form_number ?? 'N/A',
                'activity_name' => $exec->action->action ?? 'N/A',
                'execution_date' => $exec->actual_start_date_gregorian?->format('Y-m-d') ?? '-',
                'amount_spent' => $exec->actual_amount,
                'completion_percentage' => $exec->completion_percentage,
                'approval_status' => $exec->approval_status,
                'approved_by' => $exec->createdBy->name ?? null,
                'created_at' => $exec->created_at,
            ]);
        }
        $implementationData = $implementationData->sortByDesc('created_at')->values();

        // Projects for filter
        $allProjects = $this->getFilteredProjectsQuery($request)
            ->select('id', 'project_name', 'form_number')
            ->orderBy('project_name')
            ->get();

        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        $viewName = ($request->has('print') || $request->input('export') === 'print')
            ? 'projects.reports.print_implementation'
            : 'projects.reports.implementation';

        return view($viewName, array_merge(compact(
            'preliminaryExecutions',
            'executiveExecutions',
            'implementationData',
            'stats',
            'allProjects',
            'projectId',
            'status',
            'type'
        ), $dropdownData));
    }

    /**
     * Display quality metrics report
     */
    public function quality(Request $request)
    {
        $projectId = $request->get('project_id');
        $qualityStatus = $request->get('quality_status', 'all');
        $aspect = $request->get('aspect', 'all');

        $query = ProjectQuality::with(['project:id,project_name,form_number', 'preliminaryActivity', 'executiveActivity'])
            ->whereHas('project', fn ($q) => $this->scopeFilter->applyProjectFilters($q, $request));

        if ($qualityStatus !== 'all' && ! empty($qualityStatus)) {
            $query->where('quality_status', $qualityStatus);
        }
        if ($aspect !== 'all' && ! empty($aspect)) {
            $query->where('quality_aspect', $aspect);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'positive' => (clone $query)->where('quality_status', 'positive')->count(),
            'negative' => (clone $query)->where('quality_status', 'negative')->count(),
            'by_aspect' => [
                'time' => (clone $query)->where('quality_aspect', 'time')->count(),
                'financial' => (clone $query)->where('quality_aspect', 'financial')->count(),
                'technical' => (clone $query)->where('quality_aspect', 'technical')->count(),
            ],
            'with_solutions' => (clone $query)->whereNotNull('proposed_solution')->where('proposed_solution', '!=', '')->count(),
            'avg_time_variance' => (clone $query)->where('quality_aspect', 'time')->avg('variance_days'),
            'avg_financial_variance' => (clone $query)->where('quality_aspect', 'financial')->avg('variance_amount'),
        ];

        $isPrint = $request->has('print') || $request->input('export') === 'print';
        $qualityRecords = $isPrint
            ? (clone $query)->orderBy('created_at', 'desc')->get()
            : $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $projects = $this->getFilteredProjectsQuery($request)
            ->select('id', 'project_name', 'form_number')
            ->orderBy('project_name')
            ->get();

        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        $viewName = $isPrint ? 'projects.reports.print_quality' : 'projects.reports.quality';

        return view($viewName, array_merge(compact(
            'qualityRecords',
            'stats',
            'projects',
            'projectId',
            'qualityStatus',
            'aspect'
        ), $dropdownData));
    }

    /**
     * Display financial overview report
     */
    public function financial(Request $request)
    {
        $projectId = $request->get('project_id');

        $query = $this->getFilteredProjectsQuery($request)
            ->with(['cost', 'preliminaryProcedures.executions', 'executiveActivityActions.executions']);

        $projects = $query->paginate(20)->withQueryString();
        $totalBudget = 0;
        $totalSpent = 0;
        $preliminarySpent = 0;
        $executiveSpent = 0;
        $projectsData = [];

        foreach ($projects as $project) {
            $budget = $project->cost ? (float) $project->cost->total_cost : 0;

            $preliminaryAmount = (float) $project->preliminaryProcedures
                ->flatMap->executions
                ->where('approval_status', 'approved')
                ->sum('actual_amount');

            $executiveAmount = (float) $project->executiveActivityActions
                ->flatMap->executions
                ->where('approval_status', 'approved')
                ->sum('actual_amount');

            $spent = $preliminaryAmount + $executiveAmount;
            $remaining = $budget - $spent;
            $utilization = $budget > 0 ? ($spent / $budget) * 100 : 0;

            $projectsData[] = [
                'project' => $project,
                'budget' => $budget,
                'preliminary_spent' => $preliminaryAmount,
                'executive_spent' => $executiveAmount,
                'total_spent' => $spent,
                'remaining' => $remaining,
                'utilization_percentage' => $utilization,
                'status' => $utilization > 100 ? 'overspent' : ($utilization > 90 ? 'warning' : 'normal'),
            ];

            $totalBudget += $budget;
            $totalSpent += $spent;
            $preliminarySpent += $preliminaryAmount;
            $executiveSpent += $executiveAmount;
        }

        $stats = [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'preliminary_spent' => $preliminarySpent,
            'executive_spent' => $executiveSpent,
            'remaining' => $totalBudget - $totalSpent,
            'utilization_percentage' => $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0,
            'projects_count' => count($projectsData),
            'overspent_count' => collect($projectsData)->where('status', 'overspent')->count(),
        ];

        $allProjects = $this->getFilteredProjectsQuery($request)
            ->select('id', 'project_name', 'form_number')
            ->orderBy('project_name')
            ->get();

        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        $viewName = ($request->has('print') || $request->input('export') === 'print')
            ? 'projects.reports.print_financial'
            : 'projects.reports.financial';

        return view($viewName, array_merge(compact(
            'projectsData',
            'stats',
            'allProjects',
            'projectId'
        ), $dropdownData));
    }

    /**
     * Display progress tracking report
     */
    public function progress(Request $request)
    {
        $projectId = $request->get('project_id');

        $query = $this->getFilteredProjectsQuery($request)->with([
            'preliminaryProcedures.executions',
            'executiveActivityActions.executions',
        ]);

        $isPrint = $request->has('print') || $request->input('export') === 'print';
        $projects = $isPrint ? $query->get() : $query->paginate(20)->withQueryString();
        $projectsProgress = [];

        foreach ($projects as $project) {
            $preliminaryTotal = $project->preliminaryProcedures->count();

            $preliminaryCompleted = $project->preliminaryProcedures
                ->flatMap->executions
                ->where('approval_status', 'approved')
                ->unique('preliminary_procedure_id')
                ->count();

            $preliminaryProgress = $preliminaryTotal > 0 ? ($preliminaryCompleted / $preliminaryTotal) * 100 : 0;

            $executiveTotal = $project->executiveActivityActions->count();

            $executiveCompleted = $project->executiveActivityActions
                ->flatMap->executions
                ->where('approval_status', 'approved')
                ->unique('executive_activity_action_id')
                ->count();

            $executiveProgress = $executiveTotal > 0 ? ($executiveCompleted / $executiveTotal) * 100 : 0;

            $overallProgress = ($preliminaryProgress + $executiveProgress) / 2;

            $startDate = $project->start_date_gregorian;
            $endDate = $project->end_date_gregorian;
            $timeProgress = 0;
            $daysRemaining = 0;

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                $now = Carbon::now();

                $totalDays = $start->diffInDays($end);
                $elapsedDays = $start->diffInDays($now);

                $timeProgress = $totalDays > 0 ? min(($elapsedDays / $totalDays) * 100, 100) : 0;
                $daysRemaining = max($now->diffInDays($end, false), 0);
            }

            $projectsProgress[] = [
                'project' => $project,
                'preliminary_progress' => $preliminaryProgress,
                'executive_progress' => $executiveProgress,
                'overall_progress' => $overallProgress,
                'time_progress' => $timeProgress,
                'days_remaining' => $daysRemaining,
                'status' => $overallProgress >= 75 ? 'on-track' : ($overallProgress >= 50 ? 'at-risk' : 'delayed'),
            ];
        }

        $stats = [
            'total_projects' => count($projectsProgress),
            'on_track' => collect($projectsProgress)->where('status', 'on-track')->count(),
            'at_risk' => collect($projectsProgress)->where('status', 'at-risk')->count(),
            'delayed' => collect($projectsProgress)->where('status', 'delayed')->count(),
            'avg_progress' => collect($projectsProgress)->avg('overall_progress'),
        ];

        $allProjects = $this->getFilteredProjectsQuery($request)
            ->select('id', 'project_name', 'form_number')
            ->orderBy('project_name')
            ->get();

        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        $viewName = $isPrint ? 'projects.reports.print_progress' : 'projects.reports.progress';

        return view($viewName, array_merge(compact(
            'projectsProgress',
            'stats',
            'allProjects',
            'projectId'
        ), $dropdownData));
    }

    /**
     * Display print options page
     */
    public function printIndex(Request $request)
    {
        $projects = $this->getFilteredProjectsQuery($request)
            ->select('id', 'project_name', 'form_number')
            ->orderBy('project_name')
            ->get();

        $dropdownData = $this->scopeFilter->getFilterDropdownData($request);

        return view('projects.reports.print_options', array_merge(compact('projects'), $dropdownData));
    }

    /**
     * Generate PDF report based on options
     */
    public function printGenerate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:individual,group,all',
            'project_id' => 'required_if:report_type,individual',
            'project_ids' => 'required_if:report_type,group|array',
            'orientation' => 'required|in:P,L',
        ]);

        try {
            $exportService = new ProjectExportService;

            return $exportService->exportCustomReports($request);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إنشاء التقرير: '.$e->getMessage());
        }
    }

    /**
     * Download Official Summary Report
     */
    public function downloadOfficialReport(Request $request)
    {
        $projects = $this->getFilteredProjectsQuery($request)
            ->with(['cost', 'locations.governorate', 'locations.directorate', 'domain'])
            ->get();

        $totalBudget = $projects->sum(fn ($p) => $p->cost?->total_cost ?? 0);
        $totalProjects = $projects->count();
        $user = auth()->user();
        $entityName = $user->entity?->name ?? 'وزارة الزراعة والثروة السمكية والموارد المائية';

        $logoPath = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : '';

        $data = [
            'projects' => $projects,
            'totalBudget' => $totalBudget,
            'totalProjects' => $totalProjects,
            'entityName' => $entityName,
            'date' => now()->format('Y-m-d'),
            'logoBase64' => $logoBase64,
            'stats' => $this->getIndexStats($request),
        ];

        if ($request->has('pdf')) {
            $pdf = Pdf::loadView('projects.reports.official_summary', $data);

            return $pdf->download('official_summary_'.date('Y_m_d').'.pdf');
        }

        return view('projects.reports.official_summary', $data);
    }
}
