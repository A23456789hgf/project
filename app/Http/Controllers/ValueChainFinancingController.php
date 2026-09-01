<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\InternalEntity;
use App\Models\ValueChain;
use App\Models\ValueChainFinancing;
use App\Models\ValueChainFinancingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValueChainFinancingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:series_financing.view')->only(['index', 'globalIndex']);
        $this->middleware('can:series_financing.create')->only(['create', 'store', 'globalCreate', 'globalStore']);
        $this->middleware('can:series_financing.edit')->only(['edit', 'update', 'globalEdit', 'globalUpdate']);
        $this->middleware('can:series_financing.delete')->only(['destroy', 'globalDestroy']);
    }

    public function index(Request $request, $valueChainId)
    {
        $valueChain = ValueChain::findOrFail($valueChainId);

        $query = $valueChain->financings()->with(['internalEntity', 'authority', 'createdBy', 'financingType']);

        if ($request->filled('financing_type_id')) {
            $query->where('value_chain_financing_type_id', $request->financing_type_id);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'oldest') {
                $query->oldest();
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $financings = $query->get();

        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.index', compact('valueChain', 'financings', 'financingTypes'));
    }

    public function globalIndex(Request $request)
    {
        $query = ValueChainFinancing::with(['valueChain', 'internalEntity', 'authority', 'createdBy', 'financingType']);

        if ($request->filled('financing_type_id')) {
            $query->where('value_chain_financing_type_id', $request->financing_type_id);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'oldest') {
                $query->oldest();
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $financings = $query->paginate(20);
        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.global_index', compact('financings', 'financingTypes'));
    }

    public function globalCreate()
    {
        $valueChains = ValueChain::all();
        $internalEntities = InternalEntity::with('parent')->where('is_active', true)->get();
        $authorities = Authority::with('parent')->get();
        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.global_create', compact('valueChains', 'internalEntities', 'authorities', 'financingTypes'));
    }

    public function globalStore(Request $request)
    {
        $request->validate([
            'value_chain_id' => 'required|exists:value_chains,id',
            'value_chain_financing_type_id' => 'required|exists:value_chain_financing_types,id',
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ]);

        ValueChainFinancing::create([
            'value_chain_id' => $request->value_chain_id,
            'value_chain_financing_type_id' => $request->value_chain_financing_type_id,
            'entity_type' => $request->entity_type,
            'internal_entity_id' => $request->entity_type === 'internal' ? $request->internal_entity_id : null,
            'authority_id' => $request->entity_type === 'external' ? $request->authority_id : null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('global-financings.index')
            ->with('success', 'تم إضافة جهة التمويل بنجاح');
    }

    public function globalEdit($id)
    {
        $financing = ValueChainFinancing::findOrFail($id);
        $valueChains = ValueChain::all();
        $internalEntities = InternalEntity::with('parent')->where('is_active', true)->get();
        $authorities = Authority::with('parent')->get();
        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.global_edit', compact('financing', 'valueChains', 'internalEntities', 'authorities', 'financingTypes'));
    }

    public function globalUpdate(Request $request, $id)
    {
        $financing = ValueChainFinancing::findOrFail($id);

        $request->validate([
            'value_chain_id' => 'required|exists:value_chains,id',
            'value_chain_financing_type_id' => 'required|exists:value_chain_financing_types,id',
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ]);

        $financing->update([
            'value_chain_id' => $request->value_chain_id,
            'value_chain_financing_type_id' => $request->value_chain_financing_type_id,
            'entity_type' => $request->entity_type,
            'internal_entity_id' => $request->entity_type === 'internal' ? $request->internal_entity_id : null,
            'authority_id' => $request->entity_type === 'external' ? $request->authority_id : null,
        ]);

        return redirect()->route('global-financings.index')
            ->with('success', 'تم تعديل جهة التمويل بنجاح');
    }

    public function globalDestroy($id)
    {
        $financing = ValueChainFinancing::findOrFail($id);
        $financing->delete();

        return redirect()->route('global-financings.index')
            ->with('success', 'تم حذف جهة التمويل بنجاح');
    }

    public function create($valueChainId)
    {
        $valueChain = ValueChain::findOrFail($valueChainId);
        $internalEntities = InternalEntity::with('parent')->where('is_active', true)->get();
        $authorities = Authority::with('parent')->get();
        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.create', compact('valueChain', 'internalEntities', 'authorities', 'financingTypes'));
    }

    public function store(Request $request, $valueChainId)
    {
        $valueChain = ValueChain::findOrFail($valueChainId);

        $request->validate([
            'value_chain_financing_type_id' => 'required|exists:value_chain_financing_types,id',
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ]);

        ValueChainFinancing::create([
            'value_chain_id' => $valueChain->id,
            'value_chain_financing_type_id' => $request->value_chain_financing_type_id,
            'entity_type' => $request->entity_type,
            'internal_entity_id' => $request->entity_type === 'internal' ? $request->internal_entity_id : null,
            'authority_id' => $request->entity_type === 'external' ? $request->authority_id : null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('value-chains.financing.index', $valueChain->id)
            ->with('success', 'تم إضافة جهة التمويل بنجاح');
    }

    public function edit($valueChainId, $id)
    {
        $valueChain = ValueChain::findOrFail($valueChainId);
        $financing = ValueChainFinancing::where('value_chain_id', $valueChainId)->findOrFail($id);
        $internalEntities = InternalEntity::with('parent')->where('is_active', true)->get();
        $authorities = Authority::with('parent')->get();
        $financingTypes = ValueChainFinancingType::where('is_active', true)->get();

        return view('value_chains.series_financing.edit', compact('valueChain', 'financing', 'internalEntities', 'authorities', 'financingTypes'));
    }

    public function update(Request $request, $valueChainId, $id)
    {
        $valueChain = ValueChain::findOrFail($valueChainId);
        $financing = ValueChainFinancing::where('value_chain_id', $valueChainId)->findOrFail($id);

        $request->validate([
            'value_chain_financing_type_id' => 'required|exists:value_chain_financing_types,id',
            'entity_type' => 'required|in:internal,external',
            'internal_entity_id' => 'required_if:entity_type,internal|nullable|exists:internal_entities,id',
            'authority_id' => 'required_if:entity_type,external|nullable|exists:authorities,id',
        ]);

        $financing->update([
            'value_chain_financing_type_id' => $request->value_chain_financing_type_id,
            'entity_type' => $request->entity_type,
            'internal_entity_id' => $request->entity_type === 'internal' ? $request->internal_entity_id : null,
            'authority_id' => $request->entity_type === 'external' ? $request->authority_id : null,
        ]);

        return redirect()->route('value-chains.financing.index', $valueChain->id)
            ->with('success', 'تم تعديل جهة التمويل بنجاح');
    }

    public function destroy($valueChainId, $id)
    {
        $financing = ValueChainFinancing::where('value_chain_id', $valueChainId)->findOrFail($id);
        $financing->delete();

        return redirect()->route('value-chains.financing.index', $valueChainId)
            ->with('success', 'تم حذف جهة التمويل بنجاح');
    }
}
