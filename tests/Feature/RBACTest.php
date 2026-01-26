<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Role;
use App\Models\Board;
use App\Models\Column;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RBACTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $instructor;
    private User $student;
    private Role $adminRole;
    private Role $instructorRole;
    private Role $studentRole;
    private Project $instructorProject;
    private Board $board;
    private Column $column;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->instructorRole = Role::create(['name' => 'instructor']);
        $this->studentRole = Role::create(['name' => 'student']);

        // Create users
        $this->admin = User::factory()->create(['role_id' => $this->adminRole->id]);
        $this->instructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $this->student = User::factory()->create(['role_id' => $this->studentRole->id]);

        // Create project owned by instructor
        $this->instructorProject = Project::factory()->create(['instructor_id' => $this->instructor->id]);

        // Create board and column
        $this->board = Board::factory()->create(['project_id' => $this->instructorProject->id]);
        $this->column = Column::factory()->create(['board_id' => $this->board->id]);

        // Create task in column
        $this->task = Task::factory()->create(['column_id' => $this->column->id]);

        // Add student to project as member
        $this->instructorProject->members()->attach($this->student->id, ['role' => 'member']);
    }

    // ============================================================================
    // ProjectPolicy Tests
    // ============================================================================

    public function test_admin_can_view_all_projects()
    {
        $this->assertTrue($this->admin->can('view', $this->instructorProject));
    }

    public function test_instructor_can_view_own_project()
    {
        $this->assertTrue($this->instructor->can('view', $this->instructorProject));
    }

    public function test_instructor_cannot_view_other_project()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);

        $this->assertFalse($this->instructor->can('view', $otherProject));
    }

    public function test_student_can_view_project_they_are_member_of()
    {
        $this->assertTrue($this->student->can('view', $this->instructorProject));
    }

    public function test_student_cannot_view_project_they_are_not_member_of()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);

        $this->assertFalse($this->student->can('view', $otherProject));
    }

    public function test_admin_can_create_project()
    {
        $this->assertTrue($this->admin->can('create', Project::class));
    }

    public function test_instructor_can_create_project()
    {
        $this->assertTrue($this->instructor->can('create', Project::class));
    }

    public function test_student_cannot_create_project()
    {
        $this->assertFalse($this->student->can('create', Project::class));
    }

    public function test_admin_can_update_any_project()
    {
        $this->assertTrue($this->admin->can('update', $this->instructorProject));
    }

    public function test_instructor_can_update_own_project()
    {
        $this->assertTrue($this->instructor->can('update', $this->instructorProject));
    }

    public function test_instructor_cannot_update_other_project()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);

        $this->assertFalse($this->instructor->can('update', $otherProject));
    }

    public function test_student_cannot_update_project()
    {
        $this->assertFalse($this->student->can('update', $this->instructorProject));
    }

    public function test_admin_can_delete_any_project()
    {
        $this->assertTrue($this->admin->can('delete', $this->instructorProject));
    }

    public function test_instructor_can_delete_own_project()
    {
        $this->assertTrue($this->instructor->can('delete', $this->instructorProject));
    }

    public function test_instructor_cannot_delete_other_project()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);

        $this->assertFalse($this->instructor->can('delete', $otherProject));
    }

    public function test_student_cannot_delete_project()
    {
        $this->assertFalse($this->student->can('delete', $this->instructorProject));
    }

    // ============================================================================
    // TaskPolicy Tests
    // ============================================================================

    public function test_admin_can_view_all_tasks()
    {
        $this->assertTrue($this->admin->can('view', $this->task));
    }

    public function test_instructor_can_view_task_in_own_project()
    {
        $this->assertTrue($this->instructor->can('view', $this->task));
    }

    public function test_student_can_view_task_in_their_project()
    {
        $this->assertTrue($this->student->can('view', $this->task));
    }

    public function test_student_cannot_view_task_in_other_project()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);
        $otherBoard = Board::factory()->create(['project_id' => $otherProject->id]);
        $otherColumn = Column::factory()->create(['board_id' => $otherBoard->id]);
        $otherTask = Task::factory()->create(['column_id' => $otherColumn->id]);

        $this->assertFalse($this->student->can('view', $otherTask));
    }

    public function test_admin_can_create_task()
    {
        $this->assertTrue($this->admin->can('create', $this->column));
    }

    public function test_instructor_can_create_task_in_own_project()
    {
        $this->assertTrue($this->instructor->can('create', $this->column));
    }

    public function test_student_can_create_task_in_their_project()
    {
        $this->assertTrue($this->student->can('create', $this->column));
    }

    public function test_student_cannot_create_task_in_other_project()
    {
        $otherInstructor = User::factory()->create(['role_id' => $this->instructorRole->id]);
        $otherProject = Project::factory()->create(['instructor_id' => $otherInstructor->id]);
        $otherBoard = Board::factory()->create(['project_id' => $otherProject->id]);
        $otherColumn = Column::factory()->create(['board_id' => $otherBoard->id]);

        $this->assertFalse($this->student->can('create', $otherColumn));
    }

    public function test_admin_can_update_any_task()
    {
        $this->assertTrue($this->admin->can('update', $this->task));
    }

    public function test_instructor_can_update_task_in_own_project()
    {
        $this->assertTrue($this->instructor->can('update', $this->task));
    }

    public function test_student_cannot_update_unassigned_task()
    {
        $this->assertFalse($this->student->can('update', $this->task));
    }

    public function test_student_can_update_assigned_task()
    {
        $this->task->update(['assignee_id' => $this->student->id]);

        $this->assertTrue($this->student->can('update', $this->task));
    }

    public function test_admin_can_delete_any_task()
    {
        $this->assertTrue($this->admin->can('delete', $this->task));
    }

    public function test_instructor_can_delete_task_in_own_project()
    {
        $this->assertTrue($this->instructor->can('delete', $this->task));
    }

    public function test_student_cannot_delete_unassigned_task()
    {
        $this->assertFalse($this->student->can('delete', $this->task));
    }

    public function test_student_can_delete_assigned_task()
    {
        $this->task->update(['assignee_id' => $this->student->id]);

        $this->assertTrue($this->student->can('delete', $this->task));
    }

    // ============================================================================
    // UserPolicy Tests
    // ============================================================================

    public function test_admin_can_view_any_user()
    {
        $this->assertTrue($this->admin->can('view', $this->student));
        $this->assertTrue($this->admin->can('view', $this->instructor));
    }

    public function test_student_can_view_own_profile()
    {
        $this->assertTrue($this->student->can('view', $this->student));
    }

    public function test_student_cannot_view_other_profile()
    {
        $this->assertFalse($this->student->can('view', $this->instructor));
    }

    public function test_instructor_can_view_own_profile()
    {
        $this->assertTrue($this->instructor->can('view', $this->instructor));
    }

    public function test_instructor_can_view_student_in_own_project()
    {
        $this->assertTrue($this->instructor->can('view', $this->student));
    }

    public function test_instructor_cannot_view_student_not_in_own_project()
    {
        $otherStudent = User::factory()->create(['role_id' => $this->studentRole->id]);

        $this->assertFalse($this->instructor->can('view', $otherStudent));
    }

    public function test_admin_can_update_any_user()
    {
        $this->assertTrue($this->admin->can('update', $this->student));
        $this->assertTrue($this->admin->can('update', $this->instructor));
    }

    public function test_user_can_update_own_profile()
    {
        $this->assertTrue($this->student->can('update', $this->student));
        $this->assertTrue($this->instructor->can('update', $this->instructor));
    }

    public function test_user_cannot_update_other_profile()
    {
        $this->assertFalse($this->student->can('update', $this->instructor));
        $this->assertFalse($this->instructor->can('update', $this->student));
    }

    public function test_admin_can_create_user()
    {
        $this->assertTrue($this->admin->can('create', User::class));
    }

    public function test_instructor_cannot_create_user()
    {
        $this->assertFalse($this->instructor->can('create', User::class));
    }

    public function test_student_cannot_create_user()
    {
        $this->assertFalse($this->student->can('create', User::class));
    }

    public function test_admin_can_delete_user()
    {
        $this->assertTrue($this->admin->can('delete', $this->student));
    }

    public function test_instructor_cannot_delete_user()
    {
        $this->assertFalse($this->instructor->can('delete', $this->student));
    }

    public function test_student_cannot_delete_user()
    {
        $this->assertFalse($this->student->can('delete', $this->instructor));
    }

    public function test_view_profile_same_as_view()
    {
        $this->assertTrue($this->admin->can('viewProfile', $this->student));
        $this->assertTrue($this->student->can('viewProfile', $this->student));
        $this->assertFalse($this->student->can('viewProfile', $this->instructor));
    }
}
