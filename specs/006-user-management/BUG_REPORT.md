# Bug Report — 006-user-management Branch

**Generated**: 2026-03-26
**Branch**: `006-user-management` (commits `68abc2f..2a20c24`)
**Purpose**: Fix all bugs listed below. Each bug has file, line, root cause, and a clear fix description.

---

## CRITICAL — Will crash at runtime

### BUG-001: Profile.vue imports non-existent export from auth store
- **File**: `resources/js/pages/Profile.vue:69`
- **Code**: `import { useAuth } from '../stores/auth';`
- **Problem**: `resources/js/stores/auth.js` exports `useAuthStore` (Pinia defineStore), NOT `useAuth`. This import will fail with a runtime error when navigating to the Profile page. The composable `useAuth` is exported from `resources/js/composables/useAuth.js`.
- **Fix**: Change line 69 to either:
  - `import { useAuthStore as useAuth } from '../stores/auth';` OR
  - `import { useAuth } from '../composables/useAuth';`
  Then verify the rest of the component uses the correct API (the composable returns `{ user, token, isAuthenticated, ... }` while the store is accessed via `authStore.user`).

### BUG-002: Routes reference InvitationController which does not exist
- **File**: `routes/api.php:140-146`
- **Problem**: Routes reference `\App\Http\Controllers\InvitationController` but this class was never created. Any request to invitation endpoints will throw `Class not found`.
- **Fix**: Create `app/Http/Controllers/InvitationController.php` with methods: `index`, `store`, `resend`, `destroy`, `accept`, `decline`, `pending`. Use the existing `Invitation` model and `InvitationPolicy`.

### BUG-003: Routes reference NotificationController which does not exist
- **File**: `routes/api.php:149-152`
- **Problem**: Routes reference `\App\Http\Controllers\NotificationController` but this class was never created.
- **Fix**: Create `app/Http/Controllers/NotificationController.php` with methods: `index`, `unreadCount`, `markAsRead`, `markAllAsRead`. Use the existing `Notification` model.

### BUG-004: Routes reference ProjectController::permissions() which does not exist
- **File**: `routes/api.php:136`
- **Code**: `Route::get('projects/{project}/permissions', [ProjectController::class, 'permissions'])`
- **Problem**: `ProjectController` has no `permissions()` method. Will throw 500 error.
- **Fix**: Add a `permissions()` method to `app/Http/Controllers/ProjectController.php` that returns the permission matrix from `config/permissions.php` for the authenticated user's role in the project.

### BUG-005: TrashController uses wrong property name `assigned_to` instead of `assignee_id`
- **File**: `app/Http/Controllers/TrashController.php:390`
- **Code**: `$isTaskAssignee = $model->assigned_to === $currentUser->id;`
- **Problem**: Task model uses `assignee_id` (confirmed in `app/Models/Task.php:24`), not `assigned_to`. This property returns `null`, so the assignee authorization check ALWAYS fails — non-owner assignees can never force-delete their own tasks.
- **Fix**: Change line 390 to: `$isTaskAssignee = $model->assignee_id === $currentUser->id;`

### BUG-006: TrashController null pointer on soft-deleted parent column
- **File**: `app/Http/Controllers/TrashController.php:383`
- **Code**: `if (!$model->column || $model->column->board->project_id !== $project->id)`
- **Problem**: `$model->column` uses the default relationship which excludes soft-deleted records. If the parent column was soft-deleted, `$model->column` is `null`, and `$model->column->board` throws "Cannot access property of null". This crashes force-delete and restore operations for tasks whose parent column is trashed.
- **Fix**: Use `$model->column()->withTrashed()->first()` or load `column` with `withTrashed` scope. Apply the same fix everywhere column is accessed in `TrashController` (check restore methods around lines 219-230 too).

---

## HIGH — Security / Authorization gaps

### BUG-007: SubtaskController has zero authorization checks
- **File**: `app/Http/Controllers/SubtaskController.php` (all methods: lines 21, 31, 56, 77, 94)
- **Problem**: None of the 5 controller methods (`index`, `store`, `update`, `destroy`, `reorder`) perform any authorization. Any authenticated user can view/create/edit/delete subtasks on ANY task in ANY project, bypassing all project membership checks.
- **Fix**: Add authorization in each method:
  - `index()`: `$this->authorize('view', $task);`
  - `store()`: `$this->authorize('update', $task);`
  - `update()`, `destroy()`, `reorder()`: `$this->authorize('update', $task);`
  These rely on TaskPolicy which checks project membership.

