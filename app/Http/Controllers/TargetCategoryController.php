<?php

namespace App\Http\Controllers;

use App\Models\TargetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TargetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TargetCategory::query();

        // Search functionality
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Sorting
        if ($sort = $request->query('sort')) {
            $direction = $request->query('direction', 'asc');
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $targetCategories = $query->paginate($perPage);

        return view('configuration.target-categories.index', compact('targetCategories'))
            ->with('search', $request->search)
            ->with('status', $request->status);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('configuration.target-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:target_categories,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        TargetCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('target-categories.index')
            ->with('success', 'تم إنشاء الفئة المستهدفة بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(TargetCategory $targetCategory)
    {
        return view('configuration.target-categories.show', compact('targetCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TargetCategory $targetCategory)
    {
        return view('configuration.target-categories.edit', compact('targetCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TargetCategory $targetCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:target_categories,name,'.$targetCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $targetCategory->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('target-categories.index')
            ->with('success', 'تم تحديث الفئة المستهدفة بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TargetCategory $targetCategory)
    {
        // Check if there are projects using this target category
        if ($targetCategory->projects()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف هذه الفئة المستهدفة لأنها مرتبطة بمشاريع موجودة');
        }

        $targetCategory->delete();

        return redirect()->route('target-categories.index')
            ->with('success', 'تم حذف الفئة المستهدفة بنجاح');
    }
}
