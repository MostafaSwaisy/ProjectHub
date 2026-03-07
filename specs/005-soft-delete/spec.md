# Feature Specification: Soft Delete Support

**Feature Branch**: `005-soft-delete`
**Created**: 2026-03-04
**Status**: Draft
**Input**: User description: "Add soft delete support to all models in the system. Currently all deletes are hard deletes — records are permanently removed. We need to implement Laravel's SoftDeletes trait across all models (User, Project, Board, Column, Task, Subtask, Comment, Label, Activity, ProjectMember) so that deleted records are retained in the database with a deleted_at timestamp. This includes: adding deleted_at columns via migrations, adding the SoftDeletes trait to all models, updating controllers to use soft delete, updating frontend to handle soft-deleted items, and adding the ability to restore or permanently delete items. The feature should also handle cascade soft deletes."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Soft Delete a Task (Priority: P1)

As a project member, I want to delete a task so that it is hidden from the board but can be recovered later if needed. When I click "Delete Task," the task disappears from the Kanban board and column view but remains in the database with a `deleted_at` timestamp.

**Why this priority**: Tasks are the most frequently deleted entity. Accidental task deletion is the highest-risk data loss scenario in a Kanban workflow. This story delivers immediate safety value.

**Independent Test**: Can be fully tested by creating a task, deleting it, verifying it disappears from the board, and confirming the database record still exists with a `deleted_at` value.

**Acceptance Scenarios**:

1. **Given** a task exists in a column, **When** the user clicks "Delete Task" and confirms, **Then** the task is removed from the board view and marked with a `deleted_at` timestamp in the database.
2. **Given** a task has been soft-deleted, **When** another user views the board, **Then** the deleted task is not visible in any column.
3. **Given** a task has subtasks and comments, **When** the task is soft-deleted, **Then** its subtasks and comments are also soft-deleted (cascade).

---

### User Story 2 - View Deleted Items in Trash (Priority: P1)

As a project member, I want to see a list of all soft-deleted items within my project so that I can review what has been removed and decide whether to restore or permanently delete them (if authorized).

**Why this priority**: Without a trash view, soft delete is invisible to the user — they cannot recover anything. This story is essential for the feature to be usable.

**Independent Test**: Can be fully tested by soft-deleting several items, navigating to the trash view, and verifying all deleted items appear with their deletion date and original context.

**Acceptance Scenarios**:

1. **Given** a project has soft-deleted tasks, **When** the user opens the project trash view, **Then** all soft-deleted tasks are listed with their title, deletion date, and who deleted them.
2. **Given** a project has soft-deleted items of various types (tasks, columns, comments), **When** the user opens the trash view, **Then** items can be filtered by type (tasks, columns, boards, etc.).
3. **Given** the trash view is open, **When** there are no deleted items, **Then** an empty state message is displayed.

---

### User Story 3 - Restore a Deleted Item (Priority: P1)

As a project owner, I want to restore a soft-deleted item so that it reappears in its original location with all its data intact.

**Why this priority**: Restore is the core value proposition of soft delete — without it, soft delete is just delayed hard delete.

**Independent Test**: Can be fully tested by soft-deleting a task with subtasks and labels, restoring it from the trash, and verifying the task reappears in its original column with all relationships intact.

**Acceptance Scenarios**:

1. **Given** a soft-deleted task exists in the trash, **When** the user clicks "Restore," **Then** the task reappears in its original column at the end of the column's task list.
2. **Given** a task was soft-deleted along with its subtasks (cascade), **When** the task is restored, **Then** all its subtasks are also restored (cascade restore).
3. **Given** a task's original column has been permanently deleted, **When** the user attempts to restore the task, **Then** the system prompts the user to select an alternative column for restoration.

---

### User Story 4 - Permanently Delete an Item (Priority: P2)

As a project owner, I want to permanently delete a soft-deleted item from the trash so that it is irreversibly removed from the database when I am certain it is no longer needed.

**Why this priority**: Permanent deletion is needed for data hygiene and storage management, but is less urgent than the ability to soft-delete and restore.

**Independent Test**: Can be fully tested by soft-deleting a task, navigating to the trash, permanently deleting it, and confirming the database record no longer exists.

**Acceptance Scenarios**:

