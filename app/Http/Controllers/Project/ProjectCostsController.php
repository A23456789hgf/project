<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

class ProjectCostsController extends Controller
{
    public function getValidationRules($isDraft = false)
    {
        return [
            'total_cost' => 'nullable|numeric|min:0',
            'year_type' => 'nullable|in:hijri,gregorian',
            'approval_date_hijri' => 'nullable|date_format:Y-m-d',
            'approval_year_gregorian' => 'nullable|digits:4|integer|min:1900|max:2100',
        ];
    }

    public function handle(Project $project, array $costData)
    {
        try {
            $data = [
                'total_cost' => $costData['total_cost'] ?? 0,
                'year_type' => $costData['year_type'] ?? 'gregorian',
                'approval_date_hijri' => $costData['approval_date_hijri'] ?? date('Y-m-d'),
                'approval_year_gregorian' => $costData['approval_year_gregorian'] ?? date('Y'),
            ];

            $project->cost()->updateOrCreate(
                ['project_id' => $project->id],
                $data
            );

            Log::info('Data added to database successfully', [
                'table' => 'project_costs',
                'project_id' => $project->id,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add data to database', [
                'table' => 'project_costs',
                'project_id' => $project->id,
                'cause' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
