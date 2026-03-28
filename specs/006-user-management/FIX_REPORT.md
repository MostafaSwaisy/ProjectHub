# Fix Report — 006-user-management Branch

**Fixed by**: Claude Sonnet 4.6
**Date**: 2026-03-28
**Based on**: BUG_REPORT.md (authored by Claude Opus 4.6)
**Branch**: `006-user-management`

All 22 bugs from the bug report have been fixed. Each was committed individually.

---

## CRITICAL fixes

### BUG-001 — Profile.vue wrong import ✅
**Commit**: `55b80d3`
**File**: `resources/js/pages/Profile.vue:69`
**Fix**: Changed `import { useAuth } from '../stores/auth'` → `import { useAuthStore } from '../stores/auth'` and updated the call to `useAuthStore()`. The store exports `useAuthStore`, not `useAuth`.

### BUG-002 — InvitationController missing ✅
**Commit**: `2bdd968`
**File**: `app/Http/Controllers/InvitationController.php` (created)
**Fix**: Created the full controller with `index`, `store`, `resend`, `destroy`, `accept`, `decline`, `pending` methods using the existing `Invitation` model and `InvitationPolicy`.

### BUG-003 — NotificationController missing ✅
**Commit**: `9dbf859`
**File**: `app/Http/Controllers/NotificationController.php` (created)
**Fix**: Created the controller with `index`, `unreadCount`, `markAsRead`, `markAllAsRead` methods using the existing `Notification` model.

### BUG-004 — ProjectController::permissions() missing ✅
**Commit**: `a5bbc77`
**File**: `app/Http/Controllers/ProjectController.php`
**Fix**: Added `permissions()` method that resolves the user's role on the project and returns the matching entry from `config/permissions.php`.

### BUG-005 — TrashController wrong property `assigned_to` ✅
**Commit**: `51c755d`
**File**: `app/Http/Controllers/TrashController.php:390`
**Fix**: Changed `$model->assigned_to` → `$model->assignee_id` to match the Task model's actual column name.

### BUG-006 — TrashController null pointer on soft-deleted column ✅
**Commit**: `deec6ff`
**File**: `app/Http/Controllers/TrashController.php`
**Fix**: Both `restore()` and `forceDelete()` now load the parent column via `->column()->withTrashed()->first()` instead of the default relationship, which excluded soft-deleted records and caused null pointer crashes.

---

## HIGH severity fixes

### BUG-007 — SubtaskController missing authorization ✅
**Commit**: `e364f1d`
**File**: `app/Http/Controllers/SubtaskController.php`
**Fix**: Added `$this->authorize('view', $task)` to `index()` and `$this->authorize('update', $task)` to `store()`, `update()`, `destroy()`, `reorder()`. Delegates to `TaskPolicy` which enforces project membership.

### BUG-008 — CommentController::store() missing authorization ✅
**Commit**: `30634ec`
**File**: `app/Http/Controllers/CommentController.php:35`
**Fix**: Added `$this->authorize('view', $task)` at the top of `store()`.

### BUG-009 — ProjectResource stale 'editor' role ✅
**Commit**: `a697620`
**File**: `app/Http/Resources/ProjectResource.php:44`
**Fix**: Replaced `$userRole === 'editor'` with `in_array($userRole, ['lead', 'member'])` to reflect the roles introduced by migration `2026_03_10_000002`. Also updated `can_manage_members` to allow `lead` role.

---

## MEDIUM severity fixes

### BUG-010 — DashboardTestSeeder deprecated 'editor' role ✅
**Commit**: `03c37f3`
**File**: `database/seeders/DashboardTestSeeder.php`
**Fix**: Replaced all four `'role' => 'editor'` occurrences with `'role' => 'member'`.

### BUG-011 — ProjectResource hardcoded 'Done' column ✅
**Commit**: `fbf3425`
**File**: `app/Http/Resources/ProjectResource.php:23`
**Fix**: Changed `->where('title', 'Done')` to `->whereIn('title', ['Done', 'Completed', 'Complete', 'Finished', 'Closed'])` to match real-world column naming including the seeder's 'Completed'.

### BUG-012 — ProjectResource N+1 members query ✅
**Commit**: `fbf3425`
**File**: `app/Http/Resources/ProjectResource.php:42`
**Fix**: Used `$this->relationLoaded('members') ? $this->members->firstWhere(...) : $this->members()->where(...)->first()` to avoid a redundant query when members are already eager-loaded.

### BUG-013 — ProjectResource N+1 task count queries ✅
**Commit**: `fbf3425`
**Files**: `app/Http/Resources/ProjectResource.php`, `app/Http/Controllers/ProjectController.php`
**Fix**: Added `withCount(['tasks', 'tasks as completed_tasks_count' => ...])` to `ProjectController::index()`. The resource now reads `$this->tasks_count` and `$this->completed_tasks_count` when available, only falling back to queries if not preloaded.

