# ProjectHub Analytics - Implementation Checklist

## Phase 1: Database and Models (COMPLETED)

### Migrations Created

#### Core Tables (7 updated/created)
- [x] **Roles Table** - `2026_01_26_123951_create_roles_table.php`
  - Columns: id, name (unique), timestamps
  - Used by: User model

- [x] **Projects Table** (Updated) - `2026_01_26_124102_create_projects_table.php`
  - Columns: id, title, description, instructor_id (FK), timeline_status, budget_status, timestamps
  - Indexes: instructor_id
  - Used by: Projects feature

- [x] **Boards Table** (Updated) - `2026_01_26_124102_create_boards_table.php`
  - Columns: id, project_id (FK), title, timestamps
  - Indexes: project_id
  - Used by: Kanban board feature

- [x] **Columns Table** (Updated) - `2026_01_26_124102_create_columns_table.php`
  - Columns: id, board_id (FK), title, position, wip_limit, timestamps
  - Indexes: board_id
  - Used by: Board column management

- [x] **Tasks Table** (Updated) - `2026_01_26_124103_create_tasks_table.php`
  - Columns: id, column_id (FK), title, description, assignee_id (FK), priority, due_date, position, timestamps
  - Indexes: column_id, assignee_id, due_date
  - Used by: Task management

- [x] **Subtasks Table** - `2026_01_26_124104_create_subtasks_table.php`
  - Columns: id, task_id (FK), title, is_completed, position, timestamps
  - Indexes: task_id
  - Used by: Task breakdown

- [x] **Comments Table** - `2026_01_26_124105_create_comments_table.php`
  - Columns: id, task_id (FK), user_id (FK), parent_id (FK), body, edited_at, timestamps
  - Indexes: task_id, user_id, parent_id
  - Used by: Task discussion threads

#### Pivot/Junction Tables (2)
- [x] **Labels Table** - `2026_01_26_124106_create_labels_table.php`
  - Columns: id, project_id (FK), name, color, timestamps
  - Indexes: project_id
  - Unique: (project_id, name)
  - Used by: Task categorization

- [x] **Task Labels Table** (Pivot) - `2026_01_26_124107_create_task_labels_table.php`
  - Columns: id, task_id (FK), label_id (FK), timestamps
  - Unique: (task_id, label_id)
  - Used by: Many-to-many task labeling

#### Tracking Tables (3)
- [x] **Activities Table** - `2026_01_26_124108_create_activities_table.php`
  - Columns: id, user_id (FK), project_id (FK), type, subject_type, subject_id, data (JSON), timestamps
  - Indexes: user_id, project_id, type, (subject_type, subject_id)
  - Used by: Activity logging and feed

- [x] **Notifications Table** - `2026_01_26_124109_create_notifications_table.php`
  - Columns: id, user_id (FK), type, data (JSON), read_at, timestamps
  - Indexes: user_id, read_at
  - Used by: User notifications

- [x] **User Preferences Table** - `2026_01_26_124110_create_user_preferences_table.php`
  - Columns: id, user_id (FK), key, value, timestamps
  - Unique: (user_id, key)
  - Used by: User settings storage

#### Membership Table (1)
- [x] **Project Members Table** (Pivot) - `2026_01_26_124111_create_project_members_table.php`
  - Columns: id, project_id (FK), user_id (FK), role, timestamps
  - Unique: (project_id, user_id)
  - Used by: Project access control

### Models Created

#### Core Models (13 total)
- [x] **User** - `app/Models/User.php` (Updated)
  - Relationships: role, ownedProjects, projectMembers, projects, assignedTasks, comments, activities, notifications, preferences
  - Fillable: name, email, password, role_id

- [x] **Role** - `app/Models/Role.php`
  - Relationships: users
  - Fillable: name

- [x] **Project** - `app/Models/Project.php`
  - Relationships: instructor, boards, members, projectMembers, labels, activities, tasks
  - Fillable: title, description, instructor_id, timeline_status, budget_status

