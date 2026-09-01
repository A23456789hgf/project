<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Services\FrappeAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FrappeDataController extends Controller
{
    protected FrappeAPIService $frappeService;

    public function __construct(FrappeAPIService $frappeService)
    {
        $this->frappeService = $frappeService;
    }

    /**
     * إنشاء مشروع جديد كمسودة (draft) مع إرساله إلى ERPNext
     *
     * المنطق:
     * 1. يُحدَّد نوع الجهة المقدِّمة للمشروع من حقل entity_type في internal_entities.
     * 2. إذا كانت Company  → يتم البحث عنها في ERPNext ضمن Company، وإنشاؤها إن لم توجد.
     * 3. إذا كانت Department → يتم البحث عنها في ERPNext ضمن Department، وإنشاؤها إن لم توجد.
     * 4. يُرسَل المشروع مرتبطاً بالجهة الصحيحة في ERPNext.
     */
    public function createProject(Request $request)
    {
        // ── التحقق من صحة البيانات ───────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'entity_name' => 'nullable|string|max:255',  // اسم الجهة المقدِّمة
            'program_id' => 'nullable|exists:programs,id',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomain_id' => 'nullable|exists:subdomains,id',
            'intervention_id' => 'nullable|exists:interventions,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for createProject API', ['errors' => $validator->errors()->toArray()]);

            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ── تحديد الجهة المقدِّمة للمشروع وإعدادها في ERPNext ───────────────────
        $entityInfo = $this->resolveAndEnsureEntityInErpNext($request->input('entity_name'));

        // ── إنشاء المشروع في قاعدة البيانات المحلية ────────────────────────────
        try {
            DB::beginTransaction();

            $project = new Project;
            $project->project_name = $request->input('project_name');
            $project->program_id = $request->input('program_id');
            $project->domain_id = $request->input('domain_id');
            $project->subdomain_id = $request->input('subdomain_id');
            $project->intervention_id = $request->input('intervention_id');
            $project->status = 'draft';
            $project->created_by = auth()->id();

            // ربط الجهة المقدِّمة بالمشروع إذا كانت موجودة في قاعدة البيانات المحلية
            if ($entityInfo['local_entity']) {
                $project->creator_entity_id = $entityInfo['local_entity']->id;
            }

            $project->save();

            // تفاصيل المشروع (اختياري)
            if ($request->hasAny(['start_date', 'end_date', 'description', 'notes'])) {
                $detail = new ProjectDetail;
                $detail->project_id = $project->id;
                $detail->start_date = $request->input('start_date');
                $detail->end_date = $request->input('end_date');
                $detail->description = $request->input('description');
                $detail->notes = $request->input('notes');
                $detail->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving project locally: '.$e->getMessage(), [
                'project_name' => $request->input('project_name'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء حفظ المشروع في قاعدة البيانات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        // ── إرسال المشروع إلى ERPNext ───────────────────────────────────────────
        $syncResult = null;
        $syncSuccess = false;

        try {
            // تحديث علاقات المشروع قبل الإرسال
            $project->refresh();
            $project->loadMissing(['creatorEntity', 'cost', 'detail']);

            $syncResult = $this->frappeService->postProjectToFrappe($project);

            if ($syncResult['success'] ?? false) {
                $syncSuccess = true;
                $project->erpnext_project_id = $syncResult['frappe_id'];
                $project->save();
            }

            Log::info('Project created and synced to ERPNext', [
                'project_id' => $project->id,
                'entity_type' => $entityInfo['entity_type'],
                'entity_name' => $entityInfo['entity_name'],
                'frappe_synced' => $syncSuccess,
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing project to ERPNext: '.$e->getMessage(), [
                'project_id' => $project->id,
            ]);
            // لا نُوقف العملية — المشروع تم إنشاؤه محلياً بنجاح
        }

        // ── إعداد الاستجابة ────────────────────────────────────────────────────
        return response()->json([
            'status' => 'success',
            'message' => $syncSuccess
                ? 'تم إنشاء المشروع وإرساله إلى ERPNext بنجاح'
                : 'تم إنشاء المشروع محلياً، لكن فشل الإرسال إلى ERPNext',
            'project' => [
                'id' => $project->id,
                'name' => $project->project_name,
                'status' => $project->status,
                'created_at' => $project->created_at->format('Y-m-d H:i:s'),
                'erpnext_id' => $project->erpnext_project_id,
            ],
            'entity' => [
                'name' => $entityInfo['entity_name'],
                'type' => $entityInfo['entity_type'],       // Company | Department | null
                'erpnext_found' => $entityInfo['erpnext_found'],     // true إذا كانت موجودة مسبقاً
                'erpnext_created' => $entityInfo['erpnext_created'],  // true إذا تم إنشاؤها
            ],
            'sync_result' => $syncResult,
        ], 201);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    // (Moved ERPNext entity resolution to FrappeAPIService)

    /**
     * POST/Sync procedures of a project to Frappe API (http://172.16.10.239:8856/api/resource/Procedure)
     */
    public function syncProjectProcedures(Project $project)
    {
        try {
            $frappeProjectId = $project->erpnext_project_id ?: $this->frappeService->findProjectByName($project->project_name);

            if (! $frappeProjectId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لم يتم العثور على معرف المشروع في ERPNext/Frappe',
                ], 404);
            }

            $result = $this->frappeService->syncProjectProcedures($project, $frappeProjectId);

            if ($result['success'] ?? false) {
                Log::info('Successfully synced project procedures to Frappe', ['project_id' => $project->id, 'frappe_id' => $frappeProjectId]);
            } else {
                Log::warning('Failed or partially failed to sync project procedures to Frappe', ['project_id' => $project->id, 'result' => $result]);
            }

            return response()->json([
                'status' => $result['success'] ? 'success' : 'partial_failure',
                'message' => $result['message'],
                'data' => $result,
            ], $result['success'] ? 200 : 207);

        } catch (\Exception $e) {
            Log::error('Error syncing procedures to Frappe: '.$e->getMessage(), [
                'project_id' => $project->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء مزامنة الإجراءات مع Frappe: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get financial items (Expense Claim Types) from ERPNext dynamically
     * Based on the Originating Entity of the user.
     * Ensures the entity exists in ERPNext before fetching.
     */
    public function getFinancialItems(Request $request)
    {
        $entityId = $request->input('entity_id') ?? auth()->user()?->entity_id ?? auth()->user()?->creator_entity_id;

        if (! $entityId) {
            return response()->json(['results' => []]);
        }

        $entity = InternalEntity::find($entityId);

        if (! $entity) {
            return response()->json(['results' => []]);
        }

        // 1. Ensure entity exists in ERPNext (Creates if not exists, including parent company)
        $ensureResult = $this->resolveAndEnsureEntityInErpNext($entity->name);

        if (! $ensureResult['erpnext_found'] && ! $ensureResult['erpnext_created']) {
            Log::warning('Could not ensure entity in ERPNext for fetching financial items', ['entity_id' => $entityId]);
            // User requirement: don't throw an error to the user if this fails, just return empty or continue.
            // But actually we need the company name to fetch items. If it failed to create, we might still try to fetch.
        }

        // 2. Determine the Company for the entity
        $companyName = null;
        if ($entity->entity_type === 'Company') {
            $companyName = $entity->name;
        } else {
            $companyName = $this->findParentCompanyName($entity);
        }

        // 3. Fetch Expense Claim Types
        $items = $this->frappeService->getExpenseClaimTypes($companyName);

        if (empty($items)) {
            Log::warning('No financial items (Expense Claim Types) found in ERPNext', ['entity_id' => $entityId, 'company_name' => $companyName]);

            return response()->json([
                'results' => [
                    [
                        'id' => '',
                        'text' => 'لا توجد بنود مالية متوفرة لهذه الجهة في ERPNext',
                        'disabled' => true,
                    ],
                ],
            ]);
        }

        // Format for Select2
        $formatted = [];
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['name'], // Using name as ID because ERPNext often uses name as primary key
                'text' => $item['expense_claim_type'] ?? $item['name'],
            ];
        }

        Log::info('Successfully fetched financial items from ERPNext', ['entity_id' => $entityId, 'company_name' => $companyName, 'count' => count($formatted)]);

        return response()->json([
            'results' => $formatted,
        ]);
    }

    /**
     * التحقق من وجود الجهة في ERPNext وإنشاؤها إن لم توجد، ثم جلب البنود المالية (Expense Claim Types).
     *
     * يُستخدم هذا الـ endpoint عند الانتقال للخطوة 4 (التمهيدية) أو 5 (التنفيذية)
     * في نموذج إنشاء المشروع — وذلك قبل حفظ المشروع في قاعدة البيانات.
     *
     * المنطق:
     * 1. حدد الجهة المنشئة من المستخدم الحالي (أو من entity_id في الطلب)
     * 2. تحقق من وجودها في ERPNext (Company أو Department) — أنشئها إن لم توجد
     * 3. اجلب Expense Claim Types من ERPNext وأرجعها منسقة لـ Select2
     */
    public function verifyEntityAndGetFinancialItems(Request $request)
    {
        // ── 1. تحديد الجهة ──────────────────────────────────────────────────────
        $entityId = $request->input('entity_id')
            ?? auth()->user()?->entity_id
            ?? auth()->user()?->creator_entity_id;

        if (! $entityId) {
            // لا توجد جهة مرتبطة بالمستخدم — أرجع قائمة فارغة مع رسالة توضيحية
            return response()->json([
                'success' => false,
                'entity_found' => false,
                'entity_created' => false,
                'entity_type' => null,
                'entity_name' => null,
                'message' => 'لا توجد جهة مرتبطة بالمستخدم الحالي',
                'items' => [],
            ]);
        }

        $entity = InternalEntity::find($entityId);

        if (! $entity) {
            return response()->json([
                'success' => false,
                'entity_found' => false,
                'entity_created' => false,
                'entity_type' => null,
                'entity_name' => null,
                'message' => 'الجهة غير موجودة في قاعدة البيانات المحلية (ID: '.$entityId.')',
                'items' => [],
            ]);
        }

        // ── 2. التحقق من الجهة في ERPNext وإنشاؤها إن لم توجد ─────────────────
        $ensureResult = $this->resolveAndEnsureEntityInErpNext($entity->name);

        $entityFound = $ensureResult['erpnext_found'];
        $entityCreated = $ensureResult['erpnext_created'];

        Log::info('verifyEntityAndGetFinancialItems: Entity verification result', [
            'entity_id' => $entityId,
            'entity_name' => $entity->name,
            'entity_type' => $entity->entity_type,
            'erpnext_found' => $entityFound,
            'erpnext_created' => $entityCreated,
        ]);

        // ── 3. تحديد اسم الشركة لجلب البنود المالية ────────────────────────────
        $companyName = null;
        if ($entity->entity_type === 'Company') {
            $companyName = $entity->name;
        } else {
            $companyName = $this->findParentCompanyName($entity);
        }

        // ── 4. جلب Expense Claim Types من ERPNext ──────────────────────────────
        $rawItems = $this->frappeService->getExpenseClaimTypes($companyName);

        if (empty($rawItems)) {
            Log::warning('verifyEntityAndGetFinancialItems: No Expense Claim Types returned', [
                'entity_name' => $entity->name,
                'company_name' => $companyName,
            ]);

            return response()->json([
                'success' => true,
                'entity_found' => $entityFound,
                'entity_created' => $entityCreated,
                'entity_type' => $entity->entity_type,
                'entity_name' => $entity->name,
                'company_name' => $companyName,
                'message' => 'لا توجد بنود مالية (Expense Claim Types) لهذه الجهة في ERPNext',
                'items' => [],
            ]);
        }

        // ── 5. تنسيق البنود لـ Select2 ─────────────────────────────────────────
        $formatted = [];
        foreach ($rawItems as $item) {
            $formatted[] = [
                'id' => $item['name'],                                        // ERPNext name كـ ID
                'text' => $item['expense_claim_type'] ?? $item['name'],         // النص المعروض
                'name' => $item['expense_claim_type'] ?? $item['name'],         // نسخة احتياطية
            ];
        }

        Log::info('verifyEntityAndGetFinancialItems: Successfully fetched financial items', [
            'entity_name' => $entity->name,
            'company_name' => $companyName,
            'items_count' => count($formatted),
        ]);

        return response()->json([
            'success' => true,
            'entity_found' => $entityFound,
            'entity_created' => $entityCreated,
            'entity_type' => $entity->entity_type,
            'entity_name' => $entity->name,
            'company_name' => $companyName,
            'message' => $entityCreated
                ? 'تم إنشاء الجهة في ERPNext وجلب البنود المالية بنجاح'
                : 'تم التحقق من الجهة وجلب البنود المالية بنجاح',
            'items' => $formatted,
        ]);
    }
}
