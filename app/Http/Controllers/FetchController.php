<?php

namespace App\Http\Controllers;

use App\Models\Directorate;
use App\Models\District;
use App\Models\Domain;
use App\Models\Executor;
use App\Models\FinancialItem;
use App\Models\FundedEntity;
use App\Models\Governorate;
use App\Models\Intervention;
use App\Models\Participant;
use App\Models\Program;
use App\Models\SubArea;
use App\Models\Subdomain;
use App\Models\Supervisor;
use App\Models\Village;
use Illuminate\Http\Request;

class FetchController extends Controller
{
    // جلب بيانات البرامج مع فلترة اختيارية بالاسم
    public function getPrograms(Request $request)
    {
        $query = Program::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $programs = $query->orderBy('id')->get();

        return response()->json($programs);
    }

    // جلب بيانات المجالات مع فلترة حسب برنامج واسم المجال
    public function getDomains(Request $request)
    {
        $query = Domain::query();

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $domains = $query->orderBy('id')->get();

        return response()->json($domains);
    }

    // جلب بيانات المجالات الفرعية مع فلترة اختيارية
    // public function getSubDomains(Request $request)
    // {
    //     $domainId = $request->query('domain_id');

    //     if (!$domainId) {
    //         return response()->json([
    //             'error' => 'domain_id query parameter is required'
    //         ], 400);
    //     }

    //     $query = \App\Models\SubDomain::where('domain_id', $domainId);

    //     if ($request->filled('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     $subdomains = $query->orderBy('id')->get();

    //     return response()->json($subdomains);
    // }

    public function getSubDomains(Request $request)
    {
        $domainId = $request->query('domain_id');

        $query = Subdomain::query();

        // إذا تم تمرير domain_id، طبق الفلتر عليه، وإذا لم يتم تمريره، إرجع الكل
        if ($domainId) {
            $query->where('domain_id', $domainId);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $subdomains = $query->orderBy('id')->get();

        return response()->json($subdomains);
    }

    // جلب بيانات التدخلات مع فلترة حسب المجال، المجال الفرعي، والبحث
    // public function getInterventions(Request $request)
    // {
    //     $subdomainId = $request->query('subdomain_id');

    //     if (!$subdomainId) {
    //         return response()->json([
    //             'error' => 'subdomain_id query parameter is required'
    //         ], 400);
    //     }

    //     $query = Intervention::with(['domain', 'subdomain'])  // حذف 'program' من هنا
    //                          ->where('subdomain_id', $subdomainId);

    //     if ($request->filled('search')) {
    //         $query->where('name', 'like', '%' . $request->search . '%');
    //     }

    //     $interventions = $query->orderBy('id')->get();

    //     return response()->json($interventions);
    // }

    public function getInterventions(Request $request)
    {
        $interventions = Intervention::all(['id', 'intervention_type']);

        return response()->json($interventions);
    }

    // جلب بيانات المحافظات
    public function getGovernorates()
    {
        $governorates = Governorate::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($governorates);
    }

    public function getDistricts(Request $request)
    {
        $districts = District::orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    public function getDirectoratesByGovernorate($governorateId)
    {
        $query = Directorate::where('is_active', true);

        if (! empty($governorateId) && $governorateId !== '0') {
            $query->where('governorate_id', $governorateId);
        }

        $directorates = $query->orderBy('name')
            ->get(['id', 'name']);

        // Add text field for Select2 compatibility if needed
        $directorates->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($directorates);
    }

    public function getAllDirectorates()
    {
        $directorates = Directorate::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $directorates->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($directorates);
    }

    public function getSubAreasByDirectorate($governorateId, $directorateId)
    {
        $query = SubArea::where('is_active', true);

        if (! empty($governorateId) && $governorateId !== '0') {
            $query->where('governorate_id', $governorateId);
        }

        if (! empty($directorateId) && $directorateId !== '0') {
            $query->where('directorate_id', $directorateId);
        }

        $subAreas = $query->orderBy('name')
            ->get(['id', 'name']);

        $subAreas->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($subAreas);
    }

    public function getAllSubAreas()
    {
        $subAreas = SubArea::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $subAreas->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($subAreas);
    }

    public function getVillagesBySubArea($governorateId, $directorateId, $subAreaId)
    {
        $query = Village::where('is_active', true);

        if (! empty($governorateId) && $governorateId !== '0') {
            $query->where('governorate_id', $governorateId);
        }

        if (! empty($directorateId) && $directorateId !== '0') {
            $query->where('directorate_id', $directorateId);
        }

        if (! empty($subAreaId) && $subAreaId !== '0') {
            $query->where('sub_area_id', $subAreaId);
        }

        $villages = $query->orderBy('name')
            ->get(['id', 'name']);

        $villages->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($villages);
    }

    public function getAllVillages()
    {
        $villages = Village::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $villages->transform(function ($item) {
            $item->text = $item->name;

            return $item;
        });

        return response()->json($villages);
    }

    // public function getDistrictsByGovernorate(Request $request)
    // {
    //     $governorateId = $request->query('governorate_id'); // جلب من ?governorate_id=xx

    //     if (!$governorateId) {
    //         return response()->json(['error' => 'Governorate ID is required'], 400);
    //     }

    //     $districts = District::where('governorate_id', $governorateId)
    //                          ->orderBy('name')
    //                          ->get(['id', 'name']);

    //     return response()->json($districts);
    // }

    // جلب بيانات المشرفين النشطاء مع فلترة اختيارية بالاسم
    public function getActiveSupervisors(Request $request)
    {
        $query = Supervisor::where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $supervisors = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($supervisors);
    }

    // جلب الجهات الممولة مع فلترة اختيارية
    public function getFundedEntities(Request $request)
    {
        $query = FundedEntity::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $entities = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($entities);
    }

    // جلب الجهات المنفذة مع فلترة اختيارية
    public function getExecutorsByName(Request $request)
    {
        $query = Executor::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $executors = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($executors);
    }

    // جلب المشاركين مع فلترة اختيارية
    public function getParticipants(Request $request)
    {
        $query = Participant::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $participants = $query->orderBy('id', 'desc')->get();

        return response()->json($participants);
    }

    public function getFinancialItems()
    {
        $items = FinancialItem::orderBy('id')->get();

        return response()->json($items);
    }
}
