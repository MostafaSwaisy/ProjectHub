# ProjectHub Analytics - Migrations and Models Summary

## Overview

All database migrations and Eloquent models have been created for the ProjectHub Analytics system. The implementation follows Laravel conventions with:
- Proper foreign key constraints with `onDelete` behavior
- Comprehensive indexing for query performance
- Full relationship definitions in models
- Type casting for dates and JSON fields
- Mass assignment protection with fillable attributes

---

## Database Migrations Created

### 1. Roles Table
**File:** `database/migrations/2026_01_26_123951_create_roles_table.php`

Columns:
- `id` (primary key)
- `name` (unique string)
- `timestamps` (created_at, updated_at)

---

### 2. Add Role ID to Users Table
**File:** `database/migrations/2026_01_26_124044_add_role_id_to_users_table.php`

Modifications:
- `role_id` (foreign key to roles, nullable, cascade on delete)

---

### 3. Projects Table (Updated)
**File:** `database/migrations/2026_01_26_124102_create_projects_table.php`

Columns:
- `id` (primary key)
- `title` (string)
- `description` (text, nullable)
- `instructor_id` (foreign key to users, cascade on delete)
- `timeline_status` (enum: behind, on_track, ahead, default: on_track)
- `budget_status` (enum: over_budget, on_budget, under_budget, default: on_budget)
- `timestamps`
- **Indexes:** instructor_id

---

### 4. Boards Table (Updated)
**File:** `database/migrations/2026_01_26_124102_create_boards_table.php`

Columns:
- `id` (primary key)
- `project_id` (foreign key to projects, cascade on delete)
- `title` (string)
- `timestamps`
- **Indexes:** project_id

---

### 5. Columns Table (Updated)
**File:** `database/migrations/2026_01_26_124102_create_columns_table.php`

Columns:
- `id` (primary key)
- `board_id` (foreign key to boards, cascade on delete)
- `title` (string)
- `position` (integer, default: 0)
- `wip_limit` (integer, default: 0)
- `timestamps`
- **Indexes:** board_id

---

### 6. Tasks Table (Updated)
**File:** `database/migrations/2026_01_26_124103_create_tasks_table.php`

Columns:
- `id` (primary key)
- `column_id` (foreign key to columns, cascade on delete)
- `title` (string)
- `description` (text, nullable)
- `assignee_id` (foreign key to users, nullable, set null on delete)
- `priority` (enum: low, medium, high, critical, default: medium)
- `due_date` (date, nullable)
- `position` (integer, default: 0)
- `timestamps`
- **Indexes:** column_id, assignee_id, due_date

---

### 7. Subtasks Table
**File:** `database/migrations/2026_01_26_124104_create_subtasks_table.php`

Columns:
- `id` (primary key)
- `task_id` (foreign key to tasks, cascade on delete)
- `title` (string)
- `is_completed` (boolean, default: false)
- `position` (integer, default: 0)
- `timestamps`
- **Indexes:** task_id

---

### 8. Comments Table
**File:** `database/migrations/2026_01_26_124105_create_comments_table.php`

Columns:
- `id` (primary key)
- `task_id` (foreign key to tasks, cascade on delete)
- `user_id` (foreign key to users, cascade on delete)
- `parent_id` (foreign key to comments, nullable, cascade on delete - for threaded comments)
- `body` (text)
- `edited_at` (timestamp, nullable)
- `timestamps`
- **Indexes:** task_id, user_id, parent_id

---

### 9. Labels Table
**File:** `database/migrations/2026_01_26_124106_create_labels_table.php`

Columns:
- `id` (primary key)
- `project_id` (foreign key to projects, cascade on delete)
- `name` (string)
- `color` (string, default: '#9CA3AF')
- `timestamps`
- **Indexes:** project_id
- **Unique Constraints:** (project_id, name)

---

### 10. Task Labels Table (Pivot)
**File:** `database/migrations/2026_01_26_124107_create_task_labels_table.php`

Columns:
- `id` (primary key)
- `task_id` (foreign key to tasks, cascade on delete)
- `label_id` (foreign key to labels, cascade on delete)
- `timestamps`
- **Indexes:** label_id
- **Unique Constraints:** (task_id, label_id)

