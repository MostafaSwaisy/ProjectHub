# Data Model: Kanban Board & Task Management

**Feature**: 004-kanban-task-management
**Date**: 2026-02-05
**Status**: Complete

## Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Project   │1─────*│    Board    │1─────*│   Column    │
└─────────────┘       └─────────────┘       └─────────────┘
       │                                           │1
       │                                           │
       │1                                          │*
       │                                    ┌─────────────┐
       │                              *─────│    Task     │─────1
       │                              │     └─────────────┘      │
       │                              │            │1            │
       │                              │            │             │
┌─────────────┐                       │     ┌──────┴──────┐      │
│    Label    │*──────────────────────┘     │             │      │
└─────────────┘                             │*            │*     │
       │                              ┌─────────────┐ ┌─────────────┐
       │                              │   Subtask   │ │   Comment   │
       │                              └─────────────┘ └─────────────┘
       │                                                    │1
       │                                                    │
       │*                                                   │*
┌─────────────┐                                      ┌─────────────┐
│  TaskLabel  │ (pivot)                              │   Comment   │
└─────────────┘                                      │  (replies)  │
                                                     └─────────────┘

┌─────────────┐       ┌─────────────┐
│    User     │1─────*│  Activity   │
└─────────────┘       └─────────────┘
       │                     │
       │1                    │ polymorphic
       │*                    │ (subject)
┌─────────────┐              │
│    Task     │◄─────────────┘
│  (assignee) │
└─────────────┘
```

---

## Entities

### Task (EXISTS)

Primary entity for work items on the Kanban board.

**Table**: `tasks`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| column_id | bigint | FK → columns.id, NOT NULL | Current column (status) |
| title | varchar(255) | NOT NULL | Task title |
| description | text | NULLABLE | Detailed description |
| assignee_id | bigint | FK → users.id, NULLABLE | Assigned user |
| priority | enum | NOT NULL, default: 'medium' | low, medium, high, critical |
| due_date | date | NULLABLE | Task deadline |
| position | integer | NOT NULL, default: 0 | Order within column |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` Column (column_id)
- `belongsTo` User as assignee (assignee_id)
- `hasMany` Subtask
- `hasMany` Comment
- `belongsToMany` Label via task_labels
- `morphMany` Activity as subject

**Computed Attributes**:
- `progress`: Percentage of completed subtasks (0-100)
- `completed_subtask_count`: Count of completed subtasks
- `is_overdue`: Boolean, true if due_date < today

**Validation Rules**:
- title: required, max 255 chars
- description: optional, string
- assignee_id: optional, must exist in users table
- priority: required, one of [low, medium, high, critical]
- due_date: optional, must be valid date
- column_id: required, must exist in columns table

---

### Board (EXISTS)

Container for Kanban columns within a project.

**Table**: `boards`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| project_id | bigint | FK → projects.id, NOT NULL | Parent project |
| title | varchar(255) | NOT NULL | Board name |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` Project (project_id)
- `hasMany` Column

**Lifecycle Events**:
- On create: Auto-create 5 default columns (Backlog, To Do, In Progress, Review, Completed)

---

### Column (EXISTS)

Status container for tasks on a board.

**Table**: `columns`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| board_id | bigint | FK → boards.id, NOT NULL | Parent board |
| title | varchar(100) | NOT NULL | Column name |
| position | integer | NOT NULL | Display order |
| wip_limit | integer | NOT NULL, default: 0 | Work-in-progress limit (0 = unlimited) |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` Board (board_id)
- `hasMany` Task

**Business Rules**:
- WIP limit of 0 means unlimited tasks allowed
- When WIP limit > 0, block adding tasks if count >= limit
- Position determines left-to-right display order

---

### Subtask (EXISTS)

Checklist items within a task.

**Table**: `subtasks`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| task_id | bigint | FK → tasks.id, NOT NULL, CASCADE | Parent task |
| title | varchar(255) | NOT NULL | Subtask description |
| is_completed | boolean | NOT NULL, default: false | Completion status |
| position | integer | NOT NULL, default: 0 | Display order |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` Task (task_id)

**Validation Rules**:
- title: required, max 255 chars
- is_completed: boolean

**Cascade**: Deleted when parent task is deleted.

---

### Comment (EXISTS)

Discussion entries on tasks.

**Table**: `comments`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| task_id | bigint | FK → tasks.id, NOT NULL, CASCADE | Parent task |
| user_id | bigint | FK → users.id, NOT NULL | Author |
| parent_id | bigint | FK → comments.id, NULLABLE, CASCADE | Parent comment (for threading) |
| body | text | NOT NULL | Comment content |
| edited_at | timestamp | NULLABLE | Last edit time (if edited) |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` Task (task_id)
- `belongsTo` User (user_id)
- `belongsTo` Comment as parent (parent_id)
- `hasMany` Comment as replies (parent_id)

**Validation Rules**:
- body: required, min 1 char
- parent_id: optional, must exist in comments table, must belong to same task

**Business Rules**:
- Edit allowed only within 15 minutes of creation
- Delete allowed only by author
- `edited_at` set on successful edit
- Threading depth: unlimited (but UI may limit display)

**Cascade**: Deleted when parent task is deleted. Replies deleted when parent comment deleted.

---

### Label (EXISTS)

Categorization tags for tasks within a project.

