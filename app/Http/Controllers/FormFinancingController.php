<?php

namespace App\Http\Controllers;

use App\Models\FinancingForm;
use Illuminate\Http\Request;

class FormFinancingController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancingForm::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $sort = in_array($request->sort, ['created_at', 'name']) ? $request->sort : 'created_at';
        $query->orderBy($sort);

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $formFinancings = $query->paginate($perPage);

        return view('configuration.formfinancing.index', compact('formFinancings'));
    }

    public function create()
    {
        return view('configuration.formfinancing.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:financing_forms,name',
        ]);

        FinancingForm::create([
            'name' => $request->name,
        ]);

        return redirect()->route('formfinancing.index')->with('success', 'تم إضافة شكل التمويل بنجاح');
    }

    public function edit(FinancingForm $formfinancing)
    {
        return view('configuration.formfinancing.edit', compact('formfinancing'));
    }

    public function update(Request $request, FinancingForm $formfinancing)
    {
        $request->validate([
            'name' => 'required|unique:financing_forms,name,'.$formfinancing->id,
        ]);

        $formfinancing->update([
            'name' => $request->name,
        ]);

        return redirect()->route('formfinancing.index')->with('success', 'تم تحديث شكل التمويل بنجاح');
    }

    public function destroy(FinancingForm $formfinancing)
    {
        $formfinancing->delete();

        return redirect()->route('formfinancing.index')->with('success', 'تم حذف شكل التمويل بنجاح');
    }
}
