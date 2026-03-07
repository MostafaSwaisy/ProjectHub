# API Contracts: Soft Delete Support

**Feature**: 005-soft-delete | **Date**: 2026-03-07

## Existing Endpoints (Modified Behavior)

These endpoints already exist. Their behavior changes from hard delete to soft delete (no URL/signature changes needed — Laravel SoftDeletes makes `delete()` a soft delete automatically):

| Method | Endpoint | Current | After |
|--------|----------|---------|-------|
| DELETE | `/api/projects/{project}` | Hard delete | Soft delete + cascade |
| DELETE | `/api/projects/{project}/boards/{board}` | Hard delete | Soft delete + cascade |
| DELETE | `/api/tasks/{task}` | Hard delete | Soft delete + cascade |
| DELETE | `/api/tasks/{task}/subtasks/{subtask}` | Hard delete | Soft delete |
| DELETE | `/api/comments/{comment}` | Hard delete | Soft delete |
| DELETE | `/api/projects/{project}/labels/{label}` | Hard delete | Soft delete |

## New Endpoints

### Trash View

#### GET `/api/projects/{project}/trash`

List all soft-deleted items within a project.

**Auth**: Required (any project member)

**Query Parameters**:
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| `type` | string | no | all | Filter by entity type: `tasks`, `boards`, `columns`, `subtasks`, `comments` |
| `page` | int | no | 1 | Pagination page |
| `per_page` | int | no | 20 | Items per page (max 100) |

**Response 200**:
```json
{
  "data": [
    {
      "id": 1,
      "type": "task",
      "title": "Task title",
      "deleted_at": "2026-03-07T10:30:00Z",
      "deleted_by": {
        "id": 1,
        "name": "John Doe"
      },
      "parent": {
        "type": "column",
        "id": 5,
        "title": "In Progress",
        "exists": true
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

**Response 403**: User is not a member of the project.

---

### Restore Endpoints

All restore endpoints use `POST` method and require the route to use `->withTrashed()` for implicit model binding.

#### POST `/api/projects/{project}/restore`

Restore a soft-deleted project and cascade-restore children.

**Auth**: Required (any project member)
**Response 200**: `{ "message": "Project restored successfully", "data": { ... } }`
**Response 404**: Project not found or not trashed.

#### POST `/api/projects/{project}/boards/{board}/restore`

Restore a soft-deleted board and cascade-restore its columns/tasks.

**Auth**: Required (any project member)
**Response 200**: `{ "message": "Board restored successfully", "data": { ... } }`

#### POST `/api/tasks/{task}/restore`

Restore a soft-deleted task and cascade-restore its subtasks/comments.

**Auth**: Required (any project member)
**Response 200**: `{ "message": "Task restored successfully", "data": { ... } }`
**Response 409**: Parent column does not exist — returns available columns for re-assignment:
```json
{
  "message": "Original column no longer exists",
  "available_columns": [
    { "id": 1, "title": "To Do" },
    { "id": 2, "title": "In Progress" }
  ]
}
```

#### POST `/api/tasks/{task}/restore`  (with body for re-assignment)

When the original column is gone, client sends:
```json
{ "column_id": 2 }
```

#### POST `/api/tasks/{task}/subtasks/{subtask}/restore`

Restore a soft-deleted subtask.

**Auth**: Required (any project member)
**Response 200**: `{ "message": "Subtask restored successfully" }`

#### POST `/api/comments/{comment}/restore`

Restore a soft-deleted comment.

**Auth**: Required (any project member)
**Response 200**: `{ "message": "Comment restored successfully" }`

---

### Force Delete Endpoints

All force-delete endpoints use `DELETE` method with `/force` suffix and require `->withTrashed()` route binding.

#### DELETE `/api/projects/{project}/force`

Permanently delete a soft-deleted project and all its children.

**Auth**: Required (project owner only)
**Response 200**: `{ "message": "Project permanently deleted" }`
**Response 403**: User is not the project owner.
**Response 404**: Project not found or not trashed.

#### DELETE `/api/projects/{project}/boards/{board}/force`

Permanently delete a soft-deleted board and its children.

**Auth**: Required (project owner only)
**Response 200**: `{ "message": "Board permanently deleted" }`

#### DELETE `/api/tasks/{task}/force`

Permanently delete a soft-deleted task and its children.

**Auth**: Required (project owner or task assignee)
**Response 200**: `{ "message": "Task permanently deleted" }`
**Response 403**: User is neither the project owner nor the task assignee.

#### DELETE `/api/tasks/{task}/subtasks/{subtask}/force`

Permanently delete a soft-deleted subtask.

**Auth**: Required (project owner or parent task assignee)
**Response 200**: `{ "message": "Subtask permanently deleted" }`

#### DELETE `/api/comments/{comment}/force`

Permanently delete a soft-deleted comment.

**Auth**: Required (project owner or comment author)
**Response 200**: `{ "message": "Comment permanently deleted" }`

---

## Activity Log Entries

New activity types logged for soft-delete operations:

| Action | `type` value | `subject_type` | `data` fields |
|--------|-------------|----------------|---------------|
| Soft delete | `deleted` | Entity class | `{ "title": "..." }` |
| Restore | `restored` | Entity class | `{ "title": "..." }` |
| Force delete | `force_deleted` | Entity class | `{ "title": "..." }` |

---

## Authorization Matrix

| Action | Project Owner | Task Assignee | Project Member |
|--------|:------------:|:-------------:|:--------------:|
| View trash | Yes | Yes | Yes |
| Restore any item | Yes | Yes | Yes |
| Force-delete project | Yes | — | No |
| Force-delete board | Yes | — | No |
| Force-delete task | Yes | Yes | No |
| Force-delete subtask | Yes | Yes* | No |
| Force-delete comment | Yes | Yes** | No |

\* Task assignee of the parent task
\** Comment author
