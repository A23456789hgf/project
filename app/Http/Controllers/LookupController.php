<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryGroup;
use App\Models\Directorate;
use App\Models\Domain;
use App\Models\FinancialItem;
use App\Models\FinancingForm;
use App\Models\FinancingType;
use App\Models\FundingSource;
use App\Models\Governorate;
use App\Models\InternalEntity;
use App\Models\Intervention;
use App\Models\MainRouter;
use App\Models\Priority;
use App\Models\Program;
use App\Models\SubArea;
use App\Models\Subdomain;
use App\Models\SubFinancingForm;
use App\Models\SubRouter;
use App\Models\Unit;
use App\Models\Village;
use App\Scopes\DomainScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LookupController extends Controller
{
    /**
     * General search method for various entities
     */
    public function search(Request $request)
    {
        $type = $request->get('type');
        $search = $request->get('q');
        $limit = $request->get('limit', 20);
        // When include_pending=1, include both approved (status=1) and pending (status=0) records.
        // This flag is sent ONLY by project-module views to support draft lookup references.
        // All other callers should NOT send this flag and will continue to see approved records only.
        $includePending = (bool) $request->get('include_pending', false);

        $query = null;
        $idField = 'id';
        $textField = 'name';

        switch ($type) {

            // ========================================
            // الجهات الخارجية
            // ========================================
            // الجهات الداخلية
            // ========================================
            case 'internal_entity':

                $user = auth()->user();

                $query = InternalEntity::withoutGlobalScope('entity_display_filtering')
                    ->with('parent');

                $entityIds = [];

                // تحميل النطاقات الجغرافية للمستخدم
                $user->loadMissing('geographicScopes');

                foreach ($user->geographicScopes as $scope) {

                    if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {

                        // محافظة كاملة
                        $entityIds = array_merge(
                            $entityIds,
                            InternalEntity::getAllByGovernorate($scope->governorate_id)
                        );

                    } elseif (! empty($scope->directorate_id)) {

                        // مديرية محددة
                        $entityIds = array_merge(
                            $entityIds,
                            InternalEntity::getAllByDirectorate($scope->directorate_id)
                        );
                    }
                }

                // احتياطي: إذا كان للمستخدم governorate_id مباشرة
                if (empty($entityIds) && $user->governorate_id) {

                    $entityIds = InternalEntity::getAllByGovernorate(
                        $user->governorate_id
                    );
                }

                $entityIds = array_unique(array_filter($entityIds));

                /*
                |--------------------------------------------------------------------------
                | إضافة الآباء
                |--------------------------------------------------------------------------
                |
                | إذا كانت الجهة داخل النطاق:
                |
                | الضالع
                |   └── مكتب الصحة
                |        └── إدارة معينة
                |
                | فإن المستخدم يرى الجهة + الأب + أب الأب.
                |
                | لكن لا يتم إضافة أي جهة من محافظة أخرى.
                |
                */

                if (! empty($entityIds)) {

                    $allowedEntityIds = $entityIds;

                    // جلب الجهات المطلوبة مع parent_id
                    $entities = InternalEntity::withoutGlobalScope('entity_display_filtering')
                        ->whereIn('id', $entityIds)
                        ->get(['id', 'parent_id']);

                    $parentIds = $entities
                        ->pluck('parent_id')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    /*
                     * الصعود في شجرة الجهات حتى الوصول إلى الجذر.
                     */
                    while (! empty($parentIds)) {

                        $newParentIds = [];

                        $parents = InternalEntity::withoutGlobalScope('entity_display_filtering')
                            ->whereIn('id', $parentIds)
                            ->get(['id', 'parent_id']);

                        foreach ($parents as $parent) {

                            if (! in_array($parent->id, $allowedEntityIds, true)) {
                                $allowedEntityIds[] = $parent->id;
                            }

                            if ($parent->parent_id) {
                                $newParentIds[] = $parent->parent_id;
                            }
                        }

                        $parentIds = array_values(
                            array_unique(
                                array_diff($newParentIds, $allowedEntityIds)
                            )
                        );
                    }

                    $allowedEntityIds = array_values(
                        array_unique($allowedEntityIds)
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | تطبيق النطاق
                    |--------------------------------------------------------------------------
                    |
                    | لا نضيف هنا كل الجهات المركزية بشكل عام.
                    | الأب يدخل فقط إذا كان أبًا فعليًا لجهة تقع داخل نطاق المستخدم.
                    |
                    */

                    $query->whereIn('id', $allowedEntityIds);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | المستخدم المركزي
                    |--------------------------------------------------------------------------
                    |
                    | لا يوجد له نطاق جغرافي محدد، لذلك نسمح له بالفلترة
                    | حسب المحافظة / المديرية المختارة في الطلب.
                    |
                    */

                    $selectedGovernorateId =
                        $request->filled('governorate_id') &&
                        $request->get('governorate_id') != '0'
                            ? $request->get('governorate_id')
                            : null;

                    $selectedDirectorateId =
                        $request->filled('directorate_id') &&
                        $request->get('directorate_id') != '0'
                            ? $request->get('directorate_id')
                            : null;

                    $selectedEntityIds = [];

                    if ($selectedDirectorateId) {

                        $selectedEntityIds = InternalEntity::getAllByDirectorate(
                            $selectedDirectorateId
                        );

                    } elseif ($selectedGovernorateId) {

                        $selectedEntityIds = InternalEntity::getAllByGovernorate(
                            $selectedGovernorateId
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | إضافة الآباء للنطاق المختار
                    |--------------------------------------------------------------------------
                    */

                    if (! empty($selectedEntityIds)) {

                        $allowedEntityIds = array_values(
                            array_unique(
                                array_filter($selectedEntityIds)
                            )
                        );

                        $parentIds = InternalEntity::withoutGlobalScope(
                            'entity_display_filtering'
                        )
                            ->whereIn('id', $allowedEntityIds)
                            ->pluck('parent_id')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        while (! empty($parentIds)) {

                            $newParentIds = [];

                            $parents = InternalEntity::withoutGlobalScope(
                                'entity_display_filtering'
                            )
                                ->whereIn('id', $parentIds)
                                ->get(['id', 'parent_id']);

                            foreach ($parents as $parent) {

                                if (! in_array($parent->id, $allowedEntityIds, true)) {
                                    $allowedEntityIds[] = $parent->id;
                                }

                                if ($parent->parent_id) {
                                    $newParentIds[] = $parent->parent_id;
                                }
                            }

                            $parentIds = array_values(
                                array_unique(
                                    array_diff($newParentIds, $allowedEntityIds)
                                )
                            );
                        }

                        $query->whereIn(
                            'id',
                            array_values(array_unique($allowedEntityIds))
                        );
                    }

                    // إذا لم يحدد المستخدم محافظة أو مديرية:
                    // يبقى المستخدم المركزي قادرًا على رؤية الجميع.
                }

                $textField = 'name';
                $idField = 'id';

                break;
            case 'program':
                $query = Program::withoutGlobalScope(DomainScope::class);
                $textField = 'name';
                break;

            case 'domain':
                $query = Domain::withoutGlobalScope(DomainScope::class);
                $textField = 'name';
                break;

            case 'subdomain':
                $query = Subdomain::withoutGlobalScope(DomainScope::class);
                $textField = 'name';
                if ($request->has('domain_id')) {
                    $query->where('domain_id', $request->get('domain_id'));
                }
                break;

            case 'intervention':
                $query = Intervention::withoutGlobalScope(DomainScope::class);
                $textField = 'name';
                if ($request->has('subdomain_id')) {
                    $query->where('subdomain_id', $request->get('subdomain_id'));
                }
                break;

            case 'financial_item':
                $query = FinancialItem::query();
                $textField = 'name';
                break;

            case 'unit':
                $query = Unit::query();
                $textField = 'unit_name';
                break;

            case 'funding_source':
                $query = FundingSource::query();
                $textField = 'name';
                break;

            case 'financing_type':
                $query = FinancingType::query();
                $textField = 'name';
                break;

            case 'financing_form':
                $query = FinancingForm::query();
                $textField = 'name';
                break;

            case 'sub_financing_form':
                $query = SubFinancingForm::query();
                $textField = 'name';
                if ($request->has('financing_form_id')) {
                    $query->where('financing_form_id', $request->get('financing_form_id'));
                }
                break;

            case 'priority':
                $query = Priority::query();
                $textField = 'priority';
                break;

            case 'main_router':
                $query = MainRouter::query();
                $textField = 'main_router';
                break;

            case 'sub_router':
                $query = SubRouter::query();
                $textField = 'sub_router';
                if ($request->has('main_router_id')) {
                    $query->where('main_router_id', $request->get('main_router_id'));
                }
                break;

                // ========================================
                // المحافظات
                // ========================================
            case 'governorate':

                $user = auth()->user();

                $query = Governorate::withoutGlobalScope(DomainScope::class);

                // تطبيق التصفية حسب النطاق الجغرافي للمستخدم
                if ($user) {

                    $governorateIds = [];

                    foreach ($user->geographicScopes as $scope) {

                        // نطاق محافظة
                        if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                            $governorateIds[] = $scope->governorate_id;
                        }

                        // نطاق مديرية
                        elseif (! empty($scope->directorate_id)) {
                            $directorate = Directorate::find($scope->directorate_id);

                            if ($directorate) {
                                $governorateIds[] = $directorate->governorate_id;
                            }
                        }
                    }

                    $governorateIds = array_unique($governorateIds);

                    if (! empty($governorateIds)) {
                        $query->whereIn('id', $governorateIds);
                    }
                }

                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // المديريات
                // ========================================
            case 'directorate':

                $user = auth()->user();

                $query = Directorate::withoutGlobalScope(DomainScope::class);

                // فلترة حسب المحافظة المختارة
                if (
                    $request->filled('governorate_id') &&
                    $request->get('governorate_id') != '0'
                ) {

                    $query->where(
                        'governorate_id',
                        $request->get('governorate_id')
                    );
                }

                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // العزل
                // ========================================
            case 'sub_area':

                $user = auth()->user();

                $query = SubArea::query();

                // فلترة حسب المديرية المختارة
                if (
                    $request->filled('directorate_id') &&
                    $request->get('directorate_id') != '0'
                ) {

                    $query->where(
                        'directorate_id',
                        $request->get('directorate_id')
                    );

                } else {

                    // لا تعرض شيء إذا لم يتم اختيار مديرية
                    $query->whereRaw('1=0');
                }

                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // القرى
                // ========================================
            case 'village':

                $query = Village::query();

                // فلترة حسب العزلة المختارة
                if (
                    $request->filled('sub_area_id') &&
                    $request->get('sub_area_id') != '0'
                ) {

                    $query->where(
                        'sub_area_id',
                        $request->get('sub_area_id')
                    );

                } else {

                    // لا تعرض شيء إذا لم يتم اختيار عزلة
                    $query->whereRaw('1=0');
                }

                $textField = 'name';
                $idField = 'id';

                break;
            case 'beneficiary_group':
                $query = BeneficiaryGroup::query();
                $textField = 'name';
                break;

            default:
                return response()->json(['error' => 'Invalid lookup type'], 400);
        }

        // تطبيق فلتر is_active / status
        if ($query && $includePending) {
            // Project module: include approved AND pending records; exclude rejected.
            if (Schema::hasColumn($query->getModel()->getTable(), 'status')) {
                $query->where(function ($q) {
                    $q->whereIn('status', [0, 1])
                        ->orWhere('status', 'approved')
                        ->orWhereNull('status');
                });
            }
            // Do NOT filter by is_active so pending (is_active=false) records are visible.
        } else {
            // Default behaviour: approved records only.
            if ($query && Schema::hasColumn($query->getModel()->getTable(), 'is_active')) {
                $query->where('is_active', true);
            }

            if ($query && Schema::hasColumn($query->getModel()->getTable(), 'status')) {
                $query->where(function ($q) {
                    $q->where('status', 1)
                        ->orWhere('status', 'approved')
                        ->orWhereNull('status');
                });
            }
        }

        // تطبيق البحث (إن وجد)
        if (! empty($search)) {
            $query->where($textField, 'like', "%{$search}%");
        }

        // تحديد الأعمدة المطلوب جلبها
        $columns = [$idField, $textField];
        if (in_array($type, ['authority', 'internal_entity'])) {
            $columns[] = 'parent_id';
        }

        $results = $query->limit($limit)->get($columns);

        // تحويل النتائج إلى صيغة مناسبة لـ Select2
        $formattedResults = $results->map(function ($item) use ($idField, $textField, $type) {
            $data = [
                'id' => $item->$idField,
                'text' => __($item->$textField),
            ];

            if ($type === 'authority') {
                $data['parent_id'] = $item->parent_id;
                $data['parent_name'] = $item->parent ? $item->parent->agency_name : 'لا توجد جهة أب';
            } elseif ($type === 'internal_entity') {
                $data['parent_id'] = $item->parent_id;
                $data['parent_name'] = $item->parent ? $item->parent->name : 'لا توجد جهة أب';
            }

            return $data;
        });

        // إدراج خيار "غير ذلك" برمجياً للبيانات المرجعية (بدون تخزينها في قاعدة البيانات)
        $otherSupportedTypes = [
            'intervention',
            'authority', 'internal_entity', 'financial_item', 'unit',
            'financing_type', 'beneficiary_group', 'sub_area',
        ];

        if (in_array($type, $otherSupportedTypes)) {
            $formattedResults = $formattedResults->reject(function ($item) {
                return trim($item['text'] ?? '') === 'غير ذلك';
            });
            $formattedResults->push(['id' => 'other', 'text' => 'غير ذلك']);
        }

        // إضافة خيار "الكل" للكيانات الجغرافية
        if (in_array($type, ['governorate', 'directorate', 'sub_area', 'village'])) {
            $allText = match ($type) {
                'governorate' => 'جميع المحافظات',
                'directorate' => 'جميع المديريات',
                'sub_area' => 'جميع المناطق الفرعية',
                'village' => 'جميع القرى والحارات',
                default => '',
            };

            // لا نضيف خيار "الكل" إذا كان هناك نتيجة واحدة فقط
            if ($allText && $formattedResults->count() !== 1) {
                $formattedResults->prepend(['id' => '0', 'text' => $allText]);
            }
        }

        return response()->json(['results' => $formattedResults]);
    }

    // -------------------------------------------------------------------------
    // Helper methods for filtering
    // -------------------------------------------------------------------------

    /**
     * تطبيق فلترة الجهة الإدارية + الأبناء (شجرة من مستوى واحد أو متعدد)
     * تحاكي منطق getProjects: الجهة الحالية + كل الجهات التابعة لها.
     *
     * @param  Builder  $query
     * @param  int|null  $entityId
     */
    private function applyInternalEntitySubtreeFilter($query, $entityId): void
    {
        if (! $entityId) {
            $query->whereRaw('1=0'); // لا نتائج

            return;
        }

        // الحصول على الجهة الحالية وجميع الأحفاد (أي مستوى)
        // طريقة بسيطة: استخدام CTE إذا كان يدعمها الإصدار، أو جلب جميع المعرفات بشكل متكرر.
        // هنا نستخدم طريقة recursion على مستوى قاعدة البيانات (مناسب للبيانات غير الضخمة)

        $ids = $this->getInternalEntityIdsWithDescendants($entityId);
        $query->whereIn('id', $ids);
    }

    /**
     * الحصول على array بمعرفات الجهة المحددة وجميع أحفادها (بأي عمق)
     *
     * @param  int  $rootId
     */
    private function getInternalEntityIdsWithDescendants($rootId): array
    {
        $ids = [$rootId];
        $children = InternalEntity::where('parent_id', $rootId)->pluck('id')->toArray();

        while (! empty($children)) {
            $ids = array_merge($ids, $children);
            $children = InternalEntity::whereIn('parent_id', $children)->pluck('id')->toArray();
        }

        return $ids;
    }
}
