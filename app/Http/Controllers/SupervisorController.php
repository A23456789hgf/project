<?php

namespace App\Http\Controllers;

use App\Models\Supervisor;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    // GET - عرض الصفحة مع جلب البيانات
    public function index()
    {
        $supervisors = Supervisor::all();

        return view('configuration.supervisors.index', compact('supervisors'));
    }

    // GET - عرض صفحة إنشاء جهة جديدة
    public function create()
    {
        $supervisors = Supervisor::all();

        return view('configuration.supervisors.create', compact('supervisors'));
    }

    // POST - إضافة سجل جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:supervisors,name',
        ]);

        Supervisor::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return redirect()->route('supervisors.index')->with('success', 'تم إضافة الجهة بنجاح');
    }

    // GET - عرض صفحة التعديل
    public function edit(Supervisor $supervisor)
    {
        return view('configuration.supervisors.edit', compact('supervisor'));
    }

    // PUT/PATCH - تحديث سجل موجود
    public function update(Request $request, Supervisor $supervisor)
    {
        $request->validate([
            'name' => 'required|unique:supervisors,name,'.$supervisor->id,
        ]);

        $supervisor->update([
            'name' => $request->name,
        ]);

        return redirect()->route('supervisors.index')->with('success', 'تم تحديث الجهة بنجاح');
    }

    // PATCH - تبديل حالة التفعيل
    public function toggleStatus(Supervisor $supervisor)
    {
        $supervisor->is_active = ! $supervisor->is_active;
        $supervisor->save();

        return redirect()->route('supervisors.index')->with('success', 'تم تعديل حالة الجهة');
    }

    // DELETE - حذف السجل
    public function destroy(Supervisor $supervisor)
    {
        $supervisor->delete();

        return redirect()->route('supervisors.index')->with('success', 'تم حذف الجهة');
    }
}
