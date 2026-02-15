# Feature Specification: Kanban Board & Task Management

**Feature Branch**: `004-kanban-task-management`
**Created**: 2026-02-05
**Status**: Draft
**Input**: User description: "Kanban Board & Task Management - Full Implementation with subtasks, comments, labels, filtering, and real-time collaboration features"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Task CRUD Operations (Priority: P1)

As a project member, I want to create, view, edit, and delete tasks on the Kanban board so I can manage my work effectively.

**Why this priority**: This is the core functionality - without working task operations, the Kanban board has no value. Users cannot do anything meaningful without being able to create and manage tasks.

**Independent Test**: Can be fully tested by creating a task, viewing it on the board, editing its details, and deleting it. Delivers immediate value by enabling basic work management.

**Acceptance Scenarios**:

1. **Given** I am viewing a project's Kanban board, **When** I click "Add Task" and fill in the required fields (title), **Then** a new task appears in the selected column
2. **Given** a task exists on the board, **When** I click on the task card, **Then** I see the full task details in a modal
3. **Given** I am viewing task details, **When** I edit the title, description, priority, or due date and save, **Then** the changes are persisted and reflected on the board
4. **Given** I am viewing task details, **When** I click delete and confirm, **Then** the task is removed from the board
5. **Given** I am not the task creator or project owner, **When** I try to delete a task, **Then** I see an appropriate permission error

---

### User Story 2 - Drag-and-Drop Task Movement (Priority: P1)

As a project member, I want to drag tasks between columns to update their status visually so I can quickly track progress.

**Why this priority**: Drag-and-drop is the defining interaction of a Kanban board. Without it, users must edit each task individually to change status, which defeats the purpose of a visual board.

**Independent Test**: Can be tested by dragging a task from "To Do" to "In Progress" and verifying the task stays in the new column after page refresh.

**Acceptance Scenarios**:

1. **Given** a task is in the "To Do" column, **When** I drag it to "In Progress", **Then** the task moves to that column and stays there after refresh
2. **Given** a column has a WIP (work-in-progress) limit of 3 tasks, **When** I try to drag a 4th task into it, **Then** I see a warning and the move is blocked
3. **Given** I am on a mobile device, **When** I tap a task and select "Move to...", **Then** I can choose a destination column from a menu
4. **Given** multiple tasks in a column, **When** I drag a task to reorder within the same column, **Then** the new order is saved

---

### User Story 3 - Subtask Management (Priority: P1)

As a task owner, I want to break down tasks into subtasks so I can track detailed progress on complex work items.

**Why this priority**: Subtasks are essential for managing complex work. Without them, users must create many small tasks or lose visibility into task progress.

**Independent Test**: Can be tested by adding subtasks to a task, checking them off, and verifying the progress bar updates on the task card.

**Acceptance Scenarios**:

1. **Given** I am viewing task details, **When** I add a subtask with a title, **Then** the subtask appears in the task's subtask list
2. **Given** a task has 3 subtasks (1 completed, 2 pending), **When** I view the task card, **Then** I see "1/3" and a 33% progress bar
3. **Given** a subtask exists, **When** I click its checkbox, **Then** it toggles between completed and pending
4. **Given** a subtask exists, **When** I click delete on it, **Then** it is removed from the task
5. **Given** multiple subtasks exist, **When** I drag to reorder them, **Then** the new order is saved

---

### User Story 4 - Task Comments (Priority: P2)

As a team member, I want to comment on tasks so I can collaborate with my team and maintain a discussion history.

**Why this priority**: Comments enable team collaboration and maintain context. Important but not blocking for basic task management.

**Independent Test**: Can be tested by adding a comment to a task and verifying it appears with author name and timestamp.

**Acceptance Scenarios**:

1. **Given** I am viewing task details, **When** I type a comment and click submit, **Then** the comment appears in the task's comment list with my name and timestamp
2. **Given** a task has multiple comments, **When** I view the task details, **Then** I see all comments in chronological order
3. **Given** I wrote a comment within the last 15 minutes, **When** I click edit, **Then** I can modify my comment text
4. **Given** I wrote a comment, **When** I click delete, **Then** my comment is removed
5. **Given** a task has 5 comments, **When** I view the task card on the board, **Then** I see a comment count indicator showing "5"

---

### User Story 5 - Task Labels (Priority: P2)

As a project member, I want to categorize tasks with colored labels so I can organize and quickly identify task types.

**Why this priority**: Labels improve visual organization and enable filtering. Valuable for larger projects but not required for basic functionality.

**Independent Test**: Can be tested by creating a label, assigning it to a task, and verifying it appears on the task card.

**Acceptance Scenarios**:

1. **Given** I am a project owner, **When** I create a new label with name "Bug" and color red, **Then** the label is available for all tasks in the project
2. **Given** a task exists, **When** I assign labels "Bug" and "Urgent" to it, **Then** both labels appear on the task card
3. **Given** a task has 5 labels, **When** I view the task card, **Then** I see the first 3 labels and "+2" indicator
4. **Given** a label is assigned to a task, **When** I remove it, **Then** the label no longer appears on the task
5. **Given** I am a project owner, **When** I delete a label, **Then** it is removed from all tasks that had it