---

### 11. Activities Table
**File:** `database/migrations/2026_01_26_124108_create_activities_table.php`

Columns:
- `id` (primary key)
- `user_id` (foreign key to users, cascade on delete)
- `project_id` (foreign key to projects, cascade on delete)
- `type` (string - e.g., task_created, task_updated, task_moved, comment_added)
- `subject_type` (string, nullable - polymorphic: Task, Comment, Project, etc.)
- `subject_id` (unsigned big integer, nullable - polymorphic)
- `data` (JSON, nullable - stores additional context)
- `timestamps`
- **Indexes:** user_id, project_id, type, (subject_type, subject_id)

---

### 12. Notifications Table
**File:** `database/migrations/2026_01_26_124109_create_notifications_table.php`

Columns:
- `id` (primary key)
- `user_id` (foreign key to users, cascade on delete)
- `type` (string - e.g., mention, assigned, comment)
- `data` (JSON - stores notification context)
- `read_at` (timestamp, nullable)
- `timestamps`
- **Indexes:** user_id, read_at

---

### 13. User Preferences Table
**File:** `database/migrations/2026_01_26_124110_create_user_preferences_table.php`

Columns:
- `id` (primary key)
- `user_id` (foreign key to users, cascade on delete)
- `key` (string)
- `value` (long text, nullable)
- `timestamps`
- **Indexes:** user_id
- **Unique Constraints:** (user_id, key)

---

### 14. Project Members Table (Pivot)
**File:** `database/migrations/2026_01_26_124111_create_project_members_table.php`

Columns:
- `id` (primary key)
- `project_id` (foreign key to projects, cascade on delete)
- `user_id` (foreign key to users, cascade on delete)
- `role` (enum: owner, editor, viewer, default: viewer)
- `timestamps`
- **Indexes:** user_id
- **Unique Constraints:** (project_id, user_id)

---

## Eloquent Models Created

### Model Locations
All models are located in `app/Models/`

### 1. Role Model
**File:** `app/Models/Role.php`

**Fillable:**
- name

**Relationships:**
- `hasMany: users()` - Users with this role

---

### 2. User Model (Updated)
**File:** `app/Models/User.php`

**Fillable:**
- name, email, password, role_id

**Relationships:**
- `belongsTo: role()` - User's assigned role
- `hasMany: ownedProjects()` - Projects where user is instructor
- `hasMany: projectMembers()` - Project membership records
- `belongsToMany: projects()` - Projects user is member of (via project_members)
- `hasMany: assignedTasks()` - Tasks assigned to user
- `hasMany: comments()` - Comments written by user
- `hasMany: activities()` - Activities performed by user
- `hasMany: notifications()` - Notifications for user
- `hasMany: preferences()` - User preferences

**Important Methods:**
- All relationships use proper foreign key references
- Password is automatically hashed via 'hashed' cast

---

### 3. Project Model
**File:** `app/Models/Project.php`

**Fillable:**
- title, description, instructor_id, timeline_status, budget_status

**Relationships:**
- `belongsTo: instructor()` - User who owns the project
- `hasMany: boards()` - Project boards
- `belongsToMany: members()` - Project members (via project_members, with role pivot)
- `hasMany: projectMembers()` - Project membership records
- `hasMany: labels()` - Labels for this project
- `hasMany: activities()` - Activities in this project
- `hasManyThrough: tasks()` - All tasks across all boards (nested relationship)

---

### 4. Board Model
**File:** `app/Models/Board.php`

**Fillable:**
- project_id, title

**Relationships:**
- `belongsTo: project()` - Parent project
- `hasMany: columns()` - Board columns (ordered by position)

---

### 5. Column Model
**File:** `app/Models/Column.php`

**Fillable:**
- board_id, title, position, wip_limit

**Relationships:**
- `belongsTo: board()` - Parent board
- `hasMany: tasks()` - Column tasks (ordered by position)

---

### 6. Task Model
**File:** `app/Models/Task.php`

**Fillable:**
- column_id, title, description, assignee_id, priority, due_date, position

