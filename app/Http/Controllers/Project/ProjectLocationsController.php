<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectLocationsController extends Controller
{
    public function getValidationRules($isDraft = false)
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'locations' => 'nullable|array',
            'locations.*.governorate_id' => $requiredRule.'|exists:governorates,id',
            'locations.*.directorate_id' => $requiredRule.'|exists:directorates,id',
            'locations.*.sub_area_id' => 'nullable|exists:sub_areas,id',
            'locations.*.village_id' => 'nullable|exists:villages,id',
        ];
    }

    public function handle(Project $project, array $data)
    {
        try {
            $locations = $data['locations'] ?? [];
            $project->locations()->delete();
            $createdLocations = [];
            foreach ($locations as $location) {
                $created = $project->locations()->create($location);
                $createdLocations[] = $created->toArray();
            }

            Log::info('Data added to database successfully', [
                'table' => 'project_locations',
                'project_id' => $project->id,
                'data' => $createdLocations,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add data to database', [
                'table' => 'project_locations',
                'project_id' => $project->id,
                'cause' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
