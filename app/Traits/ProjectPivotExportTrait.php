<?php

namespace App\Traits;

use App\Exports\ProjectPivotExport;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ProjectPivotExportTrait
{
    /**
     * Export single project as Pivot Table Excel
     */
    public function exportProjectPivot(Project $project)
    {
        try {
            $exporter = new ProjectPivotExport;
            $spreadsheet = $exporter->exportSingleProject($project);

            $fileName = 'Project_Pivot_'.($project->form_number ?? $project->id).'_'.date('Y-m-d').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export project pivot: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'فشل في تصدير ملف Pivot Excel: '.$e->getMessage());
        }
    }

    /**
     * Export all projects as Pivot Table Excel
     */
    public function exportAllProjectsPivot(Request $request)
    {
        try {
            // Build query with filters
            $query = Project::with([
                'program', 'domain', 'subdomain', 'intervention', 'priority',
                'detail', 'locations', 'mainObjectives', 'specialObjectives', 'risks', 'cost',
                'financings', 'supervisingAuthorities', 'implementingEntities',
                'participatingEntities', 'beneficiaryEntities', 'preliminaryActivities',
                'preliminaryFinancialSummaries', 'executiveActivities', 'executiveFinancialSummaries',
            ]);

            // Apply filters if provided
            if ($request->has('program_id') && $request->program_id) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->has('domain_id') && $request->domain_id) {
                $query->where('domain_id', $request->domain_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $projects = $query->latest()->get();

            if ($projects->isEmpty()) {
                flash()->warning('لا توجد مشاريع للتصدير / No projects to export');

                return back();
            }

            $exporter = new ProjectPivotExport;
            $spreadsheet = $exporter->exportMultipleProjects($projects);

            $fileName = 'Projects_Pivot_Export_'.date('Y-m-d_H-i-s').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export all projects pivot: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return back()->with('error', 'فشل في تصدير ملفات Pivot Excel: '.$e->getMessage());
        }
    }
}
