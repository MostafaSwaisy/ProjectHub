# ProjectHub Analytics - Database Schema Diagram

## Entity Relationship Diagram (ASCII)

```
┌─────────────┐         ┌───────────────┐
│    Users    │────────▶│     Roles     │
│             │         │               │
│ id (PK)     │         │ id (PK)       │
│ name        │         │ name (UNIQUE) │
│ email       │         │ timestamps    │
│ password    │         └───────────────┘
│ role_id (FK)│
│ timestamps  │
└─────────────┘
       │
       │
       ├──────────────────────┬──────────────┬──────────────┬──────────────┐
       │                      │              │              │              │
       ▼                      ▼              ▼              ▼              ▼
┌──────────────────┐  ┌──────────────┐ ┌─────────────┐ ┌──────────────┐ ┌──────────────┐
│ Projects         │  │   Comments   │ │  Activities │ │Notifications│ │ UserPrefs    │
│ (as instructor)  │  │              │ │             │ │              │ │              │
│ id (PK)          │  │ id (PK)      │ │ id (PK)     │ │ id (PK)      │ │ id (PK)      │
│ title            │  │ task_id (FK) │ │ user_id(FK) │ │ user_id (FK) │ │ user_id (FK) │
│ description      │  │ user_id (FK) │ │ project(FK) │ │ type         │ │ key          │
│ instructor_id(FK)│  │ parent_id(FK)│ │ type        │ │ data (JSON)  │ │ value        │
│ timeline_status  │  │ body         │ │ subject_type│ │ read_at      │ │ timestamps   │
│ budget_status    │  │ edited_at    │ │ subject_id  │ │ timestamps   │ └──────────────┘
│ timestamps       │  │ timestamps   │ │ data (JSON) │ └──────────────┘
└──────────────────┘  │ (Self-ref)   │ │ timestamps  │
       │              └──────────────┘ └─────────────┘
       │                     ▲
       │ 1:N                 │ 1:N
       ▼                     │
┌──────────────────┐         │
│ ProjectMembers   │◀────────┘
│ (Pivot table)    │
│                  │
│ id (PK)          │
│ project_id (FK)  │
│ user_id (FK)     │
│ role             │
│ timestamps       │
└──────────────────┘
       │
       │ 1:1
       │
       └──────────────────────────────────────────┐
                                                  │
┌──────────────────┐                              │
│   Boards         │                              │
│                  │                              │
│ id (PK)          │                              │
│ project_id (FK)──┼──────┐                       │
│ title            │      │                       │
│ timestamps       │      │                       │
└──────────────────┘      │                       │
       │ 1:N              │                       │
       ▼                  │ Many projects         │
┌──────────────────┐      │ have many members    │
│   Columns        │      │                       │
│                  │      └──────────────┐        │
│ id (PK)          │                     │        │
│ board_id (FK)────┼────┐                │        │
│ title            │    │                │        │
│ position         │    │                │        │
│ wip_limit        │    │                │        │
│ timestamps       │    │                │        │
└──────────────────┘    │                │        │
       │ 1:N            │                │        │
       │                │                │        │
       ▼                │                │        │
┌──────────────────┐    │                │        │
│   Tasks          │    │                │        │
│                  │    │                │        │
│ id (PK)          │    │                │        │
│ column_id (FK)───┼────┤                │        │
│ title            │    └─ Defines task  │        │
│ description      │        positioning  │        │
│ assignee_id (FK) │        on columns   │        │
│ priority         │                     │        │
│ due_date         │                     │        │
│ position         │                     │        │
│ timestamps       │                     │        │
└──────────────────┘                     │        │
       │ 1:N                            │        │
       ├────────┬──────────┬────────┐   │        │
       │        │          │        │   │        │
       ▼        ▼          ▼        ▼   │        │
   Subtasks  Comments  Labels  TaskLabels        │
   (M:1)     (M:1)     (N:M)    (Pivot)          │
   ├─────┐   ├─────┐   ├──────┐                 │
   │ id  │   │ id  │   │ id   │                 │
   │task │   │task │   │projct│                 │
   │user │   │user │   │name  │                 │
   │ ... │   │ ... │   │color │                 │
   └─────┘   └─────┘   └──────┘                 │
                                  └──────────────┘
```

---

## Table Dependencies

### Creation Order (respecting foreign keys):

