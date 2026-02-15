# Quickstart: Kanban Board & Task Management

**Feature**: 004-kanban-task-management
**Date**: 2026-02-05

## Overview

This feature implements a full Kanban board system with task management, subtasks, comments, labels, and activity tracking. Most infrastructure exists - this guide covers the remaining implementation work.

---

## Prerequisites

- Node.js 18+ and npm/yarn
- PHP 8.2+ with Composer
- SQLite database (already configured)
- Existing authentication system working

---

## Quick Start

### 1. Verify Existing Setup

```bash
# Ensure dependencies are installed
composer install
npm install

# Run migrations (most tables exist)
php artisan migrate

# Start development servers
php artisan serve &
npm run dev
```

### 2. Access the Kanban Board

1. Login to the application
2. Navigate to a project: `/projects/{id}`
3. Click "Kanban" or navigate to `/projects/{id}/kanban`

---

## Key Implementation Areas

### Backend (Laravel)

#### New Controllers to Create

```php
// app/Http/Controllers/SubtaskController.php
// - index, store, update, destroy, reorder

// app/Http/Controllers/CommentController.php
// - index, store, update, destroy
// - 15-minute edit window enforcement

// app/Http/Controllers/LabelController.php
// - index, store, update, destroy (project-scoped)

// app/Http/Controllers/ActivityController.php
// - index (task activities)
// - projectActivities (all project activities)
```

#### New Form Requests

```php
// app/Http/Requests/StoreSubtaskRequest.php
// app/Http/Requests/UpdateSubtaskRequest.php
// app/Http/Requests/StoreCommentRequest.php
// app/Http/Requests/UpdateCommentRequest.php
// app/Http/Requests/StoreLabelRequest.php
// app/Http/Requests/UpdateLabelRequest.php
```

#### New Policies

```php
// app/Policies/CommentPolicy.php
// - update: check 15-minute window + author
// - delete: check author

// app/Policies/LabelPolicy.php
// - create, update, delete: project owner only
```

#### Routes to Add (routes/api.php)

```php
// Subtasks
Route::apiResource('tasks.subtasks', SubtaskController::class);
Route::post('tasks/{task}/subtasks/reorder', [SubtaskController::class, 'reorder']);

// Comments
Route::apiResource('tasks.comments', CommentController::class)->shallow();

// Labels
Route::apiResource('projects.labels', LabelController::class);

// Activities
Route::get('tasks/{task}/activities', [ActivityController::class, 'index']);
Route::get('projects/{project}/activities', [ActivityController::class, 'projectActivities']);

// Task labels
Route::post('tasks/{task}/labels', [TaskController::class, 'syncLabels']);
```

### Frontend (Vue 3)

#### New Components to Create

```vue
<!-- resources/js/components/kanban/SubtaskList.vue -->
<!-- - List subtasks with checkboxes -->
<!-- - Add new subtask input -->
<!-- - Drag-to-reorder -->
<!-- - Delete button -->

<!-- resources/js/components/kanban/CommentSection.vue -->
<!-- - Comment list with threading -->
<!-- - Add comment textarea -->
<!-- - Edit/delete within 15 min -->
<!-- - Relative timestamps -->

<!-- resources/js/components/kanban/ActivityFeed.vue -->
<!-- - Chronological activity list -->
<!-- - Activity type icons -->
<!-- - User avatars -->
<!-- - "Load more" pagination -->

<!-- resources/js/components/kanban/LabelManager.vue -->
<!-- - Create/edit/delete labels -->
<!-- - Color picker (preset palette) -->
<!-- - Label preview -->

<!-- resources/js/components/kanban/FilterBar.vue -->
<!-- - Search input -->
<!-- - Label filter multiselect -->
<!-- - Assignee filter dropdown -->
<!-- - Priority filter checkboxes -->
<!-- - Due date range picker -->
<!-- - Clear all filters button -->
<!-- - URL sync -->
```

#### Enhance Existing Components

```vue
<!-- resources/js/components/kanban/TaskDetailModal.vue -->
<!-- ADD: SubtaskList section -->
<!-- ADD: CommentSection section -->
<!-- ADD: ActivityFeed section -->
<!-- ADD: Label selector -->

<!-- resources/js/components/kanban/TaskCard.vue -->
<!-- ENHANCE: Show up to 3 labels + overflow indicator -->
<!-- ENHANCE: Comment count badge -->

<!-- resources/js/components/kanban/BoardHeader.vue -->
<!-- ADD: FilterBar integration -->
<!-- ADD: Active filter count badge -->
```

#### New Stores

```javascript
// resources/js/stores/comments.js
// - state: comments, loading, error
// - actions: fetchComments, addComment, updateComment, deleteComment

// resources/js/stores/labels.js
// - state: labels, loading, error
// - actions: fetchLabels, createLabel, updateLabel, deleteLabel
```

