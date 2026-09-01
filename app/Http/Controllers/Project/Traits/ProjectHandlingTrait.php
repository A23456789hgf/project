<?php

namespace App\Http\Controllers\Project\Traits;

use App\Models\Authority;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\Stage;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ProjectHandlingTrait
{
    /**
     * معالجة تخزين المشروع الجديد
     */
    protected function handleProjectStore(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::debug('Incoming data before normalization', $request->all());
            $this->normalizeRequestData($request);
            Log::debug('Data after normalization', $request->all());

            $validator = Validator::make($request->all(), $this->getStoreValidationRules($request));

            $validator->after(function ($validator) use ($request) {
                $this->validateProjectComponents($validator, $request);

                if ($request->input('status') === 'final') {
                    $this->validateFinalProjectRequirements($validator, $request);
                }
            });

            $validated = $validator->validate();

            $project = $this->projectService->createProject($validated);

            // Create initial assembly approval stage for the new project
            $this->createInitialAssemblyApprovalStage($project, $validated);

            // Process all project components
            $this->projectService->processProjectComponents($project, $validated, $request);

            DB::commit();

            Log::channel('projects')->info('New project added successfully', [
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => $request->ip(),
            ]);

            return $this->handleSuccessResponse($request, $project, 'Project added successfully!');

        } catch (ValidationException $e) {
            DB::rollBack();

            return $this->handleValidationError($request, $e);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleException($request, $e);
        }
    }

    /**
     * معالجة تحديث المشروع الموجود
     */
    protected function handleProjectUpdate(Request $request, Project $project)
    {
        DB::beginTransaction();
        try {
            Log::debug('Incoming data before normalization - update', $request->all());
            $this->normalizeRequestData($request);
            Log::debug('Data after normalization - update', $request->all());

            $navDir = $request->input('navigation_direction');
            $isDraftSave = $request->input('status') === 'draft' || in_array($navDir, ['draft', 'prev', 'back']);

            $validated = [];

            if ($isDraftSave) {
                $validated = $request->all();
                $validated['draft_saved_at'] = now();
            } else {
                $validator = Validator::make($request->all(), $this->getUpdateValidationRules($request));

                $validator->after(function ($validator) use ($request, $project) {
                    $this->validateProjectComponents($validator, $request);

                    if ($request->input('status') === 'final') {
                        $this->validateFinalProjectRequirements($validator, $request);

                        if (! $project->draft_saved_at) {
                            $validator->errors()->add('status', 'Cannot save project as final without saving it as draft first');
                        }
                    }

                    if ($project->status === 'final' && $request->input('status') !== 'final') {
                        $validator->errors()->add('status', 'Cannot edit a project that has been saved as final');
                    }
                });

                $validated = $validator->validate();
            }

            if ($request->has('last_saved_step')) {
                $validated['last_saved_step'] = $request->input('last_saved_step');
            }

            $this->projectService->updateProject($project, $validated);

            // Process all project components
            $this->projectService->processProjectComponents($project, $validated, $request, $isDraftSave ?? false);

            // ─── استكمال بيانات المشروع القديم وإرسال إشعار ───
            if ($project->project_type === 'old' && ! $project->is_data_completed) {
                $project->is_data_completed = true;
                $project->data_completed_at = now();
                $project->save();

                // استدعاء خدمة الإشعارات لإرسال إشعار اكتمال البيانات
                app(NotificationService::class)->notifyOldProjectDataCompleted($project);
            }

            DB::commit();

            Log::channel('projects')->info('Project updated successfully', [
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'form_number' => $project->form_number,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => $request->ip(),
            ]);

            return $this->handleSuccessResponse($request, $project, 'Project updated successfully!');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::channel('projects')->error('Data validation failed while updating project', [
                'project_id' => $project->id,
                'errors' => $e->errors(),
                'request' => $request->except('_token'),
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => $request->ip(),
            ]);

            return $this->handleValidationError($request, $e);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('projects')->error('Failed to update project: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
                'request' => $request->except('_token'),
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => $request->ip(),
            ]);

            return $this->handleException($request, $e);
        }
    }

    /**
     * الحفظ التلقائي للمشروع (إنشاء جديد أو تحديث موجود)
     */
    protected function handleAutoSave(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            Log::debug('Auto-save: Incoming data', ['has_project_id' => $request->has('project_id')]);

            $this->normalizeRequestData($request);

            // Minimal validation for auto-save (only basic required fields)
            $rules = $this->getAutoSaveValidationRules($request);
            $validated = $request->validate($rules);

            $project = $this->projectService->autoSaveProject($validated, $request->input('project_id'));

            // Process all project components (with error handling for incomplete data)
            try {
                $this->projectService->processProjectComponents($project, $validated, $request);
            } catch (\Exception $e) {
                // Log but don't fail - partial data is acceptable for auto-save
                Log::warning('Auto-save: Partial data saved', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'project' => [
                    'id' => $project->id,
                    'form_number' => $project->form_number,
                    'project_name' => $project->project_name,
                ],
                'message' => 'تم الحفظ التلقائي',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-save error: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل الحفظ التلقائي',
            ], 500);
        }
    }

    /**
     * الحفظ التلقائي للتحديث للمشروع الموجود
     */
    protected function handleAutoSaveUpdate(Request $request, Project $project): JsonResponse
    {
        DB::beginTransaction();
        try {
            Log::debug('Auto-save update: Project '.$project->id);

            $this->normalizeRequestData($request);

            // Minimal validation for auto-save
            $rules = $this->getAutoSaveValidationRules($request);
            $validated = $request->validate($rules);

            $this->projectService->autoSaveUpdateProject($project, $validated);

            // Process all project components (with error handling)
            try {
                $this->projectService->processProjectComponents($project, $validated, $request);
            } catch (\Exception $e) {
                Log::warning('Auto-save update: Partial data saved', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'project' => [
                    'id' => $project->id,
                    'form_number' => $project->form_number,
                    'project_name' => $project->project_name,
                ],
                'message' => 'تم الحفظ التلقائي',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-save update error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'فشل الحفظ التلقائي',
            ], 500);
        }
    }

    /**
     * معالجة بيانات الطلب وتطبيعها
     */
    protected function normalizeRequestData(Request $request)
    {
        return $this->projectService->normalizeRequestData($request);
    }

    /**
     * التحقق من جميع مكونات المشروع
     */
    protected function validateProjectComponents($validator, Request $request)
    {
        if ($request->input('status') === 'draft') {
            return;
        }

        $this->validateRisksOnRequest($validator, $request);
        $this->validateSupervisingAuthoritiesOnRequest($validator, $request);
        $this->validateImplementingEntitiesOnRequest($validator, $request);
        $this->validateParticipatingEntitiesOnRequest($validator, $request);
        $this->validateBeneficiaryEntitiesOnRequest($validator, $request);
        $this->validatePreliminaryActivitiesOnRequest($validator, $request);
        $this->validateExecutiveActivitiesOnRequest($validator, $request);
        $this->validateFinancingsOnRequest($validator, $request);
    }

    /**
     * معالجة استجابة النجاح
     */
    protected function handleSuccessResponse(Request $request, Project $project, string $message)
    {
        $redirectUrl = $request->input('redirect_to', route('projects.index'));

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'message' => $message,
                'project_id' => $project->id,
                'status' => $project->status,
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', $message);
    }

    /**
     * معالجة أخطاء التحقق
     */
    protected function handleValidationError(Request $request, ValidationException $e)
    {
        $errorMessages = [];
        foreach ($e->errors() as $field => $errors) {
            foreach ($errors as $error) {
                $errorMessages[] = "$field: $error";
            }
        }

        Log::error('Validation errors: '.implode(', ', $errorMessages), [
            'errors' => $e->errors(),
            'request_data' => $request->except(['_token']),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Data validation failed: '.implode(', ', $errorMessages),
                'errors' => $e->errors(),
            ], 422);
        }

        return back()->withErrors($e->errors())->withInput();
    }

    /**
     * معالجة الاستثناءات العامة
     */
    protected function handleException(Request $request, \Exception $e)
    {
        // Check if this is a SQL integrity constraint violation
        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1] ?? null;

            // 1048 = Column cannot be null
            if ($errorCode === 1048) {
                $message = $this->parseIntegrityViolationMessage($e->getMessage());

                Log::error('SQL Integrity Constraint Violation', [
                    'error_code' => $errorCode,
                    'original_message' => $e->getMessage(),
                    'user_message' => $message,
                    'request_data' => $request->except('_token'),
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return back()->withInput()->with('error', $message);
            }
        }

        Log::error('Unexpected error: '.$e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: '.$e->getMessage(),
            ], 500);
        }

        return back()->withInput()->with('error', 'An unexpected error occurred: '.$e->getMessage());
    }

    /**
     * Parse SQL integrity violation message to user-friendly Arabic message
     */
    private function parseIntegrityViolationMessage(string $sqlError): string
    {
        // Extract column name from error message
        if (preg_match("/Column '(\w+)' cannot be null/", $sqlError, $matches)) {
            $columnName = $matches[1];

            // Map database column names to user-friendly Arabic field names
            $fieldNames = [
                'measurement_unit' => 'وحدة القياس',
                'objective' => 'الهدف',
                'objective_weight' => 'وزن الهدف',
                'target_value' => 'القيمة المستهدفة',
                'result_name' => 'اسم النتيجة',
                'output' => 'المخرج',
                'indicator_unit' => 'وحدة المؤشر',
            ];

            $arabicFieldName = $fieldNames[$columnName] ?? $columnName;

            // Try to extract table name for more context
            $tableName = '';
            if (preg_match("/insert into `(\w+)`/", $sqlError, $tableMatches)) {
                $tableName = $tableMatches[1];

                $tableNames = [
                    'special_objectives' => 'الأهداف الخاصة',
                    'objective_results' => 'نتائج الأهداف',
                    'result_outputs' => 'مخرجات النتائج',
                    'main_objectives' => 'الأهداف الرئيسية',
                ];

                $arabicTableName = $tableNames[$tableName] ?? '';

                if ($arabicTableName) {
                    return "فشل الحفظ: الحقل '{$arabicFieldName}' مطلوب في {$arabicTableName} ولا يمكن أن يكون فارغاً. يرجى التأكد من ملء جميع الحقول المطلوبة.";
                }
            }

            return "فشل الحفظ: الحقل '{$arabicFieldName}' مطلوب ولا يمكن أن يكون فارغاً. يرجى التأكد من ملء جميع الحقول المطلوبة.";
        }

        // Generic message if we can't parse the specific column
        return 'فشل الحفظ: يرجى التأكد من ملء جميع الحقول المطلوبة في النموذج.';
    }

    /**
     * معالجة حذف المشروع
     */
    protected function handleProjectDestroy(Project $project)
    {
        try {
            $this->projectService->deleteProject($project);

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully!');
        } catch (\Exception $e) {
            Log::channel('projects')->error('Failed to delete project: '.$e->getMessage(), [
                'project_id' => $project->id ?? 'unknown',
                'project_name' => $project->project_name ?? 'unknown',
                'form_number' => $project->form_number ?? 'unknown',
                'exception' => $e,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => request()->ip(),
            ]);

            return back()->with('error', 'An error occurred while deleting the project: '.$e->getMessage());
        }
    }

    /**
     * معالجة استعراض المشروع
     */
    protected function handleProjectReview(Project $project)
    {
        try {
            if ($project->status !== 'draft') {
                return redirect()->route('projects.index')
                    ->with('error', 'Only draft projects can be reviewed');
            }

            $projectData = $this->projectService->reviewProject($project);

            return view('projects.review', ['project' => $projectData]);

        } catch (\Exception $e) {
            Log::error('Failed to display project review page: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('projects.index')
                ->with('error', 'An error occurred while displaying the review page: '.$e->getMessage());
        }
    }

    /**
     * معالجة تحويل المشروع إلى حالة نهائية
     */
    protected function handleProjectFinalize(Request $request, Project $project)
    {
        try {
            $this->projectService->finalizeProject($request, $project);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project saved as final successfully!',
                    'project_id' => $project->id,
                    'status' => $project->status,
                    'redirect_url' => route('projects.show', $project->id),
                ]);
            }

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Project saved as final successfully!');

        } catch (\Exception $e) {
            Log::channel('projects')->error('Failed to convert project to final: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
                'user_id' => auth()->id() ?? 'guest',
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred during final save: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'An error occurred during final save: '.$e->getMessage());
        }
    }

    /**
     * معالجة الحصول على المخاطر
     */
    protected function handleGetRisks(Project $project, Request $request)
    {
        try {
            $result = $this->projectService->getProjectRisks($project, $request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return view('projects.risks.index', array_merge(['project' => $project], $result));

        } catch (\Exception $e) {
            Log::error('Failed to fetch project risks', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to fetch project risks',
                ], 500);
            }

            return back()->with('error', 'Failed to fetch project risks');
        }
    }

    /**
     * معالجة تحليل المخاطر
     */
    protected function handleAnalyzeRisks(Project $project, Request $request)
    {
        try {
            $result = $this->projectService->analyzeProjectRisks($project, $request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return view('projects.risks.analysis', array_merge(['project' => $project], $result));

        } catch (\Exception $e) {
            Log::error('Failed to analyze project risks', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to analyze project risks',
                ], 500);
            }

            return back()->with('error', 'Failed to analyze project risks');
        }
    }

    /**
     * معالجة حذف المخاطر
     */
    protected function handleDeleteRisk(Project $project, $riskId, Request $request)
    {
        try {
            $result = $this->projectService->deleteProjectRisk($project, $riskId, $request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Failed to delete risk', [
                'project_id' => $project->id,
                'risk_id' => $riskId,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete risk',
                ], 500);
            }

            return back()->with('error', 'Failed to delete risk');
        }
    }

    /**
     * معالجة الحصول على التلخيصات المالية
     */
    protected function handleGetFinancialSummary(Project $project, string $type = 'preliminary')
    {
        try {
            if ($type === 'preliminary') {
                $summary = $this->projectService->getPreliminaryFinancialSummary($project);
            } else {
                $summary = $this->projectService->getExecutiveFinancialSummary($project);
            }

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get financial summary: '.$e->getMessage(), [
                'project_id' => $project->id,
                'type' => $type,
                'error' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get financial summary',
            ], 500);
        }
    }

    /**
     * معالجة الحصول على بيانات التمويل
     */
    protected function handleGetFinancingsSummary(Project $project)
    {
        try {
            $summary = $this->projectService->getFinancingsSummary($project);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get financings summary: '.$e->getMessage(), [
                'project_id' => $project->id,
                'error' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get financings summary',
            ], 500);
        }
    }

    /**
     * معالجة الحصول على التلخيصات المالية الأولية
     */
    protected function handleGetPreliminaryFinancialSummaries(Project $project)
    {
        try {
            $summaries = $this->projectService->getPreliminaryFinancialSummaries($project);

            return response()->json([
                'success' => true,
                'data' => $summaries,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get preliminary financial summaries: '.$e->getMessage(), [
                'project_id' => $project->id,
                'error' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get financial summaries',
            ], 500);
        }
    }

    /**
     * التحقق من صحة البيانات قبل الحفظ النهائي
     */
    protected function validateBeforeFinalSave(Project $project, Request $request)
    {
        $errors = [];

        // التحقق من البيانات الأساسية
        if (empty($project->project_name)) {
            $errors[] = 'Project name is required';
        }

        if (empty($project->program_id)) {
            $errors[] = 'Program is required';
        }

        if (empty($project->domain_id)) {
            $errors[] = 'Domain is required';
        }

        // التحقق من المكونات
        if ($project->supervisingAuthorities->isEmpty()) {
            $errors[] = 'At least one supervising authority is required';
        }

        if ($project->implementingEntities->isEmpty()) {
            $errors[] = 'At least one implementing entity is required';
        }

        if ($project->financings->isEmpty()) {
            $errors[] = 'At least one financing source is required';
        }

        // التحقق من التكاليف
        $totalCost = $project->cost->total_cost ?? 0;
        $totalFinancing = $project->financings->sum('financing_amount');

        if ($totalCost > 0 && abs($totalCost - $totalFinancing) > 0.01) {
            $errors[] = "Total financing amount ({$totalFinancing}) must match project total cost ({$totalCost})";
        }

        return $errors;
    }

    /**
     * إنشاء نسخة احتياطية من بيانات المشروع
     */
    protected function createProjectBackup(Project $project)
    {
        try {
            $backupData = [
                'project' => $project->toArray(),
                'details' => $project->detail ? $project->detail->toArray() : null,
                'locations' => $project->locations->toArray(),
                'objectives' => [
                    'main' => $project->mainObjectives->toArray(),
                    'special' => $project->specialObjectives->toArray(),
                ],
                'risks' => $project->risks->toArray(),
                'cost' => $project->cost ? $project->cost->toArray() : null,
                'financings' => $project->financings->toArray(),
                'entities' => [
                    'supervising' => $project->supervisingAuthorities->toArray(),
                    'implementing' => $project->implementingEntities->toArray(),
                    'participating' => $project->participatingEntities->toArray(),
                    'beneficiary' => $project->beneficiaryEntities->toArray(),
                ],
                'activities' => [
                    'preliminary' => $project->preliminaryActivities->toArray(),
                    'executive' => $project->executiveActivities->toArray(),
                ],
                'timestamp' => now()->toISOString(),
                'user_id' => auth()->id() ?? 'system',
            ];

            // حفظ النسخة الاحتياطية في نظام الملفات أو قاعدة البيانات
            $backupFileName = 'project_backup_'.$project->id.'_'.now()->format('Y-m-d_H-i-s').'.json';
            $backupPath = storage_path('app/backups/'.$backupFileName);

            // التأكد من وجود المجلد
            if (! file_exists(dirname($backupPath))) {
                mkdir(dirname($backupPath), 0755, true);
            }

            file_put_contents($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));

            Log::info('Project backup created', [
                'project_id' => $project->id,
                'backup_file' => $backupFileName,
            ]);

            return $backupFileName;

        } catch (\Exception $e) {
            Log::error('Failed to create project backup', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * استعادة المشروع من النسخة الاحتياطية
     */
    protected function restoreProjectFromBackup(Project $project, string $backupFileName)
    {
        DB::beginTransaction();
        try {
            $backupPath = storage_path('app/backups/'.$backupFileName);

            if (! file_exists($backupPath)) {
                throw new \Exception('Backup file not found');
            }

            $backupData = json_decode(file_get_contents($backupPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid backup file format');
            }

            // استعادة البيانات الأساسية
            $project->update($backupData['project']);

            // استعادة المكونات الأخرى
            // (سيتم تنفيذ هذا بناءً على هيكل النسخة الاحتياطية)

            DB::commit();

            Log::info('Project restored from backup', [
                'project_id' => $project->id,
                'backup_file' => $backupFileName,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore project from backup', [
                'project_id' => $project->id,
                'backup_file' => $backupFileName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create the initial approval stage when a project is first created
     */
    protected function createInitialAssemblyApprovalStage(Project $project, array $validated)
    {
        try {
            // Use the dynamic approval stages to determine the first stage
            $approvalStages = $this->projectService->getApprovalStages($project);

            if (empty($approvalStages)) {
                Log::warning('No dynamic approval stages found for project creation, falling back to manual detection', ['project_id' => $project->id]);

                // Fallback to manual detection if dynamic fails
                $authorityId = null;
                if (isset($validated['supervising_entities']) && is_array($validated['supervising_entities'])) {
                    foreach ($validated['supervising_entities'] as $supervisingEntity) {
                        if (isset($supervisingEntity['authority_id'])) {
                            $authorityId = $supervisingEntity['authority_id'];
                            break;
                        }
                    }
                }

                if (! $authorityId) {
                    $authority = Authority::first();
                    $authorityId = $authority ? $authority->id : 1;
                }

                ProjectApproval::create([
                    'project_id' => $project->id,
                    'authority_id' => $authorityId,
                    'drop' => 'assembly', // Legacy fallback
                    'step_order' => 1,
                    'status' => 'pending',
                    'notes' => $validated['assembly_approval_notes'] ?? 'بداية عملية الاعتماد',
                    'created_by' => auth()->id(),
                ]);

                return;
            }

            // Get the first stage from the dynamic chain
            $firstStageData = $approvalStages[0];

            // Ensure the Stage model exists for this code
            $stage = Stage::where('code', $firstStageData['drop'])->first();
            if (! $stage) {
                $stage = Stage::create([
                    'code' => $firstStageData['drop'],
                    'name_ar' => $firstStageData['stage_name'],
                    'name_en' => $firstStageData['stage_name_en'] ?? $firstStageData['stage_name'],
                    'order' => $firstStageData['drop_order'],
                    'type' => 'approval',
                    'is_active' => true,
                    'is_system' => true,
                ]);
            }

            ProjectApproval::create([
                'project_id' => $project->id,
                'authority_id' => $firstStageData['authority_id'] ?? $firstStageData['entity_id'],
                'drop' => $firstStageData['drop'],
                'stage_id' => $stage->id,
                'step_order' => $firstStageData['drop_order'],
                'status' => 'pending',
                'notes' => $validated['assembly_approval_notes'] ?? 'بداية عملية الاعتماد',
                'created_by' => auth()->id(),
            ]);

            // Update project with initial stage info
            $project->update([
                'current_approval_stage_id' => $stage->id,
                'current_stage_id' => $stage->id,
                'current_stage_name' => $stage->name_ar,
                'current_stage_order' => $stage->order,
            ]);

            Log::info('Initial dynamic approval stage created', [
                'project_id' => $project->id,
                'stage_code' => $firstStageData['drop'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create initial approval stage', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية للمشروع
     */
    protected function getProjectBackups(Project $project)
    {
        try {
            $backupDir = storage_path('app/backups');
            $backups = [];

            if (file_exists($backupDir)) {
                $files = glob($backupDir.'/project_backup_'.$project->id.'_*.json');

                foreach ($files as $file) {
                    $backups[] = [
                        'filename' => basename($file),
                        'size' => filesize($file),
                        'modified' => filemtime($file),
                        'path' => $file,
                    ];
                }

                // ترتيب النسخ الاحتياطية من الأحدث إلى الأقدم
                usort($backups, function ($a, $b) {
                    return $b['modified'] - $a['modified'];
                });
            }

            return $backups;

        } catch (\Exception $e) {
            Log::error('Failed to get project backups', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
