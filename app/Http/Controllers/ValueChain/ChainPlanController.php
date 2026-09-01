<?php

namespace App\Http\Controllers\ValueChain;

use App\Exports\ChainPlanExport;
use App\Exports\ChainPlanTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ChainPlanImport;
use App\Models\Authority;
use App\Models\ChainPlan;
use App\Models\Directorate;
use App\Models\Domain;
use App\Models\Governorate;
use App\Models\InternalEntity;
use App\Models\ValueChain;
use App\Models\ValueChainFinancingType;
use App\Services\ImportTrackingService;
use Flasher\Laravel\Facade\Flasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ChainPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:chain_plans.view')->only(['index', 'show']);
        $this->middleware('can:chain_plans.create')->only(['create', 'store']);
        $this->middleware('can:chain_plans.edit')->only(['edit', 'update']);
        $this->middleware('can:chain_plans.delete')->only(['destroy']);
        $this->middleware('can:chain_plans.import')->only(['showImportForm', 'previewImport', 'processImport', 'downloadTemplate']);
        $this->middleware('can:chain_plans.export')->only(['export']);
        $this->middleware('can:chain_plans.batch-print')->only(['batchPrint']);
    }

    /**
     * قائمة الحقول المتاحة للاستيراد مع تسمياتها.
     * تستخدم في واجهة المطابقة.
     */
    protected function getAvailableFields(): array
    {
        return [
            'governorate_id' => 'المحافظة',
            'directorate_id' => 'المديرية',
            'value_chain_id' => 'السلسلة',
            'domain_id' => 'المجال',
            'project_name' => 'اسم المشروع',
            'activity_name' => 'اسم النشاط',
            'indicator' => 'المؤشر',
            'number' => 'العدد',
            'value_chain_financing_type_id' => 'نوع التمويل',
            'funding_source_id' => 'جهة التمويل',
            'authority_id' => 'الجهة المنفذة',
            'implementing_entity_id' => 'الجهة المشرفة',
        ];
    }

    /**
     * عرض قائمة خطط السلاسل.
     */
    public function index(Request $request)
    {
        $title = 'خطط سلسلة القيمة';
        $icon = 'list-alt';

        $query = ChainPlan::with([
            'governorate',
            'directorate',
            'valueChain',
            'domain',
            'financingType',
            'fundingSource',
            'implementingEntity',
        ]);

        // 🔍 البحث
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('indicator', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'governorate',
                        fn ($q) => $q->where('name', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'valueChain',
                        fn ($q) => $q->where('name', 'like', "%{$search}%")
                    );
            });
        }

        // 🧭 فلترة حسب السلسلة
        if ($request->filled('value_chain_id')) {
            $query->where('value_chain_id', $request->value_chain_id);
        }

        // 🏛️ فلترة حسب المحافظة
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // 🏙️ فلترة حسب المديرية
        if ($request->filled('directorate_id')) {
            $query->where('directorate_id', $request->directorate_id);
        }

        // 🔃 الترتيب
        $allowedSorts = [
            'id',
            'indicator',
            'number',
            'created_at',
            'governorate_id',
            'directorate_id',
            'value_chain_id',
        ];

        $sortField = $request->get('sort');

        $sortDir = in_array($request->get('direction'), ['asc', 'desc'])
            ? $request->get('direction')
            : 'asc';

        if ($sortField && in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->latest();
        }

        // 📄 عدد السجلات في الصفحة
        $perPage = in_array($request->get('per_page'), [20, 50, 100, 500])
            ? (int) $request->get('per_page')
            : 20;

        $chainPlans = $query->paginate($perPage)->withQueryString();

        // القوائم المستخدمة في الفلاتر
        $governorates = Governorate::where('is_active', true)
            ->orderBy('name')
            ->get();

        $valueChains = ValueChain::orderBy('name')->get();

        // تحميل المديريات عند اختيار محافظة
        $directorates = collect();

        if ($request->filled('governorate_id')) {
            $directorates = Directorate::where(
                'governorate_id',
                $request->governorate_id
            )
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('value_chains.chain_plans.index', compact(
            'chainPlans',
            'title',
            'icon',
            'governorates',
            'valueChains',
            'directorates'
        ));
    }

    public function getDirectoratesByGovernorate($governorateId)
    {
        return Directorate::where('governorate_id', $governorateId)
            ->select('id', 'name')
            ->get();
    }

    /**
     * عرض نموذج إنشاء خطة سلسلة جديدة.
     */
    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $valueChains = ValueChain::orderBy('name')->get();
        $domains = Domain::where('is_active', true)->orderBy('name')->get();
        $financingTypes = ValueChainFinancingType::orderBy('name')->get();
        $authorities = Authority::where('is_active', true)->orderBy('agency_name')->get();

        return view('value_chains.chain_plans.create', compact('governorates', 'valueChains', 'domains', 'financingTypes', 'authorities'));
    }

    /**
     * تخزين خطة سلسلة جديدة.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'governorate_id' => 'nullable|exists:governorates,id',
            'directorate_id' => 'nullable|exists:directorates,id',

            'value_chain_id' => 'nullable|string|max:255',
            'domain_id' => 'nullable|string|max:255',

            'project_name' => 'nullable|string|max:255',
            'activity_name' => 'nullable|string|max:255',

            'indicator' => 'required|string|max:255',
            'number' => 'nullable|string|max:255',

            'value_chain_financing_type_id' => 'nullable|string|max:255',
            'funding_source_id' => 'nullable|string|max:255',

            'authority_id' => 'nullable|string|max:255',
            'implementing_entity_id' => 'nullable|string|max:255',
        ]);

        ChainPlan::create($validated);

        Flasher::addSuccess('تمت إضافة خطة السلسلة بنجاح');

        return redirect()->route('chain_plans.index');
    }

    /**
     * عرض نموذج تعديل خطة سلسلة.
     */
    public function edit(ChainPlan $chainPlan)
    {
        $governorates = Governorate::where('is_active', true)
            ->orderBy('name')
            ->get();

        $directorates = Directorate::where('governorate_id', $chainPlan->governorate_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $valueChains = ValueChain::orderBy('name')->get();
        $domains = Domain::where('is_active', true)->orderBy('name')->get();
        $financingTypes = ValueChainFinancingType::orderBy('name')->get();
        $authorities = Authority::where('is_active', true)->orderBy('agency_name')->get();

        return view(
            'value_chains.chain_plans.edit',
            compact(
                'chainPlan',
                'governorates',
                'directorates',
                'valueChains',
                'domains',
                'financingTypes',
                'authorities'
            )
        );
    }

    /**
     * تحديث خطة سلسلة.
     */
    public function update(Request $request, ChainPlan $chainPlan)
    {
        $validated = $request->validate([
            'governorate_id' => 'nullable|exists:governorates,id',
            'directorate_id' => 'nullable|exists:directorates,id',

            'value_chain_id' => 'nullable|string|max:255',
            'domain_id' => 'nullable|string|max:255',

            'project_name' => 'nullable|string|max:255',
            'activity_name' => 'nullable|string|max:255',

            'indicator' => 'required|string|max:255',
            'number' => 'nullable|string|max:255',

            'value_chain_financing_type_id' => 'nullable|string|max:255',
            'funding_source_id' => 'nullable|string|max:255',

            'authority_id' => 'nullable|string|max:255',
            'implementing_entity_id' => 'nullable|string|max:255',
        ]);

        $chainPlan->update($validated);

        Flasher::addSuccess('تم تحديث خطة السلسلة بنجاح');

        return redirect()->route('chain_plans.index');
    }

    /**
     * عرض تفاصيل خطة السلسلة.
     */
    public function show(ChainPlan $chainPlan)
    {
        $chainPlan->load([
            'governorate',
            'directorate',
            'valueChain',
            'domain',
            'financingType',
            'fundingSource',
            'fundingEntity',
            'authority',
            'implementingEntity',
        ]);

        return view('value_chains.chain_plans.show', compact('chainPlan'));
    }

    /**
     * حذف خطة سلسلة.
     */
    public function destroy(ChainPlan $chainPlan)
    {
        $chainPlan->delete();
        Flasher::addSuccess('تم حذف خطة السلسلة بنجاح');

        return redirect()->route('chain_plans.index');
    }

    /**
     * طباعة خطة سلسلة واحدة.
     */
    public function print(ChainPlan $chainPlan)
    {
        $chainPlan->load(['governorate', 'directorate', 'valueChain', 'domain', 'financingType', 'fundingSource', 'implementingEntity']);
        $printDate = now()->format('Y/m/d');

        return view('value_chains.chain_plans.print', compact('chainPlan', 'printDate'));
    }

    /**
     * طباعة مجموعة من الخطط (دفعة واحدة).
     */
    public function batchPrint(Request $request)
    {
        $query = ChainPlan::with([
            'governorate',
            'directorate',
            'valueChain',
            'domain',
            'financingType',
            'fundingSource',
            'authority',
            'implementingEntity',
        ]);

        $user = auth()->user();
        $geographicScopes = $user->geographicScopes;
        $hasAdminScope = ! empty($user->administrative_scope_id);
        $hasGeoScope = $geographicScopes->isNotEmpty();

        if (! $user->isAdmin() && ! $hasGeoScope && ! $hasAdminScope) {
            $query->whereRaw('0 = 1');
        } else {
            $entityIdsByEnt = InternalEntity::getAllChildrenIds($user->administrative_scope_id);
            $entityIdsByGeo = [];
            foreach ($geographicScopes as $scope) {
                if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByGovernorate($scope->governorate_id);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                } elseif (! empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByDirectorate($scope->directorate_id);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                }
            }
            $entityIds = array_merge($entityIdsByEnt ?? [], $entityIdsByGeo ?? []);
            $entityIds = array_unique($entityIds);

            $query->where(function ($q) use ($entityIds, $geographicScopes) {
                $q->whereIn('implementing_entity_id', $entityIds)
                    ->orWhereIn('authority_id', $entityIds);

                foreach ($geographicScopes as $scope) {
                    if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                        $q->orWhere('governorate_id', $scope->governorate_id);
                    } elseif (! empty($scope->directorate_id)) {
                        $q->orWhere('directorate_id', $scope->directorate_id);
                    }
                }
            });
        }

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        } elseif ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%")
                    ->orWhere('indicator', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%");
            });
        }

        $chainPlans = $query->get();
        $printDate = now()->format('Y/m/d');

        return view(
            'value_chains.chain_plans.print_batch',
            compact('chainPlans', 'printDate')
        );
    }

    /**
     * تصدير البيانات إلى ملف إكسل.
     */
    public function export(Request $request)
    {
        $search = $request->get('search');
        $filename = 'خطط_السلاسل_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ChainPlanExport($search), $filename);
    }

    /**
     * عرض نموذج رفع ملف الاستيراد.
     */
    public function importForm()
    {
        return view('value_chains.chain_plans.import');
    }

    /**
     * تحميل قالب الاستيراد.
     */
    public function downloadTemplate(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'قالب_استيراد_خطط_السلاسل_'.now()->format('Y-m-d').'.'.$format;

        return Excel::download(new ChainPlanTemplateExport, $filename);
    }

    /**
     * معاينة ملف الاستيراد وعرض الأعمدة لاختيار المطابقة.
     * هذه الدالة تُستخدم عبر AJAX لإرجاع بيانات JSON.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            $fullPath = Storage::path($path);

            // قراءة البيانات لاستخراج رؤوس الأعمدة وأول 5 صفوف للمعاينة
            $data = Excel::toArray([], $fullPath);
            $rows = $data[0] ?? [];
            $headers = $rows[0] ?? [];
            $previewRows = array_slice($rows, 1, 5);

            // الحقول المتاحة للمطابقة
            $availableFields = $this->getAvailableFields();

            return response()->json([
                'success' => true,
                'file_path' => $path,
                'headers' => $headers,
                'available_fields' => $availableFields,
                'preview_rows' => $previewRows,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قراءة الملف: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * تنفيذ عملية الاستيراد بناءً على المطابقة التي اختارها المستخدم.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string|in:'.implode(',', array_keys($this->getAvailableFields())),
        ]);

        try {
            $filePath = $request->input('file_path');
            $fullPath = Storage::path($filePath);

            if (! file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الملف غير موجود. يرجى إعادة الرفع.',
                ], 404);
            }

            // تمرير المطابقة إلى الـ Import class
            $trackingService = new ImportTrackingService;
            $importLog = $trackingService->startImport('خطط سلاسل القيمة', ChainPlan::class, basename($filePath));

            $import = new ChainPlanImport($request->input('mapping'), $trackingService, $importLog);

            Excel::import($import, $fullPath);

            $trackingService->finishImport($importLog, clone $import->rowCount, $import->importedCount, 0, []);

            // حذف الملف المؤقت بعد الاستيراد
            Storage::delete($filePath);

            return response()->json([
                'success' => true,
                'message' => 'تم استيراد خطط السلاسل بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاستيراد: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * إرجاع قائمة الحقول المتاحة بتنسيق JSON (للاستخدام في الواجهة).
     */
    public function getFields()
    {
        return response()->json($this->getAvailableFields());
    }

    /**
     * الحصول على بيانات مفلترة (للاستخدام في الصفحة الرئيسية عبر AJAX).
     */
    public function getFilteredData(Request $request)
    {
        $query = ChainPlan::with([
            'governorate',
            'directorate',
            'valueChain',
            'domain',
            'financingType',
            'fundingSource',
            'authority',
            'implementingEntity',
        ]);

        $user = auth()->user();
        $geographicScopes = $user->geographicScopes;
        $hasAdminScope = ! empty($user->administrative_scope_id);
        $hasGeoScope = $geographicScopes->isNotEmpty();

        if (! $user->isAdmin() && ! $hasGeoScope && ! $hasAdminScope) {
            $query->whereRaw('0 = 1');
        } else {
            $entityIdsByEnt = InternalEntity::getAllChildrenIds($user->administrative_scope_id);
            $entityIdsByGeo = [];
            foreach ($geographicScopes as $scope) {
                if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByGovernorate($scope->governorate_id);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                } elseif (! empty($scope->directorate_id)) {
                    $ids = InternalEntity::getAllByDirectorate($scope->directorate_id);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                }
            }
            $entityIds = array_merge($entityIdsByEnt ?? [], $entityIdsByGeo ?? []);
            $entityIds = array_unique($entityIds);

            $query->where(function ($q) use ($entityIds, $geographicScopes) {
                $q->whereIn('implementing_entity_id', $entityIds)
                    ->orWhereIn('authority_id', $entityIds);

                foreach ($geographicScopes as $scope) {
                    if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                        $q->orWhere('governorate_id', $scope->governorate_id);
                    } elseif (! empty($scope->directorate_id)) {
                        $q->orWhere('directorate_id', $scope->directorate_id);
                    }
                }
            });
        }

        // Apply filters
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        if ($request->filled('directorate_id')) {
            $query->where('directorate_id', $request->directorate_id);
        }

        if ($request->filled('value_chain_id')) {
            $query->where('value_chain_id', $request->value_chain_id);
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('financing_type_id')) {
            $query->where('value_chain_financing_type_id', $request->financing_type_id);
        }

        if ($request->filled('funding_source_id')) {
            $query->where('funding_source_id', $request->funding_source_id);
        }

        if ($request->filled('authority_id')) {
            $query->where('authority_id', $request->authority_id);
        }

        if ($request->filled('implementing_entity_id')) {
            $query->where('implementing_entity_id', $request->implementing_entity_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%")
                    ->orWhere('indicator', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhereHas('governorate', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('directorate', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('valueChain', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('domain', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('financingType', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('fundingSource', function ($q) use ($search) {
                        $q->where('agency_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('authority', function ($q) use ($search) {
                        $q->where('agency_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('implementingEntity', function ($q) use ($search) {
                        $q->where('agency_name', 'like', "%{$search}%");
                    });
            });
        }

        $chainPlans = $query->paginate(15);

        return view('value_chains.chain_plans.partials.filtered_data', compact('chainPlans'));
    }

    /**
     * الحصول على المشاريع المرتبطة بسلسلة معينة.
     */
    public function getProjectsByChain(Request $request)
    {
        $valueChainId = $request->get('value_chain_id');

        if (! $valueChainId) {
            return response()->json(['projects' => []]);
        }

        $projects = ChainPlan::where('value_chain_id', $valueChainId)
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->distinct()
            ->orderBy('project_name')
            ->pluck('project_name');

        return response()->json([
            'projects' => $projects,
        ]);
    }

    /**
     * الحصول على الأنشطة المرتبطة بسلسلة ومشروع معين.
     */
    public function getActivitiesByProject(Request $request)
    {
        $valueChainId = $request->get('value_chain_id');
        $projectName = $request->get('project_name');

        if (! $valueChainId || ! $projectName) {
            return response()->json(['activities' => []]);
        }

        $activities = ChainPlan::where('value_chain_id', $valueChainId)
            ->where('project_name', $projectName)
            ->whereNotNull('activity_name')
            ->where('activity_name', '!=', '')
            ->distinct()
            ->orderBy('activity_name')
            ->pluck('activity_name');

        return response()->json([
            'activities' => $activities,
        ]);
    }
}
