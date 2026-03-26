<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $member;
    private User $viewer;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);

        // Create users with roles
        $this->owner = User::factory()->create([
            'name' => 'Project Owner',
            'role_id' => $instructorRole->id,
        ]);
        $this->member = User::factory()->create([
            'name' => 'Project Member',
            'role_id' => $studentRole->id,
        ]);
        $this->viewer = User::factory()->create([
            'name' => 'Project Viewer',
            'role_id' => $studentRole->id,
        ]);

        // Create project
        $this->project = Project::factory()->create([
            'instructor_id' => $this->owner->id,
            'title' => 'Test Project',
        ]);

        // Add members (instructor is owner)
        $this->project->members()->attach($this->owner->id, ['role' => 'owner']);
        $this->project->members()->attach($this->member->id, ['role' => 'member']);
        $this->project->members()->attach($this->viewer->id, ['role' => 'viewer']);
    }

    private function actingAsWithToken(User $user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    // ============ GET MEMBERS TESTS ============

    public function test_members_requires_authentication(): void
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/members");
        $response->assertStatus(401);
    }

    public function test_members_returns_project_members(): void
    {
        $response = $this->actingAsWithToken($this->owner)
            ->getJson("/api/projects/{$this->project->id}/members");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');

        // Verify structure
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'avatar_url',
                    'role',
                ],
            ],
        ]);

        // Verify all members are present
        $memberIds = $response->json('data.*.id');
        $this->assertContains($this->owner->id, $memberIds);
        $this->assertContains($this->member->id, $memberIds);
        $this->assertContains($this->viewer->id, $memberIds);
    }

    public function test_members_includes_user_data(): void
    {
        $response = $this->actingAsWithToken($this->owner)
            ->getJson("/api/projects/{$this->project->id}/members");

        $members = $response->json('data');
        $ownerData = collect($members)->firstWhere('id', $this->owner->id);

        $this->assertEquals($this->owner->name, $ownerData['name']);
        $this->assertEquals($this->owner->email, $ownerData['email']);
        $this->assertEquals('owner', $ownerData['role']);
    }

    // ============ GET ASSIGNABLE MEMBERS TESTS ============

    public function test_assignable_members_requires_authentication(): void
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/members/assignable");
        $response->assertStatus(401);
    }

    public function test_assignable_members_excludes_viewers(): void
    {
        $response = $this->actingAsWithToken($this->owner)
            ->getJson("/api/projects/{$this->project->id}/members/assignable");

        $response->assertStatus(200);
        // Should be 2: owner and member, but not viewer
        $response->assertJsonCount(2, 'data');

        $memberIds = $response->json('data.*.id');
        $this->assertContains($this->owner->id, $memberIds);
        $this->assertContains($this->member->id, $memberIds);
        $this->assertNotContains($this->viewer->id, $memberIds);
    }

    public function test_assignable_members_includes_only_assignable_roles(): void
    {
        $response = $this->actingAsWithToken($this->owner)
            ->getJson("/api/projects/{$this->project->id}/members/assignable");

        $members = $response->json('data');
        $roles = collect($members)->pluck('role');

        $this->assertTrue($roles->every(fn ($role) => \in_array($role, ['owner', 'lead', 'member'])));
    }

    // ============ REMOVE MEMBER TESTS ============

    public function test_remove_member_requires_authentication(): void
    {
        $response = $this->deleteJson("/api/projects/{$this->project->id}/members/{$this->member->id}");
        $response->assertStatus(401);
    }

    public function test_remove_member_succeeds(): void
    {
        $response = $this->actingAsWithToken($this->owner)
            ->deleteJson("/api/projects/{$this->project->id}/members/{$this->member->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Member removed from project.');

        // Verify member was removed
        $this->assertDatabaseMissing('project_members', [
            'project_id' => $this->project->id,
            'user_id' => $this->member->id,
        ]);
    }

    public function test_remove_member_unassigns_tasks(): void
    {
        // Create a board and column
        $board = $this->project->boards()->create(['title' => 'Test Board']);
        $column = $board->columns()->create(['title' => 'To Do', 'position' => 1]);

        // Create tasks assigned to member
        $task1 = Task::factory()->create([
            'column_id' => $column->id,
            'assignee_id' => $this->member->id,
            'title' => 'Task 1',
        ]);

        $task2 = Task::factory()->create([
            'column_id' => $column->id,
            'assignee_id' => $this->member->id,
            'title' => 'Task 2',
        ]);

        // Remove member
        $response = $this->actingAsWithToken($this->owner)
            ->deleteJson("/api/projects/{$this->project->id}/members/{$this->member->id}");

        $response->assertStatus(200);

        // Verify tasks were unassigned
        $this->assertDatabaseHas('tasks', [
            'id' => $task1->id,
            'assignee_id' => null,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task2->id,
            'assignee_id' => null,
        ]);
    }

    public function test_remove_member_returns_unassigned_count(): void
    {
        // Create board and column
        $board = $this->project->boards()->create(['title' => 'Test Board']);
        $column = $board->columns()->create(['title' => 'To Do', 'position' => 1]);

        // Create 3 tasks assigned to member
        for ($i = 0; $i < 3; $i++) {
            Task::factory()->create([
                'column_id' => $column->id,
                'assignee_id' => $this->member->id,
            ]);
        }

        // Remove member
        $response = $this->actingAsWithToken($this->owner)
            ->deleteJson("/api/projects/{$this->project->id}/members/{$this->member->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('tasks_unassigned', 3);
    }

    public function test_non_owner_cannot_remove_member(): void
    {
        $response = $this->actingAsWithToken($this->member)
            ->deleteJson("/api/projects/{$this->project->id}/members/{$this->viewer->id}");

        $response->assertStatus(403);
    }
}
