<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\InternalEntity;
use App\Services\FrappeAPIService;
use Illuminate\Http\Request;

class BudgetWebController extends Controller
{
    protected FrappeAPIService $frappe;

    public function __construct(FrappeAPIService $frappe)
    {
        $this->frappe = $frappe;
    }

    /**
     * عرض صفحة الموازنات
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->loadMissing(['entity', 'role']);

        // تحديد الجهات المسموح بها لعرضها في فلتر الصفحة
        $isAdmin = $user->isAdmin() || ($user->role && $user->role->full_access);

        $userEntityName = null;
        $userEntityErpId = null;
        $allowedCompanies = null; // null = جميع الجهات

        if (! $isAdmin && $user->entity) {
            $userEntityName = $user->entity->name;
            $userEntityErpId = $user->entity->erpnext_id ?: $user->entity->name;
            $allowedCompanies = [$userEntityErpId];
            $companies = InternalEntity::where('entity_type', 'Company')
                ->where('id', $user->entity_id)
                ->active()
                ->get();
        } else {
            $companies = InternalEntity::where('entity_type', 'Company')
                ->active()
                ->get();
        }

        return view('projects.budgets.index', compact(
            'isAdmin',
            'userEntityName',
            'userEntityErpId',
            'allowedCompanies',
            'companies'
        ));
    }
}