---

### User Story 6 - Task Filtering & Search (Priority: P2)

As a project member, I want to filter and search tasks so I can quickly find specific items on a busy board.

**Why this priority**: Essential for boards with many tasks, but basic functionality works without filtering.

**Independent Test**: Can be tested by searching for a task title and verifying only matching tasks appear.

**Acceptance Scenarios**:

1. **Given** the board has 20 tasks, **When** I type "login" in the search box, **Then** only tasks with "login" in title or description are shown
2. **Given** I select the "High" priority filter, **When** the filter is applied, **Then** only high-priority tasks are visible
3. **Given** I apply filters (label: Bug, assignee: John), **When** I copy the page URL and open it in a new tab, **Then** the same filters are applied
4. **Given** I have multiple filters active, **When** I click "Clear All Filters", **Then** all tasks become visible again
5. **Given** filters are active, **When** I view the filter area, **Then** I see a count of active filters (e.g., "3 filters applied")

---

### User Story 7 - Task Assignment (Priority: P2)

As a project manager, I want to assign tasks to team members so work is clearly distributed and everyone knows their responsibilities.

**Why this priority**: Important for team coordination but tasks can function without assignment.

**Independent Test**: Can be tested by assigning a user to a task and verifying their avatar appears on the task card.

**Acceptance Scenarios**:

1. **Given** I am editing a task, **When** I select a team member as assignee, **Then** their avatar appears on the task card
2. **Given** a task is assigned to me, **When** I filter by "Assigned to me", **Then** only my tasks are shown
3. **Given** a task is assigned, **When** I set assignee to "Unassigned", **Then** the task shows no assignee avatar
4. **Given** I am assigning a task, **When** I view the assignee dropdown, **Then** I only see members of this project

---

### User Story 8 - Due Date Management (Priority: P2)

As a task owner, I want to set and track due dates so I can manage deadlines effectively.

**Why this priority**: Due dates are important for time-sensitive work but not required for basic task tracking.

**Independent Test**: Can be tested by setting a due date on a task and verifying the visual indicator changes based on urgency.

**Acceptance Scenarios**:

1. **Given** I am editing a task, **When** I set a due date to tomorrow, **Then** the task card shows "Tomorrow" with an orange indicator
2. **Given** a task's due date has passed, **When** I view the board, **Then** the task shows "Overdue" in red
3. **Given** a task is due in 5 days, **When** I view the task card, **Then** I see "in 5 days"
4. **Given** I want to find urgent work, **When** I sort by due date, **Then** tasks are ordered with soonest due dates first

---

### User Story 9 - Activity Trail (Priority: P3)

As a project member, I want to see task activity history so I can understand what changes were made and by whom.

**Why this priority**: Nice-to-have for accountability and context. Not required for functional task management.

**Independent Test**: Can be tested by making changes to a task and viewing the activity log in the task details.

**Acceptance Scenarios**:

1. **Given** I change a task's status from "To Do" to "In Progress", **When** I view task details, **Then** I see an activity entry showing "John moved task to In Progress - 5 min ago"
2. **Given** someone comments on a task, **When** I view the activity log, **Then** I see "Jane commented - 2 hours ago"
3. **Given** a task has 10+ activities, **When** I view task details, **Then** I see the most recent activities first

---

### User Story 10 - Board Statistics (Priority: P3)

As a project manager, I want to see board statistics so I can understand overall project progress at a glance.

**Why this priority**: Useful for project oversight but not required for individual task management.

**Independent Test**: Can be tested by viewing the stats section and verifying counts match the actual tasks on the board.

**Acceptance Scenarios**:

1. **Given** the board has 10 tasks (3 done, 2 in progress), **When** I view the stats section, **Then** I see "Total: 10, In Progress: 2, Completed: 3"
2. **Given** 2 tasks are overdue, **When** I view the stats, **Then** I see "Overdue: 2" highlighted in red

---

### Edge Cases

- What happens when a user tries to drag a task to a column at its WIP limit? → Show warning message and prevent the drop
- What happens when searching and no tasks match? → Show "No tasks found" message with option to clear search
- What happens when deleting a task with subtasks and comments? → Delete all associated data (cascade)
- What happens when a user loses network connection while dragging? → Revert the task to original position and show error
- What happens when two users edit the same task simultaneously? → Last save wins, but show warning if data changed
- What happens when due date is set to past date? → Allow it but immediately show as overdue

## Requirements *(mandatory)*

### Functional Requirements

**Task Core**
- **FR-001**: System MUST allow authenticated project members to create tasks with title (required), description, priority, and due date
- **FR-002**: System MUST display tasks in their assigned columns on the Kanban board
- **FR-003**: System MUST allow task editing by the task creator, assignee, or project owner
- **FR-004**: System MUST allow task deletion by the task creator or project owner
- **FR-005**: System MUST persist all task changes to the database immediately

