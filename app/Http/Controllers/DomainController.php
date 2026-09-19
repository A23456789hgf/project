<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    // عرض قائمة المجالات
    public function index()
    {
        $this->authorize('viewAny', Domain::class);
        $query = Domain::query();

        // Apply entity visibility filter
        $query->visibleToUser('domains');

        $domains = $query->orderBy('id')->get();

        return view('configuration.domains.index', compact('domains'));
    }

    // عرض صفحة إنشاء مجال جديد
    public function create()
    {
        $this->authorize('create', Domain::class);

        return view('configuration.domains.create');
    }

    // عرض صفحة تعديل مجال
    public function edit($id)
    {
        $domain = Domain::findOrFail($id);

        return view('configuration.domains.create', compact('domain'));
    }

    // تخزين مجال جديد
    public function store(Request $request)
    {
        $this->authorize('create', Domain::class);
        $validated = $request->validate([
            'name' => 'required|unique:domains,name',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = auth()->user();
        Domain::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
            'created_by_entity' => $user->department ?? $user->entity_id,
            'created_by_user_id' => $user->id,
        ]);

        return redirect()->route('domains.index')
            ->with('success', 'تمت إضافة المجال بنجاح');
    }

    // تحديث بيانات المجال
    public function update(Request $request, $id)
    {
        $this->authorize('update', Domain::class);
        $domain = Domain::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|unique:domains,name,'.$domain->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $domain->name = $validated['name'];
        $domain->is_active = $validated['is_active'] ?? $domain->is_active;
        $domain->save();

        return redirect()->route('domains.index')
            ->with('success', 'تم تحديث المجال بنجاح');
    }

    // حذف المجال
    public function destroy($id)
    {
        $this->authorize('delete', Domain::class);
        $domain = Domain::findOrFail($id);
        $domain->delete();

        return redirect()->route('domains.index')
            ->with('success', 'تم حذف المجال بنجاح');
    }

    // تبديل حالة التفعيل/التعطيل
    public function toggleStatus($id)
    {
        $domain = Domain::findOrFail($id);
        $domain->is_active = ! $domain->is_active;
        $domain->save();

        return redirect()->route('domains.index')
            ->with('success', 'تم تعديل حالة المجال');
    }
    // تبديل حالة التفعيل/التعطيل

}
