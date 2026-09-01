<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\EmpowermentBeneficiary;
use App\Models\EmpowermentProject;
use App\Models\Governorate;
use Illuminate\Http\Request;

class EmpowermentBeneficiaryController extends Controller
{
    /**
     * عرض كل المستفيدين في النظام (خاص بالقروض)
     * GET /projects/empowerment/beneficiaries
     */
    public function all(Request $request)
    {
        $query = EmpowermentBeneficiary::with(['empowermentProject', 'governorate', 'directorate', 'subArea', 'village'])
            ->orderByDesc('created_at');

        // بحث بالاسم أو رقم الهوية
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%");
            });
        }

        $selectedProject = null;
        $avgLoan = 0;
        $isFull = false;
        $currentCount = null;

        // فلترة بحسب المشروع
        if ($projectId = $request->get('project_id')) {
            $query->where('empowerment_project_id', $projectId);
            $selectedProject = EmpowermentProject::find($projectId);
            if ($selectedProject) {
                $currentCount = EmpowermentBeneficiary::where('empowerment_project_id', $selectedProject->id)->count();
                $isFull = $currentCount >= $selectedProject->number_of_beneficiaries;
                $avgLoan = $selectedProject->number_of_beneficiaries > 0
                    ? ($selectedProject->total_loan_amount / $selectedProject->number_of_beneficiaries)
                    : 0;
            }
        }

        $beneficiaries = $query->paginate(25)->withQueryString();
        $projects = EmpowermentProject::orderBy('project_name')->get();
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();

        return view('projects.empowerment.beneficiaries.all', compact(
            'beneficiaries', 'projects', 'selectedProject', 'avgLoan', 'isFull', 'governorates', 'currentCount'
        ));
    }

    /**
     * عرض قائمة المستفيدين لمشروع تمكين معين
     */
    public function index(EmpowermentProject $empowermentProject)
    {
        $empowermentProject->load('project');

        $beneficiaries = EmpowermentBeneficiary::where('empowerment_project_id', $empowermentProject->id)
            ->with(['governorate', 'directorate', 'subArea', 'village'])
            ->get();

        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();

        // حساب نصيب المستفيد الواحد
        $avgLoan = $empowermentProject->number_of_beneficiaries > 0
            ? ($empowermentProject->total_loan_amount / $empowermentProject->number_of_beneficiaries)
            : 0;

        return view('projects.empowerment.beneficiaries.index', compact(
            'empowermentProject',
            'beneficiaries',
            'governorates',
            'avgLoan'
        ));
    }

    /**
     * حفظ مستفيد جديد
     */
    public function store(Request $request, EmpowermentProject $empowermentProject)
    {
        // التحقق من الحد الأقصى
        $currentCount = EmpowermentBeneficiary::where('empowerment_project_id', $empowermentProject->id)->count();
        if ($currentCount >= $empowermentProject->number_of_beneficiaries) {
            return back()->with('error', 'عذراً، تم الوصول للحد الأقصى لعدد المستفيدين المسموح به لهذا المشروع.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'id_number' => 'required|string|max:50',
            'governorate_id' => 'required|exists:governorates,id',
            'directorate_id' => 'required|exists:directorates,id',
            'sub_area_id' => 'nullable|exists:sub_areas,id',
            'village_id' => 'nullable|exists:villages,id',
            'repayment_method' => 'required|in:monthly,annually,seasonally',
            'installments_count' => 'required|integer|min:1',
        ]);

        // جلب مبلغ القرض تلقائياً من المشروع
        $avgLoan = $empowermentProject->number_of_beneficiaries > 0
            ? ($empowermentProject->total_loan_amount / $empowermentProject->number_of_beneficiaries)
            : 0;

        EmpowermentBeneficiary::create([
            'empowerment_project_id' => $empowermentProject->id,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'id_number' => $validated['id_number'],
            'governorate_id' => $validated['governorate_id'],
            'directorate_id' => $validated['directorate_id'],
            'sub_area_id' => $validated['sub_area_id'] ?? null,
            'village_id' => $validated['village_id'] ?? null,
            'loan_amount' => $avgLoan,
            'repayment_method' => $validated['repayment_method'],
            'installments_count' => $validated['installments_count'],
        ]);

        return back()->with('success', 'تم إضافة المستفيد بنجاح');
    }

    /**
     * حذف مستفيد
     */
    public function destroy(EmpowermentProject $empowermentProject, EmpowermentBeneficiary $beneficiary)
    {
        if ($beneficiary->empowerment_project_id !== $empowermentProject->id) {
            abort(403);
        }

        $beneficiary->delete();

        return back()->with('success', 'تم حذف المستفيد بنجاح');
    }
}
