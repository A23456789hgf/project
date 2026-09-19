<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Beneficiary::class);
        $query = Beneficiary::query();

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Sort
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $beneficiaries = $query->paginate($perPage);

        return view('configuration.beneficiaries.index', compact('beneficiaries'));
    }

    public function create()
    {
        $this->authorize('create', Beneficiary::class);

        return view('configuration.beneficiaries.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Beneficiary::class);
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|unique:beneficiaries|max:255',
        ]);

        // إنشاء مستفيد جديد دون تمرير _token
        Beneficiary::create($validated);

        return redirect()->route('beneficiaries.index')->with('success', 'تمت الإضافة بنجاح');
    }

    public function edit(Beneficiary $beneficiary)
    {
        $this->authorize('update', Beneficiary::class);

        return view('configuration.beneficiaries.edit', compact('beneficiary'));
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
        $this->authorize('update', Beneficiary::class);
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|unique:beneficiaries,name,'.$beneficiary->id.'|max:255',
        ]);

        // تحديث المستفيد
        $beneficiary->update($validated);

        return redirect()->route('beneficiaries.index')->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(Beneficiary $beneficiary)
    {
        $this->authorize('delete', Beneficiary::class);
        $beneficiary->delete();

        return redirect()->route('beneficiaries.index')->with('success', 'تم الحذف بنجاح');
    }
}
