<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProjectRisksController extends Controller
{
    /**
     * Build validation rules for risks depending on draft/final status.
     */
    public function getValidationRules(bool $isDraft = false): array
    {
        $requiredRule = $isDraft ? 'nullable' : 'required';

        return [
            'risks' => 'nullable|array',
            'risks.*.risk' => $requiredRule.'|string|max:255',
            'risks.*.risk_rate' => $requiredRule.'|integer|min:1|max:10',
            'risks.*.proposed_solution' => $requiredRule.'|string|max:500',
        ];
    }

    /**
     * Create or update project risks and provide structured feedback.
     */
    public function createOrUpdate(Project $project, ?array $risks): array
    {
        Log::info('Starting project risks save operation', [
            'project_id' => $project->id,
            'risks_count' => $risks ? count($risks) : 0,
        ]);

        DB::beginTransaction();
        try {
            $deletedCount = $project->risks()->delete();
            Log::info('Previous project risks removed', [
                'project_id' => $project->id,
                'deleted_count' => $deletedCount,
            ]);

            if (! $risks || empty($risks)) {
                DB::commit();
                Log::info('No new risks provided');

                return [
                    'status' => 'success',
                    'message' => 'Project risks updated successfully (no risks provided).',
                    'created_count' => 0,
                    'high_risk_count' => 0,
                    'risks' => [],
                ];
            }

            $createdCount = 0;
            $highRiskCount = 0;
            $createdRisks = [];

            foreach ($risks as $index => $riskData) {
                $riskNumber = $index + 1;
                $this->assertRiskDataIsValid($riskData, $riskNumber);

                $createdRisk = $project->risks()->create([
                    'risk' => $riskData['risk'],
                    'risk_rate' => $riskData['risk_rate'],
                    'proposed_solution' => $riskData['proposed_solution'] ?? null,
                ]);

                $createdCount++;
                $createdRisks[] = $createdRisk->toArray();

                if ($riskData['risk_rate'] >= 7) {
                    $highRiskCount++;
                    Log::warning('High level project risk created', [
                        'project_id' => $project->id,
                        'risk_id' => $createdRisk->id,
                        'risk_rate' => $riskData['risk_rate'],
                    ]);
                }

                Log::info('Project risk saved successfully', [
                    'project_id' => $project->id,
                    'risk_id' => $createdRisk->id,
                    'risk_number' => $riskNumber,
                ]);
            }

            DB::commit();

            $successMessage = sprintf(
                'Project risks saved successfully. %d risks added; %d are high level.',
                $createdCount,
                $highRiskCount
            );

            Log::info($successMessage, [
                'project_id' => $project->id,
                'created_risks' => $createdRisks,
            ]);

            return [
                'status' => 'success',
                'message' => $successMessage,
                'created_count' => $createdCount,
                'high_risk_count' => $highRiskCount,
                'risks' => $createdRisks,
            ];
        } catch (ValidationException $exception) {
            DB::rollBack();
            Log::error('Risk validation failed', [
                'project_id' => $project->id,
                'errors' => $exception->errors(),
            ]);

            throw $exception;
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Unexpected failure while saving project risks', [
                'project_id' => $project->id,
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('Failed to save project risks: '.$exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Fetch project risks along with statistics.
     */
    public function index(Project $project): array
    {
        try {
            $risks = $project->risks()->orderByDesc('risk_rate')->get();

            $statistics = [
                'total' => $risks->count(),
                'high' => $risks->where('risk_rate', '>=', 7)->count(),
                'medium' => $risks->whereBetween('risk_rate', [4, 6])->count(),
                'low' => $risks->where('risk_rate', '<=', 3)->count(),
            ];

            Log::info('Project risks fetched successfully', [
                'project_id' => $project->id,
                'statistics' => $statistics,
            ]);

            return [
                'status' => 'success',
                'message' => 'Project risks fetched successfully.',
                'risks' => $risks,
                'statistics' => $statistics,
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to fetch project risks', [
                'project_id' => $project->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to fetch project risks: '.$exception->getMessage(),
                'risks' => collect([]),
                'statistics' => [
                    'total' => 0,
                    'high' => 0,
                    'medium' => 0,
                    'low' => 0,
                ],
            ];
        }
    }

    /**
     * Analyse project risks and provide a quick summary.
     */
    public function analyse(Project $project): array
    {
        try {
            $risks = $project->risks;
            if ($risks->isEmpty()) {
                Log::info('No project risks to analyse', ['project_id' => $project->id]);

                return [
                    'status' => 'success',
                    'message' => 'No project risks to analyse.',
                    'analysis' => [
                        'total_risks' => 0,
                        'average_risk_rate' => 0,
                        'risk_level' => 'Low',
                        'recommendations' => [],
                    ],
                ];
            }

            $averageRiskRate = $risks->avg('risk_rate');
            $highRiskCount = $risks->where('risk_rate', '>=', 7)->count();

            [$riskLevel, $recommendations] = $this->determineRiskLevel($averageRiskRate, $highRiskCount);

            $analysis = [
                'total_risks' => $risks->count(),
                'average_risk_rate' => round($averageRiskRate, 2),
                'risk_level' => $riskLevel,
                'max_risk_rate' => $risks->max('risk_rate'),
                'high_risks_count' => $highRiskCount,
                'recommendations' => $recommendations,
            ];

            Log::info('Project risks analysed successfully', [
                'project_id' => $project->id,
                'analysis' => $analysis,
            ]);

            return [
                'status' => 'success',
                'message' => 'Project risks analysed successfully.',
                'analysis' => $analysis,
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to analyse project risks', [
                'project_id' => $project->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to analyse project risks: '.$exception->getMessage(),
                'analysis' => [
                    'total_risks' => 0,
                    'average_risk_rate' => 0,
                    'risk_level' => 'Unknown',
                    'recommendations' => [],
                ],
            ];
        }
    }

    /**
     * Delete a single project risk.
     */
    public function delete(Project $project, int $riskId): array
    {
        try {
            $risk = $project->risks()->findOrFail($riskId);
            $riskDescription = $risk->risk;
            $risk->delete();

            Log::info('Project risk deleted successfully', [
                'project_id' => $project->id,
                'risk_id' => $riskId,
                'risk_description' => $riskDescription,
            ]);

            return [
                'status' => 'success',
                'message' => 'Project risk deleted successfully.',
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to delete project risk', [
                'project_id' => $project->id,
                'risk_id' => $riskId,
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('Failed to delete project risk: '.$exception->getMessage(), previous: $exception);
        }
    }

    /**
     * Additional, request-level validation for risks (similar to the old trait helper).
     */
    public function validateRisksOnRequest($validator, Request $request): void
    {
        $isDraft = $request->input('status') === 'draft';
        $risks = $request->input('risks', []);

        if ($isDraft || empty($risks)) {
            return;
        }

        foreach ($risks as $index => $risk) {
            if (empty(trim($risk['risk'] ?? ''))) {
                $validator->errors()->add(
                    "risks.{$index}.risk",
                    'The risk description is required for each risk when saving the project.'
                );
            }

            $riskRate = $risk['risk_rate'] ?? null;
            if ($riskRate === null || $riskRate < 1 || $riskRate > 10) {
                $validator->errors()->add(
                    "risks.{$index}.risk_rate",
                    'The risk rate must be between 1 and 10 when saving the project.'
                );
            }
        }
    }

    /**
     * Validate individual risk payload.
     */
    private function assertRiskDataIsValid(array $risk, int $riskNumber): void
    {
        if (empty($risk['risk']) || trim($risk['risk']) === '') {
            throw ValidationException::withMessages([
                "risks.{$riskNumber}.risk" => ['The risk description cannot be empty.'],
            ]);
        }

        if (! isset($risk['risk_rate']) || $risk['risk_rate'] < 1 || $risk['risk_rate'] > 10) {
            throw ValidationException::withMessages([
                "risks.{$riskNumber}.risk_rate" => ['The risk rate must be between 1 and 10.'],
            ]);
        }

        if (empty($risk['proposed_solution']) || trim($risk['proposed_solution']) === '') {
            Log::warning('Risk proposed solution missing', [
                'risk_number' => $riskNumber,
            ]);
        }
    }

    /**
     * Determine the overall risk level and recommended actions.
     */
    private function determineRiskLevel(float $averageRiskRate, int $highRiskCount): array
    {
        if ($averageRiskRate >= 7 || $highRiskCount >= 3) {
            return [
                'High',
                [
                    'Conduct an immediate review for high-level risks.',
                    'Prepare contingency plans.',
                    'Schedule frequent follow-ups on high risks.',
                ],
            ];
        }

        if ($averageRiskRate >= 4) {
            return [
                'Medium',
                [
                    'Monitor medium-level risks closely.',
                    'Implement alternative strategies.',
                    'Review risk status periodically.',
                ],
            ];
        }

        return [
            'Low',
            [
                'Perform routine monitoring.',
                'Conduct regular reviews.',
            ],
        ];
    }
}