**Table**: `labels`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| project_id | bigint | FK → projects.id, NOT NULL, CASCADE | Parent project |
| name | varchar(50) | NOT NULL | Label name |
| color | varchar(7) | NOT NULL | Hex color code (#RRGGBB) |
| created_at | timestamp | NOT NULL | Creation time |
| updated_at | timestamp | NOT NULL | Last modification |

**Unique Constraint**: (project_id, name) - no duplicate names per project

**Relationships**:
- `belongsTo` Project (project_id)
- `belongsToMany` Task via task_labels

**Validation Rules**:
- name: required, max 50 chars, unique per project
- color: required, valid hex format (#RRGGBB)

**Cascade**: Deleted when parent project is deleted. Pivot records removed.

---

### TaskLabel (EXISTS - Pivot)

Many-to-many relationship between tasks and labels.

**Table**: `task_labels`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| task_id | bigint | FK → tasks.id, NOT NULL, CASCADE | Task |
| label_id | bigint | FK → labels.id, NOT NULL, CASCADE | Label |
| created_at | timestamp | NOT NULL | Association time |
| updated_at | timestamp | NOT NULL | Last modification |

**Unique Constraint**: (task_id, label_id) - no duplicate associations

---

### Activity (EXISTS)

Audit trail for task-related events.

**Table**: `activities`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Unique identifier |
| user_id | bigint | FK → users.id, NOT NULL | Actor |
| project_id | bigint | FK → projects.id, NOT NULL, CASCADE | Project context |
| type | varchar(50) | NOT NULL | Event type |
| subject_type | varchar(255) | NOT NULL | Polymorphic model class |
| subject_id | bigint | NOT NULL | Polymorphic model ID |
| data | json | NULLABLE | Event-specific metadata |
| created_at | timestamp | NOT NULL | Event time |
| updated_at | timestamp | NOT NULL | Last modification |

**Relationships**:
- `belongsTo` User (user_id)
- `belongsTo` Project (project_id)
- `morphTo` subject (subject_type, subject_id)

**Activity Types**:
| Type | Subject | Data Schema |
|------|---------|-------------|
| `task.created` | Task | `{title: string}` |
| `task.updated` | Task | `{changes: {field: {old, new}}}` |
| `task.moved` | Task | `{from_column: string, to_column: string}` |
| `task.assigned` | Task | `{assignee_id: int, assignee_name: string}` |
| `task.due_date_changed` | Task | `{old_date: string, new_date: string}` |
| `task.deleted` | Task | `{title: string}` |
| `subtask.created` | Subtask | `{title: string}` |
| `subtask.completed` | Subtask | `{title: string}` |
| `comment.created` | Comment | `{excerpt: string}` |
| `label.assigned` | Task | `{label_name: string}` |
| `label.removed` | Task | `{label_name: string}` |

---

## State Transitions

### Task Status (via Column Movement)

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│ Backlog  │────▶│  To Do   │────▶│In Progress│────▶│  Review  │────▶│Completed │
└──────────┘     └──────────┘     └──────────┘     └──────────┘     └──────────┘
     ▲                │                 │                │                │
     │                │                 │                │                │
     └────────────────┴─────────────────┴────────────────┴────────────────┘
                              (any column can move to any other)
```

**Notes**:
- Tasks can move freely between any columns
- Movement blocked if target column at WIP limit
- Each move generates activity log entry

### Subtask Completion

```
┌──────────┐                    ┌──────────┐
│ Pending  │◄──────────────────▶│Completed │
│  (false) │                    │  (true)  │
└──────────┘                    └──────────┘
```

**Notes**:
- Simple boolean toggle
- Completion updates parent task's progress percentage
- Generates activity log entry

### Comment Edit Window

```
┌──────────────┐   15 min elapsed   ┌──────────────┐
│   Editable   │──────────────────▶│    Locked    │
│ (can modify) │                    │ (read-only)  │
└──────────────┘                    └──────────────┘
```

**Notes**:
- Edit/delete allowed within 15 minutes of creation
- `edited_at` timestamp set on edit
- UI hides edit/delete buttons after window expires
- Backend enforces window in CommentPolicy

---

## Indexes

### Performance Indexes

```sql
-- Task queries
CREATE INDEX idx_tasks_column_position ON tasks(column_id, position);
CREATE INDEX idx_tasks_assignee ON tasks(assignee_id);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_tasks_priority ON tasks(priority);

-- Subtask queries
CREATE INDEX idx_subtasks_task_position ON subtasks(task_id, position);

-- Comment queries
CREATE INDEX idx_comments_task_created ON comments(task_id, created_at);
CREATE INDEX idx_comments_parent ON comments(parent_id);

-- Label queries
CREATE INDEX idx_labels_project ON labels(project_id);
CREATE INDEX idx_task_labels_task ON task_labels(task_id);
CREATE INDEX idx_task_labels_label ON task_labels(label_id);

-- Activity queries
CREATE INDEX idx_activities_project_created ON activities(project_id, created_at DESC);
CREATE INDEX idx_activities_subject ON activities(subject_type, subject_id);
```

---

## Migration Notes

Most tables already exist from previous implementations. New migrations needed:

1. **Add `edited_at` to comments** (if not present):
   ```php
   Schema::table('comments', function (Blueprint $table) {
       $table->timestamp('edited_at')->nullable()->after('body');
   });
   ```

2. **Verify indexes exist** - run migration to add missing indexes.

No schema changes required for core entities - models and tables are already properly structured.
