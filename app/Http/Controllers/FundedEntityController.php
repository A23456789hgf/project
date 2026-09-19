<?php

namespace App\Http\Controllers;

use App\Models\FundedEntity;
use App\Models\FundingSource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // تأكد من إضافة هذا الاستيراد

class FundedEntityController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FundedEntity::class);
        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $fundedEntities = FundedEntity::with('fundingSource')->paginate($perPage);

        return view('configuration.funded-entities.index', compact('fundedEntities'));
    }

    public function create()
    {
        $this->authorize('create', FundedEntity::class);
        $fundingSources = FundingSource::where('is_active', true)->get();

        return view('configuration.funded-entities.create', compact('fundingSources'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', FundedEntity::class);
        $validated = $request->validate([
            'funding_source_id' => 'required|exists:funding_sources,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funded_entities')->where(function ($query) use ($request) {
                    return $query->where('funding_source_id', $request->funding_source_id);
                }),
            ],
        ], [
            'name.unique' => 'هذه الجهة مسجلة already لهذا المصدر التمويلي',
        ]);

        FundedEntity::create($validated);

        return redirect()->route('funded-entities.index')
            ->with('success', 'تم إضافة الجهة الممولة بنجاح');
    }

    public function edit(FundedEntity $fundedEntity)
    {
        $this->authorize('update', FundedEntity::class);
        $fundingSources = FundingSource::where('is_active', true)->get();

        return view('configuration.funded-entities.edit', compact('fundedEntity', 'fundingSources'));
    }

    public function update(Request $request, FundedEntity $fundedEntity)
    {
        $this->authorize('update', FundedEntity::class);
        $validated = $request->validate([
            'funding_source_id' => 'required|exists:funding_sources,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funded_entities')->where(function ($query) use ($request) {
                    return $query->where('funding_source_id', $request->funding_source_id);
                })->ignore($fundedEntity->id),
            ],
        ], [
            'name.unique' => 'هذه الجهة مسجلة already لهذا المصدر التمويلي',
        ]);

        $fundedEntity->update($validated);

        return redirect()->route('funded-entities.index')
            ->with('success', 'تم تحديث الجهة الممولة بنجاح');
    }

    public function destroy(FundedEntity $fundedEntity)
    {
        $this->authorize('delete', FundedEntity::class);
        $fundedEntity->delete();

        return redirect()->route('funded-entities.index')
            ->with('success', 'تم حذف الجهة الممولة بنجاح');
    }
}
