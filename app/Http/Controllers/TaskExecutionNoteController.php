<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskExecutionNote;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskExecutionNoteController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly created execution note.
     */
    public function store(Request $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'note' => 'required|string',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['task_id'] = $task->id;
        $validated['created_by'] = auth()->id();

        DB::transaction(function () use ($validated, $task) {
            $note = TaskExecutionNote::create($validated);

            $properties = [
                'note_preview' => mb_substr($note->note, 0, 100),
            ];

            if ($note->progress_percentage !== null) {
                $properties['progress_percentage'] = $note->progress_percentage;

                // If progress reaches 100%, mark task as completed
                if ($note->progress_percentage === 100 && $task->status !== 'completed') {
                    $originalStatus = $task->status;
                    $task->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    $this->taskService->logActivity($task, 'status_changed', $task, [
                        'old' => ['status' => $originalStatus],
                        'new' => ['status' => 'completed'],
                    ]);
                }
            }

            $this->taskService->logActivity($task, 'execution_note_added', $note, $properties);
        });

        session()->flash('success', 'تم إضافة ملاحظة التنفيذ بنجاح.');

        return redirect()->back();
    }
}