- [x] **Board** - `app/Models/Board.php`
  - Relationships: project, columns
  - Fillable: project_id, title

- [x] **Column** - `app/Models/Column.php`
  - Relationships: board, tasks
  - Fillable: board_id, title, position, wip_limit

- [x] **Task** - `app/Models/Task.php`
  - Relationships: column, assignee, subtasks, comments, labels, activities
  - Fillable: column_id, title, description, assignee_id, priority, due_date, position
  - Casts: due_date (date), created_at/updated_at (datetime)

- [x] **Subtask** - `app/Models/Subtask.php`
  - Relationships: task
  - Fillable: task_id, title, is_completed, position
  - Casts: is_completed (boolean)

- [x] **Comment** - `app/Models/Comment.php`
  - Relationships: task, user, parent, replies
  - Fillable: task_id, user_id, parent_id, body, edited_at

- [x] **Label** - `app/Models/Label.php`
  - Relationships: project, tasks
  - Fillable: project_id, name, color

- [x] **Activity** - `app/Models/Activity.php`
  - Relationships: user, project, subject (polymorphic)
  - Fillable: user_id, project_id, type, subject_type, subject_id, data
  - Casts: data (json)

- [x] **Notification** - `app/Models/Notification.php`
  - Relationships: user
  - Fillable: user_id, type, data, read_at
  - Methods: markAsRead(), markAsUnread(), isRead(), isUnread()

- [x] **UserPreference** - `app/Models/UserPreference.php`
  - Relationships: user
  - Fillable: user_id, key, value

- [x] **ProjectMember** - `app/Models/ProjectMember.php`
  - Relationships: project, user
  - Fillable: project_id, user_id, role

### Documentation Created

- [x] **MIGRATIONS_AND_MODELS_SUMMARY.md** - Comprehensive documentation of all migrations and models
- [x] **DATABASE_SCHEMA.md** - Visual ERD diagram and relationship documentation

### Test Coverage (Ready for implementation)

- [ ] Model tests (unit tests for relationships)
- [ ] Migration tests (reversibility, column types)
- [ ] Factory tests (can generate valid instances)
- [ ] Feature tests (API endpoints using models)

---

## Phase 2: Authentication & Authorization (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/Auth/RegisterController.php`
  - POST /api/register - Create new user with role
  - Validate email, password (min 8 chars, contains number), name
  - Return JWT token

- [ ] `app/Http/Controllers/Auth/LoginController.php`
  - POST /api/login - Authenticate user
  - Return JWT token and user data

- [ ] `app/Http/Controllers/Auth/LogoutController.php`
  - POST /api/logout - Invalidate user token

- [ ] `app/Http/Controllers/Auth/PasswordResetController.php`
  - POST /api/password/forgot - Send reset email
  - POST /api/password/reset - Reset password

### Form Requests to Create

- [ ] `app/Http/Requests/Auth/RegisterRequest.php`
- [ ] `app/Http/Requests/Auth/LoginRequest.php`
- [ ] `app/Http/Requests/Auth/PasswordResetRequest.php`

### Middleware to Create

- [ ] `app/Http/Middleware/RoleMiddleware.php`
  - Check user role matches required role(s)
  - Return 403 if not authorized

- [ ] `app/Http/Middleware/EnsureProjectAccess.php`
  - Check user is member of project
  - Return 403 if not member

### Policies to Create

- [ ] `app/Policies/ProjectPolicy.php`
  - view(User $user, Project $project)
  - create(User $user)
  - update(User $user, Project $project)
  - delete(User $user, Project $project)

- [ ] `app/Policies/TaskPolicy.php`
  - view, create, update, delete

- [ ] `app/Policies/BoardPolicy.php`
  - view, create, update, delete

### Routes to Create

- [ ] `routes/api.php` - Update with auth routes

### Services to Create