#### New Composables

```javascript
// resources/js/composables/useCommentEditing.js
// - isEditable(comment) - checks 15-min window
// - canEdit(comment) - checks window + author
// - timeUntilLocked(comment) - remaining edit time
```

---

## Testing

### Backend Tests

```bash
# Run all tests
php artisan test

# Run specific feature tests
php artisan test --filter=SubtaskControllerTest
php artisan test --filter=CommentControllerTest
php artisan test --filter=LabelControllerTest
```

### Frontend Tests

```bash
# Run Vitest
npm run test

# Run with coverage
npm run test:coverage
```

### Key Test Scenarios

1. **Task CRUD**: Create, read, update, delete tasks
2. **Drag-Drop**: Move task between columns, verify WIP limits
3. **Subtasks**: Add, complete, reorder, delete subtasks
4. **Comments**: Add, edit within window, delete, verify 15-min lock
5. **Labels**: Create label, assign to task, filter by label
6. **Filtering**: Search, filter combinations, URL persistence
7. **Activity**: Verify all events logged correctly

---

## API Quick Reference

### Task Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks` | List with filters |
| POST | `/api/tasks` | Create task |
| GET | `/api/tasks/{id}` | Get details |
| PUT | `/api/tasks/{id}` | Update task |
| DELETE | `/api/tasks/{id}` | Delete task |
| POST | `/api/tasks/{id}/move` | Move to column |
| POST | `/api/tasks/{id}/labels` | Sync labels |

### Subtask Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks/{id}/subtasks` | List subtasks |
| POST | `/api/tasks/{id}/subtasks` | Create subtask |
| PATCH | `/api/tasks/{id}/subtasks/{sid}` | Update subtask |
| DELETE | `/api/tasks/{id}/subtasks/{sid}` | Delete subtask |
| POST | `/api/tasks/{id}/subtasks/reorder` | Reorder subtasks |

### Comment Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks/{id}/comments` | List comments |
| POST | `/api/tasks/{id}/comments` | Add comment |
| PATCH | `/api/comments/{id}` | Edit (15 min window) |
| DELETE | `/api/comments/{id}` | Delete comment |

### Label Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/projects/{id}/labels` | List labels |
| POST | `/api/projects/{id}/labels` | Create label |
| PUT | `/api/projects/{id}/labels/{lid}` | Update label |
| DELETE | `/api/projects/{id}/labels/{lid}` | Delete label |

### Activity Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks/{id}/activities` | Task activities |
| GET | `/api/projects/{id}/activities` | Project activities |

---

## Common Patterns

### Optimistic Updates (Frontend)

```javascript
// Example: Toggle subtask completion
async function toggleSubtask(subtask) {
  // Optimistic update
  subtask.is_completed = !subtask.is_completed;

  try {
    await axios.patch(`/api/tasks/${taskId}/subtasks/${subtask.id}`, {
      is_completed: subtask.is_completed
    });
  } catch (error) {
    // Rollback on failure
    subtask.is_completed = !subtask.is_completed;
    toast.error('Failed to update subtask');
  }
}
```

### Comment Edit Window (Frontend)

```javascript
// composables/useCommentEditing.js
export function useCommentEditing() {
  const EDIT_WINDOW_MINUTES = 15;

  function isEditable(comment, currentUserId) {
    if (comment.user.id !== currentUserId) return false;

    const createdAt = new Date(comment.created_at);
    const now = new Date();
    const diffMinutes = (now - createdAt) / (1000 * 60);

    return diffMinutes < EDIT_WINDOW_MINUTES;
  }

  return { isEditable };
}
```

### Activity Logging (Backend)

```php
// In Task model or observer
protected static function booted()
{
    static::created(function ($task) {
        Activity::create([
            'user_id' => auth()->id(),
            'project_id' => $task->column->board->project_id,
            'type' => 'task.created',
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'data' => ['title' => $task->title],
        ]);
    });
}
```

---

## Troubleshooting

### Common Issues

1. **WIP limit not enforced**: Check `MoveTaskRequest` validation and column `wip_limit` value
2. **Comments not showing edit button**: Verify `is_editable` computed in CommentResource
3. **Labels not filtering**: Check `useTaskFiltering` composable and store state
4. **Activities not logging**: Ensure model observers are registered in `AppServiceProvider`
5. **Drag-drop not working on mobile**: Use "Move to..." menu instead

### Debug Commands

```bash
# Check database structure
php artisan db:table tasks
php artisan db:table comments

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check routes
php artisan route:list --path=tasks
php artisan route:list --path=comments
```

---

## Next Steps

After completing this feature:

1. Run `/speckit.tasks` to generate the detailed task list
2. Implement tasks in priority order (P1 → P2 → P3)
3. Run tests after each major component
4. Update CLAUDE.md with any new patterns discovered
