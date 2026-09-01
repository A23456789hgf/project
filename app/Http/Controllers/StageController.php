<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct()
    {
        // التأكد من وجود المراحل النظامية عند أي تحميل للكونترولر
        $this->ensureSystemStagesExist();
    }

    /**
     * التأكد من وجود المراحل النظامية الأساسية
     */
    protected function ensureSystemStagesExist()
    {
        $systemStages = [
            ['code' => 'association', 'name_ar' => 'الجمعية', 'name_en' => 'Association'],
            ['code' => 'union', 'name_ar' => 'الاتحاد', 'name_en' => 'Union'],
            ['code' => 'committee', 'name_ar' => 'اللجنة', 'name_en' => 'Committee'],
            ['code' => 'implementation', 'name_ar' => 'التنفيذ', 'name_en' => 'Implementation'],
        ];

        foreach ($systemStages as $stageData) {
            Stage::firstOrCreate(
                ['code' => $stageData['code']],
                array_merge($stageData, ['is_system' => true, 'is_active' => true, 'order' => 0])
            );
        }
    }

    /**
     * عرض جميع المراحل على شكل شجرة.
     */
    public function index()
    {
        $stages = Stage::with('children', 'parent')->orderBy('order')->get();

        $totalStages = $stages->count();
        $activeStages = $stages->where('is_active', true)->count();
        $systemStages = $stages->where('is_system', true)->count();
        $stagesWithPath = $stages->whereNotNull('approval_path')->count();

        return view('stages.index', compact('stages', 'totalStages', 'activeStages', 'systemStages', 'stagesWithPath'));
    }

    /**
     * نموذج إضافة مرحلة جديدة.
     */
    public function create()
    {
        $parents = Stage::orderBy('order')->get();

        return view('stages.create', compact('parents'));
    }

    /**
     * حفظ مرحلة جديدة.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:stages,code',
            'name_ar' => 'required',
            'name_en' => 'nullable',
            'description_ar' => 'nullable',
            'description_en' => 'nullable',
            'order' => 'required|integer',
            'type' => 'required',
            'parent_id' => 'nullable|exists:stages,id',
            'is_active' => 'sometimes|boolean',
        ]);

        Stage::create($request->all());

        return redirect()
            ->route('stages.index')
            ->with('success', 'تمت إضافة المرحلة بنجاح');
    }

    /**
     * نموذج تعديل مرحلة.
     */
    public function edit(Stage $stage)
    {
        if ($stage->is_system) {
            return redirect()
                ->route('stages.index')
                ->with('error', 'لا يمكن تعديل المرحلة النظامية.');
        }

        $parents = Stage::where('id', '!=', $stage->id)->orderBy('order')->get();

        return view('stages.edit', compact('stage', 'parents'));
    }

    /**
     * تحديث المرحلة.
     */
    public function update(Request $request, Stage $stage)
    {
        if ($stage->is_system) {
            return redirect()
                ->route('stages.index')
                ->with('error', 'لا يمكن تعديل المرحلة النظامية.');
        }

        $request->validate([
            'code' => 'required|unique:stages,code,'.$stage->id,
            'name_ar' => 'required',
            'name_en' => 'nullable',
            'description_ar' => 'nullable',
            'description_en' => 'nullable',
            'order' => 'required|integer',
            'type' => 'required',
            'parent_id' => 'nullable|exists:stages,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $stage->update($request->all());

        return redirect()
            ->route('stages.index')
            ->with('success', 'تم تحديث المرحلة بنجاح');
    }

    /**
     * عرض مسار الاعتماد الخاص بمرحلة معينة.
     */
    public function showApprovalPath(Stage $stage)
    {
        $nextStages = collect();
        if ($stage->approval_path && is_array($stage->approval_path)) {
            $nextStages = Stage::whereIn('id', $stage->approval_path)->get();
        }

        return view('stages.approval-path', compact('stage', 'nextStages'));
    }

    /**
     * حذف مرحلة (محمي للمراحل النظامية).
     */
    public function destroy(Stage $stage)
    {
        if ($stage->is_system) {
            return redirect()
                ->route('stages.index')
                ->with('error', 'لا يمكن حذف المرحلة النظامية.');
        }

        $stage->delete();

        return redirect()
            ->route('stages.index')
            ->with('success', 'تم حذف المرحلة بنجاح');
    }
}
