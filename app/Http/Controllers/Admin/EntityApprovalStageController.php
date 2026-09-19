<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EntityResponsibilityType;
use App\Enums\UserResponsibilityType;
use App\Http\Controllers\Controller;
use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\ProjectApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EntityApprovalStageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin');
    }

    public function index(Request $request)
    {
        $query = EntityApprovalStage::with(['entity', 'responsibleUser']);

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        $stages = $query->orderBy('entity_id')
            ->orderBy('stage_order')
            ->paginate(20);

        $entities = InternalEntity::orderBy('name')->get();

        return view('admin.entity-stages.index', compact('stages', 'entities'));
    }

    public function create()
    {
        $entities = InternalEntity::orderBy('name')->get();
        $stageTypes = EntityResponsibilityType::orderedCases();

        return view('admin.entity-stages.create', compact('entities', 'stageTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entity_id' => ['required', 'integer', Rule::exists('internal_entities', 'id')],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*' => ['string', Rule::in(array_column(EntityResponsibilityType::cases(), 'value'))],
        ]);

        $entityId = $request->entity_id;
        $selectedStages = $request->stages;

        DB::transaction(function () use ($entityId, $selectedStages) {
            foreach (EntityResponsibilityType::orderedCases() as $stageType) {
                if (in_array($stageType->value, $selectedStages)) {
                    // Create if not exists (prevent duplicates)
                    EntityApprovalStage::firstOrCreate(
                        [
                            'entity_id' => $entityId,
                            'stage' => $stageType->value,
                        ],
                        [
                            'stage_order' => $stageType->stageOrder(),
                            'created_by' => Auth::id(),
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.entity-stages.index')->with('success', 'تم إضافة المراحل للجهة بنجاح.');
    }

    public function edit(InternalEntity $entity)
    {
        $entity->load('approvalStages');
        $stageTypes = EntityResponsibilityType::orderedCases();

        return view('admin.entity-stages.edit', compact('entity', 'stageTypes'));
    }

    public function update(Request $request, InternalEntity $entity)
    {
        $stageTypes = EntityResponsibilityType::orderedCases();

        $rules = [];
        foreach ($stageTypes as $stageType) {
            $key = $stageType->value;
            $rules["stages.{$key}.enabled"] = ['nullable', 'boolean'];
            $rules["stages.{$key}.responsible_user_id"] = [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ];
        }

        $validated = $request->validate($rules);
        $stages = $validated['stages'] ?? [];
        $errors = [];

        DB::transaction(function () use ($entity, $stageTypes, $stages, &$errors) {
            foreach ($stageTypes as $stageType) {
                $key = $stageType->value;
                $isEnabled = ! empty($stages[$key]['enabled']);
                $userId = $stages[$key]['responsible_user_id'] ?? null;

                if (! $isEnabled) {
                    // Stage disabled – check for pending approvals
                    $hasPending = ProjectApproval::where('entity_id', $entity->id)
                        ->where('phase', $stageType->phaseCode())
                        ->where('status', 'Pending')
                        ->exists();

                    if ($hasPending) {
                        $errors[$key] = "لا يمكن حذف مرحلة [{$stageType->label()}] لأن هناك معاملات نشطة تعتمد عليها.";

                        continue;
                    }

                    EntityApprovalStage::where('entity_id', $entity->id)
                        ->where('stage', $key)
                        ->update(['is_active' => false]);

                    continue;
                }

                if ($userId) {
                    $user = User::withoutGlobalScopes()->find($userId);

                    if (! $user || $user->status !== 'Active' || (int) $user->entity_id !== (int) $entity->id) {
                        $errors[$key] = "المستخدم المحدد لمرحلة [{$stageType->label()}] غير صالح أو لا ينتمي للجهة.";

                        continue;
                    }

                    $requiredResponsibility = UserResponsibilityType::forStage($stageType);
                    if ($user->responsibility !== $requiredResponsibility) {
                        $errors[$key] = 'المستخدم المحدد مسؤوليته لا تتوافق مع نوع المرحلة.';

                        continue;
                    }
                }

                EntityApprovalStage::updateOrCreate(
                    [
                        'entity_id' => $entity->id,
                        'stage' => $key,
                    ],
                    [
                        'stage_order' => $stageType->stageOrder(),
                        'is_active' => true,
                        'responsible_user_id' => $userId ?: null,
                        'updated_by' => Auth::id(),
                    ]
                );
            }
        });

        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput()->with('warning', 'تم حفظ بعض المراحل مع وجود أخطاء.');
        }

        return redirect()->route('admin.entity-stages.index')->with('success', 'تم تحديث إعداد مراحل الجهة بنجاح.');
    }

    public function toggleActive(EntityApprovalStage $stage)
    {
        if ($stage->is_active) {
            $stageType = EntityResponsibilityType::from($stage->stage);
            $hasPending = ProjectApproval::where('entity_id', $stage->entity_id)
                ->where('phase', $stageType->phaseCode())
                ->where('status', 'Pending')
                ->exists();

            if ($hasPending) {
                return back()->with('error', 'لا يمكن إيقاف هذه المرحلة لوجود معاملات نشطة تعتمد عليها.');
            }
        }

        $stage->update(['is_active' => ! $stage->is_active]);

        return back()->with('success', 'تم تغيير حالة المرحلة بنجاح.');
    }

    public function destroy(EntityApprovalStage $stage)
    {
        // Guard check
        $stageType = EntityResponsibilityType::from($stage->stage);
        $hasPending = ProjectApproval::where('entity_id', $stage->entity_id)
            ->where('phase', $stageType->phaseCode())
            ->where('status', 'Pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'لا يمكن حذف هذه المرحلة لوجود معاملات نشطة تعتمد عليها.');
        }

        $stage->delete();

        return back()->with('success', 'تم حذف المرحلة بنجاح.');
    }

    public function eligibleUsers(Request $request)
    {
        $request->validate([
            'entity_id' => ['required', 'integer', Rule::exists('internal_entities', 'id')],
            'stage' => ['required', 'string', Rule::in(array_column(EntityResponsibilityType::cases(), 'value'))],
        ]);

        $stageType = EntityResponsibilityType::from($request->stage);
        $requiredResp = UserResponsibilityType::forStage($stageType);

        $users = User::withoutGlobalScopes()
            ->where('entity_id', $request->entity_id)
            ->where('status', 'Active')
            ->where('responsibility', $requiredResp->value)
            ->orderBy('name')
            ->get(['id', 'name', 'user_id']);

        return response()->json($users);
    }
}
