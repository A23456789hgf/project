<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FrappeAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BudgetController extends Controller
{
    protected FrappeAPIService $frappe;

    public function __construct(FrappeAPIService $frappe)
    {
        $this->frappe = $frappe;
    }

    /**
     * جلب قائمة الموازنات من ERPNext مع تطبيق فلاتر الصلاحيات وفلاتر المستخدم.
     *
     * GET /api/budgets
     * GET /api/budgets?company=FU&project=PROJ-0003&page=1&per_page=20
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // ──────────────────────────────────────────────────────────────────
        // 1. تحديد الجهات المسموح بها للمستخدم الحالي
        // ──────────────────────────────────────────────────────────────────
        $allowedCompanies = $this->getAllowedCompanies($user);

        // ──────────────────────────────────────────────────────────────────
        // 2. التحقق من فلتر company الذي أرسله المستخدم
        //    إذا طلب جهة غير مسموح بها → 403
        // ──────────────────────────────────────────────────────────────────
        $requestedCompany = $request->query('company');
        if ($requestedCompany && ! $this->isCompanyAllowed($requestedCompany, $allowedCompanies)) {
            Log::warning('BudgetController: User attempted to access unauthorized company', [
                'user_id' => $user->id,
                'requested_company' => $requestedCompany,
                'allowed_companies' => $allowedCompanies,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى بيانات هذه الجهة.',
            ], 403);
        }

        // ──────────────────────────────────────────────────────────────────
        // 3. بناء فلاتر ERPNext
        // ──────────────────────────────────────────────────────────────────
        $filters = $this->buildFilters($request, $allowedCompanies, $requestedCompany);

        // ──────────────────────────────────────────────────────────────────
        // 4. Pagination → تحويله إلى limit_start / limit_page_length
        // ──────────────────────────────────────────────────────────────────
        $perPage = (int) $request->query('per_page', 20);
        $page = max(1, (int) $request->query('page', 1));
        $limitStart = ($page - 1) * $perPage;

        // ──────────────────────────────────────────────────────────────────
        // 5. Sorting
        // ──────────────────────────────────────────────────────────────────
        $sortBy = $request->query('sort_by', 'modified');
        $sortOrder = strtoupper($request->query('sort_order', 'DESC')) === 'ASC' ? 'asc' : 'desc';
        $orderBy = "{$sortBy} {$sortOrder}";

        // ──────────────────────────────────────────────────────────────────
        // 6. إرسال الطلب إلى FrappeAPIService مع التكييش المؤقت (60 ثانية)
        // ──────────────────────────────────────────────────────────────────
        $params = [
            'fields' => json_encode(['*']),
            'filters' => json_encode($filters),
            'limit_start' => $limitStart,
            'limit_page_length' => $perPage,
            'order_by' => $orderBy,
        ];

        $cacheKey = 'api_budgets_'.md5(json_encode($params).'_'.$user->id);
        $cachedResponse = Cache::remember($cacheKey, now()->addSeconds(60), function () use ($params, $filters) {
            $result = $this->frappe->httpGet('Budget', $params);

            if (! $result['success']) {
                return [
                    'error' => true,
                    'status' => $result['status'] ?? 502,
                ];
            }

            // جلب العدد الإجمالي للصفحات
            $countResult = $this->frappe->httpGet('Budget', [
                'filters' => json_encode($filters),
                'limit_page_length' => 0,
                'fields' => json_encode(['name']),
            ]);
            $total = count($countResult['data'] ?? []);

            return [
                'error' => false,
                'data' => $result['data'] ?? [],
                'total' => $total,
            ];
        });

        if ($cachedResponse['error']) {
            Log::error('BudgetController: Failed to fetch budgets from Frappe', [
                'user_id' => $user->id,
                'status' => $cachedResponse['status'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال بـ ERPNext أو لا توجد بيانات.',
                'status' => $cachedResponse['status'],
            ], 502);
        }

        $total = $cachedResponse['total'];

        return response()->json([
            'success' => true,
            'data' => $cachedResponse['data'],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'page' => $page,
                'pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
            ],
        ]);
    }

    /**
     * جلب تفاصيل موازنة واحدة من ERPNext مع التحقق من صلاحية الجهة.
     *
     * GET /api/budgets/{name}
     */
    public function show(Request $request, string $name)
    {
        $user = auth()->user();

        // جلب الموازنة من Frappe
        $result = $this->frappe->httpGet("Budget/{$name}");

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على الموازنة المطلوبة.',
            ], 404);
        }

        $budget = $result['data'];

        // التحقق من أن الجهة (company) ضمن الجهات المسموح بها للمستخدم
        $allowedCompanies = $this->getAllowedCompanies($user);
        $budgetCompany = $budget['company'] ?? null;

        if ($budgetCompany && ! $this->isCompanyAllowed($budgetCompany, $allowedCompanies)) {
            Log::warning('BudgetController: User attempted to view budget of unauthorized company', [
                'user_id' => $user->id,
                'budget_name' => $name,
                'budget_company' => $budgetCompany,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى هذه الموازنة.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $budget,
        ]);
    }

    /**
     * جلب الجهات (companies) المسموح بها من ERPNext بناءً على entity المستخدم.
     *
     * المنطق:
     * - المستخدم الـ Admin (full_access) → جميع الجهات (null = بلا قيود)
     * - المستخدم العادي → entity→erpnext_id أو entity→name كـ company
     *
     * @return array|null null = مسموح بالكل، array = قائمة الشركات المسموحة
     */
    protected function getAllowedCompanies($user): ?array
    {
        // Admin / Full Access → لا قيود
        $isAdmin = $user->isAdmin() || ($user->role && $user->role->full_access);
        if ($isAdmin) {
            return null; // null = جميع الجهات
        }

        $user->loadMissing('entity');
        $entity = $user->entity; // InternalEntity

        if (! $entity) {
            Log::warning('BudgetController: User has no entity assigned', ['user_id' => $user->id]);

            return []; // لا يرى شيئاً
        }

        // استخدام erpnext_id أو name كاسم الشركة في ERPNext
        // بناءً على بنية InternalEntity الموجودة فعلاً
        $companyName = $entity->erpnext_id ?: $entity->name;

        return [$companyName];
    }

    /**
     * التحقق من أن الشركة المطلوبة ضمن الجهات المسموح بها.
     */
    protected function isCompanyAllowed(string $company, ?array $allowedCompanies): bool
    {
        if ($allowedCompanies === null) {
            return true; // Admin → جميع الجهات مسموحة
        }

        foreach ($allowedCompanies as $allowed) {
            // مطابقة جزئية لمرونة أكبر
            if (str_contains(strtolower($company), strtolower($allowed))
                || str_contains(strtolower($allowed), strtolower($company))) {
                return true;
            }
        }

        return false;
    }

    /**
     * بناء فلاتر ERPNext بناءً على طلب المستخدم وصلاحياته.
     */
    protected function buildFilters(Request $request, ?array $allowedCompanies, ?string $requestedCompany): array
    {
        $filters = [];

        // ── فلتر الجهة (company) ──────────────────────────────────────────
        if ($requestedCompany) {
            $filters[] = ['company', 'like', "%{$requestedCompany}%"];
        } elseif ($allowedCompanies !== null && count($allowedCompanies) === 1) {
            // مستخدم جهة واحدة → فرض فلتر الجهة تلقائياً
            $filters[] = ['company', 'like', "%{$allowedCompanies[0]}%"];
        } elseif ($allowedCompanies !== null && count($allowedCompanies) > 1) {
            // عدة جهات → فلتر IN (نستخدم أول جهة كمثال؛ Frappe لا يدعم IN مباشرة في filters JSON)
            // الحل: استخدام OR عبر like متعددة - Frappe يدعم OR بدمج الفلاتر
            // نكتفي بأول جهة مع ملاحظة تطوير مستقبلي لـ OR filters
            $filters[] = ['company', 'in', $allowedCompanies];
        }
        // null → Admin → لا فلتر

        // ── فلاتر المستخدم الاختيارية ──────────────────────────────────────
        $filterMap = [
            'project' => ['project', 'like'],
            'account' => ['account', 'like'],
            'cost_center' => ['cost_center', 'like'],
            'budget_against' => ['budget_against', '='],
            'from_fiscal_year' => ['from_fiscal_year', '='],
            'to_fiscal_year' => ['to_fiscal_year', '='],
            'distribution_frequency' => ['distribution_frequency', '='],
            'docstatus' => ['docstatus', '='],
        ];

        foreach ($filterMap as $param => [$field, $operator]) {
            $value = $request->query($param);
            if ($value !== null && $value !== '') {
                $filters[] = [$field, $operator, $operator === 'like' ? "%{$value}%" : $value];
            }
        }

        return $filters;
    }
}