1. **Given** a soft-deleted item exists in the trash, **When** the user clicks "Permanently Delete" and confirms, **Then** the item and all its cascade-deleted children are permanently removed from the database.
2. **Given** a user attempts to permanently delete an item, **When** the confirmation dialog appears, **Then** it clearly warns that this action cannot be undone.
3. **Given** a project member who is neither the project owner nor the task assignee views the trash, **When** they see deleted items, **Then** they can only restore items (permanent delete is restricted to the project owner and the task's assignee).

---

### User Story 5 - Cascade Soft Delete on Project Deletion (Priority: P2)

As a project owner, I want that when I delete a project, all its boards, columns, tasks, subtasks, comments, and labels are also soft-deleted, preserving the entire project structure for potential recovery.

**Why this priority**: Project-level cascade ensures data integrity when higher-level entities are deleted. Without it, orphaned records would remain active.

**Independent Test**: Can be fully tested by creating a project with boards, columns, and tasks, soft-deleting the project, and verifying all nested entities have `deleted_at` timestamps.

**Acceptance Scenarios**:

1. **Given** a project with boards, columns, and tasks, **When** the project is soft-deleted, **Then** all boards, columns, tasks, subtasks, comments, and label associations under the project are also soft-deleted.
2. **Given** a project has been cascade soft-deleted, **When** the project is restored, **Then** all its child entities (boards, columns, tasks, subtasks, comments) are also restored.
3. **Given** a board within a project is soft-deleted independently, **When** the project is later soft-deleted, **Then** the already-deleted board remains in its deleted state without being double-deleted.

---

### User Story 6 - Soft Delete for Comments and Subtasks (Priority: P3)

As a task owner, I want to delete comments and subtasks with the same soft-delete behavior so that accidental deletions of discussion history and checklist items can be recovered.

**Why this priority**: Comments and subtasks are lower-risk entities, but preserving discussion history adds value for audit trails.

**Independent Test**: Can be fully tested by deleting a comment and a subtask, verifying they disappear from the task detail view, and restoring them from the trash.

**Acceptance Scenarios**:

1. **Given** a comment exists on a task, **When** the user deletes the comment, **Then** it is soft-deleted and hidden from the task detail view.
2. **Given** a subtask is soft-deleted, **When** the parent task's subtask progress is recalculated, **Then** the deleted subtask is excluded from the count.

---

### Edge Cases

- What happens when a user tries to restore an item whose parent has been permanently deleted (e.g., restore a task whose column was force-deleted)?
  - The system prompts the user to select a new parent (e.g., choose a different column).
- What happens when a cascade soft-delete is triggered but some children are already soft-deleted?
  - Already-deleted items retain their original `deleted_at` timestamp and are not modified.
- What happens when the same item is deleted and restored multiple times?
  - Each delete sets `deleted_at` to the current timestamp; each restore clears it. No history of previous deletions is maintained (activity log records the events).
- What happens when a project member who is neither the owner nor the assignee tries to permanently delete an item?
  - The action is denied with a permission error; only the project owner and the task's assignee can permanently delete.
- What happens when a user restores a task but the column's WIP limit is already reached?
  - The restore succeeds but the task is placed at the end of the column, and a warning is shown about the WIP limit being exceeded.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST add `deleted_at` nullable timestamp and `deleted_by` nullable user ID columns to all model tables: users, projects, boards, columns, tasks, subtasks, comments, labels, activities, and project_members.
- **FR-002**: System MUST use Laravel's `SoftDeletes` trait on all models: User, Project, Board, Column, Task, Subtask, Comment, Label, Activity, and ProjectMember.
- **FR-003**: System MUST ensure that all existing `delete()` calls in controllers perform soft deletes (setting `deleted_at` instead of removing the record).
- **FR-004**: System MUST implement cascade soft deletes: deleting a Project soft-deletes its Boards; deleting a Board soft-deletes its Columns; deleting a Column soft-deletes its Tasks; deleting a Task soft-deletes its Subtasks and Comments.
- **FR-005**: System MUST provide a restore endpoint for each restorable entity (Project, Board, Column, Task, Subtask, Comment) that clears the `deleted_at` timestamp.
- **FR-006**: System MUST implement cascade restore: restoring a parent entity restores only children whose `deleted_at` matches the parent's (same cascade batch). Children that were independently deleted before the cascade remain in the trash.
- **FR-007**: System MUST provide a force-delete endpoint for each entity that permanently removes the record from the database.
- **FR-008**: System MUST restrict permanent deletion to the project owner and the item's assignee (for tasks). Other project members cannot permanently delete items.
- **FR-009**: System MUST exclude soft-deleted records from all standard queries (Laravel's SoftDeletes handles this by default via global scopes).
- **FR-010**: System MUST provide a trash tab within the project view (alongside Boards, Members, etc.) that lists all soft-deleted items within that project.
- **FR-011**: System MUST allow filtering of trash items by entity type (tasks, columns, boards, comments, subtasks).
- **FR-012**: System MUST display the deletion date, who deleted the item (from `deleted_by`), and entity title/description in the trash view.
- **FR-013**: System MUST show a confirmation dialog before permanent deletion, warning the action is irreversible.
- **FR-014**: System MUST handle the scenario where a restored item's parent no longer exists by prompting the user to select a new parent.
- **FR-015**: System MUST log all soft-delete, restore, and permanent-delete actions in the activity feed.
- **FR-016**: System MUST recalculate board statistics (task counts, column counts) to exclude soft-deleted items.
- **FR-017**: System MUST recalculate subtask progress on a task to exclude soft-deleted subtasks.

### Key Entities

- **Soft-Deletable Record**: Any model record that can be marked as deleted without physical removal. Key attributes: `deleted_at` timestamp (null = active, non-null = deleted), `deleted_by` user ID (who performed the deletion).
- **Trash View**: A tab within the project view (alongside Boards, Members, etc.) that aggregates all soft-deleted entities belonging to that project. Supports filtering by entity type and sorting by deletion date.
- **Cascade Relationship**: The parent-child hierarchy that determines which entities are automatically soft-deleted or restored together: Project > Board > Column > Task > Subtask/Comment.

## Clarifications

### Session 2026-03-04

- Q: Who can access the trash view and permanently delete items? → A: All project members can view the trash and restore items. Permanent deletion is allowed for the project owner and the user assigned to the task (assignee). Other members cannot permanently delete.
- Q: How to track "who deleted" an item? → A: Add a `deleted_by` nullable user ID column alongside `deleted_at` on all soft-deletable tables.
- Q: Should cascade restore also restore independently-deleted children? → A: No. Only restore children whose `deleted_at` matches the parent's (same cascade batch). Independently-deleted children remain in the trash.
- Q: Where does the trash view live in the UI? → A: A tab within the project view (e.g., alongside "Boards / Members / Trash").

## Assumptions

- **A-001**: The Activity and Label models will receive the SoftDeletes trait for consistency, but they will not have dedicated restore/force-delete endpoints in the initial implementation. Labels are shared resources; soft-deleting a label hides it from future assignment but does not affect existing task-label associations.
- **A-002**: The User model will receive the SoftDeletes trait, but user soft-delete/restore will be managed via a separate admin interface, not the project trash view.
- **A-003**: The ProjectMember pivot model will use SoftDeletes so that removing a member from a project is recoverable, but this is managed through the project member management UI, not the trash view.
- **A-004**: Standard data retention follows the "keep until permanently deleted" model — there is no automatic purge of soft-deleted records after a time period.
- **A-005**: Cascade soft-delete does not propagate to labels (many-to-many). Soft-deleting a task does not soft-delete its associated labels, only the pivot table entries are affected if the task is permanently deleted.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All delete operations across the system result in soft deletes — no records are permanently removed unless explicitly force-deleted by an authorized user.
- **SC-002**: Users can restore any soft-deleted item within a project and have it return to its original location (or choose a new parent) within 3 clicks.
- **SC-003**: The trash view loads and displays all soft-deleted items for a project within 2 seconds for projects with up to 500 deleted items.
- **SC-004**: Cascade soft-delete correctly propagates to all child entities — deleting a project with 5 boards, 20 columns, and 100 tasks results in all 125+ entities being soft-deleted in a single operation.
- **SC-005**: Board statistics, column task counts, and subtask progress accurately reflect only active (non-deleted) items after any delete or restore operation.
- **SC-006**: All soft-delete, restore, and permanent-delete actions appear in the project activity feed with correct timestamps and user attribution.
- **SC-007**: Project members who are neither the project owner nor the task assignee cannot permanently delete items — the system denies the action with an appropriate error message.
