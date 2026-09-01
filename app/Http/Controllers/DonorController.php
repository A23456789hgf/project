<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use Illuminate\Http\Request;

class DonorController extends Controller
{
    // Index with pagination, search, and sorting
    public function index(Request $request)
    {
        $query = Donor::query();

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Sort
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $donors = $query->paginate($perPage);

        return view('configuration.donors.index', compact('donors'));
    }

    // Create form
    public function create()
    {
        return view('configuration.donors.create');
    }

    // Store new donor
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:donors|max:255']);
        Donor::create($request->all());

        return redirect()->route('donors.index')->with('success', 'تمت الإضافة بنجاح');
    }

    // Edit form
    public function edit(Donor $donor)
    {
        return view('configuration.donors.edit', compact('donor'));
    }

    // Update donor (PUT/PATCH)
    public function update(Request $request, Donor $donor)
    {
        $request->validate(['name' => 'required|unique:donors,name,'.$donor->id.'|max:255']);
        $donor->update($request->all());

        return redirect()->route('donors.index')->with('success', 'تم التحديث بنجاح');
    }

    // Delete donor
    public function destroy(Donor $donor)
    {
        $donor->delete();

        return redirect()->route('donors.index')->with('success', 'تم الحذف بنجاح');
    }
}
