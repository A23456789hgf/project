<?php

namespace App\Http\Controllers;

use App\Models\FinancingForm;
use App\Models\SubFinancingForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubFinancingFormController extends Controller
{
    // عرض جميع الأشكال الفرعية
    public function index(Request $request)
    {
        $this->authorize('viewAny', SubFinancingForm::class);
        $query = SubFinancingForm::with('financingForm');

        // البحث النصي
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                    ->orWhereHas('financingForm', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'like', "%$searchTerm%");
                    });
            });
        }

        // التصفية حسب شكل التمويل الرئيسي
        if ($request->filled('financing_form_id')) {
            $query->where('financing_form_id', $request->financing_form_id);
        }

        // التصفية حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $subForms = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());
        $financingForms = FinancingForm::all();

        return view('configuration.subfinancingform.index', compact('subForms', 'financingForms'));
    }

    // صفحة إضافة شكل فرعي
    public function create()
    {
        $this->authorize('create', SubFinancingForm::class);
        $financingForms = FinancingForm::all();

        return view('configuration.subfinancingform.create', compact('financingForms'));
    }

    // تخزين البيانات
    public function store(Request $request)
    {
        $this->authorize('create', SubFinancingForm::class);
        $request->validate([
            'financing_form_id' => 'required|exists:financing_forms,id',
            'name' => 'required|unique:sub_financing_forms,name|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            SubFinancingForm::create($request->all());
            DB::commit();

            return redirect()->route('subfinancing-forms.index')->with('success', 'تمت الإضافة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }

    // صفحة التعديل
    public function edit(SubFinancingForm $subFinancingForm)
    {
        $this->authorize('update', SubFinancingForm::class);
        $financingForms = FinancingForm::all();

        return view('configuration.subfinancingform.edit', compact('subFinancingForm', 'financingForms'));
    }

    // تحديث البيانات
    public function update(Request $request, SubFinancingForm $subFinancingForm)
    {
        $this->authorize('update', SubFinancingForm::class);
        $request->validate([
            'financing_form_id' => 'required|exists:financing_forms,id',
            'name' => 'required|unique:sub_financing_forms,name,'.$subFinancingForm->id.'|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $subFinancingForm->update($request->all());
            DB::commit();

            return redirect()->route('subfinancing-forms.index')->with('success', 'تم التحديث بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }

    // الحذف
    public function destroy(SubFinancingForm $subFinancingForm)
    {
        $this->authorize('delete', SubFinancingForm::class);
        DB::beginTransaction();
        try {
            $subFinancingForm->delete();
            DB::commit();

            return redirect()->route('subfinancing-forms.index')->with('success', 'تم الحذف بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }

    // API للحصول على الأشكال الفرعية حسب الشكل الرئيسي
    public function getByFinancingForm($financingFormId)
    {
        $subForms = SubFinancingForm::where('financing_form_id', $financingFormId)
            ->where('is_active', 1)
            ->get();

        return response()->json($subForms);
    }
}
