# Data Model: Soft Delete Support

**Feature**: 005-soft-delete | **Date**: 2026-03-07

## Schema Changes

### Migration: Add Soft Delete Columns

A single migration adds `deleted_at` and `deleted_by` columns to all 10 target tables.

**Tables affected**: users, projects, boards, columns, tasks, subtasks, comments, labels, activities, project_members

**New columns per table**:

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|
| `deleted_at` | timestamp | yes | null | yes | When the record was soft-deleted |
| `deleted_by` | foreignId (unsigned bigint) | yes | null | no | User who performed the deletion |

**Index**: `deleted_at` is indexed on each table for efficient `onlyTrashed()` queries in the trash view.

**Foreign key**: `deleted_by` references `users.id` with `ON DELETE SET NULL` (if the deleting user is later removed, the record remains but loses attribution).

## Entity Definitions

### User (existing — modified)
```
users
├── id (PK)
├── name
├── email (unique)
├── email_verified_at
├── password
├── remember_token
├── role_id (FK → roles.id)
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasApiTokens, HasFactory, Notifiable, SoftDeletes
```

### Project (existing — modified)
```
projects
├── id (PK)
├── title
├── description
├── instructor_id (FK → users.id)
├── timeline_status
├── budget_status
├── is_archived
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: boards
```

### Board (existing — modified)
```
boards
├── id (PK)
├── project_id (FK → projects.id)
├── title
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: columns
```

### Column (existing — modified)
```
columns
├── id (PK)
├── board_id (FK → boards.id)
├── title
├── position
├── wip_limit
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: tasks
```

### Task (existing — modified)
```
tasks
├── id (PK)
├── column_id (FK → columns.id)
├── title
├── description
├── assignee_id (FK → users.id, nullable)
├── priority
├── due_date
├── position
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: subtasks, comments
```

### Subtask (existing — modified)
```
subtasks
├── id (PK)
├── task_id (FK → tasks.id)
├── title
├── is_completed
├── position
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: none (leaf entity)
```

### Comment (existing — modified)
```
comments
├── id (PK)
├── task_id (FK → tasks.id)
├── user_id (FK → users.id)
├── parent_id (FK → comments.id, self-referencing)
├── body
├── edited_at
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: none (leaf entity)
```

### Label (existing — modified)
```
labels
├── id (PK)
├── project_id (FK → projects.id)
├── name
├── color
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: none (shared entity, no cascade)
```

### Activity (existing — modified)
```
activities
├── id (PK)
├── user_id (FK → users.id)
├── project_id (FK → projects.id)
├── type
├── subject_type
├── subject_id
├── data (JSON)
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: none (log entity, no cascade)
```

### ProjectMember (existing — modified)
```
project_members
├── id (PK)
├── project_id (FK → projects.id)
├── user_id (FK → users.id)
├── role
├── created_at
├── updated_at
├── deleted_at       ← NEW
└── deleted_by       ← NEW (FK → users.id)

Traits: HasFactory, SoftDeletes
Cascade deletes: none
```

## Cascade Hierarchy

```
Project
├── Board (cascade soft-delete/restore)
│   └── Column (cascade soft-delete/restore)
│       └── Task (cascade soft-delete/restore)
│           ├── Subtask (cascade soft-delete/restore)
│           └── Comment (cascade soft-delete/restore)
├── Label (NO cascade — shared resource)
├── Activity (NO cascade — audit log)
└── ProjectMember (NO cascade — managed separately)
```

## Cascade Restore Rules

When restoring a parent entity:
1. Query children with `onlyTrashed()` where `deleted_at` = parent's `deleted_at` timestamp
2. Restore only those matching children (same cascade batch)
3. Recursively apply the same logic down the hierarchy
4. Children that were independently deleted (different `deleted_at`) remain trashed

## New Traits

### HasSoftDeleteUser
- Hooks into `deleting` model event
- Sets `deleted_by` to `auth()->id()` before soft-delete
- Clears `deleted_by` on `restoring` event

### HasCascadeSoftDeletes
- Defines `$cascadeDeletes` array property on models (e.g., `['boards']` on Project)
- On `deleting`: iterates cascade relationships, calls `delete()` on each child
- On `restoring`: iterates cascade relationships, restores children with matching `deleted_at`
- On `forceDeleting`: iterates cascade relationships, calls `forceDelete()` on each child
- Checks `$this->isForceDeleting()` to differentiate behavior

## Validation Rules

- `deleted_at`: Managed by framework — no manual validation needed
- `deleted_by`: Auto-populated via trait — no user input
- Restore: Must verify parent exists (or prompt user for new parent if parent is force-deleted)
- Force-delete: Must verify user is project owner or task assignee (FR-008)
