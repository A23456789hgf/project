<?php

namespace App\Services;

use App\Models\InternalEntity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait ScopesDataByEntity
{
    /**
     * جلب معرفات الكيانات المسموح للمستخدم برؤيتها بناءً على النطاق الإداري والجغرافي
     * من الجلسة (Session) أو من بيانات المستخدم الافتراضية.
     */
    protected function getScopedEntityIds(): array
    {
        $user = Auth::user();

        $adminScope = Session::get('selected_administrative_scope_id', $user->administrative_scope_id ?? null);
        $entity = Session::get('selected_entity_id', $user->entity_id ?? null);
        $geoScopes = Session::get('selected_geographic_scopes', $user->geographicScopes ?? []);

        // 1. النطاق الإداري
        $entityIdsByEnt = $adminScope ? InternalEntity::getAllChildrenIds($adminScope) : [];

        // 2. النطاق الجغرافي
        $entityIdsByGeo = [];
        if ($geoScopes) {
            foreach ($geoScopes as $scope) {
                $govId = is_array($scope) ? ($scope['governorate_id'] ?? null) : ($scope->governorate_id ?? null);
                $dirId = is_array($scope) ? ($scope['directorate_id'] ?? null) : ($scope->directorate_id ?? null);

                if (! empty($dirId)) {
                    $entityIdsByGeo = array_merge($entityIdsByGeo, InternalEntity::getAllByDirectorate($dirId));
                } elseif ($govId === 'all' || $govId === 0 || $govId === '0' || $govId === null) {
                    $ids = DB::table('internal_entities')->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                } elseif (! empty($govId)) {
                    $entityIdsByGeo = array_merge($entityIdsByGeo, InternalEntity::getAllByGovernorate($govId));
                }
            }
        }

        // 3. دمج النطاقات
        $entityIdsByMyEnt = [];
        if (empty($entityIdsByEnt) && empty($entityIdsByGeo)) {
            $entityIdsByMyEnt = $entity ? InternalEntity::getAllChildrenIds($entity) : [];
        }

        $entityIds = array_unique(array_merge($entityIdsByEnt, $entityIdsByMyEnt, $entityIdsByGeo));

        // إرجاع مصفوفة نظيفة من الأرقام الموجبة فقط
        return array_values(array_filter($entityIds, fn ($id) => is_numeric($id) && $id > 0));
    }
}
