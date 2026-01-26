<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $instructor;
    private User $student;
    private Project $project;
    private Board $board;
    private Column $column;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Create users
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->instructor = User::factory()->create(['role_id' => $instructorRole->id]);
        $this->student = User::factory()->create(['role_id' => $studentRole->id]);

        // Create project with instructor as owner
        $this->project = Project::factory()->create(['instructor_id' => $this->instructor->id]);

        // Add students to project
        $this->project->members()->attach($this->student, ['role' => 'viewer']);

        // Create board and column
        $this->board = Board::factory()->create(['project_id' => $this->project->id]);
        $this->column = Column::factory()->create(['board_id' => $this->board->id]);
    }

    // ============ INDEX TESTS ============

    public function test_list_tasks_without_authentication(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    public function test_list_tasks_returns_paginated_results(): void
    {
        Task::factory(25)->create(['column_id' => $this->column->id]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
        $response->assertJsonCount(20, 'data');
    }

    public function test_list_tasks_filter_by_column(): void
    {
        $column2 = Column::factory()->create(['board_id' => $this->board->id]);
        Task::factory(5)->create(['column_id' => $this->column->id]);
        Task::factory(3)->create(['column_id' => $column2->id]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/tasks?column_id={$this->column->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_list_tasks_filter_by_priority(): void
    {
        Task::factory(3)->create(['column_id' => $this->column->id, 'priority' => 'high']);
        Task::factory(2)->create(['column_id' => $this->column->id, 'priority' => 'low']);

        $response = $this->actingAs($this->student)
            ->getJson('/api/tasks?priority=high');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_list_tasks_filter_by_assignee(): void
    {
        Task::factory(3)->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->student->id,
        ]);
        Task::factory(2)->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->instructor->id,
        ]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/tasks?assignee_id={$this->student->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    // ============ STORE TESTS ============

    public function test_create_task_without_authentication(): void
    {
        $response = $this->postJson('/api/tasks', [
            'column_id' => $this->column->id,
            'title' => 'New Task',
            'priority' => 'medium',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_task_with_valid_data(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/tasks', [
                'column_id' => $this->column->id,
                'title' => 'New Task',
                'description' => 'Task description',
                'priority' => 'high',
                'assignee_id' => $this->student->id,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'column_id',
                'title',
                'description',
                'priority',
                'assignee_id',
                'position',
            ],
        ]);
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'column_id' => $this->column->id,
        ]);
    }

    public function test_create_task_missing_required_fields(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/tasks', [
                'title' => 'New Task',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['column_id', 'priority']);
    }

    public function test_create_task_invalid_priority(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/tasks', [
                'column_id' => $this->column->id,
                'title' => 'New Task',
                'priority' => 'invalid',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['priority']);
    }

    public function test_create_task_invalid_column(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/tasks', [
                'column_id' => 999,
                'title' => 'New Task',
                'priority' => 'medium',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['column_id']);
    }

    // ============ SHOW TESTS ============

    public function test_show_task_without_authentication(): void
    {
        $task = Task::factory()->create(['column_id' => $this->column->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    public function test_show_task_returns_full_details(): void
    {
        $task = Task::factory()
            ->has(\App\Models\Subtask::factory()->count(3))
            ->create([
                'column_id' => $this->column->id,
                'assignee_id' => $this->student->id,
            ]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'column_id',
                'title',
                'assignee_id',
                'priority',
                'due_date',
                'position',
                'progress',
                'is_overdue',
            ],
        ]);
    }

    public function test_show_task_not_found(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/tasks/999');

        $response->assertStatus(404);
    }

    // ============ UPDATE TESTS ============

    public function test_update_task_without_authentication(): void
    {
        $task = Task::factory()->create(['column_id' => $this->column->id]);

        $response = $this->patchJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_task_by_assignee(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->student->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->student)
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_task_by_instructor_owner(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->instructor)
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_task_unauthorized_student(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->instructor->id,
        ]);

        $response = $this->actingAs($this->student)
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_task_invalid_data(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->student)
            ->patchJson("/api/tasks/{$task->id}", [
                'priority' => 'invalid',
            ]);

        $response->assertStatus(422);
    }

    // ============ DELETE TESTS ============

    public function test_delete_task_without_authentication(): void
    {
        $task = Task::factory()->create(['column_id' => $this->column->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    public function test_delete_task_by_assignee(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->student->id,
        ]);

        $response = $this->actingAs($this->student)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_delete_task_by_instructor_owner(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->instructor)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);
    }

    public function test_delete_task_unauthorized(): void
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => $this->instructor->id,
        ]);

        $response = $this->actingAs($this->student)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    // ============ MOVE/DRAG-AND-DROP TESTS ============

    public function test_move_task_to_different_column(): void
    {
        $column2 = Column::factory()->create(['board_id' => $this->board->id]);
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'position' => 0,
        ]);

        $response = $this->actingAs($this->student)
            ->postJson("/api/tasks/{$task->id}/move", [
                'column_id' => $column2->id,
                'position' => 0,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'column_id' => $column2->id,
        ]);
    }

    public function test_move_task_respects_wip_limit(): void
    {
        $column2 = Column::factory()->create([
            'board_id' => $this->board->id,
            'wip_limit' => 1,
        ]);
        Task::factory()->create(['column_id' => $column2->id]);
        $task = Task::factory()->create(['column_id' => $this->column->id]);

        $response = $this->actingAs($this->student)
            ->postJson("/api/tasks/{$task->id}/move", [
                'column_id' => $column2->id,
                'position' => 1,
            ]);

        $response->assertStatus(422);
    }

    public function test_move_task_invalid_column(): void
    {
        $task = Task::factory()->create(['column_id' => $this->column->id]);

        $response = $this->actingAs($this->student)
            ->postJson("/api/tasks/{$task->id}/move", [
                'column_id' => 999,
                'position' => 0,
            ]);

        $response->assertStatus(422);
    }
}
