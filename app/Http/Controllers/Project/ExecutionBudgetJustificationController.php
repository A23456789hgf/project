<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\ExecutionBudgetJustification;
use App\Models\PreliminaryProcedureExecution;
use App\Models\Project;
use App\Models\ProjectExecution;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExecutionBudgetJustificationController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * عرض جميع التبريرات المالية لمشروع معين
     */
    public function index(Project $project)
    {
        $this->projectService->authorizeProjectAccess($project);
        $justifications = ExecutionBudgetJustification::where('project_id', $project->id)
            ->latest()
            ->get();

        return view('projects.execution.budget_justifications.index', compact('project', 'justifications'));
    }

    /**
     * تخزين تبرير مالي جديد (تنفيذي أو تمهيدي)
     */
    public function store(Request $request, Project $project)
    {
        $this->projectService->authorizeProjectAccess($project);
        $validated = $request->validate([
            'execution_id' => 'required',
            'execution_type' => 'required|in:executive,preliminary',
            'planned_amount' => 'required|numeric|min:0',
            'actual_amount' => 'required|numeric|min:0',
            'justification' => 'required|string|min:10',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if ($validated['execution_type'] === 'executive') {
            $execution = ProjectExecution::where('project_id', $project->id)
                ->findOrFail($validated['execution_id']);
        } else {
            $execution = PreliminaryProcedureExecution::where('project_id', $project->id)
                ->findOrFail($validated['execution_id']);
        }

        $planned = $validated['planned_amount'];
        $actual = $validated['actual_amount'];
        $overage = $actual - $planned;

        $paths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store("projects/{$project->id}/budget-justifications", 'public');
            }
        }

        $justification = ExecutionBudgetJustification::updateOrCreate(
            [
                'execution_id' => $execution->id,
                'execution_type' => $validated['execution_type'],
            ],
            [
                'project_id' => $project->id,
                'planned_amount' => $planned,
                'actual_amount' => $actual,
                'overage_amount' => $overage,
                'justification' => $validated['justification'],
                'attachments' => $paths,
                'approval_status' => 'pending',
                'created_by' => auth()->id(),
            ]
        );

        try {
            app(NotificationService::class)->notifyBudgetJustification($project, $justification, 'submitted', auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in ExecutionBudgetJustificationController store: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'تم حفظ التبرير المالي بنجاح');
    }

    /**
     * اعتماد أو رفض التبرير المالي من قبل المراجع المالي
     */
    public function approve(Request $request, Project $project, $id)
    {
        $this->projectService->authorizeProjectAccess($project);
        // تأكد أن المستخدم مراجع مالي
        if (! auth()->user()->hasRole('financial_reviewer')) {
            abort(403, 'غير مصرح لك بالمراجعة المالية');
        }

        $validated = $request->validate([
            'approval_status' => 'required|in:approved,rejected',
            'reviewer_notes' => 'nullable|string',
        ]);

        $justification = ExecutionBudgetJustification::where('project_id', $project->id)
            ->findOrFail($id);

        $justification->update([
            'approval_status' => $validated['approval_status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_notes' => $validated['reviewer_notes'] ?? null,
        ]);

        try {
            app(NotificationService::class)->notifyBudgetJustification($project, $justification, $validated['approval_status'], auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in ExecutionBudgetJustificationController approve: '.$e->getMessage());
        }

        return redirect()->back()->with(
            'success',
            $validated['approval_status'] === 'approved'
                ? 'تم اعتماد التبرير المالي بنجاح'
                : 'تم رفض التبرير المالي'
        );
    }

    /**
     * تحميل مرفق
     */
    public function download(Project $project, $id, Request $request)
    {
        $this->projectService->authorizeProjectAccess($project);
        $path = $request->query('path');

        $justification = ExecutionBudgetJustification::where('project_id', $project->id)
            ->findOrFail($id);

        if (! in_array($path, $justification->attachments ?? [])) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }

    /**
     * حذف تبرير مالي
     */
    public function destroy(Project $project, $id)
    {
        $this->projectService->authorizeProjectAccess($project);
        $justification = ExecutionBudgetJustification::where('project_id', $project->id)
            ->findOrFail($id);

        if (is_array($justification->attachments)) {
            foreach ($justification->attachments as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $justification->delete();

        return redirect()->back()->with('success', 'تم حذف التبرير المالي بنجاح');
    }
}
