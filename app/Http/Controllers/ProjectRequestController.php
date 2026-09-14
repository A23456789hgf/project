<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Program;
use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\Subdomain;
use App\Services\NotificationService;
use App\Services\ProjectNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectRequestController extends Controller
{
    /**
     * Display create form for new project request
     */
    public function create()
    {
        $programs = Program::active()->get();
        $domains = Domain::active()->get();
        $subdomains = Subdomain::active()->get();
        $interventions = Intervention::active()->get();

        return view('project-requests.create', compact('programs', 'domains', 'subdomains', 'interventions'));
    }

    /**
     * Store a newly created project request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomain_id' => 'nullable|exists:subdomains,id',
            'intervention_id' => 'nullable|exists:interventions,id',
            'start_date_gregorian' => 'nullable|date',
            'start_date_hijri' => 'nullable|string',
            'end_date_gregorian' => 'nullable|date',
            'end_date_hijri' => 'nullable|string',
            'number_of_beneficiaries' => 'nullable|integer|min:1',
            'additional_data' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Generate request number
            $requestNumber = ProjectRequest::generateRequestNumber();

            // Create project request in draft status
            $projectRequest = ProjectRequest::create([
                'request_number' => $requestNumber,
                'created_by_user_id' => Auth::id(),
                'project_name' => $validated['project_name'],
                'program_id' => $validated['program_id'] ?? null,
                'domain_id' => $validated['domain_id'] ?? null,
                'subdomain_id' => $validated['subdomain_id'] ?? null,
                'intervention_id' => $validated['intervention_id'] ?? null,
                'start_date_gregorian' => $validated['start_date_gregorian'] ?? null,
                'start_date_hijri' => $validated['start_date_hijri'] ?? null,
                'end_date_gregorian' => $validated['end_date_gregorian'] ?? null,
                'end_date_hijri' => $validated['end_date_hijri'] ?? null,
                'number_of_beneficiaries' => $validated['number_of_beneficiaries'] ?? null,
                'project_data' => $validated['additional_data'] ?? [],
                'status' => 'draft',
                'last_saved_step' => 1,
            ]);

            DB::commit();

            return redirect()->route('project-requests.show', $projectRequest)
                ->with('success', "تم إنشاء طلب المشروع بنجاح برقم: {$requestNumber}");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الطلب: '.$e->getMessage());
        }
    }

    /**
     * Show edit form for project request
     */
    public function edit(ProjectRequest $projectRequest)
    {
        // Only draft requests can be edited
        if (! $projectRequest->isDraft()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'لا يمكن تعديل طلب ليس في حالة المسودة');
        }

        $programs = Program::active()->get();
        $domains = Domain::active()->get();
        $subdomains = Subdomain::active()->get();
        $interventions = Intervention::active()->get();

        return view('project-requests.edit', compact('projectRequest', 'programs', 'domains', 'subdomains', 'interventions'));
    }

    /**
     * Update project request
     */
    public function update(Request $request, ProjectRequest $projectRequest)
    {
        if (! $projectRequest->isDraft()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'لا يمكن تعديل طلب ليس في حالة المسودة');
        }

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomain_id' => 'nullable|exists:subdomains,id',
            'intervention_id' => 'nullable|exists:interventions,id',
            'start_date_gregorian' => 'nullable|date',
            'start_date_hijri' => 'nullable|string',
            'end_date_gregorian' => 'nullable|date',
            'end_date_hijri' => 'nullable|string',
            'number_of_beneficiaries' => 'nullable|integer|min:1',
            'additional_data' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $projectRequest->update([
                'project_name' => $validated['project_name'],
                'program_id' => $validated['program_id'] ?? null,
                'domain_id' => $validated['domain_id'] ?? null,
                'subdomain_id' => $validated['subdomain_id'] ?? null,
                'intervention_id' => $validated['intervention_id'] ?? null,
                'start_date_gregorian' => $validated['start_date_gregorian'] ?? null,
                'start_date_hijri' => $validated['start_date_hijri'] ?? null,
                'end_date_gregorian' => $validated['end_date_gregorian'] ?? null,
                'end_date_hijri' => $validated['end_date_hijri'] ?? null,
                'number_of_beneficiaries' => $validated['number_of_beneficiaries'] ?? null,
                'project_data' => $validated['additional_data'] ?? [],
            ]);

            DB::commit();

            return redirect()->route('project-requests.show', $projectRequest)
                ->with('success', 'تم تحديث طلب المشروع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التحديث: '.$e->getMessage());
        }
    }

    /**
     * Display list of project requests (referrals)
     */
    public function index(Request $request)
    {
        try {
            $query = ProjectRequest::with([
                'createdBy:id,name',
                'approvedBy:id,name',
                'program:id,name',
                'domain:id,name',
                'subdomain:id,name',
                'intervention:id,name',
                'project:id,project_name,form_number',
            ])->orderByDesc('created_at');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by program
            if ($request->filled('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            // Filter by domain
            if ($request->filled('domain_id')) {
                $query->where('domain_id', $request->domain_id);
            }

            // Search by request number or project name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('request_number', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('assigned_project_number', 'like', "%{$search}%");
                });
            }

            $projectRequests = $query->paginate(20);

            // Get statistics via single GROUP BY query
            $statsRaw = ProjectRequest::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $stats = [
                'total' => $statsRaw->sum(),
                'draft' => $statsRaw['draft'] ?? 0,
                'submitted' => $statsRaw['submitted'] ?? 0,
                'pending_approval' => $statsRaw['pending_approval'] ?? 0,
                'approved' => $statsRaw['approved'] ?? 0,
                'rejected' => $statsRaw['rejected'] ?? 0,
                'transferred' => $statsRaw['transferred'] ?? 0,
            ];

            return view('project-requests.index', compact('projectRequests', 'stats'));
        } catch (\Exception $e) {
            // Log the error and show a user-friendly message
            \Log::error('Error in ProjectRequestController@index: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب قائمة الطلبات. الرجاء المحاولة لاحقاً أو الاتصال بالدعم الفني.');
        }
    }

    /**
     * Display a specific project request
     */
    public function show(ProjectRequest $projectRequest)
    {
        $projectRequest->load([
            'createdBy',
            'approvedBy',
            'program',
            'domain',
            'subdomain',
            'intervention',
            'project',
        ]);

        return view('project-requests.show', compact('projectRequest'));
    }

    /**
     * Submit a draft project request for approval
     */
    public function submit(ProjectRequest $projectRequest)
    {
        if (! $projectRequest->isDraft()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'يمكن فقط إرسال الطلبات في حالة المسودة');
        }

        DB::beginTransaction();
        try {
            $projectRequest->markAsSubmitted();
            DB::commit();

            // إرسال إشعار تقديم طلب المشروع
            app(NotificationService::class)->notifyProjectRequest($projectRequest, 'submitted');

            return redirect()->route('project-requests.show', $projectRequest)
                ->with('success', 'تم إرسال طلب المشروع للموافقة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الطلب: '.$e->getMessage());
        }
    }

    /**
     * Approve a project request
     */
    public function approve(Request $request, ProjectRequest $projectRequest)
    {
        if (! $projectRequest->canBeApproved()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'هذا الطلب لا يمكن الموافقة عليه من حالته الحالية');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $projectRequest->approve(Auth::id(), $validated['approval_notes'] ?? null);
            DB::commit();

            // إرسال إشعار الموافقة على طلب المشروع
            app(NotificationService::class)->notifyProjectRequest($projectRequest, 'approved');

            return redirect()->route('project-requests.show', $projectRequest)
                ->with('success', 'تم الموافقة على طلب المشروع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الموافقة: '.$e->getMessage());
        }
    }

    /**
     * Reject a project request
     */
    public function reject(Request $request, ProjectRequest $projectRequest)
    {
        if (! $projectRequest->canBeApproved()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'هذا الطلب لا يمكن رفضه من حالته الحالية');
        }

        $validated = $request->validate([
            'rejection_notes' => 'required|string|min:10|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $projectRequest->reject(Auth::id(), $validated['rejection_notes']);
            DB::commit();

            // إرسال إشعار رفض طلب المشروع
            app(NotificationService::class)->notifyProjectRequest($projectRequest, 'rejected');

            return redirect()->route('project-requests.show', $projectRequest)
                ->with('success', 'تم رفض طلب المشروع');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الرفض: '.$e->getMessage());
        }
    }

    /**
     * Transfer approved request to project table and assign project number
     */
    public function transfer(ProjectRequest $projectRequest)
    {
        if (! $projectRequest->canBeTransferred()) {
            return redirect()->route('project-requests.show', $projectRequest)
                ->with('error', 'يمكن فقط تحويل الطلبات الموافق عليها');
        }

        DB::beginTransaction();
        try {
            // Generate final project number
            $projectNumber = ProjectNumberGenerator::getNextProjectNumber();

            // Get all project data from the request
            $projectData = $projectRequest->project_data ?? [];

            // Create new project with data from request
            $project = Project::create([
                'project_name' => $projectRequest->project_name,
                'form_number' => $projectNumber,
                'program_id' => $projectRequest->program_id,
                'domain_id' => $projectRequest->domain_id,
                'subdomain_id' => $projectRequest->subdomain_id,
                'intervention_id' => $projectRequest->intervention_id,
                'start_date_gregorian' => $projectRequest->start_date_gregorian,
                'start_date_hijri' => $projectRequest->start_date_hijri,
                'end_date_gregorian' => $projectRequest->end_date_gregorian,
                'end_date_hijri' => $projectRequest->end_date_hijri,
                'number_of_beneficiaries' => $projectRequest->number_of_beneficiaries,
                'status' => 'in_progress',
                'created_by_user_id' => $projectRequest->created_by_user_id,
                'approval_status' => 'approved',
                // Merge any additional data from project_data JSON (if it's an array)
                ...(is_array($projectData) ? $projectData : []),
            ]);

            // Update project request with new project reference and transfer details
            $projectRequest->update([
                'status' => 'transferred',
                'project_id' => $project->id,
                'assigned_project_number' => $projectNumber,
                'transferred_at' => now(),
            ]);

            DB::commit();

            // إرسال إشعار تحويل الطلب إلى مشروع
            app(NotificationService::class)->notifyProjectRequest($projectRequest, 'transferred');

            return redirect()->route('projects.show', $project)
                ->with('success', "تم تحويل الطلب بنجاح إلى مشروع برقم: {$projectNumber}");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء التحويل: '.$e->getMessage());
        }
    }

    /**
     * Delete a project request (only drafts and rejected can be deleted)
     */
    public function destroy(ProjectRequest $projectRequest)
    {
        if (! in_array($projectRequest->status, ['draft', 'rejected'])) {
            return redirect()->route('project-requests.index')
                ->with('error', 'لا يمكن حذف هذا الطلب من حالته الحالية');
        }

        try {
            $projectRequest->delete();

            return redirect()->route('project-requests.index')
                ->with('success', 'تم حذف طلب المشروع بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الحذف: '.$e->getMessage());
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats()
    {
        return [
            'total' => ProjectRequest::count(),
            'draft' => ProjectRequest::where('status', 'draft')->count(),
            'pending_approval' => ProjectRequest::whereIn('status', ['submitted', 'pending_approval'])->count(),
            'approved' => ProjectRequest::where('status', 'approved')->count(),
            'rejected' => ProjectRequest::where('status', 'rejected')->count(),
            'transferred' => ProjectRequest::where('status', 'transferred')->count(),
        ];
    }
}