**Drag-and-Drop**
- **FR-006**: System MUST allow dragging tasks between columns (To Do, In Progress, In Review, Done)
- **FR-007**: System MUST enforce WIP limits per column and show warning when exceeded
- **FR-008**: System MUST save task position changes after drag-and-drop
- **FR-009**: System MUST provide "Move to..." menu as mobile alternative to drag-and-drop

**Subtasks**
- **FR-010**: System MUST allow creating subtasks within a task
- **FR-011**: System MUST allow toggling subtask completion status
- **FR-012**: System MUST display subtask progress (completed/total) on task cards
- **FR-013**: System MUST allow deleting subtasks
- **FR-014**: System MUST allow reordering subtasks within a task

**Comments**
- **FR-015**: System MUST allow adding comments to tasks
- **FR-016**: System MUST display comments with author name and timestamp
- **FR-017**: System MUST allow editing own comments within 15 minutes of posting
- **FR-018**: System MUST allow deleting own comments
- **FR-019**: System MUST display comment count on task cards

**Labels**
- **FR-020**: System MUST allow project owners to create, edit, and delete project labels
- **FR-021**: System MUST allow assigning multiple labels to a task
- **FR-022**: System MUST display up to 3 labels on task cards with overflow indicator

**Filtering & Search**
- **FR-023**: System MUST allow searching tasks by title, description, and task ID
- **FR-024**: System MUST allow filtering by labels, assignee, priority, and due date range
- **FR-025**: System MUST persist filter state in URL for sharing
- **FR-026**: System MUST provide "Clear All Filters" functionality

**Assignment**
- **FR-027**: System MUST allow assigning one team member to a task
- **FR-028**: System MUST display assignee avatar on task cards
- **FR-029**: System MUST only show project members in the assignee dropdown

**Due Dates**
- **FR-030**: System MUST allow setting and editing due dates on tasks
- **FR-031**: System MUST display relative due dates (Today, Tomorrow, X days ago/from now)
- **FR-032**: System MUST visually indicate overdue tasks (red) and due soon tasks (orange)
- **FR-033**: System MUST allow sorting tasks by due date

**Activity**
- **FR-034**: System MUST log task creation, status changes, assignments, and comments
- **FR-035**: System MUST display activity history in task detail view

### Key Entities

- **Task**: A work item with title, description, priority, due date, and position within a column. Belongs to a column (status), may have one assignee, multiple labels, subtasks, and comments.

- **Subtask**: A checklist item within a task with title, completion status, and position for ordering.

- **Comment**: A discussion entry on a task with content, author, and timestamp. Editable within 15 minutes of creation.

- **Label**: A project-level categorization tag with name and color. Can be assigned to multiple tasks.

- **Column**: A status container (To Do, In Progress, In Review, Done) with optional WIP limit and position for ordering.

- **Activity**: An audit log entry recording changes to tasks (created, moved, assigned, commented).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can create a new task and see it on the board within 2 seconds
- **SC-002**: Drag-and-drop task movement completes and persists within 1 second
- **SC-003**: Users can complete all task CRUD operations without encountering errors 99% of the time
- **SC-004**: Search results appear within 500ms of user stopping typing
- **SC-005**: Task board loads and displays up to 100 tasks within 3 seconds
- **SC-006**: 95% of users can successfully create and move a task on first attempt without guidance
- **SC-007**: Filter state persists correctly when sharing URLs 100% of the time
- **SC-008**: All features function correctly on mobile devices (768px width and below)
- **SC-009**: Subtask progress accurately reflects completion status on all task cards
- **SC-010**: Activity log captures 100% of task changes (creates, updates, moves, comments)

## Assumptions

1. **Authentication**: Users are already authenticated via the existing auth system before accessing the Kanban board
2. **Project Context**: Tasks exist within a project context; users access boards via `/projects/{id}/kanban`
3. **Default Columns**: Every project board has 4 default columns: To Do, In Progress, In Review, Done
4. **Single Assignee**: Tasks support single assignee only (not multiple assignees)
5. **Immediate Save**: All changes are saved immediately (no draft/save button workflow)
6. **Priority Levels**: Four priority levels exist: Low, Medium, High, Critical
7. **No Soft Delete**: Deleted tasks, subtasks, and comments are permanently removed
8. **Comment Edit Window**: 15-minute edit window is a fixed business rule
9. **WIP Limits Optional**: WIP limits are configurable per column but disabled (0) by default

## Out of Scope

1. Real-time collaborative editing (multiple users editing same task simultaneously)
2. WebSocket-based instant updates (will use polling or manual refresh initially)
3. Task dependencies/blocking relationships
4. Time tracking and estimation
5. Recurring tasks
6. Task templates
7. Board customization (custom columns, column colors)
8. Export/import functionality
9. Keyboard shortcuts
10. Offline mode