**Casts:**
- due_date: date
- created_at, updated_at: datetime

**Relationships:**
- `belongsTo: column()` - Parent column
- `belongsTo: assignee()` - Assigned user
- `hasMany: subtasks()` - Task subtasks (ordered by position)
- `hasMany: comments()` - Task comments
- `belongsToMany: labels()` - Task labels (via task_labels)
- `hasMany: activities()` - Activities related to this task

---

### 7. Subtask Model
**File:** `app/Models/Subtask.php`

**Fillable:**
- task_id, title, is_completed, position

**Casts:**
- is_completed: boolean
- created_at, updated_at: datetime

**Relationships:**
- `belongsTo: task()` - Parent task

---

### 8. Comment Model
**File:** `app/Models/Comment.php`

**Fillable:**
- task_id, user_id, parent_id, body, edited_at

**Casts:**
- edited_at, created_at, updated_at: datetime

**Relationships:**
- `belongsTo: task()` - Parent task
- `belongsTo: user()` - Comment author
- `belongsTo: parent()` - Parent comment (for threads)
- `hasMany: replies()` - Reply comments

---

### 9. Label Model
**File:** `app/Models/Label.php`

**Fillable:**
- project_id, name, color

**Relationships:**
- `belongsTo: project()` - Parent project
- `belongsToMany: tasks()` - Tasks with this label (via task_labels)

---

### 10. Activity Model
**File:** `app/Models/Activity.php`

**Fillable:**
- user_id, project_id, type, subject_type, subject_id, data

**Casts:**
- data: json
- created_at, updated_at: datetime

**Relationships:**
- `belongsTo: user()` - User who performed activity
- `belongsTo: project()` - Project where activity occurred
- `morphTo: subject()` - Polymorphic relationship to the subject (Task, Comment, etc.)

**Note:** Activity logging is polymorphic, allowing tracking of actions on multiple entity types.

---

### 11. Notification Model
**File:** `app/Models/Notification.php`

**Fillable:**
- user_id, type, data, read_at

**Casts:**
- data: json
- read_at: datetime
- created_at, updated_at: datetime

**Relationships:**
- `belongsTo: user()` - Notification recipient

**Helper Methods:**
- `markAsRead()` - Mark notification as read
- `markAsUnread()` - Mark notification as unread
- `isRead()` - Check if read
- `isUnread()` - Check if unread

---

### 12. UserPreference Model
**File:** `app/Models/UserPreference.php`

**Fillable:**
- user_id, key, value

**Relationships:**
- `belongsTo: user()` - User owning this preference

**Use Cases:**
- Theme preference (dark/light)
- Notification settings
- UI layout preferences
- Any key-value user settings

---

### 13. ProjectMember Model
**File:** `app/Models/ProjectMember.php`

**Fillable:**
- project_id, user_id, role

**Relationships:**
- `belongsTo: project()` - Member project
- `belongsTo: user()` - Member user

**Roles:**
- owner: Full project control
- editor: Can edit tasks and board
- viewer: Read-only access

---

## Key Design Decisions

### 1. Cascading Deletes
- Most foreign keys use `onDelete('cascade')` to automatically clean up related records
- User assignments use `onDelete('set null')` to preserve task history
- This ensures data integrity and prevents orphaned records

### 2. Polymorphic Activities
- Activities table uses polymorphic relationships to track actions on different entity types
- Allows flexible activity logging without separate tables for each entity type
- Query with eager loading: `Activity::with('subject')->get()`

### 3. Pivot Tables
- `project_members`: Stores project membership with role
- `task_labels`: Many-to-many relationship between tasks and labels
- Both include `timestamps` for audit trails

### 4. Ordering by Position
- Columns and tasks are ordered by `position` field
- Enables drag-and-drop functionality on frontend
- Relationships automatically order by position for consistency

### 5. Enum Fields
- Priority: low, medium, high, critical
- Timeline Status: behind, on_track, ahead
- Budget Status: over_budget, on_budget, under_budget
- Project Member Role: owner, editor, viewer
- Provides type safety and database constraints

