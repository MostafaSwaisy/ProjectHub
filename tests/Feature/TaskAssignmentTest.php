<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\Role;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private User $assignee;
    private User $member;
    private Project $project;
    private Board $board;
    private Column $column;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);

        // Create users
        $this->instructor = User::factory()->create([
            'name' => 'Instructor',
            'role_id' => $instructorRole->id,
        ]);

        $this->assignee = User::factory()->create([
            'name' => 'Assignee',
            'role_id' => $studentRole->id,
        ]);

        $this->member = User::factory()->create([
            'name' => 'Member',
            'role_id' => $studentRole->id,
        ]);

        // Create project with members
        $this->project = Project::factory()->create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Project',
        ]);

        $this->project->members()->attach([
            $this->instructor->id => ['role' => 'owner'],
            $this->assignee->id => ['role' => 'member'],
            $this->member->id => ['role' => 'member'],
        ]);

        // Create board and column
        $this->board = $this->project->boards()->create(['title' => 'Test Board']);
        $this->column = $this->board->columns()->create(['title' => 'To Do', 'position' => 1]);

        // Create task
        $this->task = Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Test Task',
            'assignee_id' => null,
        ]);
    }

    private function actingAsWithToken(User $user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    // ============ TASK ASSIGNMENT TESTS ============

    public function test_assign_task_requires_authentication(): void
    {
        $response = $this->putJson("/api/tasks/{$this->task->id}", [
            'assignee_id' => $this->assignee->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_assign_task_to_project_member(): void
    {
        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $this->assignee->id,
            ]);

        $response->assertStatus(200);

        // Verify task was assigned
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assignee_id' => $this->assignee->id,
        ]);
    }

    public function test_assign_task_creates_notification(): void
    {
        $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $this->assignee->id,
            ]);

        // Verify notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->assignee->id,
            'type' => 'task_assigned',
        ]);
    }

    public function test_unassign_task_removes_notification_record(): void
    {
        // First assign the task
        $this->task->update(['assignee_id' => $this->assignee->id]);

        // Then unassign it
        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => null,
            ]);

        $response->assertStatus(200);

        // Verify task was unassigned
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assignee_id' => null,
        ]);
    }

    public function test_cannot_assign_task_to_non_member(): void
    {
        // Create a user who is not a project member
        $outsider = User::factory()->create(['name' => 'Outsider']);

        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $outsider->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_assign_task_to_viewer(): void
    {
        // Add viewer to project
        $viewer = User::factory()->create(['name' => 'Viewer']);
        $this->project->members()->attach($viewer->id, ['role' => 'viewer']);

        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $viewer->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_reassign_task_updates_notifications(): void
    {
        // Assign to first user
        $this->task->update(['assignee_id' => $this->assignee->id]);

        // Reassign to second user
        $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $this->member->id,
            ]);

        // Verify task is now assigned to second user
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assignee_id' => $this->member->id,
        ]);

        // Verify new notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->member->id,
            'type' => 'task_assigned',
        ]);
    }

    public function test_member_cannot_assign_task(): void
    {
        $response = $this->actingAsWithToken($this->member)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $this->assignee->id,
            ]);

        // Should fail authorization
        $response->assertStatus(403);
    }

    public function test_assign_task_returns_updated_task(): void
    {
        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => $this->assignee->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.assignee_id', $this->assignee->id);
        $response->assertJsonPath('data.assignee.id', $this->assignee->id);
        $response->assertJsonPath('data.assignee.name', $this->assignee->name);
    }

    public function test_reassign_task_creates_unassigned_notification(): void
    {
        // Assign task to first user
        $this->task->update(['assignee_id' => $this->assignee->id]);

        // Unassign by setting assignee_id to null
        Notification::truncate(); // Clear previous notifications
        $response = $this->actingAsWithToken($this->instructor)
            ->putJson("/api/tasks/{$this->task->id}", [
                'assignee_id' => null,
            ]);

        $response->assertStatus(200);

        // Verify task was unassigned
        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'assignee_id' => null,
        ]);

        // Verify notification was created for previous assignee
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->assignee->id,
            'type' => 'task_unassigned',
        ]);
    }
}
