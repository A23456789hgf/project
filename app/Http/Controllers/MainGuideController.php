<?php

namespace App\Http\Controllers;

use App\Models\MainGuide;
use Illuminate\Http\Request;

class MainGuideController extends Controller
{
    // عرض جميع السجلات
    public function index()
    {
        $guides = MainGuide::all();

        return view('configuration.main_guides.index', compact('guides'));
    }

    // عرض نموذج الإضافة
    public function create()
    {
        return view('configuration.main_guides.create');
    }

    // حفظ السجل الجديد
    public function store(Request $request)
    {
        $request->validate([
            'main_guide' => 'required|unique:main_guides|max:255',
        ]);

        MainGuide::create($request->only('main_guide'));

        return redirect()->route('main_guides.index')
            ->with('success', 'تم إضافة التوجيه الرئيسي بنجاح');
    }

    // عرض السجل
    public function show($id)
    {
        $guide = MainGuide::findOrFail($id);

        return view('configuration.main_guides.show', compact('guide'));
    }

    // عرض نموذج التعديل
    public function edit($id)
    {
        $guide = MainGuide::findOrFail($id);

        return view('configuration.main_guides.edit', compact('guide'));
    }

    // تحديث السجل
    public function update(Request $request, $id)
    {
        $request->validate([
            'main_guide' => 'required|unique:main_guides,main_guide,'.$id.'|max:255',
        ]);

        $guide = MainGuide::findOrFail($id);
        $guide->update($request->only('main_guide'));

        return redirect()->route('main_guides.index')
            ->with('success', 'تم تحديث التوجيه الرئيسي بنجاح');
    }

    // حذف السجل
    public function destroy($id)
    {
        $guide = MainGuide::findOrFail($id);
        $guide->delete();

        return redirect()->route('main_guides.index')
            ->with('success', 'تم حذف التوجيه الرئيسي بنجاح');
    }
}
