<?php

namespace App\Http\Controllers;

use App\Models\FinancialItem;
use App\Traits\HasApprovalWorkflow;
use Illuminate\Http\Request;

class FinancialItemController extends Controller
{
    use HasApprovalWorkflow;

    /**
     * عرض جميع البنود المالية
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', FinancialItem::class);
        $query = FinancialItem::query();
        $query = $this->applyStatusFilter($query, $request);
        $items = $query->paginate(20)->withQueryString();

        return view('configuration.financialitems.index', compact('items'));
    }

    public function approve(FinancialItem $financialItem)
    {
        return $this->approveModel($financialItem, 'financial-items.index');
    }

    public function reject(FinancialItem $financialItem)
    {
        return $this->rejectModel($financialItem, 'financial-items.index');
    }

    /**
     * عرض نموذج إنشاء بند مالي جديد
     */
    public function create()
    {
        $this->authorize('create', FinancialItem::class);

        return view('configuration.financialitems.create');
    }

    /**
     * حفظ بند مالي جديد
     */
    public function store(Request $request)
    {
        $this->authorize('create', FinancialItem::class);
        $request->validate([
            'code' => 'required|string|unique:financial_items,code',
            'name' => 'required|string',
        ]);

        FinancialItem::create([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return redirect()->route('financial-items.index')
            ->with('success', 'تمت إضافة البند المالي بنجاح');
    }

    /**
     * عرض نموذج تعديل بند مالي
     */
    public function edit(FinancialItem $financialItem)
    {
        $this->authorize('update', FinancialItem::class);

        return view(
            'configuration.financialitems.edit',
            compact('financialItem')
        );
    }

    /**
     * تحديث بيانات بند مالي
     */
    public function update(Request $request, FinancialItem $financialItem)
    {
        $this->authorize('update', FinancialItem::class);
        $request->validate([
            'code' => 'required|string|unique:financial_items,code,'.$financialItem->id,
            'name' => 'required|string',
        ]);

        $financialItem->update([
            'code' => $request->code,
            'name' => $request->name,
        ]);

        return redirect()->route('financial-items.index')
            ->with('success', 'تم تحديث البند المالي بنجاح');
    }

    /**
     * حذف بند مالي
     */
    public function destroy(FinancialItem $financialItem)
    {
        $this->authorize('delete', FinancialItem::class);
        $financialItem->delete();

        return redirect()->route('financial-items.index')
            ->with('success', 'تم حذف البند المالي بنجاح');
    }
}
