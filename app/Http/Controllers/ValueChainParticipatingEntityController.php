<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\InternalEntity;
use App\Models\ValueChain;
use App\Models\ValueChainParticipatingEntity;
use Illuminate\Http\Request;

class ValueChainParticipatingEntityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:value-chains.view')->only(['index']);
        $this->middleware('permission:value-chains.edit')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ValueChain $valueChain)
    {
        $entities = $valueChain->participatingEntities()->with(['internalEntity', 'authority', 'creator', 'updater'])->latest()->get();

        return view('value_chains.participating_entities.index', compact('valueChain', 'entities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ValueChain $valueChain)
    {
        $internalEntities = InternalEntity::active()->orderBy('name')->get();
        $authorities = Authority::orderBy('agency_name')->get();

        return view('value_chains.participating_entities.create', compact('valueChain', 'internalEntities', 'authorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ValueChain $valueChain)
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ], [
            'entity_type.required' => 'نوع الجهة مطلوب.',
            'internal_entity_id.required_if' => 'يرجى اختيار الجهة الداخلية.',
            'authority_id.required_if' => 'يرجى اختيار الجهة الخارجية.',
        ]);

        // Prevent duplicate entries for the same entity in the same chain
        $exists = ValueChainParticipatingEntity::where('value_chain_id', $valueChain->id)
            ->where('entity_type', $validated['entity_type'])
            ->where(function ($query) use ($validated) {
                if ($validated['entity_type'] === 'internal') {
                    $query->where('internal_entity_id', $validated['internal_entity_id']);
                } else {
                    $query->where('authority_id', $validated['authority_id']);
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'هذه الجهة تمت إضافتها مسبقاً لهذه السلسلة.');
        }

        $valueChain->participatingEntities()->create($validated);

        return redirect()->route('value-chains.participating-entities.index', $valueChain)
            ->with('success', 'تمت إضافة الجهة المشاركة بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ValueChain $valueChain, ValueChainParticipatingEntity $participatingEntity)
    {
        // Ensure the entity belongs to this chain
        if ($participatingEntity->value_chain_id !== $valueChain->id) {
            abort(404);
        }

        $internalEntities = InternalEntity::active()->orderBy('name')->get();
        $authorities = Authority::orderBy('agency_name')->get();

        return view('value_chains.participating_entities.edit', compact('valueChain', 'participatingEntity', 'internalEntities', 'authorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ValueChain $valueChain, ValueChainParticipatingEntity $participatingEntity)
    {
        // Ensure the entity belongs to this chain
        if ($participatingEntity->value_chain_id !== $valueChain->id) {
            abort(404);
        }

        $validated = $request->validate([
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ], [
            'entity_type.required' => 'نوع الجهة مطلوب.',
            'internal_entity_id.required_if' => 'يرجى اختيار الجهة الداخلية.',
            'authority_id.required_if' => 'يرجى اختيار الجهة الخارجية.',
        ]);

        // Clear the unused foreign key
        if ($validated['entity_type'] === 'internal') {
            $validated['authority_id'] = null;
        } else {
            $validated['internal_entity_id'] = null;
        }

        // Check duplicates excluding self
        $exists = ValueChainParticipatingEntity::where('value_chain_id', $valueChain->id)
            ->where('id', '!=', $participatingEntity->id)
            ->where('entity_type', $validated['entity_type'])
            ->where(function ($query) use ($validated) {
                if ($validated['entity_type'] === 'internal') {
                    $query->where('internal_entity_id', $validated['internal_entity_id']);
                } else {
                    $query->where('authority_id', $validated['authority_id']);
                }
            })
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'هذه الجهة تمت إضافتها مسبقاً لهذه السلسلة.');
        }

        $participatingEntity->update($validated);

        return redirect()->route('value-chains.participating-entities.index', $valueChain)
            ->with('success', 'تم تحديث بيانات الجهة بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ValueChain $valueChain, ValueChainParticipatingEntity $participatingEntity)
    {
        // Ensure the entity belongs to this chain
        if ($participatingEntity->value_chain_id !== $valueChain->id) {
            abort(404);
        }

        $participatingEntity->delete();

        return redirect()->route('value-chains.participating-entities.index', $valueChain)
            ->with('success', 'تم حذف الجهة المشاركة بنجاح.');
    }
}
