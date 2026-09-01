<?php

namespace App\Http\Controllers\Project\Traits;

use App\Models\Project;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait ProjectExportTrait
{
    /**
     * تصدير مشروع واحد كـ PDF
     */
    public function exportProjectPdf(Project $project)
    {
        try {
            AuditLogService::log(
                action: 'export',
                module: 'Projects',
                description: "Exported Project PDF: {$project->project_name} ({$project->form_number})",
                modelType: Project::class,
                modelId: $project->id
            );

            return $this->exportService->exportProjectPdf($project);
        } catch (\Exception $e) {
            Log::error('Failed to export project PDF in trait: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير ملف PDF: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير ملف PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير مشروع واحد كـ PDF مفصل
     */
    public function exportDetailedProjectPdf(Project $project)
    {
        try {
            AuditLogService::log(
                action: 'export',
                module: 'Projects',
                description: "Exported Detailed Project PDF: {$project->project_name} ({$project->form_number})",
                modelType: Project::class,
                modelId: $project->id
            );

            return $this->exportService->exportDetailedProjectPdf($project);
        } catch (\Exception $e) {
            Log::error('Failed to export detailed project PDF in trait: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير الملف المفصل: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير الملف المفصل: '.$e->getMessage());
        }
    }

    /**
     * تصدير مشروع واحد كـ Excel
     */
    public function exportProjectExcel(Project $project)
    {
        try {
            AuditLogService::log(
                action: 'export',
                module: 'Projects',
                description: "Exported Project Excel: {$project->project_name} ({$project->form_number})",
                modelType: Project::class,
                modelId: $project->id
            );

            return $this->exportService->exportProjectExcel($project);
        } catch (\Exception $e) {
            Log::error('Failed to export project Excel in trait: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير ملف Excel: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير ملف Excel: '.$e->getMessage());
        }
    }

    /**
     * تصدير جميع المشاريع كـ Excel
     */
    public function exportAllProjectsExcel(Request $request)
    {
        try {
            AuditLogService::log(
                action: 'export',
                module: 'Projects',
                description: 'Exported All Projects Excel'
            );

            return $this->exportService->exportAllProjectsExcel($request);
        } catch (\Exception $e) {
            Log::error('Failed to export all projects Excel in trait: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير ملفات Excel: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير ملفات Excel: '.$e->getMessage());
        }
    }

    /**
     * تصدير جميع المشاريع كـ PDF
     */
    public function exportAllProjectsPdf(Request $request)
    {
        try {
            return $this->exportService->exportAllProjectsPdf($request);
        } catch (\Exception $e) {
            Log::error('Failed to export all projects PDF in trait: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير ملفات PDF: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير ملفات PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير المشاريع إلى Excel (الطريقة الأساسية)
     */
    public function exportExcel()
    {
        try {
            return $this->exportService->exportProjectsExcel();
        } catch (\Exception $e) {
            Log::error('Failed to export projects Excel in trait: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير ملف Excel: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير ملف Excel: '.$e->getMessage());
        }
    }

    /**
     * تصدير قائمة المشاريع كـ PDF
     */
    public function exportProjectsListPdf(Request $request)
    {
        try {
            return $this->exportService->exportProjectsListPdf($request);
        } catch (\Exception $e) {
            Log::error('Failed to export projects list PDF in trait: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير قائمة PDF: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير قائمة PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير التلخيص المالي للمشروع كـ PDF
     */
    public function exportFinancialSummaryPdf(Project $project)
    {
        try {
            return $this->exportService->exportFinancialSummaryPdf($project);
        } catch (\Exception $e) {
            Log::error('Failed to export financial summary PDF in trait: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير التلخيص المالي: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير التلخيص المالي: '.$e->getMessage());
        }
    }

    /**
     * تصدير التلخيص المالي للمشروع كـ Excel
     */
    public function exportFinancialSummaryExcel(Project $project)
    {
        try {
            return $this->exportService->exportFinancialSummaryExcel($project);
        } catch (\Exception $e) {
            Log::error('Failed to export financial summary Excel in trait: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تصدير التلخيص المالي: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تصدير التلخيص المالي: '.$e->getMessage());
        }
    }

    /**
     * تصدير بيانات المشروع بتنسيق مخصص
     */
    public function exportCustomFormat(Project $project, string $format)
    {
        try {
            switch (strtolower($format)) {
                case 'json':
                    return $this->exportProjectJson($project);

                case 'csv':
                    return $this->exportProjectCsv($project);

                case 'xml':
                    return $this->exportProjectXml($project);

                default:
                    throw new \Exception('تنسيق التصدير غير مدعوم: '.$format);
            }
        } catch (\Exception $e) {
            Log::error('Failed to export project in custom format: '.$e->getMessage(), [
                'project_id' => $project->id,
                'format' => $format,
                'exception' => $e,
            ]);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في التصدير: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في التصدير: '.$e->getMessage());
        }
    }

    /**
     * تصدير المشروع كـ JSON
     */
    protected function exportProjectJson(Project $project)
    {
        try {
            $project->load([
                'program', 'domain', 'subdomain', 'intervention', 'priority',
                'detail', 'locations.governorate', 'mainObjectives', 'specialObjectives',
                'risks', 'cost', 'financings', 'supervisingAuthorities',
                'implementingEntities', 'participatingEntities', 'beneficiaryEntities',
                'preliminaryActivities', 'executiveActivities',
            ]);

            $exportData = $this->prepareProjectDataForExport($project);

            $fileName = 'project_'.$project->form_number.'_'.date('Y-m-d').'.json';

            return response()->streamDownload(function () use ($exportData) {
                echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, $fileName, [
                'Content-Type' => 'application/json',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export project as JSON: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * تصدير المشروع كـ CSV
     */
    protected function exportProjectCsv(Project $project)
    {
        try {
            $project->load([
                'program', 'domain', 'subdomain', 'intervention', 'priority',
                'detail', 'locations.governorate', 'mainObjectives', 'specialObjectives',
                'risks', 'cost', 'financings', 'supervisingAuthorities',
                'implementingEntities', 'participatingEntities', 'beneficiaryEntities',
            ]);

            $exportData = $this->prepareProjectDataForExport($project);

            $fileName = 'project_'.$project->form_number.'_'.date('Y-m-d').'.csv';

            return response()->streamDownload(function () use ($exportData) {
                $output = fopen('php://output', 'w');

                // كتابة الرأس
                fputcsv($output, ['Field', 'Value']);

                // كتابة البيانات الأساسية
                $this->addDataToCsv($output, $exportData['basic_info'], '');

                // كتابة البيانات الأخرى
                foreach ($exportData as $section => $data) {
                    if ($section !== 'basic_info') {
                        fputcsv($output, [strtoupper(str_replace('_', ' ', $section)), '']);
                        $this->addDataToCsv($output, $data, '  ');
                    }
                }

                fclose($output);
            }, $fileName, [
                'Content-Type' => 'text/csv; charset=utf-8',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export project as CSV: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * تصدير المشروع كـ XML
     */
    protected function exportProjectXml(Project $project)
    {
        try {
            $project->load([
                'program', 'domain', 'subdomain', 'intervention', 'priority',
                'detail', 'locations.governorate', 'mainObjectives', 'specialObjectives',
                'risks', 'cost', 'financings', 'supervisingAuthorities',
                'implementingEntities', 'participatingEntities', 'beneficiaryEntities',
            ]);

            $exportData = $this->prepareProjectDataForExport($project);

            $fileName = 'project_'.$project->form_number.'_'.date('Y-m-d').'.xml';

            return response()->streamDownload(function () use ($exportData) {
                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project></project>');
                $this->arrayToXml($exportData, $xml);
                echo $xml->asXML();
            }, $fileName, [
                'Content-Type' => 'application/xml',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export project as XML: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * تحضير بيانات المشروع للتصدير
     */
    protected function prepareProjectDataForExport(Project $project): array
    {
        return [
            'basic_info' => [
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'status' => $project->status,
                'program' => $project->program->name ?? '',
                'domain' => $project->domain->name ?? '',
                'subdomain' => $project->subdomain->name ?? '',
                'intervention' => $project->intervention->name ?? '',
                'start_date' => $project->start_date_gregorian,
                'end_date' => $project->end_date_gregorian,
                'number_of_beneficiaries' => $project->number_of_beneficiaries,
            ],
            'objectives' => [
                'main_objectives' => $project->mainObjectives->map(function ($objective) {
                    return [
                        'objective' => $objective->objective,
                        'indicator' => $objective->indicator,
                        'indicator_unit' => $objective->indicator_unit,
                    ];
                })->toArray(),
                'special_objectives' => $project->specialObjectives->map(function ($objective) {
                    return [
                        'objective' => $objective->objective,
                        'indicator' => $objective->indicator,
                        'indicator_unit' => $objective->indicator_unit,
                        'indicator_value' => $objective->indicator_value,
                        'objective_weight' => $objective->objective_weight,
                    ];
                })->toArray(),
            ],
            'locations' => $project->locations->map(function ($location) {
                return [
                    'governorate' => $location->governorate->name ?? '',
                    'directorate' => $location->directorate->name ?? '',
                    'sub_area' => $location->sub_area->name ?? '',
                    'village' => $location->village->name ?? '',
                ];
            })->toArray(),
            'financial_info' => [
                'total_cost' => $project->cost->total_cost ?? 0,
                'financings' => $project->financings->map(function ($financing) {
                    return [
                        'funding_source' => $financing->fundingSource->name ?? '',
                        'authority' => $financing->authority->name ?? '',
                        'financing_amount' => $financing->financing_amount,
                        'financing_percentage' => $financing->financing_percentage,
                    ];
                })->toArray(),
            ],
            'entities' => [
                'supervising_authorities' => $project->supervisingAuthorities->map(function ($authority) {
                    return [
                        'authority_type' => $authority->authority_type,
                        'authority_name' => $authority->authority->name ?? '',
                    ];
                })->toArray(),
                'implementing_entities' => $project->implementingEntities->map(function ($entity) {
                    return [
                        'entity_type' => $entity->entity_type,
                        'authority_name' => $entity->authority->name ?? '',
                    ];
                })->toArray(),
                'participating_entities' => $project->participatingEntities->map(function ($entity) {
                    return [
                        'entity_type' => $entity->entity_type,
                        'authority_name' => $entity->authority->name ?? '',
                    ];
                })->toArray(),
                'beneficiary_entities' => $project->beneficiaryEntities->map(function ($entity) {
                    return [
                        'entity_type' => $entity->entity_type,
                        'authority_name' => $entity->authority->name ?? '',
                    ];
                })->toArray(),
            ],
            'risks' => $project->risks->map(function ($risk) {
                return [
                    'risk' => $risk->risk,
                    'risk_rate' => $risk->risk_rate,
                    'proposed_solution' => $risk->proposed_solution,
                ];
            })->toArray(),
            'metadata' => [
                'exported_at' => now()->toISOString(),
                'exported_by' => auth()->user()->name ?? 'System',
                'project_version' => '1.0',
            ],
        ];
    }

    /**
     * إضافة بيانات إلى CSV
     */
    private function addDataToCsv($output, array $data, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (! empty($value) && isset($value[0]) && is_array($value[0])) {
                    // مصفوفة متعددة الأبعاد
                    foreach ($value as $index => $item) {
                        fputcsv($output, [$prefix.$key.'['.$index.']', '']);
                        $this->addDataToCsv($output, $item, $prefix.'  ');
                    }
                } else {
                    // مصفوفة أحادية البعد
                    fputcsv($output, [$prefix.$key, '']);
                    $this->addDataToCsv($output, $value, $prefix.'  ');
                }
            } else {
                fputcsv($output, [$prefix.$key, $value]);
            }
        }
    }

    /**
     * تحويل المصفوفة إلى XML
     */
    private function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item_'.$key;
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                if (is_numeric($key)) {
                    $key = 'item_'.$key;
                }
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * الحصول على إحصائيات التصدير
     */
    public function getExportStatistics(): JsonResponse
    {
        try {
            $statistics = $this->exportService->getExportStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get export statistics in trait: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get export statistics',
            ], 500);
        }
    }

    /**
     * تنزيل نموذج استيراد المشاريع
     */
    public function downloadImportTemplate(string $format = 'excel')
    {
        try {
            switch (strtolower($format)) {
                case 'excel':
                    return $this->downloadExcelImportTemplate();

                case 'csv':
                    return $this->downloadCsvImportTemplate();

                default:
                    throw new \Exception('تنسيق النموذج غير مدعوم: '.$format);
            }
        } catch (\Exception $e) {
            Log::error('Failed to download import template: '.$e->getMessage());

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تنزيل النموذج: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'فشل في تنزيل النموذج: '.$e->getMessage());
        }
    }

    /**
     * تنزيل نموذج استيراد Excel
     */
    protected function downloadExcelImportTemplate()
    {
        // سيتم تنفيذ هذا في خدمة التصدير
        return $this->exportService->downloadExcelImportTemplate();
    }

    /**
     * تنزيل نموذج استيراد CSV
     */
    protected function downloadCsvImportTemplate()
    {
        // سيتم تنفيذ هذا في خدمة التصدير
        return $this->exportService->downloadCsvImportTemplate();
    }

    /**
     * تصدير تقرير عن حالة التصدير
     */
    public function exportExportReport(): JsonResponse
    {
        try {
            $statistics = $this->exportService->getExportStatistics();
            $report = [
                'export_statistics' => $statistics,
                'generated_at' => now()->toISOString(),
                'generated_by' => auth()->user()->name ?? 'System',
                'total_exports' => array_sum(array_values($statistics)),
            ];

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export export report: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate export report',
            ], 500);
        }
    }
}