1. **Users** (base table)
2. **Roles** (independent)
3. **Users** update (add role_id FK)
4. **Projects** (FK: instructor_id → Users)
5. **ProjectMembers** (FK: project_id, user_id)
6. **Boards** (FK: project_id)
7. **Columns** (FK: board_id)
8. **Tasks** (FK: column_id, assignee_id)
9. **Subtasks** (FK: task_id)
10. **Comments** (FK: task_id, user_id, parent_id)
11. **Labels** (FK: project_id)
12. **TaskLabels** (FK: task_id, label_id)
13. **Activities** (FK: user_id, project_id, subject_id)
14. **Notifications** (FK: user_id)
15. **UserPreferences** (FK: user_id)

---

## Relationship Matrix

| Model | Relationship | Type | Target | Pivot/Notes |
|-------|--------------|------|--------|-------------|
| **User** | role | BelongsTo | Role | - |
| | ownedProjects | HasMany | Project | instructor_id |
| | projectMembers | HasMany | ProjectMember | - |
| | projects | BelongsToMany | Project | project_members (role) |
| | assignedTasks | HasMany | Task | assignee_id |
| | comments | HasMany | Comment | - |
| | activities | HasMany | Activity | - |
| | notifications | HasMany | Notification | - |
| | preferences | HasMany | UserPreference | - |
| **Role** | users | HasMany | User | - |
| **Project** | instructor | BelongsTo | User | instructor_id |
| | boards | HasMany | Board | - |
| | members | BelongsToMany | User | project_members (role) |
| | projectMembers | HasMany | ProjectMember | - |
| | labels | HasMany | Label | - |
| | activities | HasMany | Activity | - |
| | tasks | HasManyThrough | Task | via columns/boards |
| **Board** | project | BelongsTo | Project | - |
| | columns | HasMany | Column | ordered by position |
| **Column** | board | BelongsTo | Board | - |
| | tasks | HasMany | Task | ordered by position |
| **Task** | column | BelongsTo | Column | - |
| | assignee | BelongsTo | User | assignee_id |
| | subtasks | HasMany | Subtask | ordered by position |
| | comments | HasMany | Comment | - |
| | labels | BelongsToMany | Label | task_labels |
| | activities | HasMany | Activity | polymorphic |
| **Subtask** | task | BelongsTo | Task | - |
| **Comment** | task | BelongsTo | Task | - |
| | user | BelongsTo | User | - |
| | parent | BelongsTo | Comment | self-referencing |
| | replies | HasMany | Comment | self-referencing |
| **Label** | project | BelongsTo | Project | - |
| | tasks | BelongsToMany | Task | task_labels |
| **Activity** | user | BelongsTo | User | - |
| | project | BelongsTo | Project | - |
| | subject | MorphTo | Any | polymorphic |
| **Notification** | user | BelongsTo | User | - |
| **UserPreference** | user | BelongsTo | User | - |
| **ProjectMember** | project | BelongsTo | Project | - |
| | user | BelongsTo | User | - |

---

## Data Flow Examples

### Creating a Project with Board and Tasks

```
User (instructor_id=1)
  ↓
Project (title, description, timeline_status, budget_status)
  ↓
Board (title)
  ↓
Column (title, position, wip_limit)
  ↓
Task (title, assignee_id, priority, due_date, position)
  ├─ Subtask (title, is_completed, position)
  ├─ Subtask (...)
  ├─ Comment (user_id, body)
  └─ Label (via task_labels pivot)
```

### Project Membership

```
Project
  ↓
ProjectMembers (role: owner|editor|viewer)
  ├─ User (role=owner) - Full control
  ├─ User (role=editor) - Can edit tasks
  └─ User (role=viewer) - Read-only
```

### Activity Logging

```
Activity
├─ user_id → User (who performed action)
├─ project_id → Project (where action occurred)
├─ type → String (task_created, comment_added, etc.)
└─ subject_type/subject_id → MorphTo (Task, Comment, etc.)
    ├─ Task (if subject_type = 'App\Models\Task')
    ├─ Comment (if subject_type = 'App\Models\Comment')
    └─ Project (if subject_type = 'App\Models\Project')
```

### Notification Workflow

