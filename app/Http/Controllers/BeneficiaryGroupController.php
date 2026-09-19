<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryGroup;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Http\Request;

class BeneficiaryGroupController extends Controller
{
    use HasApprovalWorkflow;

    /**
     * عرض قائمة الفئات المستفيدة
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', BeneficiaryGroup::class);
        $perPage = $request->query('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500]) ? $perPage : 20;

        $query = BeneficiaryGroup::query();
        $query = $this->applyStatusFilter($query, $request);
        $beneficiaryGroups = $query->paginate($perPage)->withQueryString();

        return view('configuration.beneficiary_group.index', compact('beneficiaryGroups'));
    }

    public function approve(BeneficiaryGroup $beneficiaryGroup)
    {
        return $this->approveModel($beneficiaryGroup, 'beneficiary-groups.index');
    }

    public function reject(BeneficiaryGroup $beneficiaryGroup)
    {
        return $this->rejectModel($beneficiaryGroup, 'beneficiary-groups.index');
    }

    /**
     * عرض نموذج إنشاء فئة مستفيدة جديدة
     */
    public function create()
    {
        $this->authorize('create', BeneficiaryGroup::class);

        return view('configuration.beneficiary_group.create');
    }

    /**
     * حفظ الفئة المستفيدة الجديدة
     */
    public function store(Request $request)
    {
        $this->authorize('create', BeneficiaryGroup::class);
        // التحقق من البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_groups,name',
        ]);

        // إنشاء الفئة المستفيدة
        BeneficiaryGroup::create($validated);

        return redirect()->route('beneficiary-groups.index')
            ->with('success', 'تم إضافة الفئة المستفيدة بنجاح');
    }

    /**
     * عرض نموذج تعديل الفئة المستفيدة
     */
    public function edit($id)
    {
        $this->authorize('update', BeneficiaryGroup::class);
        $beneficiaryGroup = BeneficiaryGroup::findOrFail($id);

        return view('configuration.beneficiary_group.edit', compact('beneficiaryGroup'));
    }

    /**
     * تحديث الفئة المستفيدة
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update', BeneficiaryGroup::class);
        $beneficiaryGroup = BeneficiaryGroup::findOrFail($id);

        // التحقق من البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:beneficiary_groups,name,'.$beneficiaryGroup->id,
        ]);

        // تحديث الفئة المستفيدة
        $beneficiaryGroup->update($validated);

        return redirect()->route('beneficiary-groups.index')
            ->with('success', 'تم تحديث الفئة المستفيدة بنجاح');
    }

    /**
     * حذف الفئة المستفيدة
     */
    public function destroy($id)
    {
        $this->authorize('delete', BeneficiaryGroup::class);
        $beneficiaryGroup = BeneficiaryGroup::findOrFail($id);
        $beneficiaryGroup->delete();

        return redirect()->route('beneficiary-groups.index')
            ->with('success', 'تم حذف الفئة المستفيدة بنجاح');
    }

    /**
     * عرض بيانات فئة مستفيدة محددة
     */
    public function show($id)
    {
        $this->authorize('view', BeneficiaryGroup::class);
        $beneficiaryGroup = BeneficiaryGroup::findOrFail($id);

        return view('configuration.beneficiary_group.show', compact('beneficiaryGroup'));
    }
}