### BUG-008: CommentController::store() has no authorization check
- **File**: `app/Http/Controllers/CommentController.php:35`
- **Problem**: The `store()` method doesn't verify the user has access to the task's project. Any authenticated user can post comments on any task.
- **Fix**: Add `$this->authorize('view', $task);` at the beginning of the `store()` method.

### BUG-009: ProjectResource uses stale role name 'editor' in permissions
- **File**: `app/Http/Resources/ProjectResource.php:44`
- **Code**: `$isEditor = $userRole === 'editor';`
- **Problem**: The migration `2026_03_10_000002` renamed `editor` to `member` and added `lead`. This check will NEVER be true because no project member has the `editor` role anymore. Users with `member` or `lead` roles will see `can_edit: false` even though they should be able to edit.
- **Fix**: Change to check both new roles: `$canEdit = in_array($userRole, ['lead', 'member']);` and update the permissions array accordingly:
  ```php
  'can_edit' => $isOwner || in_array($userRole, ['lead', 'member']),
  'can_manage_members' => $isOwner || $userRole === 'lead',
  ```

---

## MEDIUM — Data integrity / Logic errors

### BUG-010: DashboardTestSeeder uses deprecated 'editor' role
- **File**: `database/seeders/DashboardTestSeeder.php:88, 94, 100, 112`
- **Code**: `'role' => 'editor'`
- **Problem**: Migration `2026_03_10_000002` changed valid roles to `['owner', 'lead', 'member', 'viewer']`. The seeder creates records with `editor` which no longer exists. Running the seeder after migrations will insert invalid role values (SQLite won't enforce the enum, but application logic will break).
- **Fix**: Replace all `'role' => 'editor'` with `'role' => 'member'` on lines 88, 94, 100, 112.

### BUG-011: ProjectResource hardcodes 'Done' for task completion count
- **File**: `app/Http/Resources/ProjectResource.php:23`
- **Code**: `$query->where('title', 'Done');`
- **Problem**: The DashboardTestSeeder creates the last column as `'Completed'` (line 145), not `'Done'`. Any project using column names other than exactly `'Done'` will show 0% completion even if all tasks are in the final column.
- **Fix**: Either standardize column names across the app, or use a more robust approach like checking the last column by position: `$query->orderByDesc('position')->limit(1)` or add an `is_done` boolean to the columns table.

### BUG-012: ProjectResource N+1 query — calls `$this->members()` outside whenLoaded
- **File**: `app/Http/Resources/ProjectResource.php:42`
- **Code**: `$membership = $user ? $this->members()->where('user_id', $user->id)->first() : null;`
- **Problem**: Even though `whenLoaded('members')` is used for the members list (line 36), line 42 executes a raw query `$this->members()` to check the current user's membership. When serializing a list of N projects, this creates N extra queries.
- **Fix**: If members are already loaded, filter in-memory: `$membership = $this->relationLoaded('members') ? $this->members->firstWhere('user_id', $user->id) : $this->members()->where('user_id', $user->id)->first();`

### BUG-013: ProjectResource also N+1 on task counts
- **File**: `app/Http/Resources/ProjectResource.php:20-25`
- **Code**: `$this->tasks()->count()` and `$this->tasks()->whereHas('column', ...)->count()`
- **Problem**: Two raw queries per project resource serialization. With 20 projects in a list, that's 40 extra queries.
- **Fix**: Use `loadCount` on the controller side or `withCount` in the query, then access `$this->tasks_count` in the resource.

### BUG-014: Migration rollback logic is broken (contradictory WHERE clauses)
- **File**: `database/migrations/2026_03_10_000002_update_project_members_roles.php:67-68`
- **Code**:
  ```php
  ->where('role', 'member')
  ->where('role', '!=', 'lead')
  ```
- **Problem**: This says "where role = 'member' AND role != 'lead'" — the second condition is always true when the first is true. But the intent was to convert `member` back to `editor` while leaving `lead` unchanged. The real bug is on lines 72-74: records with role `lead` also satisfy `whereIn(['owner', 'viewer', 'lead'])` but then `where('role', '!=', 'member')` is redundant since they're already not 'member'. The actual issue: records with `role_temp = null` (lead users who weren't matched by either query) will lose their role data.
- **Fix**: Simplify the down method:
  ```php
  DB::table('project_members')->where('role', 'member')->update(['role_temp' => 'editor']);
  DB::table('project_members')->where('role', 'lead')->update(['role_temp' => 'editor']);
  DB::table('project_members')->whereIn('role', ['owner', 'viewer'])->update(['role_temp' => DB::raw('role')]);
  ```

