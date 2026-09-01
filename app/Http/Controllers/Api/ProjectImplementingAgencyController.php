<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectImplementingAgency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectImplementingAgencyController extends Controller
{
    /**
     * Display a listing of the resource for a specific project.
     */
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');

        if (! $projectId) {
            return response()->json(['error' => 'Project ID is required'], 400);
        }

        $agencies = ProjectImplementingAgency::forProject($projectId)
            ->ordered()
            ->get()
            ->map(function ($agency) {
                return [
                    'id' => $agency->id,
                    'agency_type' => $agency->agency_type,
                    'agency_type_arabic' => $agency->agency_type_arabic,
                    'agency_name' => $agency->agency_name,
                    'sequence_order' => $agency->sequence_order,
                    'badge_class' => $agency->badge_class,
                ];
            });

        return response()->json($agencies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'agency_type' => ['required', Rule::in([
                'ministry', 'authority', 'agency', 'department', 'committee',
                'council', 'organization', 'company', 'institution',
            ])],
            'agency_name' => 'required|string|max:255',
            'sequence_order' => 'nullable|integer|min:1',
        ]);

        // If no sequence order provided, set it to the next available number
        if (! isset($validated['sequence_order'])) {
            $maxOrder = ProjectImplementingAgency::where('project_id', $validated['project_id'])
                ->max('sequence_order') ?? 0;
            $validated['sequence_order'] = $maxOrder + 1;
        }

        $agency = ProjectImplementingAgency::create($validated);

        return response()->json([
            'id' => $agency->id,
            'agency_type' => $agency->agency_type,
            'agency_type_arabic' => $agency->agency_type_arabic,
            'agency_name' => $agency->agency_name,
            'sequence_order' => $agency->sequence_order,
            'badge_class' => $agency->badge_class,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $agency = ProjectImplementingAgency::findOrFail($id);

        return response()->json([
            'id' => $agency->id,
            'project_id' => $agency->project_id,
            'agency_type' => $agency->agency_type,
            'agency_type_arabic' => $agency->agency_type_arabic,
            'agency_name' => $agency->agency_name,
            'sequence_order' => $agency->sequence_order,
            'badge_class' => $agency->badge_class,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $agency = ProjectImplementingAgency::findOrFail($id);

        $validated = $request->validate([
            'agency_type' => ['sometimes', Rule::in([
                'ministry', 'authority', 'agency', 'department', 'committee',
                'council', 'organization', 'company', 'institution',
            ])],
            'agency_name' => 'sometimes|string|max:255',
            'sequence_order' => 'sometimes|integer|min:1',
        ]);

        $agency->update($validated);

        return response()->json([
            'id' => $agency->id,
            'agency_type' => $agency->agency_type,
            'agency_type_arabic' => $agency->agency_type_arabic,
            'agency_name' => $agency->agency_name,
            'sequence_order' => $agency->sequence_order,
            'badge_class' => $agency->badge_class,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $agency = ProjectImplementingAgency::findOrFail($id);
        $agency->delete();

        return response()->json(['message' => 'Agency deleted successfully']);
    }

    /**
     * Update the sequence order of multiple agencies.
     */
    public function updateSequence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agencies' => 'required|array',
            'agencies.*.id' => 'required|exists:project_implementing_agencies,id',
            'agencies.*.sequence_order' => 'required|integer|min:1',
        ]);

        foreach ($validated['agencies'] as $agencyData) {
            ProjectImplementingAgency::where('id', $agencyData['id'])
                ->update(['sequence_order' => $agencyData['sequence_order']]);
        }

        return response()->json(['message' => 'Sequence updated successfully']);
    }
}
