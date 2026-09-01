<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\District;
use App\Models\Governorate;
use Illuminate\Http\Request;

class AssociationController extends Controller
{
    public function index(Request $request)
    {
        $query = Association::with(['governorate', 'district']);

        // البحث
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // الفرز
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $associations = $query->paginate($perPage);

        return view('configuration.association.index', compact('associations'));
    }

    public function create()
    {
        $governorates = Governorate::getCachedAll();

        return view('configuration.association.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:associations|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'district_id' => 'required|exists:districts,id',
        ], [
            'governorate_id.required' => 'يرجى اختيار المحافظة',
            'district_id.required' => 'يرجى اختيار المديرية',
            'name.required' => 'يرجى إدخال اسم الجمعية',
            'name.unique' => 'اسم الجمعية مسجل مسبقاً',
        ]);

        Association::create($validated);

        return redirect()->route('associations.index')->with('success', 'تمت إضافة الجمعية بنجاح');
    }

    public function edit(Association $association)
    {
        $governorates = Governorate::getCachedAll();
        $districts = District::where('governorate_id', $association->governorate_id)->get();

        return view('configuration.association.edit', compact('association', 'governorates', 'districts'));
    }

    public function update(Request $request, Association $association)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:associations,name,'.$association->id,
            'governorate_id' => 'required|exists:governorates,id',
            'district_id' => 'required|exists:districts,id',
        ], [
            'governorate_id.required' => 'يرجى اختيار المحافظة',
            'district_id.required' => 'يرجى اختيار المديرية',
            'name.required' => 'يرجى إدخال اسم الجمعية',
            'name.unique' => 'اسم الجمعية مسجل مسبقاً',
        ]);

        $association->update($validated);

        return redirect()->route('associations.index')->with('success', 'تم تحديث بيانات الجمعية بنجاح');
    }

    public function destroy(Association $association)
    {
        $association->delete();

        return redirect()->route('associations.index')->with('success', 'تم حذف الجمعية بنجاح');
    }

    // دالة لتحميل المديريات حسب المحافظة (للاستخدام مع AJAX)
    public function getDistricts($governorateId)
    {
        $districts = District::where('governorate_id', $governorateId)->pluck('name', 'id');

        return response()->json($districts);
    }
}
