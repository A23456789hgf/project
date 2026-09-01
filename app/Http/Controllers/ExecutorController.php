<?php

namespace App\Http\Controllers;

use App\Models\Executor;
use Illuminate\Http\Request;

class ExecutorController extends Controller
{
    public function index(Request $request)
    {
        $query = Executor::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $executors = $query->orderBy('id', 'desc')->paginate($perPage);

        return view('configuration.executors.index', compact('executors'));
    }

    public function create()
    {
        return view('configuration.executors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:executors,name',
        ]);

        Executor::create([
            'name' => $data['name'],
            'is_active' => true,
        ]);

        return redirect()->route('executors.index')->with('success', 'تم إضافة الجهة المنفذة بنجاح.');
    }

    public function edit(Executor $executor)
    {
        return view('configuration.executors.edit', compact('executor'));
    }

    public function update(Request $request, Executor $executor)
    {
        $data = $request->validate([
            'name' => 'required|unique:executors,name,'.$executor->id,
        ]);

        $executor->update($data);

        return redirect()->route('executors.index')->with('success', 'تم تحديث بيانات الجهة المنفذة.');
    }

    public function destroy(Executor $executor)
    {
        $executor->delete();

        return redirect()->route('executors.index')->with('success', 'تم حذف الجهة المنفذة.');
    }

    // لتغيير حالة التفعيل (تعطيل / تفعيل)
    public function toggle(Executor $executor)
    {
        $executor->is_active = ! $executor->is_active;
        $executor->save();

        return redirect()->route('executors.index')->with('success', 'تم تحديث حالة الجهة المنفذة.');
    }
}
