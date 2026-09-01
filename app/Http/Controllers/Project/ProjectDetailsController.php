<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectDetailsController extends Controller
{
    public function getValidationRules($isDraft = false)
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'is_part_of_plan' => 'nullable|boolean',
            'project_summary' => 'nullable|string',
            'project_introduction' => 'nullable|string',
            'problem_and_justification' => 'nullable|string',
            'project_components' => 'nullable|string',
            'expected_impact' => 'nullable|string',
        ];
    }

    public function handle(Project $project, array $data)
    {
        DB::beginTransaction();

        try {
            $detailData = [];
            $fields = [
                'is_part_of_plan',
                'project_summary',
                'project_introduction',
                'problem_and_justification',
                'project_components',
                'expected_impact',
            ];

            foreach ($fields as $field) {
                if (array_key_exists($field, $data)) {
                    $detailData[$field] = $data[$field] ?? '';
                }
            }

            if (empty($detailData)) {
                DB::commit();

                return $project->detail;
            }

            // استخدام updateOrCreate مع transaction
            $projectDetail = ProjectDetail::updateOrCreate(
                ['project_id' => $project->id],
                $detailData
            );

            DB::commit();

            Log::info('Project details saved successfully', [
                'table' => 'project_details',
                'project_id' => $project->id,
                'detail_id' => $projectDetail->id,
                'data' => $detailData,
            ]);

            return $projectDetail;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to save project details', [
                'table' => 'project_details',
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
