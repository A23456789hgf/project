<?php

namespace App\Http\Controllers;

use App\Models\FinancingForm;
use App\Models\FormFinancing;
use Illuminate\Http\Request;

class FormFinancingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FormFinancing::class);
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
        $this->authorize('create', FormFinancing::class);

        return view('configuration.formfinancing.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', FormFinancing::class);
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
        $this->authorize('update', FormFinancing::class);

        return view('configuration.formfinancing.edit', compact('formfinancing'));
    }

    public function update(Request $request, FinancingForm $formfinancing)
    {
        $this->authorize('update', FormFinancing::class);
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
        $this->authorize('delete', FormFinancing::class);
        $formfinancing->delete();

        return redirect()->route('formfinancing.index')->with('success', 'تم حذف شكل التمويل بنجاح');
    }
}
