<?php

namespace App\Http\Controllers;

use App\Models\MainRouter;
use Illuminate\Http\Request;

class MainRouterController extends Controller
{
    // عرض جميع السجلات
    public function index()
    {
        $routers = MainRouter::all();

        return view('configuration.main_routers.index', compact('routers'));
    }

    // عرض نموذج الإضافة
    public function create()
    {
        return view('configuration.main_routers.create');
    }

    // حفظ السجل الجديد
    public function store(Request $request)
    {
        $request->validate([
            'main_router' => 'required|unique:main_routers|max:255',
        ]);

        MainRouter::create([
            'main_router' => $request->main_router,
            'is_active' => $request->has('is_active') ? 1 : 1,
        ]);

        return redirect()->route('main-routers.index')
            ->with('success', 'تم إضافة الراوتر بنجاح');
    }

    // عرض نموذج التعديل
    public function edit($id)
    {
        $router = MainRouter::findOrFail($id);

        return view('configuration.main_routers.edit', compact('router'));
    }

    // تحديث السجل
    public function update(Request $request, $id)
    {
        $request->validate([
            'main_router' => 'required|unique:main_routers,main_router,'.$id.'|max:255',
        ]);

        $router = MainRouter::findOrFail($id);
        $router->update([
            'main_router' => $request->main_router,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('main-routers.index')
            ->with('success', 'تم تحديث الراوتر بنجاح');
    }

    // حذف السجل
    public function destroy($id)
    {
        $router = MainRouter::findOrFail($id);
        $router->delete();

        return redirect()->route('main-routers.index')
            ->with('success', 'تم حذف الراوتر بنجاح');
    }
}