### BUG-015: Models missing HasSoftDeleteUser trait — deleted_by never populated
- **Files**:
  - `app/Models/Label.php:13` — has SoftDeletes but missing HasSoftDeleteUser
  - `app/Models/Activity.php:12` — has SoftDeletes but missing HasSoftDeleteUser
  - `app/Models/ProjectMember.php:12` — has SoftDeletes but missing HasSoftDeleteUser
- **Problem**: The migration `2026_03_07_000000` adds `deleted_by` column to these tables, but without the `HasSoftDeleteUser` trait, the column is never populated. Audit trail is incomplete.
- **Fix**: Add `use HasSoftDeleteUser;` to each model and add `'deleted_by'` to their `$fillable` arrays.

### BUG-016: Task model appended attributes cause N+1 queries
- **File**: `app/Models/Task.php:38-45`
- **Problem**: Six attributes are appended (`progress`, `completed_subtask_count`, `is_overdue`, `subtask_count`, `comment_count`, `label_count`). The accessors for these execute count queries. When serializing a collection of tasks (e.g., 50 tasks on a board), this generates 50 x 5 = 250+ extra queries.
- **Fix**: Remove from `$appends`. Instead, use `withCount(['subtasks', 'comments', 'labels'])` on the query and compute progress in the resource/serialization layer. Or use `loadCount()` in the controller before returning.

---

## LOW — Code quality / Minor issues

### BUG-017: Subtasks store uses shallow reference instead of copy for rollback
- **File**: `resources/js/stores/subtasks.js:149`
- **Code**: `const originalSubtask = subtasks.value[index];`
- **Problem**: This captures a reference, not a copy. When the subtask is mutated optimistically, the "original" is mutated too, making rollback-on-error impossible.
- **Fix**: `const originalSubtask = { ...subtasks.value[index] };`

### BUG-018: Comments store deleteComment doesn't guard against -1 index
- **File**: `resources/js/stores/comments.js:122-123`
- **Problem**: If `commentIndex` is -1 (comment not found), the splice will still execute and corrupt the array.
- **Fix**: Add `if (commentIndex === -1) return;` before the splice.

### BUG-019: UserFilters search timeout not cleared on unmount
- **File**: `resources/js/components/users/UserFilters.vue:98`
- **Problem**: `searchTimeout` is set but never cleared when the component unmounts, causing potential memory leaks.
- **Fix**: Add `onUnmounted(() => clearTimeout(searchTimeout));`

### BUG-020: KanbanView doesn't re-fetch board when projectId changes via navigation
- **File**: `resources/js/pages/projects/KanbanView.vue:107-120`
- **Problem**: `projectId` is a computed from `route.params.id` but there's no watcher to reload the board when it changes. Navigating between projects may show stale board data.
- **Fix**: Add a `watch` on `projectId` that re-fetches the board data.

### BUG-021: PasswordChangeForm accesses error arrays without existence check
- **File**: `resources/js/components/profile/PasswordChangeForm.vue:15, 30, 42`
- **Code**: `{{ errors.current_password[0] }}`
- **Problem**: If backend returns errors as strings instead of arrays, or the field key doesn't exist, this throws a TypeError.
- **Fix**: Use optional chaining: `errors.current_password?.[0]`

### BUG-022: ProjectsList switchTab doesn't reset pagination
- **File**: `resources/js/pages/projects/ProjectsList.vue:344-348`
- **Problem**: When switching tabs (active/archived), pagination state carries over. If user was on page 3 of active projects and switches to archived (which has only 1 page), the API call will return empty results.
- **Fix**: Reset page to 1 in `switchTab()` before calling `loadProjects()`.

---

## Summary

| Severity | Count | Impact |
|----------|-------|--------|
| CRITICAL | 6     | Runtime crashes, 500 errors, missing controllers |
| HIGH     | 3     | Security holes — unauthorized data access |
| MEDIUM   | 7     | Wrong data, broken permissions, N+1 queries |
| LOW      | 6     | Edge-case errors, minor UX issues |
| **Total**| **22**| |

## Recommended Fix Order

1. **BUG-001** — Profile page completely broken (import error)
2. **BUG-002, BUG-003, BUG-004** — Missing controllers/methods (routes crash)
3. **BUG-005, BUG-006** — TrashController runtime errors
4. **BUG-009** — Permissions show `can_edit: false` for all non-owners
5. **BUG-007, BUG-008** — Authorization gaps (security)
6. **BUG-010** — Seeder uses invalid role
7. **BUG-011, BUG-012, BUG-013** — ProjectResource query/logic issues
8. Everything else
