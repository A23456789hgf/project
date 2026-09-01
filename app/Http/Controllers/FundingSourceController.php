<?php

namespace App\Http\Controllers;

use App\Models\FundingSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FundingSourceController extends Controller
{
    /**
     * Display a listing of funding sources with search, sort and pagination.
     */
    public function index(Request $request)
    {
        // Initialize query builder
        $query = FundingSource::query();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', '%'.$searchTerm.'%');
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort parameters
        if (in_array($sortField, ['name', 'created_at', 'updated_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        // Pagination
        $sources = $query->paginate($perPage)->appends($request->query());

        return view('configuration.sourcefunding.index', compact('sources', 'sortField', 'sortDirection'));
    }

    /**
     * Show the form for creating a new funding source.
     */
    public function create()
    {
        return view('configuration.sourcefunding.create');
    }

    /**
     * Store a newly created funding source.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:funding_sources|max:255',
        ], [
            'name.required' => 'حقل اسم مصدر التمويل مطلوب',
            'name.unique' => 'اسم مصدر التمويل هذا موجود مسبقاً',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفاً',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create new record
        FundingSource::create($request->only('name'));

        return redirect()->route('funding-sources.index')
            ->with('success', 'تم إنشاء مصدر التمويل بنجاح');
    }

    /**
     * Show the form for editing the specified funding source.
     */
    public function edit(FundingSource $fundingSource)
    {
        return view('configuration.sourcefunding.edit', compact('fundingSource'));
    }

    /**
     * Update the specified funding source (full update - PUT).
     */
    public function update(Request $request, FundingSource $fundingSource)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:funding_sources,name,'.$fundingSource->id,
        ], [
            'name.required' => 'حقل اسم مصدر التمويل مطلوب',
            'name.unique' => 'اسم مصدر التمويل هذا موجود مسبقاً',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفاً',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update record
        $fundingSource->update($request->only('name'));

        return redirect()->route('funding-sources.index')
            ->with('success', 'تم تحديث مصدر التمويل بنجاح');
    }

    /**
     * Remove the specified funding source.
     */
    public function destroy(FundingSource $fundingSource)
    {
        $fundingSource->delete();

        return redirect()->route('funding-sources.index')
            ->with('success', 'تم حذف مصدر التمويل بنجاح');
    }

    /**
     * Partial update (PATCH) - Not required but included for completeness
     */
    public function patchUpdate(Request $request, FundingSource $fundingSource)
    {
        // Validate partial input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|max:255|unique:funding_sources,name,'.$fundingSource->id,
        ], [
            'name.required' => 'حقل اسم مصدر التمويل مطلوب',
            'name.unique' => 'اسم مصدر التمويل هذا موجود مسبقاً',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفاً',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Partial update
        if ($request->has('name')) {
            $fundingSource->name = $request->name;
            $fundingSource->save();
        }

        return redirect()->route('funding-sources.index')
            ->with('success', 'تم تحديث مصدر التمويل بنجاح');
    }
}
