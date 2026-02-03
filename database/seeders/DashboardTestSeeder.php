<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DashboardTestSeeder extends Seeder
{
    /**
     * Seed test data for dashboard testing
     * Creates realistic project data with boards, columns, and tasks
     */
    public function run(): void
    {
        // Create test users if they don't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => 1,
            ]
        );

        $teamMember1 = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Developer',
                'password' => Hash::make('password'),
                'role_id' => 2,
            ]
        );

        $teamMember2 = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Designer',
                'password' => Hash::make('password'),
                'role_id' => 2,
            ]
        );

        $teamMember3 = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Tester',
                'password' => Hash::make('password'),
                'role_id' => 2,
            ]
        );

        // Create 3 projects for admin user
        $project1 = Project::create([
            'title' => 'Website Redesign',
            'description' => 'Complete redesign of company website with modern UI/UX',
            'instructor_id' => $adminUser->id,
            'timeline_status' => 'on_track',
            'budget_status' => 'on_budget',
        ]);

        $project2 = Project::create([
            'title' => 'Mobile App Development',
            'description' => 'Native iOS and Android app for customer engagement',
            'instructor_id' => $adminUser->id,
            'timeline_status' => 'behind',
            'budget_status' => 'on_budget',
        ]);

        $project3 = Project::create([
            'title' => 'API Integration',
            'description' => 'Integrate third-party payment and analytics APIs',
            'instructor_id' => $adminUser->id,
            'timeline_status' => 'on_track',
            'budget_status' => 'on_budget',
        ]);

        // Add team members to projects
        ProjectMember::create([
            'project_id' => $project1->id,
            'user_id' => $teamMember1->id,
            'role' => 'editor',
        ]);

        ProjectMember::create([
            'project_id' => $project1->id,
            'user_id' => $teamMember2->id,
            'role' => 'editor',
        ]);

        ProjectMember::create([
            'project_id' => $project2->id,
            'user_id' => $teamMember1->id,
            'role' => 'editor',
        ]);

        ProjectMember::create([
            'project_id' => $project2->id,
            'user_id' => $teamMember3->id,
            'role' => 'viewer',
        ]);

        ProjectMember::create([
            'project_id' => $project3->id,
            'user_id' => $teamMember2->id,
            'role' => 'editor',
        ]);

        // Create boards and columns for each project
        foreach ([$project1, $project2, $project3] as $project) {
            $board = Board::create([
                'project_id' => $project->id,
                'title' => $project->title . ' Board',
            ]);

            // Create standard kanban columns
            $backlog = Column::create([
                'board_id' => $board->id,
                'title' => 'Backlog',
                'position' => 1,
                'wip_limit' => 0,
            ]);

            $todo = Column::create([
                'board_id' => $board->id,
                'title' => 'To Do',
                'position' => 2,
                'wip_limit' => 5,
            ]);

            $inProgress = Column::create([
                'board_id' => $board->id,
                'title' => 'In Progress',
                'position' => 3,
                'wip_limit' => 3,
            ]);

            $review = Column::create([
                'board_id' => $board->id,
                'title' => 'Review',
                'position' => 4,
                'wip_limit' => 5,
            ]);

            $done = Column::create([
                'board_id' => $board->id,
                'title' => 'Done',
                'position' => 5,
                'wip_limit' => 0,
            ]);

            // Create tasks for this board
            $this->createTasks($project, $backlog, $todo, $inProgress, $review, $done, $teamMember1, $teamMember2, $teamMember3);
        }

        $this->command->info('✅ Dashboard test data seeded successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('   - 4 users (1 admin + 3 team members)');
        $this->command->info('   - 3 projects (all owned by admin)');
        $this->command->info('   - 5 project members relationships');
        $this->command->info('   - 3 boards (1 per project)');
        $this->command->info('   - 15 columns (5 per board)');
        $this->command->info('   - ~30 tasks (mix of active, done, and overdue)');
    }

    /**
     * Create tasks for a project across different columns
     */
    private function createTasks($project, $backlog, $todo, $inProgress, $review, $done, $user1, $user2, $user3): void
    {
        $projectName = $project->title;

        // Backlog tasks (2)
        Task::create([
            'column_id' => $backlog->id,
            'title' => 'Research user requirements for ' . $projectName,
            'description' => 'Conduct user interviews and gather requirements',
            'assignee_id' => null,
            'priority' => 'low',
            'due_date' => now()->addDays(30),
            'position' => 1,
        ]);

        Task::create([
            'column_id' => $backlog->id,
            'title' => 'Create wireframes',
            'description' => 'Design initial wireframes and mockups',
            'assignee_id' => $user2->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(25),
            'position' => 2,
        ]);

        // To Do tasks (3)
        Task::create([
            'column_id' => $todo->id,
            'title' => 'Set up development environment',
            'description' => 'Configure local dev environment with required tools',
            'assignee_id' => $user1->id,
            'priority' => 'high',
            'due_date' => now()->addDays(7),
            'position' => 1,
        ]);

        Task::create([
            'column_id' => $todo->id,
            'title' => 'Database schema design',
            'description' => 'Design and implement database schema',
            'assignee_id' => $user1->id,
            'priority' => 'high',
            'due_date' => now()->addDays(10),
            'position' => 2,
        ]);

        Task::create([
            'column_id' => $todo->id,
            'title' => 'API endpoint documentation',
            'description' => 'Document all API endpoints and contracts',
            'assignee_id' => $user3->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(14),
            'position' => 3,
        ]);

        // In Progress tasks (3)
        Task::create([
            'column_id' => $inProgress->id,
            'title' => 'Implement authentication',
            'description' => 'Build login/register functionality with JWT',
            'assignee_id' => $user1->id,
            'priority' => 'critical',
            'due_date' => now()->addDays(5),
            'position' => 1,
        ]);

        Task::create([
            'column_id' => $inProgress->id,
            'title' => 'Design system components',
            'description' => 'Create reusable UI components library',
            'assignee_id' => $user2->id,
            'priority' => 'high',
            'due_date' => now()->addDays(8),
            'position' => 2,
        ]);

        // Overdue task in progress (should count as overdue)
        Task::create([
            'column_id' => $inProgress->id,
            'title' => 'Fix critical bug in payment flow',
            'description' => 'Urgent: Payment processing fails for certain cards',
            'assignee_id' => $user1->id,
            'priority' => 'critical',
            'due_date' => now()->subDays(3), // OVERDUE!
            'position' => 3,
        ]);

        // Review tasks (2)
        Task::create([
            'column_id' => $review->id,
            'title' => 'Code review: User dashboard',
            'description' => 'Review PR for user dashboard implementation',
            'assignee_id' => $user3->id,
            'priority' => 'medium',
            'due_date' => now()->addDays(2),
            'position' => 1,
        ]);

        // Overdue task in review (should count as overdue)
        Task::create([
            'column_id' => $review->id,
            'title' => 'Security audit',
            'description' => 'Complete security audit of authentication system',
            'assignee_id' => $user3->id,
            'priority' => 'high',
            'due_date' => now()->subDays(1), // OVERDUE!
            'position' => 2,
        ]);

        // Done tasks (3) - These should NOT count as active or overdue
        Task::create([
            'column_id' => $done->id,
            'title' => 'Setup CI/CD pipeline',
            'description' => 'Configure GitHub Actions for automated testing',
            'assignee_id' => $user1->id,
            'priority' => 'medium',
            'due_date' => now()->subDays(5),
            'position' => 1,
        ]);

        Task::create([
            'column_id' => $done->id,
            'title' => 'Create project documentation',
            'description' => 'Write comprehensive README and setup guide',
            'assignee_id' => $user2->id,
            'priority' => 'low',
            'due_date' => now()->subDays(10),
            'position' => 2,
        ]);

        Task::create([
            'column_id' => $done->id,
            'title' => 'Initial design mockups',
            'description' => 'Create high-fidelity mockups for key screens',
            'assignee_id' => $user2->id,
            'priority' => 'medium',
            'due_date' => now()->subDays(7),
            'position' => 3,
        ]);
    }
}