- [ ] `app/Services/AuthService.php`
  - Token generation and validation

---

## Phase 3: Project Management (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/ProjectController.php`
  - GET /api/projects - List user's projects
  - POST /api/projects - Create project
  - GET /api/projects/{id} - Get project details
  - PUT /api/projects/{id} - Update project
  - DELETE /api/projects/{id} - Delete project

- [ ] `app/Http/Controllers/ProjectMemberController.php`
  - POST /api/projects/{id}/members - Add member
  - DELETE /api/projects/{id}/members/{userId} - Remove member

- [ ] `app/Http/Controllers/ProjectHealthController.php`
  - GET /api/projects/{id}/health - Get health status

### Form Requests

- [ ] `app/Http/Requests/StoreProjectRequest.php`
- [ ] `app/Http/Requests/UpdateProjectRequest.php`
- [ ] `app/Http/Requests/AddProjectMemberRequest.php`

### Resources to Create

- [ ] `app/Http/Resources/ProjectResource.php`
- [ ] `app/Http/Resources/ProjectCollectionResource.php`

---

## Phase 4: Kanban Board Management (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/BoardController.php`
  - GET /api/projects/{id}/boards
  - POST /api/projects/{id}/boards
  - PUT /api/boards/{id}
  - DELETE /api/boards/{id}

- [ ] `app/Http/Controllers/ColumnController.php`
  - GET /api/boards/{id}/columns
  - POST /api/boards/{id}/columns
  - PUT /api/columns/{id}

- [ ] `app/Http/Controllers/TaskController.php`
  - GET /api/boards/{id}/tasks
  - POST /api/tasks
  - PUT /api/tasks/{id}
  - PUT /api/tasks/{id}/move (with WIP limit check)
  - DELETE /api/tasks/{id}

### Form Requests

- [ ] `app/Http/Requests/StoreTaskRequest.php`
- [ ] `app/Http/Requests/MoveTaskRequest.php`
- [ ] `app/Http/Requests/StoreSubtaskRequest.php`

### Resources

- [ ] `app/Http/Resources/TaskResource.php`
- [ ] `app/Http/Resources/BoardResource.php`

### Services

- [ ] `app/Services/TaskService.php`
  - Move task (with WIP limit validation)
  - Reorder tasks

---

## Phase 5: Comments & Subtasks (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/CommentController.php`
  - GET /api/tasks/{id}/comments
  - POST /api/tasks/{id}/comments
  - PUT /api/comments/{id}
  - DELETE /api/comments/{id}

- [ ] `app/Http/Controllers/SubtaskController.php`
  - GET /api/tasks/{id}/subtasks
  - POST /api/tasks/{id}/subtasks
  - PUT /api/subtasks/{id}
  - PUT /api/subtasks/{id}/toggle
  - DELETE /api/subtasks/{id}

### Form Requests

- [ ] `app/Http/Requests/StoreCommentRequest.php`
- [ ] `app/Http/Requests/StoreSubtaskRequest.php`

### Services

- [ ] `app/Services/CommentService.php`
  - processMentions($comment) - Extract @mentions and create notifications

---

## Phase 6: Dashboard & Analytics (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/DashboardController.php`
  - GET /api/dashboard/stats
  - GET /api/dashboard/health-matrix
  - GET /api/dashboard/progress
  - GET /api/dashboard/deadlines

- [ ] `app/Http/Controllers/ActivityController.php`
  - GET /api/activities (paginated, filterable by type)

### Services

- [ ] `app/Services/DashboardService.php`
  - Calculate stats (project count, task counts, trends)
  - Build health matrix
  - Calculate completion rates

---

## Phase 7: Student Performance Analytics (PENDING)

### Controllers to Create

- [ ] `app/Http/Controllers/StudentAnalyticsController.php`
  - GET /api/students?search=
  - GET /api/students/{id}/performance
  - GET /api/students/{id}/contributions
  - GET /api/students/{id}/funnel
  - GET /api/students/{id}/metrics
  - GET /api/students/{id}/skills-gap
  - GET /api/students/{id}/insights

