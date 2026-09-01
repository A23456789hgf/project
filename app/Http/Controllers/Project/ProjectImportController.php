<?php

namespace App\Http\Controllers\Project;

use App\Exceptions\MissingDropdownValuesException;
use App\Exports\DropdownMissingValuesExport;
use App\Exports\FailedProjectsExport;
use App\Exports\ProjectTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProjectImport;
use App\Models\Domain;
use App\Models\Intervention;
use App\Models\Priority;
use App\Models\Program;
use App\Models\Project;
use App\Models\Subdomain;
use App\Services\AuditLogService;
use App\Services\DropdownValueMappingService;
use App\Services\ImportTrackingService;
use App\Services\ProjectImportService;
use App\Traits\MapsImportHeadings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProjectImportController extends Controller
{
    use MapsImportHeadings;

    // -----------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------

    public function showImportForm()
    {
        return view('projects.import');
    }

    public function downloadTemplate()
    {
        $fileName = 'projects_import_template_'.date('Y-m-d').'.xlsx';
        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::download(new ProjectTemplateExport, $fileName);
    }

    // -----------------------------------------------------------------------
    // Preview (unchanged)
    // -----------------------------------------------------------------------

    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'operation' => 'required|in:insert,update,both',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            $fullPath = Storage::path($path);

            $data = Excel::toArray(new ProjectImport, $fullPath);
            $sheets = array_values($data);
            $rows = $sheets[0] ?? [];

            $programs = Program::pluck('name', 'id');
            $domains = Domain::pluck('name', 'id');
            $subdomains = Subdomain::pluck('name', 'id');
            $interventions = Intervention::pluck('name', 'id');
            $priorities = Priority::pluck('priority', 'id');

            $sheetCounts = [
                'details' => count($sheets[1] ?? []),
                'locations' => count($sheets[2] ?? []),
                'main_obj' => count($sheets[3] ?? []),
                'special_obj' => count($sheets[4] ?? []),
                'results' => count($sheets[5] ?? []),
                'prelim_act' => count($sheets[6] ?? []),
                'exec_act' => count($sheets[7] ?? []),
                'financings' => count($sheets[8] ?? []),
                'entities' => count($sheets[9] ?? []),
            ];

            $previewData = [];
            foreach ($rows as $index => $row) {
                $mappedRow = $this->mapArabicHeadings($row);
                if (empty($mappedRow['project_name'])) {
                    continue;
                }

                $previewData[] = [
                    'row_number' => $index + 2,
                    'project_name' => $mappedRow['project_name'] ?? '',
                    'program' => $programs[$mappedRow['program_id'] ?? null] ?? '-',
                    'domain' => $domains[$mappedRow['domain_id'] ?? null] ?? '-',
                    'subdomain' => $subdomains[$mappedRow['subdomain_id'] ?? null] ?? '-',
                    'intervention' => $interventions[$mappedRow['intervention_id'] ?? null] ?? '-',
                    'priority' => $priorities[$mappedRow['priority_id'] ?? null] ?? '-',
                    'start_date' => $mappedRow['start_date_gregorian'] ?? '-',
                    'end_date' => $mappedRow['end_date_gregorian'] ?? '-',
                    'status' => $mappedRow['status'] ?? 'draft',
                ];
            }

            return response()->json([
                'success' => true,
                'preview_data' => $previewData,
                'total_rows' => count($previewData),
                'sheet_counts' => $sheetCounts,
                'file_path' => $path,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء قراءة الملف: '.$e->getMessage()], 422);
        }
    }

    // -----------------------------------------------------------------------
    // Main import process
    // -----------------------------------------------------------------------

    public function processImport(Request $request): JsonResponse
    {
        $request->validate([
            'file_path' => 'required|string',
            'operation' => 'required|in:insert,update,both',
        ]);

        try {
            $filePath = $request->input('file_path');
            $fullPath = Storage::path($filePath);

            if (! file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'الملف غير موجود. يرجى إعادة رفع الملف.'], 404);
            }

            // Build session ID — tied to authenticated user (constraint #12)
            $importSession = DropdownValueMappingService::buildSession(auth()->id());

            $trackingService = new ImportTrackingService;
            $importLog = $trackingService->startImport('المشاريع', Project::class, basename($fullPath));

            $import = new ProjectImport($trackingService, $importLog);
            Excel::import($import, $fullPath);

            $report = $import->process();

            $failures = $import->failures();
            $dropdownSkipped = $import->getDropdownSkipped();
            $groupedMissing = $import->getGroupedMissingValues();

            $trackingService->finishImport(
                $importLog,
                $report['total_rows'] ?? 0,
                ($report['successful_inserts'] ?? 0) + ($report['successful_updates'] ?? 0),
                $report['failed_rows'] ?? 0,
                $failures
            );

            AuditLogService::log(
                action: 'import',
                module: 'Projects',
                description: "Imported Projects from file: {$filePath} "
                    .'(Created: '.($report['successful_inserts'] ?? 0)
                    .', Updated: '.($report['successful_updates'] ?? 0)
                    .', Skipped-Dropdown: '.($report['skipped_dropdown_rows'] ?? 0).')'
            );

            // Store skipped projects in a temporary JSON file for re-import
            $skippedFilePath = null;
            if (! empty($dropdownSkipped)) {
                $skippedFilePath = 'temp/skipped_'.$importSession.'.json';
                Storage::put($skippedFilePath, json_encode([
                    'session' => $importSession,
                    'user_id' => auth()->id(),
                    'skipped' => $dropdownSkipped,
                    'headings' => $import->getOriginalHeadings(),
                    'created_at' => now()->toDateTimeString(),
                ], JSON_UNESCAPED_UNICODE));

                $report['skipped_file'] = $skippedFilePath;
            }

            // Generate Excel report for missing values if any
            $dropdownExportUrl = null;
            if (! empty($dropdownSkipped)) {
                $exportPath = 'temp/dropdown_missing_'.$importSession.'.xlsx';
                Excel::store(
                    new DropdownMissingValuesExport($dropdownSkipped, $import->getOriginalHeadings()),
                    $exportPath
                );
                $dropdownExportUrl = route('projects.import-download-dropdown-report', ['session' => $importSession]);
                $report['dropdown_export_session'] = $importSession;
            }

            // Clean up original temp file
            Storage::delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'تم استيراد المشاريع',
                'report' => $report,
                'failures' => $failures,
                'skipped' => $import->getSkippedRowsDetails(),
                'updated' => $import->getUpdatedRowsDetails(),
                'dropdown_skipped' => $dropdownSkipped,
                'grouped_missing' => array_values($groupedMissing),
                'import_session' => ! empty($dropdownSkipped) ? $importSession : null,
                'skipped_file' => $skippedFilePath,
                'dropdown_export_url' => $dropdownExportUrl,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الاستيراد: '.$e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // Phase 2: Dropdown options (constraint #12 — strict table filtering)
    // -----------------------------------------------------------------------

    public function getDropdownOptions(Request $request, string $fieldKey): JsonResponse
    {
        // Validate fieldKey is in the known registry
        $meta = ProjectImportService::getDropdownFieldMeta($fieldKey);
        if (! $meta) {
            return response()->json(['success' => false, 'message' => 'حقل غير معروف.'], 422);
        }

        $parentId = $request->query('parent_id') ? (int) $request->query('parent_id') : null;

        $service = new DropdownValueMappingService;
        $options = $service->getDropdownOptions($fieldKey, $parentId);

        return response()->json([
            'success' => true,
            'field_key' => $fieldKey,
            'field_label' => $meta['label'],
            'table' => $meta['table'],
            'options' => $options,
        ]);
    }

    // -----------------------------------------------------------------------
    // Phase 2: Save a mapping (replace / add / edit_add)
    // -----------------------------------------------------------------------

    public function saveMissingValueMapping(Request $request): JsonResponse
    {
        $request->validate([
            'import_session' => 'required|string|max:100',
            'field_key' => 'required|string|max:100',
            'original_value' => 'required|string|max:500',
            'action' => 'required|in:replace,add,edit_add',
            'target_id' => 'nullable|integer|min:1',
            'edited_value' => 'nullable|string|max:500',
            'parent_fk' => 'nullable|string|max:100',
            'parent_id' => 'nullable|integer',
        ]);

        $session = $request->input('import_session');

        // Constraint #12: session ownership
        if (! DropdownValueMappingService::validateSessionOwnership($session, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'جلسة الاستيراد غير صالحة أو لا تخصك.'], 403);
        }

        $service = new DropdownValueMappingService;

        try {
            $mapping = match ($request->input('action')) {
                'replace' => $service->replace(
                    $session,
                    $request->input('field_key'),
                    $request->input('original_value'),
                    (int) $request->input('target_id'),
                    auth()->id(),
                    $request->input('parent_id') ? (int) $request->input('parent_id') : null
                ),
                'add' => $service->addNew(
                    $session,
                    $request->input('field_key'),
                    $request->input('original_value'),
                    auth()->id(),
                    $request->input('parent_fk'),
                    $request->input('parent_id') ? (int) $request->input('parent_id') : null
                ),
                'edit_add' => $service->editAndAdd(
                    $session,
                    $request->input('field_key'),
                    $request->input('original_value'),
                    $request->input('edited_value', ''),
                    auth()->id(),
                    $request->input('parent_fk'),
                    $request->input('parent_id') ? (int) $request->input('parent_id') : null
                ),
            };

            return response()->json([
                'success' => true,
                'message' => 'تمت المعالجة بنجاح',
                'mapping' => [
                    'id' => $mapping->id,
                    'action' => $mapping->action,
                    'action_label' => $mapping->action_label,
                    'target_id' => $mapping->target_id,
                    'target_value' => $mapping->target_value,
                ],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ: '.$e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // Phase 2: Re-import skipped projects
    // -----------------------------------------------------------------------

    public function reImportSkipped(Request $request): JsonResponse
    {
        $request->validate([
            'import_session' => 'required|string|max:100',
            'skipped_file' => 'required|string',
        ]);

        $session = $request->input('import_session');
        $skippedFile = $request->input('skipped_file');

        // Constraint #12: session ownership
        if (! DropdownValueMappingService::validateSessionOwnership($session, auth()->id())) {
            return response()->json(['success' => false, 'message' => 'جلسة الاستيراد غير صالحة أو لا تخصك.'], 403);
        }

        if (! Storage::exists($skippedFile)) {
            return response()->json(['success' => false, 'message' => 'ملف المشاريع المتخطاة غير موجود أو انتهت صلاحيته.'], 404);
        }

        $payload = json_decode(Storage::get($skippedFile), true);

        // Validate session belongs to this user
        if (($payload['user_id'] ?? null) != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك الوصول إلى هذه الجلسة.'], 403);
        }

        $skippedProjects = $payload['skipped'] ?? [];
        $mappingService = new DropdownValueMappingService;
        $importService = new ProjectImportService;

        $reImportReport = [
            'total_skipped' => count($skippedProjects),
            'now_imported' => 0,
            'still_skipped' => 0,
            'errors' => 0,
            'imported_projects' => [],
            'still_skipped_list' => [],
            'errors_list' => [],
        ];

        $updatedStillSkipped = [];

        foreach ($skippedProjects as $skipped) {
            $projectName = $skipped['project_name'];
            $projectData = $skipped['full_data'] ?? $skipped['original_row'] ?? [];
            $missingValues = $skipped['missing_values'];

            try {
                // Apply all saved mappings to resolve missing values
                $resolvedData = $mappingService->applyMappingsAndValidate($session, $projectData, $missingValues);

                // Re-import atomically (constraint #15)
                $project = $importService->import($resolvedData);

                $reImportReport['now_imported']++;
                $reImportReport['imported_projects'][] = $projectName;

                AuditLogService::log(
                    action: 'import',
                    module: 'Projects',
                    description: "Re-imported skipped project: {$projectName} (session: {$session})"
                );

            } catch (MissingDropdownValuesException $e) {
                // Still has unresolved values — keep in skipped list with updated missing values
                $reImportReport['still_skipped']++;
                $updatedSkipped = $skipped;
                $updatedSkipped['missing_values'] = $e->getMissingValues();
                $updatedStillSkipped[] = $updatedSkipped;

                $reImportReport['still_skipped_list'][] = [
                    'project_name' => $projectName,
                    'missing_values' => $e->getMissingValues(),
                ];

            } catch (\Exception $e) {
                $reImportReport['errors']++;
                $reImportReport['errors_list'][] = [
                    'project_name' => $projectName,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Update the skipped JSON file — constraint #9: only keep truly still-skipped
        if (! empty($updatedStillSkipped)) {
            $payload['skipped'] = $updatedStillSkipped;
            $payload['updated_at'] = now()->toDateTimeString();
            Storage::put($skippedFile, json_encode($payload, JSON_UNESCAPED_UNICODE));

            // Regenerate Excel report with remaining skipped
            $exportPath = 'temp/dropdown_missing_'.$session.'.xlsx';
            Excel::store(
                new DropdownMissingValuesExport($updatedStillSkipped, $payload['headings'] ?? []),
                $exportPath
            );
        } else {
            // All resolved — clean up temp files
            Storage::delete($skippedFile);
            Storage::delete('temp/dropdown_missing_'.$session.'.xlsx');
        }

        // Regenerate grouped missing for frontend
        $groupedStillMissing = [];
        foreach ($updatedStillSkipped as $skipped) {
            foreach ($skipped['missing_values'] as $mv) {
                $key = $mv['field_key'].':::'.mb_strtolower(trim($mv['value']));
                if (! isset($groupedStillMissing[$key])) {
                    $groupedStillMissing[$key] = [
                        'field_key' => $mv['field_key'],
                        'field_label' => $mv['field_label'],
                        'table_name' => $mv['table_name'],
                        'original_value' => $mv['value'],
                        'affected_count' => 0,
                        'affected_projects' => [],
                        'status' => 'pending',
                    ];
                }
                $groupedStillMissing[$key]['affected_count']++;
                $groupedStillMissing[$key]['affected_projects'][] = $skipped['project_name'];
            }
        }

        return response()->json([
            'success' => true,
            'report' => $reImportReport,
            'still_grouped_missing' => array_values($groupedStillMissing),
            'has_remaining' => ! empty($updatedStillSkipped),
        ]);
    }

    // -----------------------------------------------------------------------
    // Download missing-values Excel report
    // -----------------------------------------------------------------------

    public function downloadDropdownReport(Request $request): mixed
    {
        $session = $request->query('session', '');

        if (! DropdownValueMappingService::validateSessionOwnership($session, auth()->id())) {
            abort(403, 'جلسة الاستيراد غير صالحة.');
        }

        $exportPath = 'temp/dropdown_missing_'.$session.'.xlsx';
        if (! Storage::exists($exportPath)) {
            abort(404, 'ملف التقرير غير موجود أو انتهت صلاحيته.');
        }

        $fileName = 'قيم_ناقصة_'.now()->format('Y-m-d').'.xlsx';

        if (ob_get_length()) {
            ob_end_clean();
        }

        return response()->download(Storage::path($exportPath), $fileName);
    }

    // -----------------------------------------------------------------------
    // Existing failure report (unchanged — for general failures, not dropdown)
    // -----------------------------------------------------------------------

    public function downloadFailureReport(Request $request): mixed
    {
        try {
            if ($request->isMethod('post')) {
                $failures = $request->input('failures', []);
                if (! empty($failures)) {
                    $fileName = 'مشاريع_فاشلة_'.now()->format('Y-m-d_H-i').'.xlsx';
                    if (ob_get_length()) {
                        ob_end_clean();
                    }

                    return Excel::download(new FailedProjectsExport($failures), $fileName);
                }
            }

            $storePath = $request->query('path', '');
            if ($storePath && Storage::exists($storePath)) {
                $fileName = 'مشاريع_فاشلة_'.now()->format('Y-m-d').'.xlsx';
                if (ob_get_length()) {
                    ob_end_clean();
                }

                return response()->download(Storage::path($storePath), $fileName);
            }

            return response()->json(['success' => false, 'message' => 'لا يوجد تقرير متاح للتنزيل.'], 404);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'خطأ في إنشاء التقرير: '.$e->getMessage()], 500);
        }
    }
}
