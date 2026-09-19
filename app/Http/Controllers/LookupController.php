<?php

namespace App\Http\Controllers;

use App\Models\Authority;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LookupController extends Controller
{
    /**
     * General search method for various entities.
     *
     * قواعد النطاق:
     * - المستخدم الجغرافي: لا يرى إلا محافظاته / مديرياته والجهات الواقعة داخلها
     *   مع الأب المباشر فقط لكل جهة.
     * - المستخدم الإداري/المركزي (لا يملك نطاقاً جغرافياً): يرى جميع المحافظات
     *   والمديريات والجهات الحكومية، ويمكنه تضييق النتائج باختيار محافظة/مديرية.
     */
    public function search(Request $request)
    {
        $type = $request->get('type');
        $search = $request->get('q');
        $limit = (int) $request->get('limit', 20);

        // Project module may request pending + approved records.
        $includePending = (bool) $request->get('include_pending', false);

        $query = null;
        $idField = 'id';
        $textField = 'name';

        $user = auth()->user();

        if (! $user) {
            return response()->json(['results' => []], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | النطاق الجغرافي الفعلي للمستخدم
        |--------------------------------------------------------------------------
        |
        | نحتسبه مرة واحدة ونستخدمه في:
        | الجهات الخارجية - الجهات الداخلية - المحافظات - المديريات.
        |
        */
        $geoBoundary = $this->resolveUserGeographicBoundary($user);
        $isGeographicUser = $geoBoundary['has_geo'];

        switch ($type) {

            // ========================================
            // الجهات الخارجية
            // ========================================
            case 'authority':

                // نتجاوز DomainScope فقط حتى يستطيع المستخدم الإداري رؤية جميع الجهات،
                // مع الإبقاء على بقية الـ Global Scopes الخاصة بالنموذج (مثل valid_names).
                $query = Authority::withoutGlobalScope(DomainScope::class)
                    ->with('parent');

                if ($isGeographicUser) {

                    /*
                    |--------------------------------------------------------------------------
                    | مستخدم جغرافي
                    |--------------------------------------------------------------------------
                    | الجهات داخل نطاقه + الأب المباشر فقط.
                    */
                    $allowedIds = $this->getEntityIdsForGeographicBoundary(
                        Authority::class,
                        $geoBoundary
                    );

                    if (empty($allowedIds)) {
                        // مهم: لا نحوله إلى مستخدم مركزي عند عدم وجود جهات في النطاق.
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $allowedIds);
                    }

                    // أي فلتر قادم من الواجهة يجب أن يبقى داخل نطاق المستخدم.
                    if (! $this->requestedLocationIsInsideBoundary($request, $geoBoundary)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $requestedIds = $this->getEntityIdsForRequestedLocation(
                            Authority::class,
                            $request
                        );

                        if ($requestedIds !== null) {
                            if (empty($requestedIds)) {
                                $query->whereRaw('1 = 0');
                            } else {
                                $query->whereIn('id', $requestedIds);
                            }
                        }
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | مستخدم إداري / مركزي
                    |--------------------------------------------------------------------------
                    | يرى جميع الجهات، وإذا اختار محافظة أو مديرية نفلتر النتائج
                    | ونبقي الأب المباشر للجهات الموجودة في الاختيار.
                    */
                    $requestedIds = $this->getEntityIdsForRequestedLocation(
                        Authority::class,
                        $request
                    );

                    if ($requestedIds !== null) {
                        if (empty($requestedIds)) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('id', $requestedIds);
                        }
                    }
                }

                $textField = 'agency_name';
                $idField = 'id';

                break;

                // ========================================
                // الجهات الداخلية
                // ========================================
            case 'internal_entity':

                /*
                 * entity_display_filtering و DomainScope قد يضيّقان النتائج قبل أن
                 * نطبق قاعدة الـ Lookup المطلوبة هنا، لذلك نتجاوزهما ثم نطبق
                 * النطاق صراحةً أدناه.
                 */
                $query = InternalEntity::withoutGlobalScopes([
                    'entity_display_filtering',
                    DomainScope::class,
                ])->with('parent');

                if ($isGeographicUser) {

                    /*
                    |--------------------------------------------------------------------------
                    | مستخدم جغرافي
                    |--------------------------------------------------------------------------
                    | كل الجهات الواقعة داخل محافظاته/مديرياته + الأب المباشر فقط.
                    */
                    $allowedIds = $this->getEntityIdsForGeographicBoundary(
                        InternalEntity::class,
                        $geoBoundary
                    );

                    if (empty($allowedIds)) {
                        // لا نسمح بالسقوط إلى "رؤية الجميع" إذا لم توجد نتائج.
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $allowedIds);
                    }

                    // حماية من تمرير محافظة/مديرية خارج النطاق يدوياً.
                    if (! $this->requestedLocationIsInsideBoundary($request, $geoBoundary)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $requestedIds = $this->getEntityIdsForRequestedLocation(
                            InternalEntity::class,
                            $request
                        );

                        if ($requestedIds !== null) {
                            if (empty($requestedIds)) {
                                $query->whereRaw('1 = 0');
                            } else {
                                $query->whereIn('id', $requestedIds);
                            }
                        }
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | مستخدم إداري / مركزي
                    |--------------------------------------------------------------------------
                    | يرى جميع الجهات الداخلية. اختيار المحافظة/المديرية مجرد فلتر.
                    */
                    $requestedIds = $this->getEntityIdsForRequestedLocation(
                        InternalEntity::class,
                        $request
                    );

                    if ($requestedIds !== null) {
                        if (empty($requestedIds)) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn('id', $requestedIds);
                        }
                    }
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

                $query = Governorate::withoutGlobalScope(DomainScope::class);

                if ($isGeographicUser) {

                    /*
                     * المديريات المحددة تتحول أيضاً إلى محافظاتها،
                     * ولذلك مستخدم مديرية في صنعاء يرى "صنعاء" فقط.
                     */
                    $allowedGovernorateIds = $geoBoundary['governorate_ids'];

                    if (empty($allowedGovernorateIds)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn('id', $allowedGovernorateIds);
                    }
                }

                // الإداري/المركزي: لا نضع whereIn => يرى جميع المحافظات.
                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // المديريات
                // ========================================
            case 'directorate':

                $query = Directorate::withoutGlobalScope(DomainScope::class);

                if ($isGeographicUser) {

                    /*
                     * إذا كان لديه نطاق محافظة كاملة: كل مديريات المحافظة.
                     * إذا كان لديه نطاق مديرية: تلك المديرية فقط.
                     */
                    $wholeGovernorateIds = $geoBoundary['whole_governorate_ids'];
                    $specificDirectorateIds = $geoBoundary['directorate_ids'];

                    if (empty($wholeGovernorateIds) && empty($specificDirectorateIds)) {

                        $query->whereRaw('1 = 0');

                    } else {

                        $query->where(function ($q) use (
                            $wholeGovernorateIds,
                            $specificDirectorateIds
                        ) {
                            $hasCondition = false;

                            if (! empty($wholeGovernorateIds)) {
                                $q->whereIn('governorate_id', $wholeGovernorateIds);
                                $hasCondition = true;
                            }

                            if (! empty($specificDirectorateIds)) {
                                if ($hasCondition) {
                                    $q->orWhereIn('id', $specificDirectorateIds);
                                } else {
                                    $q->whereIn('id', $specificDirectorateIds);
                                }
                            }
                        });
                    }
                }

                /*
                 * فلترة حسب المحافظة المختارة.
                 * للمستخدم الجغرافي هذا الفلتر يتقاطع مع نطاقه ولا يستطيع توسيعه.
                 */
                if (
                    $request->filled('governorate_id') &&
                    $request->get('governorate_id') != '0'
                ) {
                    $selectedGovernorateId = (int) $request->get('governorate_id');

                    if (
                        $isGeographicUser &&
                        ! in_array(
                            $selectedGovernorateId,
                            $geoBoundary['governorate_ids'],
                            true
                        )
                    ) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->where('governorate_id', $selectedGovernorateId);
                    }
                }

                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // العزل
                // ========================================
            case 'sub_area':

                $query = SubArea::query();

                if (
                    $request->filled('directorate_id') &&
                    $request->get('directorate_id') != '0'
                ) {
                    $query->where(
                        'directorate_id',
                        $request->get('directorate_id')
                    );
                } else {
                    $query->whereRaw('1 = 0');
                }

                $textField = 'name';
                $idField = 'id';

                break;

                // ========================================
                // القرى
                // ========================================
            case 'village':

                $query = Village::query();

                if (
                    $request->filled('sub_area_id') &&
                    $request->get('sub_area_id') != '0'
                ) {
                    $query->where(
                        'sub_area_id',
                        $request->get('sub_area_id')
                    );
                } else {
                    $query->whereRaw('1 = 0');
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

        // ========================================
        // approved / pending / active filters
        // ========================================
        if ($query && $includePending) {

            if (Schema::hasColumn($query->getModel()->getTable(), 'status')) {
                $query->where(function ($q) {
                    $q->whereIn('status', [0, 1])
                        ->orWhere('status', 'approved')
                        ->orWhereNull('status');
                });
            }

            // لا نفلتر is_active هنا حتى تظهر المسودات pending.

        } else {

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

        // ========================================
        // البحث النصي
        // ========================================
        if (! empty($search)) {
            $query->where($textField, 'like', "%{$search}%");
        }

        // ========================================
        // الأعمدة
        // ========================================
        $columns = [$idField, $textField];

        if (in_array($type, ['authority', 'internal_entity'], true)) {
            $columns[] = 'parent_id';
        }

        $results = $query
            ->limit(max(1, min($limit, 100)))
            ->get(array_unique($columns));

        // ========================================
        // Select2 response
        // ========================================
        $formattedResults = $results->map(function ($item) use ($idField, $textField, $type) {

            $data = [
                'id' => $item->$idField,
                'text' => __($item->$textField),
            ];

            if ($type === 'authority') {
                $data['parent_id'] = $item->parent_id;
                $data['parent_name'] = $item->parent
                    ? $item->parent->agency_name
                    : 'لا توجد جهة أب';

            } elseif ($type === 'internal_entity') {
                $data['parent_id'] = $item->parent_id;
                $data['parent_name'] = $item->parent
                    ? $item->parent->name
                    : 'لا توجد جهة أب';
            }

            return $data;
        });

        // ========================================
        // خيار "غير ذلك"
        // ========================================
        $otherSupportedTypes = [
            'intervention',
            'authority',
            'internal_entity',
            'financial_item',
            'unit',
            'financing_type',
            'beneficiary_group',
            'sub_area',
        ];

        if (in_array($type, $otherSupportedTypes, true)) {
            $formattedResults = $formattedResults->reject(function ($item) {
                return trim($item['text'] ?? '') === 'غير ذلك';
            });

            $formattedResults->push([
                'id' => 'other',
                'text' => 'غير ذلك',
            ]);
        }

        // ========================================
        // خيار "الكل" للبيانات الجغرافية
        // ========================================
        if (in_array($type, ['governorate', 'directorate', 'sub_area', 'village'], true)) {

            $allText = match ($type) {
                'governorate' => $isGeographicUser
                    ? 'جميع المحافظات ضمن النطاق'
                    : 'جميع المحافظات',

                'directorate' => $isGeographicUser
                    ? 'جميع المديريات ضمن النطاق'
                    : 'جميع المديريات',

                'sub_area' => 'جميع المناطق الفرعية',
                'village' => 'جميع القرى والحارات',
                default => '',
            };

            // لا نضيف "الكل" إذا كانت هناك نتيجة واحدة فقط.
            if ($allText && $formattedResults->count() !== 1) {
                $formattedResults->prepend([
                    'id' => '0',
                    'text' => $allText,
                ]);
            }
        }

        return response()->json([
            'results' => $formattedResults,
        ]);
    }

    // -------------------------------------------------------------------------
    // Geographic scope helpers
    // -------------------------------------------------------------------------

    /**
     * بناء النطاق الجغرافي الفعلي للمستخدم من جميع المصادر الموجودة في النظام:
     *
     * 1) user_geographic_scopes
     * 2) users.governorate_id / users.directorate_id
     * 3) الجهة/الـ authority المرتبطة بالمستخدم عبر helpers الموجودة في User
     *
     * directorate_id له أولوية على governorate_id في نفس التعيين:
     * - محافظة بدون مديرية => المحافظة كاملة.
     * - مديرية => المديرية فقط.
     */
    private function resolveUserGeographicBoundary($user): array
    {
        $user->loadMissing('geographicScopes');

        $wholeGovernorateIds = [];
        $directorateIds = [];

        // -------------------------------------------------
        // 1) النطاقات الإضافية من user_geographic_scopes
        // -------------------------------------------------
        foreach ($user->geographicScopes as $scope) {

            if (! empty($scope->directorate_id)) {
                $directorateIds[] = (int) $scope->directorate_id;

            } elseif (! empty($scope->governorate_id)) {
                $wholeGovernorateIds[] = (int) $scope->governorate_id;
            }
        }

        // -------------------------------------------------
        // 2) التعيين المباشر على المستخدم
        // -------------------------------------------------
        if (! empty($user->directorate_id)) {
            $directorateIds[] = (int) $user->directorate_id;

        } elseif (! empty($user->governorate_id)) {
            $wholeGovernorateIds[] = (int) $user->governorate_id;
        }

        // -------------------------------------------------
        // 3) الموقع المستنتج من جهة المستخدم / authority
        // -------------------------------------------------
        $primaryDirectorateId = (int) ($user->getAssignedDirectorateId() ?? 0);
        $primaryGovernorateId = (int) ($user->getAssignedGovernorateId() ?? 0);

        if ($primaryDirectorateId > 0) {
            $directorateIds[] = $primaryDirectorateId;

        } elseif ($primaryGovernorateId > 0) {
            $wholeGovernorateIds[] = $primaryGovernorateId;
        }

        $wholeGovernorateIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $wholeGovernorateIds)
                )
            )
        );

        $directorateIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $directorateIds)
                )
            )
        );

        /*
         * نحصل على محافظات المديريات المحددة لكي نستخدمها في Dropdown المحافظات
         * وفي التحقق من governorate_id القادم من الطلب.
         */
        $directorateGovernorateIds = [];

        if (! empty($directorateIds)) {
            $directorateGovernorateIds = Directorate::withoutGlobalScope(DomainScope::class)
                ->whereIn('id', $directorateIds)
                ->pluck('governorate_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $governorateIds = array_values(
            array_unique(
                array_merge(
                    $wholeGovernorateIds,
                    $directorateGovernorateIds
                )
            )
        );

        return [
            'has_geo' => ! empty($wholeGovernorateIds) || ! empty($directorateIds),

            // محافظات يسمح للمستخدم بكل مديرياتها وكل جهاتها.
            'whole_governorate_ids' => $wholeGovernorateIds,

            // مديريات محددة يسمح للمستخدم بها فقط.
            'directorate_ids' => $directorateIds,

            // جميع المحافظات التي ينتمي إليها نطاق المستخدم، بما فيها محافظات المديريات المحددة.
            'governorate_ids' => $governorateIds,
        ];
    }

    /**
     * جلب IDs الجهات الواقعة داخل النطاق الجغرافي + الأب المباشر فقط.
     *
     * @param  class-string  $modelClass  Authority::class أو InternalEntity::class
     */
    private function getEntityIdsForGeographicBoundary(
        string $modelClass,
        array $boundary
    ): array {
        return $this->getEntityIdsForLocation(
            $modelClass,
            $boundary['whole_governorate_ids'] ?? [],
            $boundary['directorate_ids'] ?? []
        );
    }

    /**
     * جلب IDs الجهات حسب المحافظة/المديرية الموجودة في request + الأب المباشر فقط.
     *
     * null = لا يوجد فلتر محافظة ولا مديرية.
     * []   = يوجد فلتر لكن لا توجد جهات مطابقة.
     */
    private function getEntityIdsForRequestedLocation(
        string $modelClass,
        Request $request
    ): ?array {
        $selectedGovernorateId =
            $request->filled('governorate_id') &&
            $request->get('governorate_id') != '0'
                ? (int) $request->get('governorate_id')
                : null;

        $selectedDirectorateId =
            $request->filled('directorate_id') &&
            $request->get('directorate_id') != '0'
                ? (int) $request->get('directorate_id')
                : null;

        if (! $selectedGovernorateId && ! $selectedDirectorateId) {
            return null;
        }

        // المديرية أكثر تحديداً من المحافظة.
        if ($selectedDirectorateId) {
            return $this->getEntityIdsForLocation(
                $modelClass,
                [],
                [$selectedDirectorateId]
            );
        }

        return $this->getEntityIdsForLocation(
            $modelClass,
            [$selectedGovernorateId],
            []
        );
    }

    /**
     * جلب الجهات المطابقة لموقع معين مع الأب المباشر فقط.
     *
     * - wholeGovernorateIds: تشمل كل الجهات التابعة للمحافظة، حتى لو كان
     *   governorate_id فارغاً في الجهة لكن directorate_id ينتمي للمحافظة.
     * - directorateIds: تشمل الجهات التابعة للمديريات المحددة فقط.
     */
    private function getEntityIdsForLocation(
        string $modelClass,
        array $wholeGovernorateIds,
        array $directorateIds
    ): array {
        $wholeGovernorateIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $wholeGovernorateIds)
                )
            )
        );

        $directorateIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $directorateIds)
                )
            )
        );

        if (empty($wholeGovernorateIds) && empty($directorateIds)) {
            return [];
        }

        /*
         * بعض البيانات قد تحمل directorate_id فقط بدون governorate_id،
         * لذلك عند نطاق محافظة كاملة نضيف كل مديريات تلك المحافظة.
         */
        $directoratesInsideWholeGovernorates = [];

        if (! empty($wholeGovernorateIds)) {
            $directoratesInsideWholeGovernorates =
                Directorate::withoutGlobalScope(DomainScope::class)
                    ->whereIn('governorate_id', $wholeGovernorateIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
        }

        /*
         * نستخدم withoutGlobalScopes هنا فقط لحساب المعرفات المسموح بها،
         * ثم الاستعلام الرئيسي يطبق Global Scopes/active/status المناسبة.
         */
        $locationQuery = $modelClass::withoutGlobalScopes();

        $locationQuery->where(function ($q) use (
            $wholeGovernorateIds,
            $directorateIds,
            $directoratesInsideWholeGovernorates
        ) {
            $hasCondition = false;

            if (! empty($wholeGovernorateIds)) {
                $q->whereIn('governorate_id', $wholeGovernorateIds);
                $hasCondition = true;
            }

            if (! empty($directoratesInsideWholeGovernorates)) {
                if ($hasCondition) {
                    $q->orWhereIn('directorate_id', $directoratesInsideWholeGovernorates);
                } else {
                    $q->whereIn('directorate_id', $directoratesInsideWholeGovernorates);
                }

                $hasCondition = true;
            }

            if (! empty($directorateIds)) {
                if ($hasCondition) {
                    $q->orWhereIn('directorate_id', $directorateIds);
                } else {
                    $q->whereIn('directorate_id', $directorateIds);
                }
            }
        });

        $entityIds = $locationQuery
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($entityIds)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | الأب المباشر فقط
        |--------------------------------------------------------------------------
        |
        | لا نصعد حتى الجذر ولا نضيف "أب الأب".
        | هذا يحقق قاعدة: الجهة داخل النطاق + الأب الخاص بها فقط.
        |
        */
        $parentIds = $modelClass::withoutGlobalScopes()
            ->whereIn('id', $entityIds)
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return array_values(
            array_unique(
                array_merge($entityIds, $parentIds)
            )
        );
    }

    /**
     * منع المستخدم الجغرافي من تمرير governorate_id/directorate_id خارج نطاقه.
     */
    private function requestedLocationIsInsideBoundary(
        Request $request,
        array $boundary
    ): bool {
        $selectedGovernorateId =
            $request->filled('governorate_id') &&
            $request->get('governorate_id') != '0'
                ? (int) $request->get('governorate_id')
                : null;

        $selectedDirectorateId =
            $request->filled('directorate_id') &&
            $request->get('directorate_id') != '0'
                ? (int) $request->get('directorate_id')
                : null;

        if (
            $selectedGovernorateId &&
            ! in_array(
                $selectedGovernorateId,
                $boundary['governorate_ids'] ?? [],
                true
            )
        ) {
            return false;
        }

        if ($selectedDirectorateId) {

            // مديرية محددة صراحة ضمن نطاق المستخدم.
            if (
                in_array(
                    $selectedDirectorateId,
                    $boundary['directorate_ids'] ?? [],
                    true
                )
            ) {
                return true;
            }

            /*
             * أو مديرية تقع داخل محافظة أعطيت للمستخدم كنطاق محافظة كاملة.
             */
            $directorateGovernorateId =
                Directorate::withoutGlobalScope(DomainScope::class)
                    ->where('id', $selectedDirectorateId)
                    ->value('governorate_id');

            if (
                ! $directorateGovernorateId ||
                ! in_array(
                    (int) $directorateGovernorateId,
                    $boundary['whole_governorate_ids'] ?? [],
                    true
                )
            ) {
                return false;
            }

            /*
             * إذا أرسل الطلب المحافظة والمديرية معاً يجب أن تكون المديرية
             * بالفعل داخل المحافظة المختارة.
             */
            if (
                $selectedGovernorateId &&
                (int) $directorateGovernorateId !== $selectedGovernorateId
            ) {
                return false;
            }
        }

        return true;
    }
}