```
Activity Occurs
  ↓
Event Triggered
  ↓
Listener Creates Notification
  ├─ user_id → User (recipient)
  ├─ type → 'mention'|'assigned'|'comment'|etc.
  └─ data → JSON (comment_id, task_id, mentioned_by, etc.)
  ↓
User Receives Real-time Alert (WebSocket)
  ↓
User Marks as Read (read_at = now())
```

---

## Indexing Strategy

### Foreign Key Indexes (Automatic)
```
users.role_id → roles.id
projects.instructor_id → users.id
project_members.project_id → projects.id
project_members.user_id → users.id
boards.project_id → projects.id
columns.board_id → boards.id
tasks.column_id → columns.id
tasks.assignee_id → users.id
subtasks.task_id → tasks.id
comments.task_id → tasks.id
comments.user_id → users.id
comments.parent_id → comments.id
labels.project_id → projects.id
task_labels.task_id → tasks.id
task_labels.label_id → labels.id
activities.user_id → users.id
activities.project_id → projects.id
notifications.user_id → users.id
user_preferences.user_id → users.id
```

### Additional Indexes

```
tasks.due_date (for deadline queries)
activities.type (for activity filtering)
notifications.read_at (for unread notifications)
activities.(subject_type, subject_id) (for polymorphic queries)
labels.(project_id, name) → unique constraint
project_members.(project_id, user_id) → unique constraint
user_preferences.(user_id, key) → unique constraint
task_labels.(task_id, label_id) → unique constraint
```

---

## Cascade Delete Behavior

### Deleting Records

| When Deleted | Cascade Effect |
|--------------|---|
| User | Deletes: owned projects, project memberships, assigned tasks, comments, activities, notifications, preferences |
| Project | Deletes: boards, columns, tasks, subtasks, comments, labels, activities, project memberships |
| Board | Deletes: columns, tasks, subtasks, comments |
| Column | Deletes: tasks, subtasks, comments |
| Task | Deletes: subtasks, comments, task_labels, task activities |
| Label | Deletes: task_labels |
| Comment | Deletes: child comments (replies) |
| Activity | No cascades |
| Notification | No cascades |
| UserPreference | No cascades |

### Special Cases

- **Task.assignee_id**: `SET NULL` on user delete (preserves task history)
- **Comment.parent_id**: `CASCADE` delete (removes threaded replies)
- **ProjectMembers.role**: Not deleted, but user is removed from project

---

## Performance Considerations

### Query Optimization

**Eager Load Relationships:**
```php
// Avoid N+1 queries
Task::with(['assignee', 'column.board', 'subtasks', 'comments.user'])->get()
Activity::with(['user', 'project', 'subject'])->get()
Project::with(['members', 'boards.columns.tasks'])->get()
```

**Use Indexes for Filtering:**
```php
Task::where('due_date', '<', now())->get() // Uses due_date index
Activity::where('type', 'task_created')->get() // Uses type index
Notification::whereNull('read_at')->get() // Uses read_at index
```

**Pagination:**
```php
Task::paginate(20) // Always paginate large result sets
Comment::where('task_id', $taskId)->paginate(10)
Activity::paginate(20)
```

### Caching Strategy

```php
// Cache frequently accessed data
Cache::remember("project.{$projectId}.members", 5*60, function() {
    return Project::find($projectId)->members;
});

Cache::remember("user.{$userId}.accessible_projects", 5*60, function() {
    return User::find($userId)->projects;
});
```

---

## JSON Fields

### Activities.data

```json
{
  "title": "Task title",
  "column": "Column name",
  "previous_column": "Old column name",
  "priority": "high",
  "previous_priority": "medium",
  "assigned_to": "User name",
  "previous_assignee": "Another user"
}
```

### Notifications.data

```json
{
  "comment_id": 123,
  "task_id": 456,
  "mentioned_by": 789,
  "task_title": "Implement feature X",
  "task_url": "/tasks/456"
}
```

### UserPreferences.value

```json
{
  "key": "theme",
  "value": "dark"
}
{
  "key": "notifications",
  "value": "{\"email\": true, \"web\": true}"
}
```

---

## Migration Dependencies Graph

```
users (from Laravel)
  ↓
roles
  ↓
users (update role_id)
  ├─ projects
  │  ├─ project_members
  │  ├─ boards → columns → tasks → subtasks
  │  └─ labels → task_labels (from tasks)
  ├─ activities (needs users, projects, tasks)
  └─ user_preferences

comments (needs users, tasks)
notifications (needs users)
```

