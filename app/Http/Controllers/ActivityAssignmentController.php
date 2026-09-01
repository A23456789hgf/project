<?php

namespace App\Http\Controllers;

use App\Models\ActivityAssignment;
use App\Models\ExecutiveActivity;
use App\Models\ExecutiveActivityAction;
use App\Models\PreliminaryActivity;
use App\Models\PreliminaryProcedure;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ActivityAssignedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActivityAssignmentController extends Controller
{
    /**
     * Store a new activity or action assignment.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('execution.assign');

        $validated = $request->validate([
            'assignable_type' => 'required|string|in:executive_activity,preliminary_activity,executive_action,preliminary_procedure',
            'assignable_id' => 'required|integer',
            'assigned_to' => 'required',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $assignedToInput = $request->input('assigned_to');
        $assignedToIds = is_array($assignedToInput) ? $assignedToInput : [$assignedToInput];

        if (empty($assignedToIds)) {
            return response()->json([
                'success' => false,
                'message' => 'يجب اختيار مستخدم واحد على الأقل.',
            ], 422);
        }

        $modelMap = [
            'executive_activity' => ExecutiveActivity::class,
            'preliminary_activity' => PreliminaryActivity::class,
            'executive_action' => ExecutiveActivityAction::class,
            'preliminary_procedure' => PreliminaryProcedure::class,
        ];

        $modelClass = $modelMap[$validated['assignable_type']];
        $assignable = $modelClass::findOrFail($validated['assignable_id']);

        $savedAssignments = [];
        $errors = [];

        foreach ($assignedToIds as $userId) {
            $user = User::where('id', $userId)->where('status', 'Active')->first();
            if (! $user) {
                $errors[] = "المستخدم ذو المعرف {$userId} غير موجود أو غير نشط.";

                continue;
            }

            // Validate that user is in the same scope as the project
            $userQuery = User::where('id', $userId);
            if ($project->internal_entity_id) {
                $userQuery->where('entity_id', $project->internal_entity_id);
            } else {
                $govIds = \DB::table('project_locations')->where('project_id', $project->id)->pluck('governorate_id')->filter()->toArray();
                $dirIds = \DB::table('project_locations')->where('project_id', $project->id)->pluck('directorate_id')->filter()->toArray();
                if (! empty($govIds) || ! empty($dirIds)) {
                    $userQuery->where(function ($q) use ($govIds, $dirIds) {
                        if (! empty($govIds)) {
                            $q->orWhereIn('governorate_id', $govIds);
                        }
                        if (! empty($dirIds)) {
                            $q->orWhereIn('directorate_id', $dirIds);
                        }
                    });
                }
            }
            if (! $userQuery->exists()) {
                $errors[] = "المستخدم ({$user->name}) ليس في نفس نطاق هذا المشروع.";

                continue;
            }

            // Check if assignment already exists
            $exists = ActivityAssignment::where('project_id', $project->id)
                ->where('assignable_type', $modelClass)
                ->where('assignable_id', $validated['assignable_id'])
                ->where('assigned_to', $userId)
                ->exists();

            if ($exists) {
                $errors[] = "المستخدم ({$user->name}) مكلف بالفعل بهذه المهمة.";

                continue;
            }

            $assignment = ActivityAssignment::create([
                'project_id' => $project->id,
                'assignable_type' => $modelClass,
                'assignable_id' => $validated['assignable_id'],
                'assigned_to' => $userId,
                'assigned_by' => auth()->id(),
                'notes' => $validated['notes'],
                'due_date' => $validated['due_date'],
                'status' => 'active',
            ]);

            Cache::forget("my_assignments_{$userId}");

            $activityType = null;
            $activityId = null;
            $procedureId = null;
            $executiveActionId = null;

            if ($validated['assignable_type'] === 'executive_activity') {
                $activityType = 'executive';
                $activityId = $assignable->id;
            } elseif ($validated['assignable_type'] === 'preliminary_activity') {
                $activityType = 'preliminary';
                $activityId = $assignable->id;
            } elseif ($validated['assignable_type'] === 'executive_action') {
                $activityType = 'executive';
                $activityId = $assignable->executive_activity_id;
                $procedureId = $assignable->id;
                $executiveActionId = $assignable->id;
            } elseif ($validated['assignable_type'] === 'preliminary_procedure') {
                $activityType = 'preliminary';
                $activityId = $assignable->activity_id;
                $procedureId = $assignable->id;
            }

            // Create a corresponding Task
            $title = '';
            if ($validated['assignable_type'] === 'executive_activity' || $validated['assignable_type'] === 'preliminary_activity') {
                $title = $assignable->name;
            } else {
                $title = $assignable->action ?? $assignable->procedure_name;
            }

            Task::create([
                'project_id' => $project->id,
                'title' => $title,
                'description' => $validated['notes'],
                'status' => 'todo',
                'priority' => 'medium',
                'assigned_to' => $userId,
                'created_by' => auth()->id(),
                'due_date' => $validated['due_date'],
                'is_within_activities' => true,
                'activity_type' => $activityType,
                'activity_id' => $activityId,
                'procedure_id' => $procedureId,
                'executive_activity_action_id' => $executiveActionId,
            ]);

            $assignment->load('assignedTo');
            $savedAssignments[] = [
                'id' => $assignment->id,
                'user_name' => $assignment->assignedTo->name,
                'due_date' => $assignment->due_date ? $assignment->due_date->format('Y-m-d') : null,
                'notes' => $assignment->notes,
            ];

            // Formulate the notification message
            $projectName = $project->project_name;
            $taskName = '';
            $typeLabel = '';

            if ($validated['assignable_type'] === 'executive_activity' || $validated['assignable_type'] === 'preliminary_activity') {
                $taskName = $assignable->name;
                $typeLabel = 'النشاط';
            } else {
                $taskName = $assignable->action ?? $assignable->procedure_name;
                $typeLabel = 'الإجراء';
            }

            $message = "تم تكليفك بـ {$typeLabel} ({$taskName}) في مشروع ({$projectName})";
            $user->notify(new ActivityAssignedNotification($assignment, $message));
        }

        if (empty($savedAssignments)) {
            return response()->json([
                'success' => false,
                'message' => implode('<br>', $errors),
            ], 422);
        }

        $message = 'تم تكليف المستخدمين بنجاح.';
        if (! empty($errors)) {
            $message = 'تم تكليف بعض المستخدمين بنجاح، مع وجود تنبيهات:<br>'.implode('<br>', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'assignments' => $savedAssignments,
        ]);
    }

    /**
     * Remove an assignment.
     */
    public function destroy(ActivityAssignment $assignment): JsonResponse
    {
        $this->authorize('execution.assign');

        $assignable = $assignment->assignable;
        $activityType = null;
        $activityId = null;
        $procedureId = null;
        $executiveActionId = null;

        if ($assignment->assignable_type === ExecutiveActivity::class) {
            $activityType = 'executive';
            $activityId = $assignment->assignable_id;
        } elseif ($assignment->assignable_type === PreliminaryActivity::class) {
            $activityType = 'preliminary';
            $activityId = $assignment->assignable_id;
        } elseif ($assignment->assignable_type === ExecutiveActivityAction::class) {
            $activityType = 'executive';
            if ($assignable) {
                $activityId = $assignable->executive_activity_id;
            }
            $procedureId = $assignment->assignable_id;
            $executiveActionId = $assignment->assignable_id;
        } elseif ($assignment->assignable_type === PreliminaryProcedure::class) {
            $activityType = 'preliminary';
            if ($assignable) {
                $activityId = $assignable->activity_id;
            }
            $procedureId = $assignment->assignable_id;
        }

        Task::where('project_id', $assignment->project_id)
            ->where('assigned_to', $assignment->assigned_to)
            ->where('is_within_activities', true)
            ->where('activity_type', $activityType)
            ->where('activity_id', $activityId)
            ->where('procedure_id', $procedureId)
            ->where('executive_activity_action_id', $executiveActionId)
            ->delete();

        $assignment->delete();

        Cache::forget("my_assignments_{$assignment->assigned_to}");

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التكليف بنجاح.',
        ]);
    }

    /**
     * Fetch users for Select2 / dropdown.
     */
    public function getUsers(Request $request): JsonResponse
    {
        $search = $request->get('q');
        $projectId = $request->get('project_id');
        $query = User::where('status', 'Active');

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                if ($project->internal_entity_id) {
                    $query->where('entity_id', $project->internal_entity_id);
                } else {
                    $govIds = \DB::table('project_locations')->where('project_id', $projectId)->pluck('governorate_id')->filter()->toArray();
                    $dirIds = \DB::table('project_locations')->where('project_id', $projectId)->pluck('directorate_id')->filter()->toArray();
                    if (! empty($govIds) || ! empty($dirIds)) {
                        $query->where(function ($q) use ($govIds, $dirIds) {
                            if (! empty($govIds)) {
                                $q->orWhereIn('governorate_id', $govIds);
                            }
                            if (! empty($dirIds)) {
                                $q->orWhereIn('directorate_id', $dirIds);
                            }
                        });
                    }
                }
            }
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $users = $query->orderBy('name')->limit(20)->get(['id', 'name']);

        return response()->json($users);
    }

    /**
     * Get active assignments for the currently logged-in user.
     */
    public function myAssignments(): JsonResponse
    {
        $user = auth()->user();

        $formatted = Cache::remember("my_assignments_{$user->id}", 300, function () use ($user) {
            $assignments = ActivityAssignment::where('assigned_to', $user->id)
                ->where('status', 'active')
                ->with([
                    'project',
                    'assignable' => function ($morphTo) {
                        $morphTo->morphWith([
                            PreliminaryProcedure::class => ['executions'],
                            ExecutiveActivityAction::class => ['executions'],
                            PreliminaryActivity::class => ['procedures.executions'],
                            ExecutiveActivity::class => ['actions.executions'],
                        ]);
                    },
                ])
                ->latest()
                ->get();

            $formattedArray = [];
            foreach ($assignments as $assignment) {
                $assignable = $assignment->assignable;
                if (! $assignable) {
                    continue;
                }

                // Check completion dynamically using pre-loaded relationships and Collection methods
                $isCompleted = false;
                if ($assignment->assignable_type === PreliminaryProcedure::class) {
                    $isCompleted = $assignable->executions->sum('completion_percentage') >= 100;
                } elseif ($assignment->assignable_type === ExecutiveActivityAction::class) {
                    $isCompleted = $assignable->executions->sum('completion_percentage') >= 100;
                } elseif ($assignment->assignable_type === PreliminaryActivity::class) {
                    $isCompleted = $assignable->procedures->every(function ($proc) {
                        return $proc->executions->sum('completion_percentage') >= 100;
                    });
                } elseif ($assignment->assignable_type === ExecutiveActivity::class) {
                    $isCompleted = $assignable->actions->every(function ($action) {
                        return $action->executions->sum('completion_percentage') >= 100;
                    });
                }

                if ($isCompleted) {
                    // Update status in database to completed so it does not load next time
                    $assignment->update(['status' => 'completed']);

                    // Also update the synced task
                    $activityType = null;
                    $activityId = null;
                    $procedureId = null;
                    $executiveActionId = null;

                    if ($assignment->assignable_type === ExecutiveActivity::class) {
                        $activityType = 'executive';
                        $activityId = $assignment->assignable_id;
                    } elseif ($assignment->assignable_type === PreliminaryActivity::class) {
                        $activityType = 'preliminary';
                        $activityId = $assignment->assignable_id;
                    } elseif ($assignment->assignable_type === ExecutiveActivityAction::class) {
                        $activityType = 'executive';
                        $activityId = $assignable->executive_activity_id;
                        $procedureId = $assignment->assignable_id;
                        $executiveActionId = $assignment->assignable_id;
                    } elseif ($assignment->assignable_type === PreliminaryProcedure::class) {
                        $activityType = 'preliminary';
                        $activityId = $assignable->activity_id;
                        $procedureId = $assignment->assignable_id;
                    }

                    Task::where('project_id', $assignment->project_id)
                        ->where('assigned_to', $assignment->assigned_to)
                        ->where('is_within_activities', true)
                        ->where('activity_type', $activityType)
                        ->where('activity_id', $activityId)
                        ->where('procedure_id', $procedureId)
                        ->where('executive_activity_action_id', $executiveActionId)
                        ->update(['status' => 'completed', 'completed_at' => now()]);

                    continue;
                }

                $taskName = '';
                $typeLabel = '';

                if ($assignment->assignable_type === ExecutiveActivity::class || $assignment->assignable_type === PreliminaryActivity::class) {
                    $taskName = $assignable->name;
                    $typeLabel = 'نشاط';
                } else {
                    $taskName = $assignable->action ?? $assignable->procedure_name;
                    $typeLabel = 'إجراء';
                }

                // Calculate days remaining
                $daysRemaining = null;
                if ($assignment->due_date) {
                    $daysRemaining = now()->startOfDay()->diffInDays($assignment->due_date, false);
                }

                // Categorize task
                $category = 'current';
                if ($daysRemaining !== null) {
                    if ($daysRemaining < 0) {
                        $category = 'overdue';
                    } elseif ($daysRemaining <= 3) {
                        $category = 'nearing';
                    }
                }

                $formattedArray[] = [
                    'id' => $assignment->id,
                    'project_name' => $assignment->project->project_name ?? 'مشروع غير معروف',
                    'project_id' => $assignment->project_id,
                    'task_name' => $taskName,
                    'type_label' => $typeLabel,
                    'due_date' => $assignment->due_date ? $assignment->due_date->format('Y-m-d') : null,
                    'notes' => $assignment->notes,
                    'days_remaining' => $daysRemaining,
                    'category' => $category,
                ];
            }

            // جلب المهام (Tasks) المسندة للمستخدم من جدول المهام
            $tasks = Task::whereHas('assignees', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->with(['project'])
                ->latest()
                ->get();

            foreach ($tasks as $task) {
                // تجنب التكرار إذا كانت المهمة ناتجة عن ActivityAssignment (لأنها ستكون مكررة إذا تم ربطها)
                // لكن المهام التي تُضاف من tasks controller يتم إضافتها كمهام مباشرة

                $daysRemaining = null;
                if ($task->due_date) {
                    $daysRemaining = now()->startOfDay()->diffInDays($task->due_date, false);
                }

                $category = 'current';
                if ($daysRemaining !== null) {
                    if ($daysRemaining < 0) {
                        $category = 'overdue';
                    } elseif ($daysRemaining <= 3) {
                        $category = 'nearing';
                    }
                }

                $formattedArray[] = [
                    'id' => 'task_'.$task->id,
                    'project_name' => $task->project->project_name ?? 'مهمة عامة / بدون مشروع',
                    'project_id' => $task->project_id,
                    'task_name' => $task->title,
                    'type_label' => 'مهمة إضافية',
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                    'notes' => $task->description,
                    'days_remaining' => $daysRemaining,
                    'category' => $category,
                    'is_standalone_task' => true,
                    'task_id' => $task->id,
                ];
            }

            return $formattedArray;
        });

        return response()->json($formatted);
    }
}
