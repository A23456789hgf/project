<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;

class TaskActivityController extends Controller
{
    /**
     * Display a listing of the task activities.
     */
    public function index(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $activities = $task->activities()
            ->with(['causer'])
            ->latest()
            ->get();

        return response()->json($activities);
    }
}
