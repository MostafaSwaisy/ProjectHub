# Tasks: Soft Delete Support

**Input**: Design documents from `/specs/005-soft-delete/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/

**Tests**: Not explicitly requested — test tasks are excluded.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create migration, traits, and base model changes needed by all user stories

- [x] T001 Create migration to add `deleted_at` and `deleted_by` columns to all 10 tables in `database/migrations/2026_03_07_000000_add_soft_deletes_to_all_tables.php`
- [x] T002 [P] Create `HasSoftDeleteUser` trait in `app/Traits/HasSoftDeleteUser.php` — hooks into `deleting` event to set `deleted_by` from `auth()->id()`, clears `deleted_by` on `restoring` event
- [x] T003 [P] Create `HasCascadeSoftDeletes` trait in `app/Traits/HasCascadeSoftDeletes.php` — reads `$cascadeDeletes` array property, cascades `delete()`/`forceDelete()`/`restore()` to child relationships, restore uses `deleted_at` timestamp matching

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add SoftDeletes trait to ALL models — MUST be complete before any user story can work

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T004 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to User model in `app/Models/User.php` — add `deleted_at` to `$casts` dates
- [x] T005 [P] Add `SoftDeletes`, `HasSoftDeleteUser`, `HasCascadeSoftDeletes` traits to Project model in `app/Models/Project.php` — set `$cascadeDeletes = ['boards']`
- [x] T006 [P] Add `SoftDeletes`, `HasSoftDeleteUser`, `HasCascadeSoftDeletes` traits to Board model in `app/Models/Board.php` — set `$cascadeDeletes = ['columns']`
- [x] T007 [P] Add `SoftDeletes`, `HasSoftDeleteUser`, `HasCascadeSoftDeletes` traits to Column model in `app/Models/Column.php` — set `$cascadeDeletes = ['tasks']`
- [x] T008 [P] Add `SoftDeletes`, `HasSoftDeleteUser`, `HasCascadeSoftDeletes` traits to Task model in `app/Models/Task.php` — set `$cascadeDeletes = ['subtasks', 'comments']`
- [x] T009 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to Subtask model in `app/Models/Subtask.php`
- [x] T010 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to Comment model in `app/Models/Comment.php`
- [x] T011 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to Label model in `app/Models/Label.php`
- [x] T012 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to Activity model in `app/Models/Activity.php`
- [x] T013 [P] Add `SoftDeletes`, `HasSoftDeleteUser` traits to ProjectMember model in `app/Models/ProjectMember.php`
- [x] T014 Run migration `php artisan migrate` to add soft delete columns to all tables

**Checkpoint**: All models now use SoftDeletes — existing `delete()` calls automatically become soft deletes

---

## Phase 3: User Story 1 — Soft Delete a Task (Priority: P1) 🎯 MVP

**Goal**: When a user deletes a task, it is soft-deleted (hidden from board) along with its subtasks and comments via cascade. All existing delete operations across controllers become soft deletes.

**Independent Test**: Create a task with subtasks, delete it, verify it disappears from board, confirm DB record has `deleted_at` set, confirm subtasks also have `deleted_at` set.

### Implementation for User Story 1

- [ ] T015 [US1] Update `destroy()` method in `app/Http/Controllers/TaskController.php` — remove manual cascade delete code for subtasks/comments/labels (trait handles cascade), add activity log entry with type `deleted`, set `deleted_by` before delete
- [ ] T016 [P] [US1] Update `destroy()` method in `app/Http/Controllers/SubtaskController.php` — remove manual delete, let SoftDeletes handle it, add activity log entry
- [ ] T017 [P] [US1] Update `destroy()` method in `app/Http/Controllers/CommentController.php` — let SoftDeletes handle delete, activity log already exists (update type to `deleted`)
- [ ] T018 [P] [US1] Update `destroy()` method in `app/Http/Controllers/ProjectController.php` — let SoftDeletes + cascade handle deletion, add activity log entry with type `deleted`
- [ ] T019 [P] [US1] Update `destroy()` method in `app/Http/Controllers/BoardController.php` — let SoftDeletes + cascade handle deletion, add activity log entry
- [ ] T020 [P] [US1] Update `destroy()` method in `app/Http/Controllers/LabelController.php` — let SoftDeletes handle delete, remove manual `detach` from tasks (label is soft-deleted, not removed)
- [ ] T021 [US1] Update frontend `resources/js/stores/tasks.js` — ensure `deleteTask` action removes task from local state on successful API response (no behavior change needed, just verify)
- [ ] T022 [US1] Update frontend `resources/js/stores/kanban.js` — ensure deleted task is removed from column's task list in local state after delete
- [ ] T023 [US1] Update subtask progress calculation in `app/Http/Resources/TaskResource.php` and `app/Http/Resources/TaskDetailResource.php` — use `subtasks()->count()` which now auto-excludes soft-deleted subtasks (FR-017)

**Checkpoint**: All delete operations are now soft deletes with cascade. Tasks disappear from board but remain in database.

---

## Phase 4: User Story 2 — View Deleted Items in Trash (Priority: P1)

**Goal**: Project members can view a trash tab within the project view listing all soft-deleted items, filtered by entity type.

**Independent Test**: Soft-delete several items, navigate to project trash tab, verify all deleted items appear with title, deletion date, and who deleted them. Filter by type.

### Implementation for User Story 2

- [ ] T024 [US2] Create `TrashController` in `app/Http/Controllers/TrashController.php` — `index()` method that aggregates soft-deleted boards, columns, tasks, subtasks, comments for a project, supports `?type=` filter, paginates results, returns unified format with `deleted_at`, `deleted_by` user info, and parent context
- [ ] T025 [US2] Create `TrashItemResource` in `app/Http/Resources/TrashItemResource.php` — unified API resource that normalizes different entity types into a consistent trash view format (id, type, title, deleted_at, deleted_by, parent info)
- [ ] T026 [US2] Add trash route `GET /api/projects/{project}/trash` in `routes/api.php` — scoped to authenticated project members
- [ ] T027 [US2] Create Pinia trash store in `resources/js/stores/trash.js` — actions: `fetchTrashItems(projectId, type?)`, state: `items`, `loading`, `pagination`, `activeFilter`
- [ ] T028 [US2] Create `TrashTab.vue` component in `resources/js/components/projects/TrashTab.vue` — displays list of deleted items with entity type icon, title, deletion date, deleted-by user name, filter buttons (All, Tasks, Boards, Columns, Comments, Subtasks), empty state when no deleted items, pagination
- [ ] T029 [US2] Add trash tab to project view — update project page/layout to include a "Trash" tab alongside existing tabs (Boards, Members), wire up route and component rendering

**Checkpoint**: Users can see all soft-deleted items in a per-project trash tab with filtering.

---

## Phase 5: User Story 3 — Restore a Deleted Item (Priority: P1)

**Goal**: Users can restore soft-deleted items from the trash. Cascade restore brings back children that were deleted in the same batch. If the parent no longer exists, user picks a new parent.

**Independent Test**: Soft-delete a task with subtasks, open trash, restore it, verify task reappears in its original column with subtasks restored.

### Implementation for User Story 3

- [ ] T030 [US3] Add `restore()` method to `TrashController` in `app/Http/Controllers/TrashController.php` — accepts entity type and ID, calls `restore()` on the model, cascade restore handled by `HasCascadeSoftDeletes` trait, logs activity with type `restored`
- [ ] T031 [US3] Add restore logic for orphaned items in `TrashController` — if a task's parent column is force-deleted, return 409 with list of available columns; accept `column_id` in request body for re-assignment before restore
- [ ] T032 [US3] Add restore routes in `routes/api.php` — `POST /api/projects/{project}/restore`, `POST /api/projects/{project}/boards/{board}/restore`, `POST /api/tasks/{task}/restore`, `POST /api/tasks/{task}/subtasks/{subtask}/restore`, `POST /api/comments/{comment}/restore` — all with `->withTrashed()`
- [ ] T033 [US3] Add `restoreItem(type, id)` action to trash Pinia store in `resources/js/stores/trash.js` — calls restore endpoint, removes item from local trash list on success, handles 409 (orphaned item) by showing column selection
- [ ] T034 [US3] Add restore button and orphan-handling UI to `TrashTab.vue` in `resources/js/components/projects/TrashTab.vue` — "Restore" button on each trash item, success toast, handle 409 response with a column-selection dropdown modal
- [ ] T035 [US3] Update `resources/js/stores/kanban.js` — after successful restore of a task, re-fetch board data or inject restored task back into the correct column's task list

**Checkpoint**: Users can restore any deleted item. Cascade restore works. Orphaned items prompt for new parent.

---

## Phase 6: User Story 4 — Permanently Delete an Item (Priority: P2)

**Goal**: Project owners and task assignees can permanently delete items from the trash. Confirmation dialog warns this is irreversible.

**Independent Test**: Soft-delete a task, open trash, permanently delete it, confirm the DB record is gone. Verify non-owners cannot force-delete.

### Implementation for User Story 4

- [ ] T036 [US4] Add `forceDelete()` method to `TrashController` in `app/Http/Controllers/TrashController.php` — accepts entity type and ID, verifies authorization (project owner or task assignee per FR-008), calls `forceDelete()` on the model, cascade force-delete handled by trait, logs activity with type `force_deleted`
- [ ] T037 [US4] Add force-delete routes in `routes/api.php` — `DELETE /api/projects/{project}/force`, `DELETE /api/projects/{project}/boards/{board}/force`, `DELETE /api/tasks/{task}/force`, `DELETE /api/tasks/{task}/subtasks/{subtask}/force`, `DELETE /api/comments/{comment}/force` — all with `->withTrashed()`
- [ ] T038 [US4] Add `forceDeleteItem(type, id)` action to trash Pinia store in `resources/js/stores/trash.js` — calls force-delete endpoint, removes item from local trash list on success, handles 403 permission errors
- [ ] T039 [US4] Add permanent delete button and confirmation dialog to `TrashTab.vue` in `resources/js/components/projects/TrashTab.vue` — "Permanently Delete" button visible only to project owner/task assignee, uses `ConfirmDialog` from shared components with warning text, hides button for unauthorized members
- [ ] T040 [US4] Implement authorization check helper — determine if current user is project owner or task assignee, expose via API response or computed property for frontend conditional rendering

**Checkpoint**: Authorized users can permanently delete items with confirmation. Non-authorized members see only the restore button.

---

## Phase 7: User Story 5 — Cascade Soft Delete on Project Deletion (Priority: P2)

**Goal**: Deleting a project cascade-soft-deletes all its boards, columns, tasks, subtasks, and comments. Restoring a project cascade-restores children from the same batch.

**Independent Test**: Create a project with boards, columns, and tasks. Delete the project. Verify all nested entities have `deleted_at` set. Restore the project. Verify all children are restored.

### Implementation for User Story 5

- [ ] T041 [US5] Verify cascade soft-delete works end-to-end for Project → Board → Column → Task → Subtask/Comment — test via tinker or manual testing that deleting a project sets `deleted_at` on all descendants
- [ ] T042 [US5] Verify cascade restore works end-to-end for Project — test that restoring a project only restores children whose `deleted_at` matches (same batch), independently-deleted children remain trashed
- [ ] T043 [US5] Handle edge case: already-deleted children during cascade — verify `HasCascadeSoftDeletes` trait skips children that are already soft-deleted (different `deleted_at` timestamp) during cascade delete
- [ ] T044 [US5] Update frontend `resources/js/stores/projects.js` — after project delete, update local state to remove project from list; after project restore (if accessible from a global trash view in future), re-fetch projects

**Checkpoint**: Full cascade chain works for project deletion and restoration with timestamp-matching logic.

---

## Phase 8: User Story 6 — Soft Delete for Comments and Subtasks (Priority: P3)

**Goal**: Comments and subtasks follow the same soft-delete pattern. Subtask progress excludes deleted subtasks. Deleted comments are hidden from task detail view.

**Independent Test**: Delete a comment and a subtask, verify they disappear from task detail, verify subtask progress recalculates correctly.

### Implementation for User Story 6

- [ ] T045 [US6] Verify subtask progress in `app/Http/Resources/TaskResource.php` correctly excludes soft-deleted subtasks — `subtasks()->count()` and `subtasks()->where('is_completed', true)->count()` should auto-exclude trashed records via SoftDeletes global scope
- [ ] T046 [US6] Verify comments list in `app/Http/Controllers/CommentController.php` `index()` method excludes soft-deleted comments — SoftDeletes global scope should handle this automatically
- [ ] T047 [US6] Update `resources/js/stores/subtasks.js` — ensure `deleteSubtask` action removes subtask from local state and recalculates progress count
- [ ] T048 [US6] Update `resources/js/stores/comments.js` — ensure `deleteComment` action removes comment from local state
- [ ] T049 [US6] Update `resources/js/components/kanban/SubtaskList.vue` — verify subtask count/progress display reflects only non-deleted subtasks (should work automatically via API response)

**Checkpoint**: Comments and subtasks are soft-deleted, hidden from views, and progress calculations are accurate.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Board statistics, activity logging consistency, and final verification

- [ ] T050 [P] Update board statistics in `app/Http/Controllers/BoardController.php` or relevant resource — ensure task counts and column counts exclude soft-deleted items (FR-016). Verify `tasks()->count()` uses SoftDeletes scope
- [ ] T051 [P] Verify all activity log entries for soft-delete, restore, and force-delete actions use consistent `type` values (`deleted`, `restored`, `force_deleted`) across all controllers (FR-015)
- [ ] T052 [P] Update `resources/js/components/kanban/BoardStats.vue` — ensure board stats API response already excludes deleted items (no frontend change needed if backend is correct)
- [ ] T053 Verify `resources/js/composables/useTaskFiltering.js` — confirm task filtering/search does not surface soft-deleted tasks (should be handled by API, but verify frontend doesn't cache stale data)
- [ ] T054 Run quickstart.md validation — execute the verification steps from `specs/005-soft-delete/quickstart.md` to confirm end-to-end soft delete, restore, and force-delete work
- [ ] T055 Code cleanup — remove any leftover manual cascade delete code in controllers that is now handled by the `HasCascadeSoftDeletes` trait

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 (migration + traits must exist) — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — Soft delete must work on models first
- **US2 (Phase 4)**: Depends on Phase 2 — Can run in parallel with US1
- **US3 (Phase 5)**: Depends on Phase 2 and US2 (needs trash view to restore from) — Can run after US2
- **US4 (Phase 6)**: Depends on US2 and US3 (needs trash view with restore to add force-delete) — Can run after US3
- **US5 (Phase 7)**: Depends on Phase 2 — Can run in parallel with US1/US2 (cascade is trait-based)
- **US6 (Phase 8)**: Depends on Phase 2 — Can run in parallel with US1
- **Polish (Phase 9)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (P1)**: After Phase 2 — No story dependencies
- **US2 (P1)**: After Phase 2 — No story dependencies (independent of US1)
- **US3 (P1)**: After US2 — Needs trash view to have a restore UI
- **US4 (P2)**: After US3 — Extends trash view with force-delete
- **US5 (P2)**: After Phase 2 — Independent (cascade is trait-based, works automatically)
- **US6 (P3)**: After Phase 2 — Independent (SoftDeletes on subtasks/comments is foundational)

### Within Each User Story

- Backend before frontend
- Controller/route changes before store/component changes
- Core implementation before edge case handling

### Parallel Opportunities

- **Phase 1**: T002 and T003 can run in parallel (different files)
- **Phase 2**: T004–T013 can ALL run in parallel (each modifies a different model file)
- **Phase 3**: T016, T017, T018, T019, T020 can run in parallel (different controllers)
- **Phase 4**: T024, T025 can start in parallel; T027, T028 can run in parallel after routes are set
- **US1 + US2 + US5 + US6**: Can all start after Phase 2 (parallel story execution)

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Launch all model updates in parallel (each touches a different file):
Task T004: "Add SoftDeletes to User model in app/Models/User.php"
Task T005: "Add SoftDeletes + cascade to Project model in app/Models/Project.php"
Task T006: "Add SoftDeletes + cascade to Board model in app/Models/Board.php"
Task T007: "Add SoftDeletes + cascade to Column model in app/Models/Column.php"
Task T008: "Add SoftDeletes + cascade to Task model in app/Models/Task.php"
Task T009: "Add SoftDeletes to Subtask model in app/Models/Subtask.php"
Task T010: "Add SoftDeletes to Comment model in app/Models/Comment.php"
Task T011: "Add SoftDeletes to Label model in app/Models/Label.php"
Task T012: "Add SoftDeletes to Activity model in app/Models/Activity.php"
Task T013: "Add SoftDeletes to ProjectMember model in app/Models/ProjectMember.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migration + traits)
2. Complete Phase 2: Foundational (SoftDeletes on all models)
3. Complete Phase 3: User Story 1 (soft delete works, cascade works)
4. **STOP and VALIDATE**: Delete a task, verify it disappears, verify DB has `deleted_at`
5. This alone delivers the core safety value — accidental deletes are recoverable

### Incremental Delivery

1. Phase 1 + 2 → Foundation ready (all deletes become soft deletes)
2. Add US1 → Soft delete works → **MVP deployed**
3. Add US2 → Trash view available → Users can see deleted items
4. Add US3 → Restore works → Users can recover items
5. Add US4 → Force-delete works → Authorized users can permanently clean up
6. Add US5 → Cascade verified → Project-level operations safe
7. Add US6 → Polish subtasks/comments → Complete feature

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Total tasks: 55
- Tasks per story: Setup=3, Foundational=11, US1=9, US2=6, US3=6, US4=5, US5=4, US6=5, Polish=6
- SoftDeletes global scope automatically handles FR-009 (exclude deleted from standard queries)
- `->withTrashed()` on routes is critical for restore/force-delete endpoints
- Commit after each phase completion for clean git history
