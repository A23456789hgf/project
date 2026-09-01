<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupervisingEntity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSupervisingEntityController extends Controller
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

        $entities = SupervisingEntity::where('project_id', $projectId)
            ->orderBy('id')
            ->get()
            ->map(function ($entity) {
                return [
                    'id' => $entity->id,
                    'entity_type' => $entity->entity_type,
                    'entity_name' => $entity->entity_name,
                ];
            });

        return response()->json($entities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'entity_type' => 'required|string|max:255',
            'entity_name' => 'required|string|max:255',
        ]);

        $entity = SupervisingEntity::create($validated);

        return response()->json([
            'id' => $entity->id,
            'entity_type' => $entity->entity_type,
            'entity_name' => $entity->entity_name,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $entity = SupervisingEntity::findOrFail($id);

        return response()->json([
            'id' => $entity->id,
            'project_id' => $entity->project_id,
            'entity_type' => $entity->entity_type,
            'entity_name' => $entity->entity_name,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $entity = SupervisingEntity::findOrFail($id);

        $validated = $request->validate([
            'entity_type' => 'sometimes|string|max:255',
            'entity_name' => 'sometimes|string|max:255',
        ]);

        $entity->update($validated);

        return response()->json([
            'id' => $entity->id,
            'entity_type' => $entity->entity_type,
            'entity_name' => $entity->entity_name,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $entity = SupervisingEntity::findOrFail($id);
        $entity->delete();

        return response()->json(['message' => 'Supervising entity deleted successfully']);
    }

    /**
     * Update the sequence order of multiple entities.
     */
    public function updateSequence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entities' => 'required|array',
            'entities.*.id' => 'required|exists:supervising_entities,id',
        ]);

        return response()->json(['message' => 'Sequence updated successfully']);
    }
}
