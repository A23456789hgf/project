<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskAttachmentController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly uploaded attachment.
     */
    public function store(Request $request, Project $project, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'attachment' => 'required|file|max:10240', // Max 10MB
        ]);

        DB::transaction(function () use ($request, $task) {
            $fileData = $this->taskService->storeAttachment($request->file('attachment'));

            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => auth()->id(),
                'file_name' => $fileData['file_name'],
                'file_path' => $fileData['file_path'],
                'file_size' => $fileData['file_size'],
                'file_type' => $fileData['file_type'],
            ]);

            $this->taskService->logActivity($task, 'attachment_uploaded', $attachment, [
                'file_name' => $attachment->file_name,
                'file_size' => $attachment->file_size,
            ]);
        });

        session()->flash('success', 'تم رفع المرفق بنجاح.');

        return redirect()->back();
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Project $project, Task $task, TaskAttachment $attachment)
    {
        $this->authorize('update', $task);

        DB::transaction(function () use ($task, $attachment) {
            $this->taskService->logActivity($task, 'attachment_deleted', $attachment, [
                'file_name' => $attachment->file_name,
            ]);
            $attachment->delete();
        });

        session()->flash('success', 'تم حذف المرفق بنجاح.');

        return redirect()->back();
    }
}
