<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Comment;
use App\Models\Label;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive Feature Tests for Kanban Task Management
 * Tests all functionality from Phase 1-9 (T001-T125)
 */
class KanbanTaskManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Board $board;
    protected Column $column;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test project
        $this->project = Project::factory()->create([
            'instructor_id' => $this->user->id,
        ]);

        // Create board with columns
        $this->board = Board::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->column = Column::factory()->create([
            'board_id' => $this->board->id,
            'title' => 'To Do',
            'position' => 1,
        ]);
    }

    /** @test */
    public function user_can_create_task()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks", [
                'column_id' => $this->column->id,
                'title' => 'Test Task',
                'description' => 'Test Description',
                'priority' => 'high',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'priority',
                    'column_id',
                    'position',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'column_id' => $this->column->id,
        ]);
    }

    /** @test */
    public function user_can_view_task()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'View Test Task',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'View Test Task',
                ]
            ]);
    }

    /** @test */
    public function user_can_update_task()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'priority' => 'critical',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'priority' => 'critical',
        ]);
    }

    /** @test */
    public function user_can_delete_task()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function user_can_move_task_between_columns()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'position' => 1,
        ]);

        $newColumn = Column::factory()->create([
            'board_id' => $this->board->id,
            'title' => 'In Progress',
            'position' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks/{$task->id}/move", [
                'column_id' => $newColumn->id,
                'position' => 1,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'column_id' => $newColumn->id,
            'position' => 0,
        ]);
    }

    /** @test */
    public function task_respects_wip_limits()
    {
        $column = Column::factory()->create([
            'board_id' => $this->board->id,
            'title' => 'Limited Column',
            'wip_limit' => 2,
        ]);

        // Create 2 tasks (at limit)
        Task::factory()->count(2)->create([
            'column_id' => $column->id,
        ]);

        // Try to add a 3rd task
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks/{$task->id}/move", [
                'column_id' => $column->id,
                'position' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Column WIP limit exceeded',
            ]);
    }

    /** @test */
    public function user_can_create_subtask()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks/{$task->id}/subtasks", [
                'title' => 'Test Subtask',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'is_completed',
                    'position',
                ]
            ]);

        $this->assertDatabaseHas('subtasks', [
            'task_id' => $task->id,
            'title' => 'Test Subtask',
        ]);
    }

    /** @test */
    public function user_can_toggle_subtask_completion()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $subtask = Subtask::factory()->create([
            'task_id' => $task->id,
            'is_completed' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/tasks/{$task->id}/subtasks/{$subtask->id}", [
                'is_completed' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('subtasks', [
            'id' => $subtask->id,
            'is_completed' => true,
        ]);
    }

    /** @test */
    public function task_progress_calculates_correctly()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        // Create 3 subtasks, 1 completed
        Subtask::factory()->create([
            'task_id' => $task->id,
            'is_completed' => true,
        ]);

        Subtask::factory()->count(2)->create([
            'task_id' => $task->id,
            'is_completed' => false,
        ]);

        $task->refresh();

        $this->assertEquals(33, $task->progress);
    }

    /** @test */
    public function user_can_add_comment_to_task()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks/{$task->id}/comments", [
                'body' => 'This is a test comment',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'body',
                    'user_id',
                    'created_at',
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'body' => 'This is a test comment',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function user_can_edit_comment_within_15_minutes()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'body' => 'Original comment',
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/comments/{$comment->id}", [
                'body' => 'Updated comment',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Updated comment',
        ]);

        $comment->refresh();
        $this->assertNotNull($comment->edited_at);
    }

    /** @test */
    public function user_cannot_edit_comment_after_15_minutes()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
            'body' => 'Original comment',
            'created_at' => now()->subMinutes(20),
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/comments/{$comment->id}", [
                'body' => 'Updated comment',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_create_label()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/labels", [
                'name' => 'Bug',
                'color' => '#EF4444',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'color',
                    'project_id',
                ]
            ]);

        $this->assertDatabaseHas('labels', [
            'project_id' => $this->project->id,
            'name' => 'Bug',
            'color' => '#EF4444',
        ]);
    }

    /** @test */
    public function user_can_assign_labels_to_task()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $label1 = Label::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Bug',
        ]);

        $label2 = Label::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Urgent',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks/{$task->id}/labels", [
                'label_ids' => [$label1->id, $label2->id],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('task_labels', [
            'task_id' => $task->id,
            'label_id' => $label1->id,
        ]);

        $this->assertDatabaseHas('task_labels', [
            'task_id' => $task->id,
            'label_id' => $label2->id,
        ]);
    }

    /** @test */
    public function user_can_search_tasks_by_title()
    {
        Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Login Bug Fix',
        ]);

        Task::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Dashboard Update',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks?search=login");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Login Bug Fix']);
    }

    /** @test */
    public function user_can_filter_tasks_by_priority()
    {
        Task::factory()->create([
            'column_id' => $this->column->id,
            'priority' => 'high',
        ]);

        Task::factory()->create([
            'column_id' => $this->column->id,
            'priority' => 'low',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks?priority=high");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['priority' => 'high']);
    }

    /** @test */
    public function user_can_filter_tasks_by_label()
    {
        $label = Label::factory()->create([
            'project_id' => $this->project->id,
            'name' => 'Bug',
        ]);

        $task1 = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);
        $task1->labels()->attach($label->id);

        $task2 = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks?label_ids={$label->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $task1->id]);
    }

    /** @test */
    public function user_can_filter_tasks_by_due_date_range()
    {
        $today = now()->startOfDay();

        Task::factory()->create([
            'column_id' => $this->column->id,
            'due_date' => $today->copy()->subDay(), // Overdue
        ]);

        Task::factory()->create([
            'column_id' => $this->column->id,
            'due_date' => $today, // Today
        ]);

        Task::factory()->create([
            'column_id' => $this->column->id,
            'due_date' => $today->copy()->addDays(5), // Future
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tasks?due_date_range=overdue");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function task_is_overdue_when_due_date_passed()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'due_date' => now()->subDay(),
        ]);

        $this->assertTrue($task->isOverdue());
    }

    /** @test */
    public function user_can_assign_task_to_team_member()
    {
        $assignee = User::factory()->create();

        $task = Task::factory()->create([
            'column_id' => $this->column->id,
            'assignee_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/tasks/{$task->id}", [
                'assignee_id' => $assignee->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => $assignee->id,
        ]);
    }

    /** @test */
    public function activity_is_logged_when_task_created()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/tasks", [
                'column_id' => $this->column->id,
                'title' => 'Activity Test Task',
                'priority' => 'medium',
            ]);

        $response->assertStatus(201);

        $task = Task::where('title', 'Activity Test Task')->first();

        $this->assertNotNull($task);

        $this->assertDatabaseHas('activities', [
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'type' => 'task.created',
        ]);
    }

    /** @test */
    public function cascade_delete_removes_subtasks_and_comments()
    {
        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $subtask = Subtask::factory()->create([
            'task_id' => $task->id,
        ]);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $this->user->id,
        ]);

        $taskId = $task->id;

        $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$task->id}");

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
        $this->assertDatabaseMissing('subtasks', ['id' => $subtask->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /** @test */
    public function unauthorized_user_cannot_delete_task()
    {
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'column_id' => $this->column->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
