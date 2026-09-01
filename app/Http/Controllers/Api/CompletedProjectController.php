<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompletedProjectController extends Controller
{
    /**
     * Display a listing of completed projects
     *
     * GET /api/completed-projects
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Project::with('cost')
                ->where('status', 'approved')
                ->orWhereHas('currentApprovalStage', function ($q) {
                    $q->where('code', 'implementation')
                        ->orWhere('code', 'completed');
                });

            // Apply filters
            if ($request->filled('program_id')) {
                $query->where('program_id', $request->input('program_id'));
            }

            if ($request->filled('start_date')) {
                $query->where('start_date_gregorian', '>=', $request->input('start_date'));
            }

            if ($request->filled('end_date')) {
                $query->where('end_date_gregorian', '<=', $request->input('end_date'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhere('form_number', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $projects = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format response
            $data = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_number' => $project->form_number,
                    'project_name' => $project->project_name,
                    'project_cost' => $project->cost->total_cost ?? 0,
                    'start_date' => $project->start_date_gregorian,
                    'end_date' => $project->end_date_gregorian,
                    'status' => $project->status,
                    'created_at' => $project->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $project->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'total' => $projects->total(),
                    'per_page' => $projects->perPage(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve completed projects',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a single completed project
     *
     * GET /api/completed-projects/{project}
     *
     * @param  mixed  $identifier  - Project ID or form_number
     */
    public function show($identifier): JsonResponse
    {
        try {
            // Try to find by ID first, then by form_number
            $project = Project::with('cost')
                ->where('id', $identifier)
                ->orWhere('form_number', $identifier)
                ->first();

            if (! $project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                ], 404);
            }

            $data = [
                'id' => $project->id,
                'project_number' => $project->form_number,
                'project_name' => $project->project_name,
                'project_cost' => $project->cost->total_cost ?? 0,
                'start_date' => $project->start_date_gregorian,
                'end_date' => $project->end_date_gregorian,
                'status' => $project->status,
                'created_at' => $project->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $project->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create or update a completed project
     *
     * POST /api/completed-projects
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'project_number' => 'nullable|string|max:255',
            'project_name' => 'required|string|max:255',
            'project_cost' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'program_id' => 'nullable|exists:programs,id',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomain_id' => 'nullable|exists:subdomains,id',
            'intervention_id' => 'nullable|exists:interventions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $projectData = [
                'project_name' => $request->input('project_name'),
                'start_date_gregorian' => $request->input('start_date'),
                'end_date_gregorian' => $request->input('end_date'),
                'status' => 'approved',
                'created_by_user_id' => auth()->id() ?? 1, // Default to user 1 if not authenticated
            ];

            // Add optional fields
            if ($request->filled('program_id')) {
                $projectData['program_id'] = $request->input('program_id');
            }
            if ($request->filled('domain_id')) {
                $projectData['domain_id'] = $request->input('domain_id');
            }
            if ($request->filled('subdomain_id')) {
                $projectData['subdomain_id'] = $request->input('subdomain_id');
            }
            if ($request->filled('intervention_id')) {
                $projectData['intervention_id'] = $request->input('intervention_id');
            }

            // Check if updating existing project by project_number
            $project = null;
            if ($request->filled('project_number')) {
                $project = Project::where('form_number', $request->input('project_number'))->first();
            }

            if ($project) {
                // Update existing project
                $project->update($projectData);
                $message = 'Project updated successfully';
            } else {
                // Create new project
                if ($request->filled('project_number')) {
                    $projectData['form_number'] = $request->input('project_number');
                }
                $project = Project::create($projectData);
                $message = 'Project created successfully';
            }

            // Create or update project cost
            ProjectCost::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'total_cost' => $request->input('project_cost'),
                    'year_type' => 'gregorian',
                ]
            );

            // Reload project with cost
            $project->load('cost');

            DB::commit();

            $data = [
                'id' => $project->id,
                'project_number' => $project->form_number,
                'project_name' => $project->project_name,
                'project_cost' => $project->cost->total_cost ?? 0,
                'start_date' => $project->start_date_gregorian,
                'end_date' => $project->end_date_gregorian,
                'status' => $project->status,
                'created_at' => $project->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $project->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