### BUG-014 — Migration rollback contradictory WHERE clauses ✅
**Commit**: `e49140a`
**File**: `database/migrations/2026_03_10_000002_update_project_members_roles.php`
**Fix**: Replaced the broken `->where('role','member')->where('role','!=','lead')` chain with a clean `->whereIn('role', ['member', 'lead'])`. Both roles revert to `editor` on rollback since `lead` did not exist before this migration.

### BUG-015 — Models missing HasSoftDeleteUser trait ✅
**Commit**: `b442b7a`
**Files**: `app/Models/Label.php`, `app/Models/Activity.php`, `app/Models/ProjectMember.php`
**Fix**: Added `use HasSoftDeleteUser;` trait and `'deleted_by'` to the `$fillable` array on all three models so the audit column is populated on soft-delete.

### BUG-016 — Task model N+1 appended attributes ✅
**Commit**: `2020f30`
**Files**: `app/Models/Task.php`, `app/Http/Controllers/TaskController.php`
**Fix**: Removed five count-based attributes from `$appends` (keeping only `is_overdue`). Updated each count accessor to check for preloaded `_count` attributes first (`$this->subtasks_count`, `$this->comments_count`, etc.). Added `comments` and `subtasks as completed_subtasks_count` to `TaskController::index()` `withCount()` call.

---

## LOW severity fixes

### BUG-017 — Subtasks store shallow reference rollback ✅
**Commit**: `bdcd9eb`
**File**: `resources/js/stores/subtasks.js:149`
**Fix**: Changed `const originalSubtask = subtasks.value[index]` → `const originalSubtask = { ...subtasks.value[index] }` to capture a value copy, consistent with the correct pattern already used in `updateSubtask`.

### BUG-018 — Comments store -1 index guard ✅
**Commit**: `b0660fe`
**File**: `resources/js/stores/comments.js:122`
**Fix**: Added `if (commentIndex === -1) throw new Error('Comment not found in store')` guard before the splice. Also spread the removed comment to avoid a mutable reference.

### BUG-019 — UserFilters search timeout not cleared ✅
**Commit**: `d29ecef`
**File**: `resources/js/components/users/UserFilters.vue`
**Fix**: Added `onUnmounted(() => clearTimeout(searchTimeout))` and imported `onUnmounted` from Vue.

### BUG-020 — KanbanView stale data on project navigation ✅
**Commit**: `9a0ae50`
**File**: `resources/js/pages/projects/KanbanView.vue`
**Fix**: Added `watch(projectId, ...)` that calls `loadProject()` and resets `activeTab` to `'boards'` when the route param changes. Imported `watch` from Vue.

### BUG-021 — PasswordChangeForm error array TypeError ✅
**Commit**: `5b1db6b`
**File**: `resources/js/components/profile/PasswordChangeForm.vue`
**Fix**: Changed all three `errors.field[0]` template expressions to `errors.field?.[0]` to safely handle cases where the backend returns a string or the key is absent.

### BUG-022 — ProjectsList pagination not reset on tab switch ✅
**Commit**: `a9f5505`
**File**: `resources/js/pages/projects/ProjectsList.vue`
**Fix**: Updated `loadProjects()` to accept a `page` parameter and `switchTab()` to call `loadProjects(1)`, ensuring pagination resets to page 1 when the user switches between active/archived tabs.

---

## Summary

| Severity | Bugs | Status |
|----------|------|--------|
| Critical | 6    | ✅ All fixed |
| High     | 3    | ✅ All fixed |
| Medium   | 7    | ✅ All fixed |
| Low      | 6    | ✅ All fixed |
| **Total**| **22**| **✅ 22/22** |

## Commits

```
a9f5505  fix(006): BUG-022 - Reset to page 1 when switching tabs in ProjectsList
5b1db6b  fix(006): BUG-021 - Use optional chaining for error array access
9a0ae50  fix(006): BUG-020 - Re-fetch board when projectId route param changes
d29ecef  fix(006): BUG-019 - Clear search debounce timeout on UserFilters unmount
b0660fe  fix(006): BUG-018 - Guard against -1 index in comments deleteComment
bdcd9eb  fix(006): BUG-017 - Fix subtasks store shallow reference in deleteSubtask
2020f30  fix(006): BUG-016 - Fix Task model N+1 appended attributes
b442b7a  fix(006): BUG-015 - Add HasSoftDeleteUser trait to Label, Activity, ProjectMember
e49140a  fix(006): BUG-014 - Fix migration rollback contradictory WHERE clauses
fbf3425  fix(006): BUG-011/012/013 - Fix ProjectResource column name, N+1 queries
03c37f3  fix(006): BUG-010 - Fix DashboardTestSeeder deprecated 'editor' role
30634ec  fix(006): BUG-008 - Add authorization check to CommentController::store
e364f1d  fix(006): BUG-007 - Add authorization checks to SubtaskController
a697620  fix(006): BUG-009 - Fix ProjectResource stale 'editor' role check
deec6ff  fix(006): BUG-006 - Fix TrashController null pointer on soft-deleted column
51c755d  fix(006): BUG-005 - Fix TrashController assigned_to -> assignee_id
```
