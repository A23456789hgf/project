<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Priority::class);
        $priorities = Priority::all();

        return view('configuration.priorities.index', compact('priorities'));
    }

    public function create()
    {
        $this->authorize('create', Priority::class);

        return view('configuration.priorities.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Priority::class);
        $request->validate([
            'priority' => 'required|string|max:255',
            'is_enabled' => 'required|boolean',
        ]);

        Priority::create($request->all());

        return redirect()->route('priorities.index')->with('success', 'تمت إضافة الأولوية بنجاح');
    }

    public function edit(Priority $priority)
    {
        $this->authorize('update', Priority::class);

        return view('configuration.priorities.edit', compact('priority'));
    }

    public function update(Request $request, Priority $priority)
    {
        $this->authorize('update', Priority::class);
        $request->validate([
            'priority' => 'required|string|max:255',
            'is_enabled' => 'required|boolean',
        ]);

        $priority->update($request->all());

        return redirect()->route('priorities.index')->with('success', 'تم تحديث الأولوية بنجاح');
    }

    public function destroy(Priority $priority)
    {
        $this->authorize('delete', Priority::class);
        $priority->delete();

        return redirect()->route('priorities.index')->with('success', 'تم حذف الأولوية بنجاح');
    }
}
