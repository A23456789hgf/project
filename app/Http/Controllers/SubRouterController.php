<?php

namespace App\Http\Controllers;

use App\Models\MainRouter;
use App\Models\SubRouter;
use Illuminate\Http\Request;

class SubRouterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', SubRouter::class);
        $subRouters = SubRouter::with('mainRouter')->get();

        return view('configuration.sub_routers.index', compact('subRouters'));
    }

    public function create()
    {
        $this->authorize('create', SubRouter::class);
        $mainRouters = MainRouter::all();

        return view('configuration.sub_routers.create', compact('mainRouters'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SubRouter::class);
        $request->validate([
            'main_router_id' => 'required|exists:main_routers,id',
            'sub_router' => 'required|max:255',
            'is_active' => 'required|boolean',
        ]);

        SubRouter::create($request->all());

        return redirect()->route('sub-routers.index')
            ->with('success', 'تم إضافة الموجه الفرعي بنجاح');
    }

    public function edit($id)
    {
        $this->authorize('update', SubRouter::class);
        $subRouter = SubRouter::findOrFail($id);
        $mainRouters = MainRouter::all();

        return view('configuration.sub_routers.edit', compact('subRouter', 'mainRouters'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', SubRouter::class);
        $request->validate([
            'main_router_id' => 'required|exists:main_routers,id',
            'sub_router' => 'required|max:255',
            'is_active' => 'required|boolean',
        ]);

        $subRouter = SubRouter::findOrFail($id);
        $subRouter->update($request->all());

        return redirect()->route('sub-routers.index')
            ->with('success', 'تم تحديث الموجه الفرعي بنجاح');
    }

    public function destroy($id)
    {
        $this->authorize('delete', SubRouter::class);
        $subRouter = SubRouter::findOrFail($id);
        $subRouter->delete();

        return redirect()->route('sub-routers.index')
            ->with('success', 'تم حذف الموجه الفرعي بنجاح');
    }
}
