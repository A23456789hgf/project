<?php

namespace App\Http\Controllers;

use App\Models\InternalEntity;
use App\Models\Project;
use App\Models\RequestDescend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestDescendController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:requests_descend.view')->only(['index', 'show']);
        $this->middleware('can:requests_descend.create')->only(['create', 'store']);
        $this->middleware('can:requests_descend.edit')->only(['edit', 'update']);
        $this->middleware('can:requests_descend.delete')->only(['destroy']);
        $this->middleware('can:requests_descend.financial')->only([
            'financial',
            'updateFinancialStatus',
            'storeMember',
            'updateMember',
            'destroyMember',
        ]);
    }

    /**
     * الحصول على جميع معرفات الجهات ضمن نطاق المستخدم
     */
    private function getEntityScopeIds()
    {
        $entityIdsByEnt = InternalEntity::getAllChildrenIds(auth()->user()->administrative_scope_id);
        $entityIdsByGovAndDist = auth()->user()->geographicScopes;
        $entityIdsByGeo = [];

        foreach ($entityIdsByGovAndDist as $scope) {
            if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                $ids = InternalEntity::getAllByGovernorate($scope->governorate_id);
                $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
            } elseif (! empty($scope->directorate_id)) {
                $ids = InternalEntity::getAllByDirectorate($scope->directorate_id);
                $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
            }
        }

        $entityIds = array_merge($entityIdsByEnt ?? [], $entityIdsByGeo ?? []);
        $entityIds = array_unique($entityIds);

        return $entityIds;
    }

    /**
     * الحصول على جميع الجهات (كائنات كاملة) ضمن نطاق المستخدم
     */
    private function getFilteredEntities()
    {
        $entityIds = $this->getEntityScopeIds();

        if (empty($entityIds)) {
            return collect();
        }

        return InternalEntity::whereIn('id', $entityIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * الحصول على الجهات بصيغة مناسبة للقوائم المنسدلة (id => name)
     */
    private function getFilteredEntitiesForDropdown()
    {
        $entities = $this->getFilteredEntities();

        return $entities->pluck('name', 'id');
    }

    /**
     * التحقق من أن الجهة المحددة ضمن نطاق المستخدم
     */
    private function validateEntityScope($entityId)
    {
        $entityIds = $this->getEntityScopeIds();

        return in_array($entityId, $entityIds);
    }

    /**
     * الحصول على قاعدة التحقق لحقل entity_id
     */
    private function getEntityValidationRule()
    {
        $entityIds = $this->getEntityScopeIds();

        if (empty($entityIds)) {
            return 'prohibited';
        }

        return 'required|in:'.implode(',', $entityIds);
    }

    public function index(Request $request)
    {
        $query = RequestDescend::with('project');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('needs', 'like', "%{$search}%")
                ->orWhere('reason_for_drop', 'like', "%{$search}%")
                ->orWhere('objective_of_drop', 'like', "%{$search}%");
        }

        $perPage = $request->get('per_page', 15);
        if (! in_array((int) $perPage, [15, 50, 100, 500])) {
            $perPage = 15;
        }

        $requests = $query->latest()->paginate($perPage)->withQueryString();

        return view('requests_descend.index', compact('requests'));
    }

    public function create()
    {
        $projects = Project::select('id', 'project_name')->orderBy('project_name')->get();
        $entities = $this->getFilteredEntities(); // استدعاء الجهات ضمن النطاق

        return view('requests_descend.create', compact('projects', 'entities'));
    }

    public function store(Request $request)
    {
        $entityRule = $this->getEntityValidationRule();

        $request->validate([
            'is_linked_to_project' => 'boolean',
            'project_id' => 'required_if:is_linked_to_project,1|nullable|exists:projects,id',
            'needs' => 'nullable|string',
            'reason_for_drop' => 'nullable|string',
            'objective_of_drop' => 'nullable|string',
            'priority' => 'nullable|in:Important,Urgent',
            'activities.*.activity' => 'nullable|string',
            'activities.*.expected_output' => 'nullable|string',
            'activities.*.from_date' => 'nullable|date',
            'activities.*.to_date' => 'nullable|date|after_or_equal:activities.*.from_date',
            'members.*.name' => 'nullable|string',
            'members.*.entity_id' => $entityRule,
            'members.*.work' => 'nullable|string',
            'members.*.daily_amount' => 'nullable|numeric|min:0',
            'members.*.duration' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $requestDescend = RequestDescend::create($request->only([
                'is_linked_to_project',
                'project_id',
                'needs',
                'reason_for_drop',
                'objective_of_drop',
                'priority',
            ]));

            if ($request->has('activities')) {
                foreach ($request->activities as $activityData) {
                    if (! empty($activityData['activity'])) {
                        $requestDescend->activities()->create($activityData);
                    }
                }
            }

            if ($request->has('members')) {
                foreach ($request->members as $memberData) {
                    if (! empty($memberData['name'])) {
                        // التحقق من صحة الجهة
                        if (isset($memberData['entity_id']) && ! $this->validateEntityScope($memberData['entity_id'])) {
                            throw new \Exception('لا يمكنك استخدام هذه الجهة - خارج النطاق المسموح.');
                        }

                        $memberData['daily_amount'] = $memberData['daily_amount'] ?? 0;
                        $memberData['duration'] = $memberData['duration'] ?? 0;
                        $memberData['total'] = $memberData['daily_amount'] * $memberData['duration'];
                        $requestDescend->members()->create($memberData);
                    }
                }
            }

            DB::commit();
            session()->flash('success', 'تم إنشاء طلب الإسناد بنجاح.');

            return redirect()->route('requests_descend.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'فشل إنشاء طلب الإسناد: '.$e->getMessage())->withInput();

            return back();
        }
    }

    public function show($id)
    {
        $requestDescend = RequestDescend::with(['project', 'activities', 'members.entity'])->findOrFail($id);

        return view('requests_descend.show', compact('requestDescend'));
    }

    public function edit($id)
    {
        $requestDescend = RequestDescend::with(['activities', 'members'])->findOrFail($id);
        $projects = Project::select('id', 'project_name')->orderBy('project_name')->get();
        $entities = $this->getFilteredEntities(); // استدعاء الجهات ضمن النطاق

        return view('requests_descend.edit', compact('requestDescend', 'projects', 'entities'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'is_linked_to_project' => 'boolean',
            'project_id' => 'required_if:is_linked_to_project,1|nullable|exists:projects,id',
            'needs' => 'nullable|string',
            'reason_for_drop' => 'nullable|string',
            'objective_of_drop' => 'nullable|string',
            'priority' => 'nullable|in:Important,Urgent',
            'activities.*.id' => 'nullable|exists:request_descend_activities,id',
            'activities.*.activity' => 'nullable|string',
            'activities.*.expected_output' => 'nullable|string',
            'activities.*.from_date' => 'nullable|date',
            'activities.*.to_date' => 'nullable|date|after_or_equal:activities.*.from_date',
        ]);

        $requestDescend = RequestDescend::findOrFail($id);

        DB::beginTransaction();
        try {
            $requestDescend->update($request->only([
                'is_linked_to_project',
                'project_id',
                'needs',
                'reason_for_drop',
                'objective_of_drop',
                'priority',
            ]));

            // تحديث الأنشطة
            $existingActivityIds = $requestDescend->activities()->pluck('id')->toArray();
            $submittedActivityIds = [];

            if ($request->has('activities')) {
                foreach ($request->activities as $activityData) {
                    if (! empty($activityData['activity'])) {
                        if (isset($activityData['id']) && in_array($activityData['id'], $existingActivityIds)) {
                            // تحديث نشاط موجود
                            $requestDescend->activities()->where('id', $activityData['id'])->update([
                                'activity' => $activityData['activity'],
                                'expected_output' => $activityData['expected_output'],
                                'from_date' => $activityData['from_date'],
                                'to_date' => $activityData['to_date'],
                            ]);
                            $submittedActivityIds[] = $activityData['id'];
                        } else {
                            // إنشاء نشاط جديد
                            $newActivity = $requestDescend->activities()->create($activityData);
                            $submittedActivityIds[] = $newActivity->id;
                        }
                    }
                }
            }

            // حذف الأنشطة التي تمت إزالتها
            $activitiesToDelete = array_diff($existingActivityIds, $submittedActivityIds);
            if (! empty($activitiesToDelete)) {
                $requestDescend->activities()->whereIn('id', $activitiesToDelete)->delete();
            }

            DB::commit();
            session()->flash('success', 'تم تحديث طلب الإسناد بنجاح.');

            return redirect()->route('requests_descend.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'فشل تحديث طلب الإسناد: '.$e->getMessage())->withInput();

            return back();
        }
    }

    public function sendForApproval($id)
    {
        try {
            $requestDescend = RequestDescend::findOrFail($id);
            if ($requestDescend->status !== 'draft') {
                session()->flash('error', 'هذا الطلب ليس مسودة ليتم إرساله للاعتماد.');

                return back();
            }

            $requestDescend->update(['status' => 'pending_approval']);

            session()->flash('success', 'تم إرسال الطلب للاعتماد النهائي بنجاح.');

            return redirect()->route('requests_descend.index');
        } catch (\Exception $e) {
            session()->flash('error', 'فشل إرسال الطلب: '.$e->getMessage());

            return back();
        }
    }

    public function processApproval(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,technical_review,financial_review,return',
            'notes' => 'nullable|string',
        ]);

        try {
            $requestDescend = RequestDescend::findOrFail($id);

            $newStatus = match ($request->action) {
                'approve' => 'approved',
                'technical_review' => 'under_technical_review',
                'financial_review' => 'under_financial_review',
                'return' => 'returned',
                default => $requestDescend->status,
            };

            $requestDescend->update([
                'status' => $newStatus,
                'notes' => $request->notes,
            ]);

            session()->flash('success', 'تم تسجيل القرار بنجاح.');

            return back();
        } catch (\Exception $e) {
            session()->flash('error', 'فشل معالجة القرار: '.$e->getMessage());

            return back();
        }
    }

    public function destroy($id)
    {
        try {
            $requestDescend = RequestDescend::findOrFail($id);
            $requestDescend->delete();
            session()->flash('success', 'تم حذف طلب الإسناد بنجاح.');

            return redirect()->route('requests_descend.index');
        } catch (\Exception $e) {
            session()->flash('error', 'فشل حذف طلب الإسناد: '.$e->getMessage());

            return back();
        }
    }

    public function financial($id)
    {
        $requestDescend = RequestDescend::with(['members.entity'])->findOrFail($id);
        $entities = $this->getFilteredEntities(); // استدعاء الجهات ضمن النطاق

        return view('requests_descend.financial', compact('requestDescend', 'entities'));
    }

    public function updateFinancialStatus(Request $request, $id)
    {
        $request->validate([
            'financial_status' => 'required|in:draft,confirmed,approved',
        ]);
        $requestDescend = RequestDescend::findOrFail($id);
        $requestDescend->update(['financial_status' => $request->financial_status]);
        session()->flash('success', 'تم تحديث حالة الملف المالي بنجاح.');

        return back();
    }

    public function storeMember(Request $request, $id)
    {
        $entityRule = $this->getEntityValidationRule();

        $request->validate([
            'name' => 'required|string',
            'entity_id' => $entityRule,
            'work' => 'nullable|string',
            'daily_amount' => 'required|numeric|min:0',
            'duration' => 'required|numeric|min:0',
        ]);

        // التحقق من صحة الجهة
        if (! $this->validateEntityScope($request->entity_id)) {
            session()->flash('error', 'هذه الجهة غير موجودة ضمن نطاق صلاحياتك.');

            return back()->withInput();
        }

        $requestDescend = RequestDescend::findOrFail($id);

        $total = $request->daily_amount * $request->duration;

        $requestDescend->members()->create([
            'name' => $request->name,
            'entity_id' => $request->entity_id,
            'work' => $request->work,
            'daily_amount' => $request->daily_amount,
            'duration' => $request->duration,
            'total' => $total,
        ]);

        session()->flash('success', 'تم إضافة العضو بنجاح.');

        return back();
    }

    public function updateMember(Request $request, $id, $memberId)
    {
        $entityRule = $this->getEntityValidationRule();

        $request->validate([
            'name' => 'required|string',
            'entity_id' => $entityRule,
            'work' => 'nullable|string',
            'daily_amount' => 'required|numeric|min:0',
            'duration' => 'required|numeric|min:0',
        ]);

        // التحقق من صحة الجهة
        if (! $this->validateEntityScope($request->entity_id)) {
            session()->flash('error', 'هذه الجهة غير موجودة ضمن نطاق صلاحياتك.');

            return back()->withInput();
        }

        $requestDescend = RequestDescend::findOrFail($id);
        $member = $requestDescend->members()->findOrFail($memberId);

        $total = $request->daily_amount * $request->duration;

        $member->update([
            'name' => $request->name,
            'entity_id' => $request->entity_id,
            'work' => $request->work,
            'daily_amount' => $request->daily_amount,
            'duration' => $request->duration,
            'total' => $total,
        ]);

        session()->flash('success', 'تم تحديث بيانات العضو بنجاح.');

        return back();
    }

    public function destroyMember($id, $memberId)
    {
        $requestDescend = RequestDescend::findOrFail($id);
        $member = $requestDescend->members()->findOrFail($memberId);
        $member->delete();

        session()->flash('success', 'تم حذف العضو بنجاح.');

        return back();
    }
}
