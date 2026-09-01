<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDocumentNote;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskDocumentNoteController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly created document note.
     */
    public function store(Request $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $validated['task_id'] = $task->id;
        $validated['created_by'] = auth()->id();

        DB::transaction(function () use ($validated, $task) {
            $note = TaskDocumentNote::create($validated);

            $this->taskService->logActivity($task, 'document_note_added', $note, [
                'note_preview' => mb_substr($note->note, 0, 100),
            ]);
        });

        session()->flash('success', 'تم إضافة ملاحظة المستند بنجاح.');

        return redirect()->back();
    }
}
