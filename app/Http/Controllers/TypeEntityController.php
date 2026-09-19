<?php

namespace App\Http\Controllers;

use App\Models\TypeEntity;
use Illuminate\Http\Request;

class TypeEntityController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', TypeEntity::class);
        $types = TypeEntity::latest()->paginate(10);

        return view('configuration.type_entity.index', compact('types'));
    }

    public function create()
    {
        $this->authorize('create', TypeEntity::class);

        return view('configuration.type_entity.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', TypeEntity::class);
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
        $this->authorize('update', TypeEntity::class);
        $type = TypeEntity::findOrFail($id);

        return view('configuration.type_entity.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', TypeEntity::class);
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
        $this->authorize('delete', TypeEntity::class);
        TypeEntity::destroy($id);

        return back();
    }
}
