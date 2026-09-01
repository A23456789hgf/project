<?php

namespace App\Http\Controllers;

use App\Models\InternalEntity;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ================================================================
        // قراءة النطاقات من الجلسة أو من المستخدم (القيم الافتراضية)
        // ================================================================
        $selectedAdminScope = Session::get('selected_administrative_scope_id', $user->administrative_scope_id);
        $selectedEntity = Session::get('selected_entity_id', $user->entity_id);
        $selectedGeographicScopes = Session::get('selected_geographic_scopes', $user->geographicScopes);

        // ================================================================
        // بناء قائمة المعرفات (entity_ids) من النطاقات الثلاثة
        // ================================================================
        $entityIds = $this->getEntityIdsFromScope($selectedAdminScope, $selectedEntity, $selectedGeographicScopes);

        // ================================================================
        // بناء الاستعلامات
        // ================================================================
        $programQuery = Program::query();
        $userQuery = User::query();
        $projectQuery = Project::withoutGlobalScopes();

        // ── فلترة البرامج والمستخدمين ──────────────────────────────────
        if ($user->isAdmin()) {
            // المدير يرى الكل — لا فلترة
        } elseif (! empty($entityIds)) {
            $programQuery->whereHas('projects', function ($q) use ($entityIds) {
                $q->whereIn('creator_entity_id', $entityIds);
            });
            $userQuery->whereIn('entity_id', $entityIds);
        } else {
            // لا نطاق مُعيَّن → يرى برامجه وكيانه فقط
            $userEntityId = $user->entity_id;
            if (! empty($userEntityId)) {
                $programQuery->whereHas('projects', function ($q) use ($userEntityId) {
                    $q->where('creator_entity_id', $userEntityId);
                });
                $userQuery->where('entity_id', $userEntityId);
            }
        }

        // ── فلترة المشاريع (مطابقة لـ ProjectService) ──────────────────
        if ($user->isAdmin()) {
            // المدير يرى الكل — لا فلترة
        } elseif (! empty($entityIds)) {
            $entityNames = DB::table('internal_entities')
                ->whereIn('id', $entityIds)
                ->pluck('name')
                ->filter()
                ->toArray();

            $projectQuery->where(function ($q) use ($entityIds, $entityNames, $user) {
                $q->whereIn('creator_entity_id', $entityIds)
                    ->orWhereIn('internal_entity_id', $entityIds)
                    ->orWhere('created_by_user_id', $user->id);

                foreach ($entityNames as $entName) {
                    $cleanEntName = trim(preg_replace('/\s+/u', ' ', $entName));
                    if (! empty($cleanEntName)) {
                        $q->orWhere('created_by_entity', 'like', "%{$cleanEntName}%");
                    }
                }
            });
        } else {
            // لا نطاق مُعيَّن → المستخدم يرى مشاريعه الشخصية أو مشاريع كيانه فقط
            $userEntityId = $user->entity_id;
            $userEntityName = $user->entity?->name;

            $projectQuery->where(function ($q) use ($user, $userEntityId, $userEntityName) {
                $q->where('created_by_user_id', $user->id);
                if (! empty($userEntityId)) {
                    $q->orWhere('creator_entity_id', $userEntityId)
                        ->orWhere('internal_entity_id', $userEntityId);
                }
                if (! empty($userEntityName)) {
                    $cleanEntName = trim(preg_replace('/\s+/u', ' ', $userEntityName));
                    $q->orWhere('created_by_entity', 'like', "%{$cleanEntName}%");
                }
            });
        }

        // ================================================================
        // الإحصائيات والمشاريع الأخيرة
        // ================================================================
        $t1 = microtime(true);
        $programCount = $programQuery->count();
        $t2 = microtime(true);
        $userCount = $userQuery->count();
        $t3 = microtime(true);
        $projectCount = $projectQuery->count();
        $t4 = microtime(true);

        $completedProjectsCount = (clone $projectQuery)->where('status', 'completed')->count();
        $t5 = microtime(true);
        $underReviewProjectsCount = (clone $projectQuery)->whereHas('currentApprovalStage', function ($q) {
            $q->whereIn('type', ['financial_review', 'technical_review']);
        })->count();
        $t6 = microtime(true);
        $underExecutionProjectsCount = (clone $projectQuery)->whereIn('status', ['in_progress', 'implementation', 'in_execution'])->count();
        $t7 = microtime(true);

        $recentProjects = $projectQuery
            ->latest()
            ->take(5)
            ->get();
        $t8 = microtime(true);

        Log::info('HomeTiming', [
            'q1' => $t2 - $t1,
            'q2' => $t3 - $t2,
            'q3' => $t4 - $t3,
            'q4' => $t5 - $t4,
            'q5' => $t6 - $t5,
            'q6' => $t7 - $t6,
            'q7' => $t8 - $t7,
            'total' => $t8 - $t1,
        ]);

        return view('home', compact(
            'user',
            'programCount',
            'projectCount',
            'userCount',
            'completedProjectsCount',
            'underReviewProjectsCount',
            'underExecutionProjectsCount',
            'recentProjects'
        ));
    }

    /**
     * بناء قائمة المعرفات من النطاقات الثلاثة.
     * مطابق لمنطق ProjectService (يدعم 'all' و null و 0 و '0').
     */
    private function getEntityIdsFromScope($adminScope, $entity, $geoScopes): array
    {
        // ── أ) النطاق الإداري ──
        $entityIdsByEnt = $adminScope ? InternalEntity::getAllChildrenIds($adminScope) : [];

        // ── ج) النطاق الجغرافي ──
        $entityIdsByGeo = [];

        if ($geoScopes) {
            foreach ($geoScopes as $scope) {
                $govId = is_array($scope) ? ($scope['governorate_id'] ?? null) : ($scope->governorate_id ?? null);
                $dirId = is_array($scope) ? ($scope['directorate_id'] ?? null) : ($scope->directorate_id ?? null);

                if (! empty($dirId)) {
                    // نطاق مديرية محددة
                    $ids = InternalEntity::getAllByDirectorate($dirId);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                } elseif ($govId === 'all' || $govId === 0 || $govId === '0' || $govId === null) {
                    // نطاق «كل المحافظات»: جلب جميع الكيانات الداخلية
                    $ids = DB::table('internal_entities')
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->toArray();
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                } elseif (! empty($govId)) {
                    // نطاق محافظة محددة
                    $ids = InternalEntity::getAllByGovernorate($govId);
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                }
            }
        }

        // ── ب) نطاق الكيان ──
        $entityIdsByMyEnt = [];
        if (empty($entityIdsByEnt) && empty($entityIdsByGeo)) {
            $entityIdsByMyEnt = $entity ? InternalEntity::getAllChildrenIds($entity) : [];
        }

        $entityIds = array_unique(array_merge($entityIdsByEnt, $entityIdsByMyEnt, $entityIdsByGeo));

        return array_values(array_filter($entityIds, fn ($id) => is_numeric($id) && $id > 0));
    }

    /**
     * (اختياري) دالة فلترة المستخدمين القديمة، معدّلة لاستخدام الجلسة.
     */
    protected function applyUserScopeFilter(Builder $query): Builder
    {
        $user = Auth::user();
        $selectedAdminScope = Session::get('selected_administrative_scope_id', $user->administrative_scope_id);
        $selectedEntity = Session::get('selected_entity_id', $user->entity_id);
        $selectedGeographicScopes = Session::get('selected_geographic_scopes', $user->geographicScopes);

        $entityIds = $this->getEntityIdsFromScope($selectedAdminScope, $selectedEntity, $selectedGeographicScopes);
        if (! empty($entityIds)) {
            $query->whereIn('entity_id', $entityIds);
        }

        return $query;
    }

    /**
     * (اختياري) دالة النطاق الجغرافي للموديولات، يمكن تعديلها لاستخدام الجلسة لاحقاً.
     */
    protected function applyGeographicScope(Builder $query, string $module): void
    {
        $user = Auth::user();

        if ($user->isAdmin() || ($user->role && $user->role->full_access)) {
            return;
        }

        $scope = $user->getModuleGeoScope($module);

        if ($scope === 'all' || $scope === '') {
            return;
        }

        if ($scope === 'none') {
            $query->whereRaw('1 = 0');

            return;
        }

        $govId = $user->governorate_id ?? $user->getAssignedGovernorateId();
        $dirId = $user->directorate_id ?? $user->getAssignedDirectorateId();

        $model = $query->getModel();

        if ($scope === 'same_governorate' && $govId) {

            if ($model instanceof User) {
                $query->where('governorate_id', $govId);
            } else {
                $query->whereHas('createdBy', function ($q) use ($govId) {
                    $q->where('governorate_id', $govId);
                });
            }

        } elseif ($scope === 'same_directorate' && $dirId) {

            if ($model instanceof User) {
                $query->where('directorate_id', $dirId);
            } else {
                $query->whereHas('createdBy', function ($q) use ($dirId) {
                    $q->where('directorate_id', $dirId);
                });
            }
        }
    }
}
