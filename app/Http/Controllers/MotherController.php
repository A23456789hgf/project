<?php

namespace App\Http\Controllers;

use App\Models\Mother;
use Illuminate\Http\Request;

class MotherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $this->authorize('viewAny', Mother::class);
        $mothers = class_exists(Mother::class) ? Mother::all() : collect([]);

        return view('configuration.mothers.index', compact('mothers'));
    }

    public function create()
    {
        $this->authorize('create', Mother::class);

        return view('configuration.mothers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Mother::class);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (class_exists(Mother::class)) {
            Mother::create($request->only('name', 'is_active'));
        }

        return redirect()->route('mothers.index')->with('success', 'تم إضافة الجهة بنجاح');
    }

    public function edit($id)
    {
        $this->authorize('update', Mother::class);

        $mother = class_exists(Mother::class) ? Mother::findOrFail($id) : (object)['id' => $id, 'name' => ''];

        return view('configuration.mothers.edit', compact('mother'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Mother::class);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if (class_exists(Mother::class)) {
            $mother = Mother::findOrFail($id);
            $mother->update($request->only('name', 'is_active'));
        }

        return redirect()->route('mothers.index')->with('success', 'تم تحديث الجهة بنجاح');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Mother::class);

        if (class_exists(Mother::class)) {
            $mother = Mother::findOrFail($id);
            $mother->delete();
        }

        return redirect()->route('mothers.index')->with('success', 'تم حذف الجهة بنجاح');
    }
}
PHP;

