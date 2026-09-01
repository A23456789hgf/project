<?php

namespace App\Http\Controllers;

use App\Exports\AuthoritiesExport;
use App\Imports\AuthoritiesImport;
use App\Models\Authority;
use App\Models\Directorate;
use App\Models\FinancingType;
use App\Models\Governorate;
use App\Models\TypeEntity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class AuthorityController extends Controller
{
    /**
     * عرض قائمة الجهات
     */
    public function index(Request $request)
    {
        try {
            $view = $request->get('view', 'list');

            // حفظ الرابط الحالي مع الفرز والفلترة في الجلسة للعودة إليه لاحقاً
            session(['authorities_index_url' => request()->fullUrl()]);

            /*
            |--------------------------------------------------------------------------
            | عرض جميع الجهات في الـ Dropdown بدون أي فلترة
            |--------------------------------------------------------------------------
            */
            // تمت إضافة with(['governorate', 'directorate']) هنا لتفادي الخطأ إذا تم استخدامها في القائمة المنسدلة
            $allAuthorities = Authority::withoutGlobalScopes()
                ->with(['governorate', 'directorate'])
                ->orderBy('agency_name')
                ->get();

            $authorityFilter = $request->query('authority_filter', 'all');

            if ($view === 'tree') {
                /*
                |--------------------------------------------------------------------------
                | Tree View
                |--------------------------------------------------------------------------
                */
                // تمت إضافة with(['governorate', 'directorate']) لتفادي خطأ Lazy Loading
                $query = Authority::whereNull('parent_id')
                    ->with(['governorate', 'directorate'])
                    ->withCount('children')
                    ->orderBy('agency_name');

                $authUser = auth()->user();

                if ($authUser && ! $authUser->isAdmin()) {
                    if (! $authUser->canViewAuthorities()) {
                        $query->whereRaw('0=1');
                    } else {
                        $query->visibleToUser($authUser);
                    }
                }

                $authorities = $query->get();

                /*
                |--------------------------------------------------------------------------
                | Statistics
                |--------------------------------------------------------------------------
                */
                $totalAuthorities = Authority::visibleToUser()->count();
                $activeAuthorities = Authority::where('is_active', true)->visibleToUser()->count();
                $mainAuthorities = Authority::whereNull('parent_id')->visibleToUser()->count();
                $averageLevels = $this->calculateAverageLevels($authorities);

            } else {
                /*
                |--------------------------------------------------------------------------
                | List View
                |--------------------------------------------------------------------------
                */
                // تمت إضافة العلاقات 'governorate' و 'directorate' و 'creator' داخل مصفوفة with
                $query = Authority::with([
                    'parent',
                    'children' => function ($query) {
                        $query->withCount('children');
                    },
                    'typeEntity',
                    'governorate',
                    'directorate',
                    'creator', // تم إضافة هذه العلاقة هنا لتفادي خطأ التحميل الكسول
                ])->withCount('children');

                $authUser = auth()->user();

                if ($authUser && ! $authUser->isAdmin()) {
                    if (! $authUser->canViewAuthorities()) {
                        $query->whereRaw('0=1');
                    } else {
                        $query->visibleToUser($authUser);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Filter by visibility scope
                |--------------------------------------------------------------------------
                */
                if ($authorityFilter === 'none') {
                    $query->whereRaw('0=1');
                } elseif ($authorityFilter === 'same_directorate') {
                    $userAuthId = $authUser?->entity?->authority_id;
                    if ($userAuthId) {
                        $query->where('id', $userAuthId)
                            ->orWhere('parent_id', $userAuthId);
                    }
                } elseif ($authorityFilter === 'same_governorate') {
                    $userAuth = $authUser?->entity?->authority;
                    if ($userAuth) {
                        $topAuthId = $userAuth->parent_id ?: $userAuth->id;
                        $query->where('id', $topAuthId)
                            ->orWhere('parent_id', $topAuthId);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('agency_name', 'like', "%{$search}%")
                            ->orWhereHas('parent', function ($q) use ($search) {
                                $q->where('agency_name', 'like', "%{$search}%");
                            });
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Status Filter
                |--------------------------------------------------------------------------
                */
                if ($request->filled('status')) {
                    $query->where('is_active', $request->status);
                }

                /*
                |--------------------------------------------------------------------------
                | Parent Filter
                |--------------------------------------------------------------------------
                */
                if ($request->filled('parent_id')) {
                    if ($request->parent_id === 'null') {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $request->parent_id);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Type Entity Filter
                |--------------------------------------------------------------------------
                */
                if ($request->filled('type_entity_id')) {
                    $query->where('type_entity_id', $request->type_entity_id);
                }

                /*
                |--------------------------------------------------------------------------
                | Sorting
                |--------------------------------------------------------------------------
                */
                $sort = $request->get('sort', 'agency_name');
                $order = $request->get('order', 'asc');

                if ($sort === 'father_name') {
                    $query->leftJoin('authorities as parent', 'authorities.parent_id', '=', 'parent.id')
                        ->orderBy('parent.agency_name', $order)
                        ->select('authorities.*');
                } else {
                    $query->orderBy($sort, $order);
                }

                /*
                |--------------------------------------------------------------------------
                | Pagination
                |--------------------------------------------------------------------------
                */
                $perPage = $request->query('per_page', 20);
                $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

                $authorities = $query->paginate($perPage);

                $totalAuthorities = $authorities->total();
                $activeAuthorities = null;
                $mainAuthorities = null;
                $averageLevels = null;
            }

            /*
            |--------------------------------------------------------------------------
            | جلب أنواع الكيانات (Entity Types) للاستخدام في العرض
            |--------------------------------------------------------------------------
            */
            try {
                $typeEntities = TypeEntity::where('is_active', true)
                    ->orderBy('name')
                    ->get();

                if ($typeEntities->isEmpty()) {
                    Log::info('No active entity types found in database.');
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch entity types: '.$e->getMessage());
                $typeEntities = collect();
            }

            /*
            |--------------------------------------------------------------------------
            | View Exists Check
            |--------------------------------------------------------------------------
            */
            if (! view()->exists('configuration.authorities.index')) {
                return redirect()->back()->with('error', 'عذراً، صفحة العرض غير متوفرة حالياً.');
            }

            /*
            |--------------------------------------------------------------------------
            | Return View
            |--------------------------------------------------------------------------
            */
            return view('configuration.authorities.index', compact(
                'authorities',
                'view',
                'allAuthorities',
                'totalAuthorities',
                'activeAuthorities',
                'mainAuthorities',
                'averageLevels',
                'authorityFilter',
                'typeEntities'
            ))->with([
                'governorates' => Governorate::where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id'),
                'directorates' => Directorate::where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@index: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل البيانات.');
        }
    }

    /**
     * حساب متوسط المستويات في الشجرة
     */
    private function calculateAverageLevels($authorities)
    {
        try {
            $totalLevels = 0;
            $totalNodes = 0;

            $calculateLevels = function ($node, $currentLevel) use (&$calculateLevels, &$totalLevels, &$totalNodes) {
                $totalLevels += $currentLevel;
                $totalNodes++;

                if ($node->children->isNotEmpty()) {
                    foreach ($node->children as $child) {
                        $calculateLevels($child, $currentLevel + 1);
                    }
                }
            };

            foreach ($authorities as $authority) {
                $calculateLevels($authority, 0);
            }

            return $totalNodes > 0 ? round($totalLevels / $totalNodes, 1) : 0;
        } catch (\Exception $e) {
            Log::error('Error calculating average levels: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * عرض صفحة إنشاء جهة جديدة
     */
    public function create(Request $request)
    {
        try {
            $parentId = $request->get('parent_id');

            // جلب أنواع الجهات
            $typeEntities = TypeEntity::where('is_active', true)->orderBy('name')->get();

            // جلب الجهات النشطة مع العلاقات (للأب والمحافظة والمديرية)
            $authorities = Authority::where('is_active', true)
                ->addableToUser()
                ->with(['parent', 'governorate', 'directorate'])
                ->orderBy('agency_name')
                ->get(); // كائنات كاملة

            $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
            $financingTypes = FinancingType::where('is_active', true)->orderBy('name')->get();
            $financingForms = $financingTypes;

            return view('configuration.authorities.create', compact(
                'authorities',
                'parentId',
                'governorates',
                'financingTypes',
                'financingForms',
                'typeEntities'
            ));
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@create: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل صفحة الإنشاء.');
        }
    }

    /**
     * حفظ جهة جديدة
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'agency_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('authorities')->where(function ($query) use ($request) {
                        return $query->where('parent_id', $request->parent_id);
                    }),
                ],
                'is_active' => 'boolean',
                'parent_id' => 'nullable|sometimes|exists:authorities,id',
                'governorate_id' => 'nullable|exists:governorates,id',
                'directorate_id' => 'nullable|exists:directorates,id',
                'type_entity_id' => 'nullable|exists:type_entities,id',
                'entity_scope' => 'nullable|string|in:internal,external',
                'financing_type_id' => 'nullable|exists:financing_types,id',
                'financing_form_id' => 'nullable|exists:financing_types,id',
            ], [
                'agency_name.unique' => 'اسم الجهة موجود بالفعل في نفس المستوى التنظيمي.',
            ]);

            // معالجة parent_id ليكون null إذا كان فارغًا
            if (empty($validated['parent_id'])) {
                $validated['parent_id'] = null;
            }

            $authority = Authority::create($validated);

            $message = $request->parent_id
                ? 'تم إنشاء الجهة التابعة بنجاح'
                : 'تم إنشاء الجهة بنجاح';

            $view = $request->get('view', 'tree');
            $sort = $request->get('sort', 'agency_name');
            $order = $request->get('order', 'asc');
            $redirectUrl = session('authorities_index_url', route('authorities.index', ['view' => $view, 'sort' => $sort, 'order' => $order]));

            return redirect($redirectUrl)->with('success', $message);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@store: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء إنشاء الجهة.')->withInput();
        }
    }

    /**
     * عرض تفاصيل جهة
     */
    public function show(string $id)
    {
        try {
            $authority = Authority::with([
                'parent',
                'children' => function ($query) {
                    $query->withCount('children')->orderBy('agency_name');
                },
                'typeEntity',
                'governorate',
                'directorate',
            ])->findOrFail($id);

            // تحميل الأطفال بشكل متكرر
            $this->loadChildrenRecursive($authority);

            $childrenCount = $authority->children->count();
            $descendantsCount = $this->countDescendants($authority);

            if (! view()->exists('configuration.authorities.show')) {
                return redirect()->route('authorities.index')->with('error', 'صفحة العرض غير متوفرة.');
            }

            return view('configuration.authorities.show', compact(
                'authority',
                'childrenCount',
                'descendantsCount'
            ));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('authorities.index')->with('error', 'الجهة غير موجودة.');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@show: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل بيانات الجهة.');
        }
    }

    /**
     * تحميل الأطفال بشكل متكرر
     */
    private function loadChildrenRecursive($authority)
    {
        try {
            $authority->load([
                'children' => function ($query) {
                    $query->withCount('children')->orderBy('agency_name');
                },
            ]);

            if ($authority->children->isNotEmpty()) {
                $authority->children->each(function ($child) {
                    $this->loadChildrenRecursive($child);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error loading children recursively: '.$e->getMessage());
        }
    }

    /**
     * حساب عدد الأحفاد بشكل متكرر
     */
    private function countDescendants($authority)
    {
        try {
            $count = 0;
            $countChildren = function ($node) use (&$countChildren, &$count) {
                if ($node->children->isNotEmpty()) {
                    foreach ($node->children as $child) {
                        $count++;
                        $countChildren($child);
                    }
                }
            };

            $countChildren($authority);

            return $count;
        } catch (\Exception $e) {
            Log::error('Error counting descendants: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * عرض صفحة تعديل جهة
     */
    public function edit(string $id)
    {
        try {
            $authority = Authority::with(['typeEntity'])->findOrFail($id);

            // جلب أنواع الجهات
            $typeEntities = TypeEntity::where('is_active', true)
                ->orderBy('name')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | تعديل: استبعاد الجهة الحالية وأطفالها، وجلب البيانات ككائنات كاملة
            |--------------------------------------------------------------------------
            */
            $authorities = Authority::where('is_active', true)
                ->addableToUser()
                ->where('id', '!=', $id)
                ->whereNotIn('id', function ($query) use ($id) {
                    $query->select('id')
                        ->from('authorities')
                        ->where('parent_id', $id);
                })
                ->with(['parent', 'governorate', 'directorate'])
                ->orderBy('agency_name')
                ->get(); // كائنات كاملة

            if (! view()->exists('configuration.authorities.edit')) {
                return redirect()->route('authorities.index')->with('error', 'صفحة التعديل غير متوفرة.');
            }

            $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
            $directorates = $authority->governorate_id
                ? Directorate::where('governorate_id', $authority->governorate_id)->where('is_active', true)->orderBy('name')->get()
                : collect();
            $financingTypes = FinancingType::where('is_active', true)->orderBy('name')->get();
            $financingForms = $financingTypes;

            return view('configuration.authorities.edit', compact(
                'authority',
                'authorities',
                'governorates',
                'directorates',
                'financingTypes',
                'financingForms',
                'typeEntities'
            ));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('authorities.index')->with('error', 'الجهة غير موجودة.');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@edit: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل صفحة التعديل.');
        }
    }

    /**
     * تحديث جهة
     */
    public function update(Request $request, string $id)
    {
        try {
            $authority = Authority::findOrFail($id);

            $validated = $request->validate([
                'agency_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('authorities')->where(function ($query) use ($request, $authority) {
                        return $query->where('parent_id', $request->parent_id)
                            ->where('id', '!=', $authority->id);
                    }),
                ],
                'is_active' => 'boolean',
                'parent_id' => [
                    'nullable',
                    'sometimes',
                    'exists:authorities,id',
                    function ($attribute, $value, $fail) use ($id) {
                        if ($value == $id) {
                            $fail('لا يمكن أن تكون الجهة أباً لنفسها.');
                        }
                    },
                ],
                'governorate_id' => 'nullable|exists:governorates,id',
                'directorate_id' => 'nullable|exists:directorates,id',
                'type_entity_id' => 'nullable|exists:type_entities,id',
                'entity_scope' => 'nullable|string|in:internal,external',
                'financing_type_id' => 'nullable|exists:financing_types,id',
                'financing_form_id' => 'nullable|exists:financing_types,id',
            ], [
                'agency_name.unique' => 'اسم الجهة موجود بالفعل في نفس المستوى التنظيمي.',
            ]);

            if (empty($validated['parent_id'])) {
                $validated['parent_id'] = null;
            }

            $authority->update($validated);

            $view = $request->get('view', 'tree');
            $redirectUrl = session('authorities_index_url', route('authorities.index', ['view' => $view]));

            return redirect($redirectUrl)->with('success', 'تم تحديث الجهة بنجاح');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            return redirect()->route('authorities.index')->with('error', 'الجهة غير موجودة.');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@update: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الجهة.')->withInput();
        }
    }

    /**
     * حذف جهة
     */
    public function destroy(string $id)
    {
        try {
            $authority = Authority::withCount('children')->findOrFail($id);

            if ($authority->children_count > 0) {
                return redirect()->back()
                    ->with('error', 'لا يمكن حذف الجهة لأنها تحتوي على جهات تابعة. يرجى نقل أو حذف الجهات التابعة أولاً.');
            }

            $authority->delete();

            $view = request()->get('view', 'tree');
            $redirectUrl = session('authorities_index_url', route('authorities.index', ['view' => $view]));

            return redirect($redirectUrl)->with('success', 'تم حذف الجهة بنجاح');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('authorities.index')->with('error', 'الجهة غير موجودة.');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@destroy: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الجهة.');
        }
    }

    /**
     * Remove multiple specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (empty($ids)) {
                return redirect()->back()->with('error', 'لم يتم تحديد أي جهات للحذف.');
            }

            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $authorities = Authority::whereIn('id', $ids)->withCount('children')->get();

            $deletedCount = 0;
            $failedCount = 0;

            foreach ($authorities as $authority) {
                if ($authority->children_count > 0) {
                    $failedCount++;
                } else {
                    $authority->delete();
                    $deletedCount++;
                }
            }

            $view = request()->get('view', 'tree');
            $redirectUrl = session('authorities_index_url', route('authorities.index', ['view' => $view]));

            if ($failedCount > 0) {
                return redirect()->to($redirectUrl)->with('warning', "تم حذف $deletedCount جهات بنجاح، ولم يتم حذف $failedCount جهة لارتباطها بجهات تابعة.");
            }

            return redirect()->to($redirectUrl)->with('success', 'تم حذف الجهات المحددة بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@bulkDestroy: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء الحذف الجماعي.');
        }
    }

    /**
     * API: الحصول على بيانات الشجرة
     */
    public function getTreeData(Request $request)
    {
        try {
            $parentId = $request->get('parent_id', null);
            $level = $request->get('level', 1);

            $query = Authority::visibleToUser()->withCount('children');

            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('agency_name', 'like', "%{$search}%");
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            $authorities = $query->orderBy('agency_name')->get();
            $treeData = $this->buildLazyTreeData($authorities, $level);

            return response()->json([
                'success' => true,
                'data' => $treeData,
                'count' => $authorities->count(),
                'parent_id' => $parentId,
                'level' => $level,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading tree data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الشجرة',
            ], 500);
        }
    }

    /**
     * بناء بيانات الشجرة مع التحميل الكسول
     */
    private function buildLazyTreeData($authorities, $currentLevel = 1)
    {
        try {
            return $authorities->map(function ($authority) use ($currentLevel) {
                $hasChildren = $authority->children_count > 0;

                return [
                    'id' => $authority->id,
                    'text' => $authority->agency_name.($hasChildren ? " ({$authority->children_count})" : ''),
                    'state' => [
                        'opened' => $currentLevel <= 2,
                        'selected' => false,
                        'loaded' => ! $hasChildren,
                    ],
                    'children' => $hasChildren ? true : false,
                    'li_attr' => [
                        'data-level' => $currentLevel,
                        'data-children-count' => $authority->children_count,
                        'class' => $authority->is_active ? 'active-authority' : 'inactive-authority',
                    ],
                    'a_attr' => [
                        'href' => route('authorities.show', $authority->id),
                        'title' => $authority->agency_name,
                    ],
                    'data' => [
                        'is_active' => $authority->is_active,
                        'children_count' => $authority->children_count,
                        'parent_id' => $authority->parent_id,
                        'level' => $currentLevel,
                        'created_at' => $authority->created_at->format('Y-m-d H:i:s'),
                        'has_children' => $hasChildren,
                        'type_entity_id' => $authority->type_entity_id,
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error building lazy tree data: '.$e->getMessage());

            return collect([]);
        }
    }

    /**
     * API: تحميل الأطفال
     */
    public function loadChildren($id)
    {
        try {
            $level = request()->get('level', 2);

            $authority = Authority::withCount('children')->findOrFail($id);

            $children = Authority::where('parent_id', $id)
                ->visibleToUser()
                ->withCount('children')
                ->orderBy('agency_name')
                ->get();

            $childrenData = $this->buildLazyTreeData($children, $level + 1);

            return response()->json([
                'success' => true,
                'data' => $childrenData,
                'parent_id' => $id,
                'level' => $level,
                'total_children' => $children->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading children: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الأطفال',
            ], 500);
        }
    }

    /**
     * API: الحصول على الأطفال
     */
    public function getChildren(Request $request)
    {
        try {
            $parentId = $request->get('parent_id');
            $level = $request->get('level', 2);

            if (! $parentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف الجهة الأب مطلوب',
                ], 400);
            }

            $children = Authority::where('parent_id', $parentId)
                ->visibleToUser()
                ->withCount('children')
                ->orderBy('agency_name')
                ->get();

            $childrenData = $children->map(function ($child) use ($level) {
                return [
                    'id' => $child->id,
                    'agency_name' => $child->agency_name,
                    'is_active' => $child->is_active,
                    'children_count' => $child->children_count,
                    'has_children' => $child->children_count > 0,
                    'level' => $level,
                    'type_entity_id' => $child->type_entity_id,
                    'url' => route('authorities.show', $child->id),
                    'edit_url' => route('authorities.edit', $child->id),
                    'delete_url' => route('authorities.destroy', $child->id),
                ];
            });

            return response()->json([
                'success' => true,
                'children' => $childrenData,
                'parent_id' => $parentId,
                'level' => $level,
                'total' => $children->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting children: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الأطفال',
            ], 500);
        }
    }

    /**
     * بحث متقدم في الشجرة
     */
    public function searchTree(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'search_in' => 'sometimes|in:name,all,structure',
        ]);

        try {
            $searchTerm = $request->query;
            $searchIn = $request->get('search_in', 'all');

            $query = Authority::visibleToUser()->with(['parent', 'typeEntity']);

            if ($searchIn === 'name') {
                $query->where('agency_name', 'like', "%{$searchTerm}%");
            } elseif ($searchIn === 'structure') {
                $matchingAuthorities = Authority::where('agency_name', 'like', "%{$searchTerm}%")->pluck('id');

                $query->where(function ($q) use ($matchingAuthorities, $searchTerm) {
                    $q->where('agency_name', 'like', "%{$searchTerm}%")
                        ->orWhereIn('parent_id', $matchingAuthorities)
                        ->orWhereIn('id', function ($subQuery) use ($searchTerm) {
                            $subQuery->select('parent_id')
                                ->from('authorities')
                                ->where('agency_name', 'like', "%{$searchTerm}%");
                        });
                });
            } else {
                $query->where('agency_name', 'like', "%{$searchTerm}%");
            }

            $authorities = $query->withCount('children')
                ->orderBy('agency_name')
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $authorities->map(function ($authority) {
                    $path = $this->getAuthorityPathArray($authority);

                    return [
                        'id' => $authority->id,
                        'agency_name' => $authority->agency_name,
                        'parent_name' => $authority->parent ? $authority->parent->agency_name : null,
                        'is_active' => $authority->is_active,
                        'children_count' => $authority->children_count,
                        'type_entity_name' => $authority->typeEntity ? $authority->typeEntity->name : null,
                        'path' => $path,
                        'path_text' => implode(' → ', array_column($path, 'name')),
                        'url' => route('authorities.show', $authority->id),
                    ];
                }),
                'count' => $authorities->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching tree: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث',
            ], 500);
        }
    }

    /**
     * الحصول على مسار الجهة كمصفوفة
     */
    private function getAuthorityPathArray($authority)
    {
        try {
            $path = [];
            $current = $authority;

            while ($current) {
                $path[] = [
                    'id' => $current->id,
                    'name' => $current->agency_name,
                ];
                $current = $current->parent;
            }

            return array_reverse($path);
        } catch (\Exception $e) {
            Log::error('Error getting authority path: '.$e->getMessage());

            return [];
        }
    }

    /**
     * تحميل مسار كامل لجهة معينة
     */
    public function loadFullPath($id)
    {
        try {
            $authority = Authority::findOrFail($id);
            $path = $this->getAuthorityPathArray($authority);
            $treeData = $this->loadPathTree($path);

            return response()->json([
                'success' => true,
                'path' => $path,
                'tree_data' => $treeData,
                'authority' => [
                    'id' => $authority->id,
                    'name' => $authority->agency_name,
                    'is_active' => $authority->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading full path: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل المسار',
            ], 500);
        }
    }

    /**
     * تحميل شجرة المسار بالكامل
     */
    private function loadPathTree($path)
    {
        try {
            $treeData = [];

            foreach ($path as $index => $node) {
                $authority = Authority::withCount('children')->find($node['id']);

                if ($authority) {
                    $children = Authority::where('parent_id', $authority->id)
                        ->visibleToUser()
                        ->withCount('children')
                        ->orderBy('agency_name')
                        ->get();

                    $treeNode = [
                        'id' => $authority->id,
                        'text' => $authority->agency_name.($authority->children_count > 0 ? " ({$authority->children_count})" : ''),
                        'state' => [
                            'opened' => true,
                            'selected' => $index === count($path) - 1,
                        ],
                        'children' => $this->buildLazyTreeData($children, $index + 2),
                        'data' => [
                            'is_active' => $authority->is_active,
                            'children_count' => $authority->children_count,
                            'type_entity_id' => $authority->type_entity_id,
                        ],
                    ];

                    $treeData[] = $treeNode;
                }
            }

            return $treeData;
        } catch (\Exception $e) {
            Log::error('Error loading path tree: '.$e->getMessage());

            return [];
        }
    }

    /**
     * نقل جهة إلى جهة أب جديدة
     */
    public function moveAuthority(Request $request, $id)
    {
        try {
            $authority = Authority::findOrFail($id);

            $validated = $request->validate([
                'new_parent_id' => 'nullable|exists:authorities,id',
            ]);

            if ($this->wouldCreateCycle($authority, $validated['new_parent_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن نقل الجهة لأن هذا سيؤدي إلى إنشاء حلقة في الهيكل التنظيمي.',
                ], 422);
            }

            $authority->update(['parent_id' => $validated['new_parent_id']]);

            return response()->json([
                'success' => true,
                'message' => 'تم نقل الجهة بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Error moving authority: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء نقل الجهة',
            ], 500);
        }
    }

    /**
     * تحديث جماعي
     */
    public function bulkUpdate(Request $request)
    {
        $this->authorize('authorities.bulk-edit');

        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:authorities,id',
                'is_active' => 'nullable|in:0,1',
                'parent_id' => 'nullable',
                'governorate_id' => 'nullable',
                'directorate_id' => 'nullable',
                'type_entity_id' => 'nullable',
            ]);

            $ids = $request->input('ids', []);
            $updateData = [];

            if ($request->filled('is_active') && $request->input('is_active') !== '') {
                $updateData['is_active'] = (bool) $request->input('is_active');
            }

            if ($request->has('governorate_id') && $request->input('governorate_id') !== '') {
                $updateData['governorate_id'] = $request->input('governorate_id') === 'null' ? null : $request->input('governorate_id');
            }

            if ($request->has('directorate_id') && $request->input('directorate_id') !== '') {
                $updateData['directorate_id'] = $request->input('directorate_id') === 'null' ? null : $request->input('directorate_id');
            }

            if ($request->has('type_entity_id') && $request->input('type_entity_id') !== '') {
                $updateData['type_entity_id'] = $request->input('type_entity_id') === 'null' ? null : $request->input('type_entity_id');
            }

            if ($request->has('entity_scope') && $request->input('entity_scope') !== '') {
                $updateData['entity_scope'] = $request->input('entity_scope') === 'null' ? null : $request->input('entity_scope');
            }

            if ($request->has('financing_type_id') && $request->input('financing_type_id') !== '') {
                $updateData['financing_type_id'] = $request->input('financing_type_id') === 'null' ? null : $request->input('financing_type_id');
            } elseif ($request->has('financing_form_id') && $request->input('financing_form_id') !== '') {
                $updateData['financing_type_id'] = $request->input('financing_form_id') === 'null' ? null : $request->input('financing_form_id');
            }

            $newParentId = null;
            $updateParent = false;
            if ($request->has('parent_id') && $request->input('parent_id') !== '') {
                $updateParent = true;
                $newParentId = $request->input('parent_id') === 'null' ? null : $request->input('parent_id');
            }

            if (empty($updateData) && ! $updateParent) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تحديد أي تعديلات لتطبيقها على الجهات المحددة.',
                ], 422);
            }

            $updatedCount = 0;
            $authorities = Authority::whereIn('id', $ids)->get();

            foreach ($authorities as $authority) {
                $rowUpdate = $updateData;
                if ($updateParent) {
                    if (! $this->wouldCreateCycle($authority, $newParentId)) {
                        $rowUpdate['parent_id'] = $newParentId;
                    }
                }
                if (! empty($rowUpdate)) {
                    $authority->update($rowUpdate);
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "تم تحديث بيانات {$updatedCount} من الجهات بنجاح.",
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@bulkUpdate: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تنفيذ التعديل الجماعي.',
            ], 500);
        }
    }

    /**
     * صفحة التعديل الجماعي
     */
    public function bulkEditPage(Request $request)
    {
        $this->authorize('authorities.bulk-edit');

        $idsInput = $request->input('ids', []);
        if (is_string($idsInput)) {
            $idsInput = explode(',', $idsInput);
        }
        $ids = array_filter(array_map('intval', (array) $idsInput));

        if (empty($ids)) {
            $selectedAuthorities = Authority::with(['parent', 'governorate', 'directorate', 'typeEntity'])
                ->orderBy('agency_name')
                ->get();
        } else {
            $selectedAuthorities = Authority::whereIn('id', $ids)
                ->with(['parent', 'governorate', 'directorate', 'typeEntity'])
                ->orderBy('agency_name')
                ->get();
        }

        $allAuthorities = Authority::orderBy('agency_name')->get();
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $directorates = Directorate::where('is_active', true)->orderBy('name')->get();
        $financingTypes = FinancingType::where('is_active', true)->orderBy('name')->get();
        $financingForms = $financingTypes;
        $typeEntities = TypeEntity::where('is_active', true)->orderBy('name')->get();

        return view('configuration.authorities.bulk_edit_page', [
            'authorities' => $selectedAuthorities,
            'allAuthorities' => $allAuthorities,
            'governorates' => $governorates,
            'directorates' => $directorates,
            'financingTypes' => $financingTypes,
            'financingForms' => $financingForms,
            'typeEntities' => $typeEntities,
        ]);
    }

    /**
     * حفظ التعديل الجماعي
     */
    public function bulkSave(Request $request)
    {
        $this->authorize('authorities.bulk-edit');

        $items = $request->input('authorities', []);
        if (! is_array($items) || empty($items)) {
            return redirect()->route('authorities.index')->with('error', 'لا توجد بيانات لحفظها.');
        }

        $updatedCount = 0;
        foreach ($items as $id => $data) {
            $authority = Authority::find($id);
            if (! $authority) {
                continue;
            }

            $parentId = ! empty($data['parent_id']) && $data['parent_id'] !== 'null' ? (int) $data['parent_id'] : null;
            if ($parentId && $this->wouldCreateCycle($authority, $parentId)) {
                continue;
            }

            $authority->agency_name = trim($data['agency_name'] ?? $authority->agency_name);

            if (isset($data['is_active']) && $data['is_active'] !== '') {
                $authority->is_active = (bool) $data['is_active'];
            }

            $authority->parent_id = $parentId;

            $govId = ! empty($data['governorate_id']) && $data['governorate_id'] !== 'null' ? (int) $data['governorate_id'] : null;
            $dirId = ! empty($data['directorate_id']) && $data['directorate_id'] !== 'null' ? (int) $data['directorate_id'] : null;

            $authority->governorate_id = $govId;
            $authority->directorate_id = $dirId;

            if (array_key_exists('type_entity_id', $data)) {
                $authority->type_entity_id = ! empty($data['type_entity_id']) && $data['type_entity_id'] !== 'null' ? (int) $data['type_entity_id'] : null;
            }

            if (array_key_exists('entity_scope', $data)) {
                $authority->entity_scope = ! empty($data['entity_scope']) && $data['entity_scope'] !== 'null' ? $data['entity_scope'] : null;
            }

            if (array_key_exists('financing_type_id', $data)) {
                $authority->financing_type_id = ! empty($data['financing_type_id']) && $data['financing_type_id'] !== 'null' ? (int) $data['financing_type_id'] : null;
            } elseif (array_key_exists('financing_form_id', $data)) {
                $authority->financing_type_id = ! empty($data['financing_form_id']) && $data['financing_form_id'] !== 'null' ? (int) $data['financing_form_id'] : null;
            }

            $authority->save();
            $updatedCount++;
        }

        return redirect()->route('authorities.index')->with('success', "تم حفظ التعديلات بنجاح لعدد ({$updatedCount}) سجل.");
    }

    /**
     * التحقق من إنشاء حلقة في الهيكل التنظيمي
     */
    private function wouldCreateCycle($authority, $newParentId)
    {
        try {
            if (! $newParentId) {
                return false;
            }

            if ($newParentId == $authority->id) {
                return true;
            }

            $newParent = Authority::find($newParentId);
            $current = $newParent;

            while ($current && $current->parent_id) {
                if ($current->parent_id == $authority->id) {
                    return true;
                }
                $current = Authority::find($current->parent_id);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error checking cycle: '.$e->getMessage());

            return true;
        }
    }

    /**
     * تصدير البيانات
     */
    public function export(Request $request)
    {
        try {
            $validated = $request->validate([
                'scope' => 'sometimes|in:all,active,inactive',
                'format' => 'sometimes|in:xlsx,csv,pdf',
                'fields' => 'sometimes|array',
                'fields.*' => 'in:id,agency_name,parent_id,governorate_id,directorate_id,type_entity_id,entity_scope,financing_type_id,financing_form_id,is_active,created_at',
                'sort_by' => 'sometimes|in:id,agency_name,created_at',
                'sort_order' => 'sometimes|in:asc,desc',
            ]);

            $scope = $request->get('scope', 'all');
            $format = $request->get('format', 'xlsx');
            $fields = $request->get('fields', ['id', 'agency_name', 'parent_id', 'governorate_id', 'directorate_id', 'type_entity_id', 'entity_scope', 'financing_type_id', 'is_active', 'created_at']);
            $sortBy = $request->get('sort_by', 'agency_name');
            $sortOrder = $request->get('sort_order', 'asc');

            $query = Authority::with(['parent', 'typeEntity', 'governorate', 'directorate', 'financingType']);

            if ($scope === 'active') {
                $query->where('is_active', true);
            } elseif ($scope === 'inactive') {
                $query->where('is_active', false);
            }

            $query->orderBy($sortBy, $sortOrder);
            $authorities = $query->get();

            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "authorities_export_{$timestamp}.{$format}";

            if (ob_get_length()) {
                ob_end_clean();
            }

            return Excel::download(new AuthoritiesExport($authorities, $fields), $filename);
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@export: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير البيانات.');
        }
    }

    /**
     * عرض صفحة الاستيراد
     */
    public function showImport()
    {
        try {
            if (! view()->exists('configuration.authorities.import')) {
                return redirect()->route('authorities.index')->with('error', 'صفحة الاستيراد غير متوفرة.');
            }

            return view('configuration.authorities.import');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@showImport: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل صفحة الاستيراد.');
        }
    }

    /**
     * معاينة الاستيراد
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        try {
            // تخزين الملف مؤقتاً
            $filePath = $file->store('temp-imports', 'public');
            $fullPath = storage_path('app/public/'.$filePath);

            // قراءة البيانات من الملف
            $import = new AuthoritiesImport('preview');
            $data = Excel::toCollection($import, $fullPath)->first();

            if (! $data || $data->isEmpty()) {
                Storage::disk('public')->delete($filePath);

                return back()->withErrors(['file' => 'الملف فارغ أو لا يحتوي على بيانات صالحة']);
            }

            // التحقق من الرأس
            $headers = $data->first()->keys()->toArray();
            $hasNameHeader = false;
            foreach ($headers as $h) {
                $lowH = strtolower($h);
                if (
                    $lowH === 'agency_name' ||
                    str_contains($lowH, 'asm') ||
                    str_contains($lowH, 'jh') ||
                    str_contains($lowH, 'gh') ||
                    str_contains($lowH, 'جهة') ||
                    str_contains($lowH, 'name') ||
                    $h === 'اسم الجهة' ||
                    $h === 'اسم_الجهة'
                ) {
                    $hasNameHeader = true;
                    break;
                }
            }

            if (! $hasNameHeader) {
                Storage::disk('public')->delete($filePath);

                return back()->withErrors(['file' => 'الملف يجب أن يحتوي على عمود "agency_name" أو "اسم الجهة"']);
            }

            // معالجة البيانات والتحقق من الأخطاء
            $errors = collect();
            $validData = collect();
            $existingAgencies = Authority::pluck('agency_name')->toArray();

            foreach ($data as $index => $row) {
                $rowErrors = [];
                $agencyName = trim($row['agency_name'] ?? $row['اسم_الجهة'] ?? $row['asm_algh'] ?? $row['asm_aljhh'] ?? '');

                if (empty($agencyName)) {
                    foreach ($row as $key => $val) {
                        if (
                            str_contains($key, 'agency_name') ||
                            str_contains($key, 'asm') ||
                            str_contains($key, 'jh') ||
                            str_contains($key, 'gh') ||
                            str_contains($key, 'جهة') ||
                            str_contains($key, 'name')
                        ) {
                            $agencyName = trim($val);
                            break;
                        }
                    }
                }

                if (empty($agencyName)) {
                    $rowErrors['agency_name'] = 'اسم الجهة مطلوب';
                } elseif (strlen($agencyName) > 255) {
                    $rowErrors['agency_name'] = 'اسم الجهة طويل جداً (الحد الأقصى 255 حرف)';
                }

                $duplicateInFile = $validData->contains('agency_name', $agencyName);
                if ($duplicateInFile && ! empty($agencyName)) {
                    $rowErrors['agency_name'] = 'اسم الجهة مكرر في الملف';
                }

                $isActive = $this->normalizeActiveStatus($row['is_active'] ?? 'نشط');

                $validRow = [
                    'agency_name' => $agencyName,
                    'is_active' => $isActive,
                    'row_number' => $index + 1,
                ];

                $validData->push($validRow);

                if (! empty($rowErrors)) {
                    $errors->put($index, $rowErrors);
                }
            }

            session()->flash('import_preview_data', [
                'filePath' => $filePath,
                'validData' => $validData,
                'totalRows' => $data->count() - 1,
            ]);

            if (! view()->exists('configuration.authorities.import_preview')) {
                Storage::disk('public')->delete($filePath);

                return redirect()->route('authorities.import')->with('error', 'صفحة معاينة الاستيراد غير متوفرة.');
            }

            return view('configuration.authorities.import_preview', [
                'data' => $data,
                'errors' => $errors,
                'fileName' => $fileName,
                'filePath' => $filePath,
                'validData' => $validData,
                'totalRows' => $data->count() - 1,
                'validRows' => $validData->count() - $errors->count(),
                'errorRows' => $errors->count(),
            ]);
        } catch (\Exception $e) {
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            Log::error('Error in AuthorityController@previewImport: '.$e->getMessage());

            return back()->withErrors(['file' => 'حدث خطأ أثناء قراءة الملف: '.$e->getMessage()]);
        }
    }

    /**
     * تنفيذ الاستيراد
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
            'import_mode' => 'required|in:add,update,add_update,skip',
            'confirmation' => 'required|accepted',
        ]);

        $filePath = storage_path('app/public/'.$request->file_path);

        if (! file_exists($filePath)) {
            return back()->withErrors(['file_path' => 'الملف غير موجود أو انتهت صلاحية الجلسة']);
        }

        try {
            $startTime = microtime(true);
            $import = new AuthoritiesImport($request->import_mode);
            Excel::import($import, $filePath);

            $results = $import->getResults();
            $results['duration'] = round(microtime(true) - $startTime, 2);

            if (! empty($results['errors'])) {
                session(['import_errors' => $results['errors']]);
            }

            Storage::disk('public')->delete($request->file_path);

            return redirect()->route('authorities.importReport')
                ->with([
                    'success' => 'تم استيراد البيانات بنجاح',
                    'import_results' => $results,
                ]);
        } catch (\Exception $e) {
            Storage::disk('public')->delete($request->file_path);
            Log::error('Error in AuthorityController@import: '.$e->getMessage());

            return back()->withErrors(['import' => 'حدث خطأ أثناء الاستيراد: '.$e->getMessage()]);
        }
    }

    /**
     * تقرير الاستيراد
     */
    public function importReport()
    {
        try {
            $resultsData = session('import_results', [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'failed' => 0,
                'errors' => [],
                'details' => [],
            ]);

            if (! view()->exists('configuration.authorities.import_report')) {
                return redirect()->route('authorities.index')->with('error', 'صفحة تقرير الاستيراد غير متوفرة.');
            }

            return view('configuration.authorities.import_report', [
                'imported' => $resultsData['imported'],
                'updated' => $resultsData['updated'],
                'failed' => $resultsData['failed'],
                'skipped' => $resultsData['skipped'],
                'duration' => $resultsData['duration'] ?? 0,
                'results' => collect($resultsData['details']),
                'errors' => collect($resultsData['errors']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@importReport: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل تقرير الاستيراد.');
        }
    }

    /**
     * تحميل تقرير الأخطاء
     */
    public function downloadErrorReport(Request $request, $fileName = null)
    {
        try {
            $errors = session('import_errors', []);

            if (empty($errors)) {
                return redirect()->back()->with('error', 'لا توجد أخطاء لتحميلها.');
            }

            $format = $request->get('format', 'xlsx');
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "authorities_import_errors_{$timestamp}.{$format}";

            $errorData = collect($errors)->map(function ($error, $index) {
                return [
                    'row_number' => $error['row'] ?? $index + 1,
                    'agency_name' => $error['data']['agency_name'] ?? '',
                    'errors' => is_array($error['errors']) ? implode(', ', array_map(function ($field, $messages) {
                        return "$field: ".(is_array($messages) ? implode('; ', $messages) : $messages);
                    }, array_keys($error['errors']), $error['errors'])) : $error['errors'],
                ];
            });

            return Excel::download(new AuthoritiesExport($errorData, ['row_number', 'agency_name', 'errors']), $filename);
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@downloadErrorReport: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل تقرير الأخطاء.');
        }
    }

    /**
     * عرض صفحة التصدير
     */
    public function showExport()
    {
        try {
            if (! view()->exists('configuration.authorities.export')) {
                return redirect()->route('authorities.index')->with('error', 'صفحة التصدير غير متوفرة.');
            }

            return view('configuration.authorities.export');
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@showExport: '.$e->getMessage());

            return redirect()->route('authorities.index')->with('error', 'حدث خطأ أثناء تحميل صفحة التصدير.');
        }
    }

    /**
     * تحميل قالب الاستيراد
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $request->validate([
                'format' => 'sometimes|in:xlsx,csv',
            ]);

            $format = $request->get('format', 'xlsx');

            $data = [
                ['id' => 1, 'agency_name' => 'وزارة الدفاع', 'parent_id' => '-', 'governorate_id' => 'أمانة العاصمة', 'directorate_id' => 'السبعين', 'entity_scope' => 'داخلي', 'financing_type_id' => 'حكومي', 'is_active' => 'نشط', 'created_at' => now()->format('Y-m-d')],
                ['id' => 2, 'agency_name' => 'هيئة الاستخبارات', 'parent_id' => 'وزارة الدفاع', 'governorate_id' => 'صنعاء', 'directorate_id' => 'سنحان', 'entity_scope' => 'داخلي', 'financing_type_id' => 'ذاتي', 'is_active' => 'نشط', 'created_at' => now()->format('Y-m-d')],
                ['id' => 3, 'agency_name' => 'إدارة التخطيط', 'parent_id' => 'هيئة الاستخبارات', 'governorate_id' => '-', 'directorate_id' => '-', 'entity_scope' => 'خارجي', 'financing_type_id' => '-', 'is_active' => 'غير نشط', 'created_at' => now()->format('Y-m-d')],
            ];

            $headers = ['id', 'agency_name', 'parent_id', 'governorate_id', 'directorate_id', 'entity_scope', 'financing_type_id', 'is_active', 'created_at'];

            $timestamp = now()->format('Y-m-d');
            $filename = "authorities_template_{$timestamp}.{$format}";

            if (ob_get_length()) {
                ob_end_clean();
            }

            return Excel::download(new AuthoritiesExport(collect($data), $headers), $filename);
        } catch (\Exception $e) {
            Log::error('Error in AuthorityController@downloadTemplate: '.$e->getMessage());

            return redirect()->back()->with('error', 'حدث خطأ أثناء تحميل القالب.');
        }
    }

    /**
     * دالة مساعدة لتحويل حالة النشاط إلى قيمة منطقية
     */
    private function normalizeActiveStatus($status): bool
    {
        try {
            $status = strtolower(trim($status));

            $activeValues = ['نشط', 'active', '1', 'true', 'yes', 'نعم'];
            $inactiveValues = ['غير نشط', 'inactive', '0', 'false', 'no', 'لا'];

            if (in_array($status, $activeValues)) {
                return true;
            }

            if (in_array($status, $inactiveValues)) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error normalizing active status: '.$e->getMessage());

            return true;
        }
    }

    /**
     * دالة لفحص صحة الملف قبل الاستيراد (API)
     */
    public function validateImportFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $filePath = $request->file('file')->store('temp-validation', 'public');
            $fullPath = storage_path('app/public/'.$filePath);

            $data = Excel::toCollection(null, $fullPath)->first();

            Storage::disk('public')->delete($filePath);

            if (! $data || $data->isEmpty()) {
                return response()->json(['valid' => false, 'message' => 'الملف فارغ']);
            }

            $headers = $data->first()->keys()->map('strtolower')->toArray();

            if (! in_array('agency_name', $headers)) {
                return response()->json(['valid' => false, 'message' => 'العمود agency_name مطلوب']);
            }

            return response()->json([
                'valid' => true,
                'rows' => $data->count() - 1,
                'headers' => $headers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating import file: '.$e->getMessage());

            return response()->json(['valid' => false, 'message' => 'خطأ في قراءة الملف']);
        }
    }

    /**
     * دالة لفحص حالة النظام
     */
    public function systemCheck()
    {
        try {
            $checks = [
                'views_directory' => is_dir(resource_path('views/configuration/authorities')),
                'model_exists' => class_exists(Authority::class),
                'storage_writable' => is_writable(storage_path()),
                'views_exist' => [
                    'index' => view()->exists('configuration.authorities.index'),
                    'create' => view()->exists('configuration.authorities.create'),
                    'show' => view()->exists('configuration.authorities.show'),
                    'edit' => view()->exists('configuration.authorities.edit'),
                    'import' => view()->exists('configuration.authorities.import'),
                    'export' => view()->exists('configuration.authorities.export'),
                ],
            ];

            return response()->json([
                'success' => true,
                'checks' => $checks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب المديريات حسب المحافظة (AJAX)
     */
    public function getDirectorates($governorate_id)
    {
        try {
            $directorates = Directorate::where('governorate_id', $governorate_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $directorates,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching directorates: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المديريات',
            ], 500);
        }
    }
}
