<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_stop_and_resume_task()
    {
        $user = User::where('username', 'root')->first() ?? User::factory()->create(['is_admin' => true]);

        $project = Project::create([
            'project_name' => 'مشروع تجريبي',
            'form_number' => 'PRJ-TEST-001',
            'created_by_user_id' => $user->id,
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'title' => 'مهمة تجريبية للاختبار',
            'status' => 'todo',
            'created_by' => $user->id,
        ]);

        $assignee = User::factory()->create();
        $task->assignees()->attach($assignee->id);

        // Act 1: Stop task
        $response = $this->actingAs($user)->postJson(route('projects.tasks.stop', [$project->id, $task->id]), [
            'reason' => 'إيقاف مؤقت للصيانة',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'new_status' => 'cancelled',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'cancelled',
        ]);

        // Act 2: Resume task
        $resumeResponse = $this->actingAs($user)->postJson(route('projects.tasks.stop', [$project->id, $task->id]), [
            'reason' => 'استئناف العمل',
        ]);

        $resumeResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'new_status' => 'todo',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'todo',
        ]);
    }
}
