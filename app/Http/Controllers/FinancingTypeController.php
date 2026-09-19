<?php

namespace App\Http\Controllers;

use App\Models\FinancingType;
use Illuminate\Http\Request;

class FinancingTypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FinancingType::class);
        $query = FinancingType::query();

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Sort
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $financingTypes = $query->paginate($perPage);

        return view('configuration.type_financing.index', compact('financingTypes'));
    }

    public function create()
    {
        $this->authorize('create', FinancingType::class);

        return view('configuration.type_financing.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', FinancingType::class);
        $request->validate(['name' => 'required|unique:financing_types|max:255']);
        FinancingType::create($request->all());

        return redirect()->route('financing-types.index')->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit(FinancingType $financingType)
    {
        $this->authorize('update', FinancingType::class);

        return view('configuration.type_financing.edit', compact('financingType'));
    }

    public function update(Request $request, FinancingType $financingType)
    {
        $this->authorize('update', FinancingType::class);
        $request->validate(['name' => 'required|unique:financing_types,name,'.$financingType->id.'|max:255']);
        $financingType->update($request->all());

        return redirect()->route('financing-types.index')->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(FinancingType $financingType)
    {
        $this->authorize('delete', FinancingType::class);
        $financingType->delete();

        return redirect()->route('financing-types.index')->with('success', 'تم الحذف بنجاح');
    }
}
