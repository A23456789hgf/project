<?php

namespace App\Http\Controllers;

use App\Models\Directorate;
use App\Models\Governorate;
use App\Models\ValueChain;
use App\Models\ValueChainMember;
use Illuminate\Http\Request;

class ValueChainMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:value_chain_members.view')->only(['index']);
        $this->middleware('can:value_chain_members.create')->only(['create', 'store']);
        $this->middleware('can:value_chain_members.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:value_chain_members.delete')->only(['destroy']);
    }

    /**
     * عرض جميع الأعضاء
     */
    public function index(Request $request)
    {
        $query = ValueChainMember::with([
            'valueChain',
            'governorate',
            'directorate',
            'createdBy',
        ]);

        // 🔹 فلترة حسب السلسلة
        if ($request->value_chain_id) {
            $query->where('value_chain_id', $request->value_chain_id);
        }

        // 🔹 فلترة حسب الدور
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // 🔹 فلترة حسب المحافظة
        if ($request->governorate_id) {
            $query->where('governorate_id', $request->governorate_id);
        }

        // 🔹 فلترة حسب المديرية
        if ($request->directorate_id) {
            $query->where('directorate_id', $request->directorate_id);
        }

        $members = $query->latest()->paginate(20)
            ->appends($request->all()); // مهم للحفاظ على الفلتر عند التنقل بين الصفحات

        return view('value_chains.value_chain_members.index', [
            'members' => $members,
            'valueChains' => ValueChain::all(),
            'governorates' => Governorate::getCachedAll(),
            'directorates' => $request->governorate_id
                ? Directorate::where('governorate_id', $request->governorate_id)->get()
                : Directorate::getCachedAll(),
            'filters' => $request->all(),
        ]);
    }

    /**
     * عرض صفحة إضافة عضو جديد
     */
    public function create()
    {
        $valueChains = ValueChain::all();
        $governorates = Governorate::getCachedAll();
        $directorates = Directorate::getCachedAll();

        return view('value_chains.value_chain_members.create', compact(
            'valueChains',
            'governorates',
            'directorates'
        ));
    }

    /**
     * حفظ عضو جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'value_chain_id' => 'nullable|exists:value_chains,id',
            'governorate_id' => 'nullable|exists:governorates,id',
            'directorate_id' => 'nullable|exists:directorates,id',
            'phone' => 'nullable|string|max:20',
        ]);

        ValueChainMember::create([
            'value_chain_id' => $request->value_chain_id,
            'name' => $request->name,
            'role' => $request->role,
            'governorate_id' => $request->governorate_id,
            'directorate_id' => $request->directorate_id,
            'phone' => $request->phone,
            'is_active' => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('value-chain-members.index')->with('success', 'تم إضافة العضو بنجاح');
    }

    /**
     * عرض بيانات للتعديل
     */
    public function edit($id)
    {
        $member = ValueChainMember::findOrFail($id);

        $valueChains = ValueChain::all();
        $governorates = Governorate::getCachedAll();
        $directorates = Directorate::getCachedAll();

        return view('value_chains.value_chain_members.edit', compact(
            'member',
            'valueChains',
            'governorates',
            'directorates'
        ));
    }

    /**
     * تحديث البيانات
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'value_chain_id' => 'nullable|exists:value_chains,id',
            'governorate_id' => 'nullable|exists:governorates,id',
            'directorate_id' => 'nullable|exists:directorates,id',
            'phone' => 'nullable|string|max:20',
        ]);

        $member = ValueChainMember::findOrFail($id);

        $member->update([
            'value_chain_id' => $request->value_chain_id,
            'name' => $request->name,
            'role' => $request->role,
            'governorate_id' => $request->governorate_id,
            'directorate_id' => $request->directorate_id,
            'phone' => $request->phone,
        ]);

        return redirect()->route('value-chain-members.index')
            ->with('success', 'تم التحديث بنجاح');
    }

    /**
     * حذف عضو
     */
    public function destroy($id)
    {
        $member = ValueChainMember::findOrFail($id);
        $member->delete();

        return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }

    /**
     * تفعيل / تعطيل العضو
     */
    public function toggleStatus($id)
    {
        $member = ValueChainMember::findOrFail($id);

        $member->is_active = ! $member->is_active;
        $member->save();

        return redirect()->back()->with('success', 'تم تغيير الحالة');
    }

    public function getDirectorates($id)
    {
        $directorates = Directorate::where('governorate_id', $id)
            ->orderBy('name')
            ->get();

        return response()->json($directorates);
    }
}
