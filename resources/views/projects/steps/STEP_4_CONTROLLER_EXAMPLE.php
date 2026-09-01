<?php

/**
 * STEP 4 Controller Example
 * Preliminary Activities - updateOrCreate Pattern
 *
 * This file demonstrates how to handle Step 4 form submission
 * with updateOrCreate logic for preliminary activities.
 */

namespace App\Http\Controllers;

use App\Models\PreliminaryActivity;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectControllerStep4Example extends Controller
{
    /**
     * STEP 4: Handle Preliminary Activities Form Submission
     *
     * Flow:
     * 1. Get project_id from request (set by Step 1)
     * 2. Find existing project
     * 3. Process preliminary activities array
     * 4. For each activity:
     *    - If it has ID: updateOrCreate will UPDATE it
     *    - If it has no ID: updateOrCreate will CREATE new one
     * 5. Delete any marked for deletion
     * 6. Update project's last_saved_step
     * 7. Return success response
     */
    public function storeStep4(Request $request)
    {
        DB::beginTransaction();
        try {
            // Get project_id from form (stored from Step 1)
            $projectId = $request->input('project_id');

            if (! $projectId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No project ID found. Please start from Step 1.',
                ], 400);
            }

            // Find project
            $project = Project::findOrFail($projectId);

            // Validate authorization
            if ($project->created_by_user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // ============ SYNC PRELIMINARY ACTIVITIES ============
            // This is the heart of the updateOrCreate pattern
            if ($request->has('preliminary_activities')) {
                $this->syncPreliminaryActivities(
                    $project,
                    $request->input('preliminary_activities'),
                    $request->input('deleted_activity_ids', '')
                );
            }

            // Update project status
            $project->update([
                'last_saved_step' => 4,
                'draft_saved_at' => now(),
                'status' => $request->input('status', 'draft'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'message' => 'تم حفظ الأنشطة التمهيدية بنجاح',
                'step' => 4,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * SYNC Preliminary Activities using updateOrCreate Pattern
     *
     * This is the key method that handles:
     * - UPDATE existing activities (those with ID)
     * - CREATE new activities (those without ID)
     * - DELETE marked activities
     */
    private function syncPreliminaryActivities(
        Project $project,
        array $activitiesData,
        string $deletedIdsString = ''
    ): void {
        // Convert nested array format
        $activities = $this->convertActivitiesArrayFormat($activitiesData);

        // ============ UPSERT ACTIVITIES ============
        foreach ($activities as $activity) {
            $activityId = $activity['id'] ?? null;

            // Build the "find by" criteria
            $attributes = [
                'project_id' => $project->id,
            ];

            // If this activity has an ID, include it in the search
            // This tells updateOrCreate: "Look for this activity by project_id AND id"
            if ($activityId) {
                $attributes['id'] = $activityId;
            }

            // Data to update or create with
            $values = [
                'activity_name' => $activity['activity_name'] ?? null,
                'description' => $activity['description'] ?? null,
                'planned_amount' => $activity['planned_amount'] ?? 0,
                'activity_type' => $activity['activity_type'] ?? null,
            ];

            // updateOrCreate Logic:
            // - Searches using $attributes
            // - If found: updates with $values
            // - If not found: creates new with $attributes + $values
            PreliminaryActivity::updateOrCreate($attributes, $values);
        }

        // ============ DELETE MARKED ACTIVITIES ============
        if (! empty($deletedIdsString)) {
            $deletedIds = explode(',', $deletedIdsString);
            $deletedIds = array_filter(array_map('trim', $deletedIds)); // Clean array

            if (! empty($deletedIds)) {
                PreliminaryActivity::where('project_id', $project->id)
                    ->whereIn('id', $deletedIds)
                    ->delete();
            }
        }
    }

    /**
     * Convert nested array format from form
     *
     * Form sends:
     * [
     *     'id' => [1, null, 2],
     *     'activity_name' => ['Activity 1', 'Activity 2', 'Activity 3'],
     *     'planned_amount' => [100, 200, 300]
     * ]
     *
     * Convert to:
     * [
     *     ['id' => 1, 'activity_name' => 'Activity 1', 'planned_amount' => 100],
     *     ['id' => null, 'activity_name' => 'Activity 2', 'planned_amount' => 200],
     *     ['id' => 2, 'activity_name' => 'Activity 3', 'planned_amount' => 300]
     * ]
     */
    private function convertActivitiesArrayFormat(array $data): array
    {
        $converted = [];
        $count = count($data['id'] ?? []);

        for ($i = 0; $i < $count; $i++) {
            $converted[] = [
                'id' => $data['id'][$i] ?? null,
                'activity_name' => $data['activity_name'][$i] ?? null,
                'description' => $data['description'][$i] ?? null,
                'planned_amount' => $data['planned_amount'][$i] ?? 0,
                'activity_type' => $data['activity_type'][$i] ?? null,
            ];
        }

        return $converted;
    }
}

// ============ ALTERNATIVE APPROACH: Using Service Layer ============

namespace App\Http\Controllers\Project\Services;

use App\Models\PreliminaryActivity;
use App\Models\Project;

class Step4Service
{
    /**
     * Handle Step 4 form submission through service layer
     */
    public function processPreliminaryActivities(
        Project $project,
        array $activitiesData,
        string $deletedIdsString = ''
    ): void {
        $this->upsertActivities($project, $activitiesData);
        $this->deleteActivities($project, $deletedIdsString);
    }

    /**
     * UPSERT: Update or Create activities
     */
    private function upsertActivities(Project $project, array $activitiesData): void
    {
        $activities = $this->normalizeArrayFormat($activitiesData);

        foreach ($activities as $activity) {
            // Skip empty rows
            if (empty($activity['activity_name'])) {
                continue;
            }

            $attributes = ['project_id' => $project->id];

            if (! empty($activity['id'])) {
                $attributes['id'] = $activity['id'];
            }

            PreliminaryActivity::updateOrCreate($attributes, [
                'activity_name' => $activity['activity_name'],
                'description' => $activity['description'] ?? null,
                'planned_amount' => $activity['planned_amount'] ?? 0,
                'activity_type' => $activity['activity_type'] ?? null,
            ]);
        }
    }

    /**
     * DELETE activities marked for deletion
     */
    private function deleteActivities(Project $project, string $deletedIdsString): void
    {
        if (empty($deletedIdsString)) {
            return;
        }

        $ids = array_filter(
            array_map('trim', explode(',', $deletedIdsString))
        );

        if (empty($ids)) {
            return;
        }

        PreliminaryActivity::where('project_id', $project->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * Convert nested array format to flat array
     */
    private function normalizeArrayFormat(array $data): array
    {
        $result = [];
        $count = count($data['id'] ?? []);

        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'id' => $data['id'][$i] ?? null,
                'activity_name' => $data['activity_name'][$i] ?? null,
                'description' => $data['description'][$i] ?? null,
                'planned_amount' => (float) ($data['planned_amount'][$i] ?? 0),
                'activity_type' => $data['activity_type'][$i] ?? null,
            ];
        }

        return $result;
    }
}

// ============ INTEGRATION IN MAIN CONTROLLER ============

namespace App\Http\Controllers;

use App\Http\Controllers\Project\Services\Step4Service;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    protected $step4Service;

    public function __construct(Step4Service $step4Service)
    {
        $this->step4Service = $step4Service;
    }

    /**
     * STEP 4: Preliminary Activities
     * Using injected service for clean separation of concerns
     */
    public function storeStep4(Request $request)
    {
        DB::beginTransaction();
        try {
            $projectId = $request->input('project_id');

            if (! $projectId) {
                throw new \Exception('No project ID found');
            }

            $project = Project::findOrFail($projectId);

            // Use service to handle the business logic
            $this->step4Service->processPreliminaryActivities(
                $project,
                $request->input('preliminary_activities', []),
                $request->input('deleted_activity_ids', '')
            );

            // Update project
            $project->update([
                'last_saved_step' => 4,
                'draft_saved_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'project_id' => $project->id,
                'message' => 'تم حفظ الأنشطة التمهيدية بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

// ============ TESTING ============

namespace Tests\Feature;

use App\Models\PreliminaryActivity;
use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class Step4Test extends TestCase
{
    protected $project;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create([
            'created_by_user_id' => $this->user->id,
            'status' => 'draft',
        ]);
    }

    /**
     * Test CREATE new activity (ID is null/empty)
     */
    public function test_create_new_activity()
    {
        $data = [
            'project_id' => $this->project->id,
            'preliminary_activities' => [
                'id' => [''], // Empty ID = CREATE
                'activity_name' => ['New Activity'],
                'planned_amount' => ['1000.00'],
            ],
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/projects/step/4', $data);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('preliminary_activities', [
            'project_id' => $this->project->id,
            'activity_name' => 'New Activity',
            'planned_amount' => 1000.00,
        ]);
    }

    /**
     * Test UPDATE existing activity (ID present)
     */
    public function test_update_existing_activity()
    {
        $activity = PreliminaryActivity::factory()->create([
            'project_id' => $this->project->id,
            'activity_name' => 'Old Activity',
            'planned_amount' => 500.00,
        ]);

        $data = [
            'project_id' => $this->project->id,
            'preliminary_activities' => [
                'id' => [(string) $activity->id], // Has ID = UPDATE
                'activity_name' => ['Updated Activity'],
                'planned_amount' => ['1500.00'],
            ],
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/projects/step/4', $data);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('preliminary_activities', [
            'id' => $activity->id,
            'activity_name' => 'Updated Activity',
            'planned_amount' => 1500.00,
        ]);
    }

    /**
     * Test DELETE activity
     */
    public function test_delete_activity()
    {
        $activity = PreliminaryActivity::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $data = [
            'project_id' => $this->project->id,
            'preliminary_activities' => [
                'id' => [],
                'activity_name' => [],
                'planned_amount' => [],
            ],
            'deleted_activity_ids' => (string) $activity->id, // Mark for deletion
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/projects/step/4', $data);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('preliminary_activities', [
            'id' => $activity->id,
        ]);
    }

    /**
     * Test mixed operations: CREATE, UPDATE, and DELETE in one request
     */
    public function test_mixed_operations()
    {
        $existingActivity = PreliminaryActivity::factory()->create([
            'project_id' => $this->project->id,
            'activity_name' => 'Existing',
        ]);

        $deleteActivity = PreliminaryActivity::factory()->create([
            'project_id' => $this->project->id,
            'activity_name' => 'To Delete',
        ]);

        $data = [
            'project_id' => $this->project->id,
            'preliminary_activities' => [
                'id' => [(string) $existingActivity->id, ''], // One existing, one new
                'activity_name' => ['Updated', 'Newly Created'],
                'planned_amount' => ['1000', '2000'],
            ],
            'deleted_activity_ids' => (string) $deleteActivity->id,
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/projects/step/4', $data);

        $response->assertJson(['success' => true]);

        // Check UPDATE
        $this->assertDatabaseHas('preliminary_activities', [
            'id' => $existingActivity->id,
            'activity_name' => 'Updated',
        ]);

        // Check CREATE
        $this->assertDatabaseHas('preliminary_activities', [
            'project_id' => $this->project->id,
            'activity_name' => 'Newly Created',
        ]);

        // Check DELETE
        $this->assertDatabaseMissing('preliminary_activities', [
            'id' => $deleteActivity->id,
        ]);
    }
}
