<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\EntityOfficial;
use App\Models\InternalEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EntityOfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('entity_officers.view');

        $officers = EntityOfficial::with(['internalEntity', 'authority'])
            ->latest()
            ->paginate(request('per_page', 15));

        return view('entity-officers.index', compact('officers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('entity_officers.create');

        $internalEntities = InternalEntity::orderBy('name')->get();
        $authorities = Authority::orderBy('agency_name')->get();

        return view('entity-officers.create', compact('internalEntities', 'authorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('entity_officers.create');

        $request->validate([
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
            'admin_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
        ]);

        EntityOfficial::create($request->all());

        session()->flash('success', 'تم إضافة مسؤول الجهة بنجاح');

        return redirect()->route('entity-officers.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntityOfficial $entityOfficer)
    {
        Gate::authorize('entity_officers.edit');

        $internalEntities = InternalEntity::orderBy('name')->get();
        $authorities = Authority::orderBy('agency_name')->get();

        return view('entity-officers.edit', compact('entityOfficer', 'internalEntities', 'authorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntityOfficial $entityOfficer)
    {
        Gate::authorize('entity_officers.edit');

        $request->validate([
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
            'admin_name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
        ]);

        // Clear the other ID if type changed
        $data = $request->all();
        if ($request->entity_type === 'internal') {
            $data['authority_id'] = null;
        } else {
            $data['internal_entity_id'] = null;
        }

        $entityOfficer->update($data);

        session()->flash('success', 'تم تحديث بيانات مسؤول الجهة بنجاح');

        return redirect()->route('entity-officers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntityOfficial $entityOfficer)
    {
        Gate::authorize('entity_officers.delete');

        $entityOfficer->delete();

        session()->flash('success', 'تم حذف مسؤول الجهة بنجاح');

        return redirect()->route('entity-officers.index');
    }
}