### 6. JSON Storage
- Activities table stores additional context in `data` JSON column
- Notifications table stores notification context in `data` JSON column
- Allows flexible data structure without schema changes

### 7. Nullable Foreign Keys
- Task.assignee_id: Optional assignment
- Comment.parent_id: Optional threading
- Activity.subject_type/subject_id: Polymorphic
- UserPreference.value: Can be null
- Label color: Default provided

### 8. Indexing Strategy
- Foreign keys are indexed for join performance
- Frequently queried columns (status fields, dates) are indexed
- Composite indexes on search-heavy combinations

---

## Migration Order

When running migrations, they will execute in this order:

1. Create users table (from Laravel default)
2. Create roles table
3. Add role_id to users table
4. Create projects table
5. Create boards table
6. Create columns table
7. Create tasks table
8. Create subtasks table
9. Create comments table
10. Create labels table
11. Create task_labels table (pivot)
12. Create activities table
13. Create notifications table
14. Create user_preferences table
15. Create project_members table (pivot)

---

## Running Migrations

```bash
# Create all tables
php artisan migrate

# Rollback all migrations (use with caution in production)
php artisan migrate:rollback

# Refresh database (drop and recreate all tables)
php artisan migrate:fresh

# See migration status
php artisan migrate:status
```

---

## Using the Models

### Create a Project
```php
$project = Project::create([
    'title' => 'Mobile App Development',
    'description' => 'Build iOS and Android apps',
    'instructor_id' => Auth::id(),
    'timeline_status' => 'on_track',
    'budget_status' => 'on_budget'
]);
```

### Add Project Members
```php
$project->members()->attach($userId, ['role' => 'editor']);
// or
ProjectMember::create([
    'project_id' => $project->id,
    'user_id' => $userId,
    'role' => 'viewer'
]);
```

### Create Board and Columns
```php
$board = $project->boards()->create(['title' => 'Development Board']);
$board->columns()->create(['title' => 'TODO', 'position' => 0]);
$board->columns()->create(['title' => 'In Progress', 'position' => 1]);
$board->columns()->create(['title' => 'Done', 'position' => 2]);
```

### Create Task
```php
$task = Task::create([
    'column_id' => $columnId,
    'title' => 'Implement authentication',
    'description' => 'Add JWT authentication',
    'assignee_id' => $userId,
    'priority' => 'high',
    'due_date' => now()->addDays(7),
    'position' => 0
]);
```

### Add Labels to Task
```php
$task->labels()->attach([$labelId1, $labelId2]);
```

### Create Comment
```php
$comment = Comment::create([
    'task_id' => $task->id,
    'user_id' => Auth::id(),
    'body' => 'Let me know when this is ready for review'
]);

// Reply to comment
$reply = Comment::create([
    'task_id' => $task->id,
    'user_id' => Auth::id(),
    'parent_id' => $comment->id,
    'body' => 'Will have it ready by Friday'
]);
```

### Log Activity
```php
Activity::create([
    'user_id' => Auth::id(),
    'project_id' => $project->id,
    'type' => 'task_created',
    'subject_type' => Task::class,
    'subject_id' => $task->id,
    'data' => [
        'title' => $task->title,
        'column' => $task->column->title
    ]
]);
```

### Send Notification
```php
Notification::create([
    'user_id' => $userId,
    'type' => 'mention',
    'data' => [
        'comment_id' => $comment->id,
        'task_id' => $task->id,
        'mentioned_by' => Auth::id()
    ]
]);
```

---

## Next Steps

1. Create Controllers for CRUD operations
2. Create API Routes and FormRequests for validation
3. Create Policies for authorization
4. Create Services for business logic
5. Create Events/Listeners for activity logging
6. Create Seeders for test data
7. Write Feature and Unit Tests

---

## Notes

- All models use `HasFactory` trait - create factories as needed
- Models follow Laravel naming conventions (singular, CamelCase)
- Relationships use proper naming conventions
- All timestamps are automatically managed by Laravel
- Foreign keys use modern Laravel syntax with `foreignId()`
- Models are fully compatible with Laravel's query builder and Eloquent ORM