### Services

- [ ] `app/Services/StudentAnalyticsService.php`
  - calculatePerformanceMetrics()
  - calculateCodeQuality()
  - calculateDeadlineAdherence()
  - calculateCollaboration()

---

## Phase 8: Events & Listeners (PENDING)

### Events to Create

- [ ] `app/Events/TaskCreated.php`
- [ ] `app/Events/TaskMoved.php`
- [ ] `app/Events/CommentAdded.php`
- [ ] `app/Events/TaskAssigned.php`

### Listeners to Create

- [ ] `app/Listeners/LogTaskActivity.php` - Log activity when task changes
- [ ] `app/Listeners/NotifyMentionedUsers.php` - Send mentions notifications
- [ ] `app/Listeners/NotifyAssignedUser.php` - Notify when assigned

---

## Phase 9: Broadcasting (PENDING)

### Setup

- [ ] Configure Laravel Echo for real-time updates
- [ ] Setup Pusher/Soketi for WebSocket broadcasting
- [ ] Create broadcast channels in `routes/channels.php`

### Channels to Create

- [ ] `private.project.{projectId}` - Project-specific updates
- [ ] `private.user.{userId}` - User notifications

---

## Phase 10: Testing (PENDING)

### Unit Tests

- [ ] `tests/Unit/Models/` - Model relationship tests
- [ ] `tests/Unit/Services/` - Service logic tests

### Feature Tests

- [ ] `tests/Feature/Auth/` - Authentication tests
- [ ] `tests/Feature/Projects/` - Project CRUD tests
- [ ] `tests/Feature/Tasks/` - Task management tests
- [ ] `tests/Feature/Dashboard/` - Dashboard API tests

### Integration Tests

- [ ] Database transaction tests
- [ ] Foreign key constraint tests

---

## Phase 11: Frontend (PENDING)

### Vue Components

- [ ] Dashboard pages
- [ ] Kanban board UI
- [ ] Task detail panels
- [ ] Analytics views

---

## Database Migration Checklist

### Before Running Migrations

- [x] All migration files created
- [x] All models created
- [x] Relationships defined
- [x] Foreign key constraints verified
- [ ] Set up test database

### Running Migrations

```bash
# Fresh database setup (development only)
php artisan migrate:fresh

# Rollback to previous state
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Create new migration
php artisan make:migration migration_name
```

---

## Next Immediate Steps

1. **Create Controllers** for basic CRUD operations
2. **Create Form Requests** for input validation
3. **Define API Routes** in routes/api.php
4. **Create Resources** for API responses
5. **Implement Authentication** with Laravel Sanctum
6. **Write Tests** for models and controllers
7. **Setup Frontend** with Vue components

---

## Database Verification Commands

```bash
# Check all tables created
php artisan tinker
>>> DB::table('information_schema.tables')->where('table_schema', 'database_name')->pluck('table_name');

# Test relationships
>>> $project = Project::with('boards.columns.tasks')->first();
>>> $project->boards; // Should work
>>> $project->boards[0]->columns; // Should work

# Check foreign keys
>>> $task = Task::first();
>>> $task->assignee; // Should return User if assigned
>>> $task->column; // Should return Column
```

---

## Summary

**Status:** Phase 1 - COMPLETE
- ✅ 14 migrations created (includes 1 update to users table)
- ✅ 13 models created with full relationships
- ✅ Documentation complete
- ⏳ Ready for Controller/Service layer implementation

**Total Files Created/Updated:**
- Migrations: 14 files
- Models: 13 files
- Documentation: 2 files

**Lines of Code:**
- Migrations: ~500 lines
- Models: ~1,200 lines
- Documentation: ~1,500 lines

**Ready to Proceed With:**
- Controller implementations
- API endpoint definitions
- Authentication system
- Testing suite
