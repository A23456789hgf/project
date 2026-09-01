<?php

namespace App\Http\Controllers;

use App\Models\TypeEntity;
use Illuminate\Http\Request;

class TypeEntityController extends Controller
{
    public function index()
    {
        $types = TypeEntity::latest()->paginate(10);

        return view('configuration.type_entity.index', compact('types'));
    }

    public function create()
    {
        return view('configuration.type_entity.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:type_entities,name',
        ]);

        TypeEntity::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('type-entity.index');
    }

    public function edit($id)
    {
        $type = TypeEntity::findOrFail($id);

        return view('configuration.type_entity.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = TypeEntity::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:type_entities,name,'.$id,
        ]);

        $type->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('type-entity.index');
    }

    public function destroy($id)
    {
        TypeEntity::destroy($id);

        return back();
    }
}
