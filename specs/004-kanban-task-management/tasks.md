# Tasks: Kanban Board & Task Management

**Input**: Design documents from `/specs/004-kanban-task-management/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.yaml, quickstart.md

**Tests**: Tests are NOT explicitly requested - implementation tasks only.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `app/` (Laravel standard structure)
- **Frontend**: `resources/js/` (Vue 3 SPA)
- **Routes**: `routes/api.php`
- **Migrations**: `database/migrations/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verify existing infrastructure and add missing foundation pieces

- [x] T001 Verify existing database tables (tasks, subtasks, comments, labels, activities) exist and match data-model.md
- [x] T002 [P] Create migration for `edited_at` column on comments table if not present in `database/migrations/2026_02_06_add_edited_at_to_comments_table.php`
- [x] T003 [P] Create comments Pinia store skeleton in `resources/js/stores/comments.js`
- [x] T004 [P] Create labels Pinia store skeleton in `resources/js/stores/labels.js`
- [x] T005 [P] Create useCommentEditing composable skeleton in `resources/js/composables/useCommentEditing.js`
- [x] T006 Run migration to add edited_at column with index

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core controllers, policies, and API routes that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Backend Controllers

- [x] T007 Create SubtaskController with index, store, update, destroy, reorder methods in `app/Http/Controllers/SubtaskController.php`
- [x] T008 [P] Create CommentController with index, store, update, destroy methods in `app/Http/Controllers/CommentController.php`
- [x] T009 [P] Create LabelController with index, store, update, destroy methods in `app/Http/Controllers/LabelController.php`
- [x] T010 [P] Create ActivityController with index and projectActivities methods in `app/Http/Controllers/ActivityController.php`

### Form Requests

- [x] T011 [P] Create StoreSubtaskRequest validation in `app/Http/Requests/StoreSubtaskRequest.php`
- [x] T012 [P] Create UpdateSubtaskRequest validation in `app/Http/Requests/UpdateSubtaskRequest.php`
- [x] T013 [P] Create StoreCommentRequest validation in `app/Http/Requests/StoreCommentRequest.php`
- [x] T014 [P] Create UpdateCommentRequest validation in `app/Http/Requests/UpdateCommentRequest.php`
- [x] T015 [P] Create StoreLabelRequest validation in `app/Http/Requests/StoreLabelRequest.php`
- [x] T016 [P] Create UpdateLabelRequest validation in `app/Http/Requests/UpdateLabelRequest.php`

### Policies

- [x] T017 Create CommentPolicy with update (15-min window check), delete methods in `app/Policies/CommentPolicy.php`
- [x] T018 [P] Create LabelPolicy with create, update, delete (project owner only) in `app/Policies/LabelPolicy.php`

### Resources

- [x] T019 [P] Create SubtaskResource in `app/Http/Resources/SubtaskResource.php`
- [x] T020 [P] Create CommentResource with is_editable computed property in `app/Http/Resources/CommentResource.php`
- [x] T021 [P] Create LabelResource in `app/Http/Resources/LabelResource.php`
- [x] T022 [P] Create ActivityResource in `app/Http/Resources/ActivityResource.php`

### API Routes

- [x] T023 Register subtask routes (nested under tasks) in `routes/api.php`
- [x] T024 Register comment routes (shallow nesting) in `routes/api.php`
- [x] T025 Register label routes (nested under projects) in `routes/api.php`
- [x] T026 Register activity routes (task and project scoped) in `routes/api.php`
- [x] T027 Add task label sync route POST `/tasks/{task}/labels` in `routes/api.php`

### Model Enhancements

- [x] T028 Update Task model with computed attributes (progress, is_overdue, label_count) in `app/Models/Task.php`
- [x] T029 [P] Update Comment model with isEditable accessor in `app/Models/Comment.php`
- [x] T030 Register CommentPolicy and LabelPolicy in `app/Providers/AuthServiceProvider.php`

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Task CRUD Operations (Priority: P1) 🎯 MVP

**Goal**: Create, view, edit, and delete tasks on the Kanban board

**Independent Test**: Create a task, view it on the board, edit its details, delete it - all operations persist correctly

### Implementation for User Story 1

- [ ] T031 [US1] Enhance TaskController@store with activity logging in `app/Http/Controllers/TaskController.php`
- [ ] T032 [US1] Enhance TaskController@update with activity logging in `app/Http/Controllers/TaskController.php`
- [ ] T033 [US1] Enhance TaskController@destroy with cascade delete and activity logging in `app/Http/Controllers/TaskController.php`
- [ ] T034 [US1] Update TaskResource with subtask_count, comment_count, progress, is_overdue in `app/Http/Resources/TaskResource.php`
- [ ] T035 [P] [US1] Create TaskDetailResource for full task view with relationships in `app/Http/Resources/TaskDetailResource.php`
- [ ] T036 [US1] Implement fetchTask action with full details in tasks store `resources/js/stores/tasks.js`
- [ ] T037 [US1] Implement createTask action with optimistic update in tasks store `resources/js/stores/tasks.js`
- [ ] T038 [US1] Implement updateTask action with optimistic update in tasks store `resources/js/stores/tasks.js`
- [ ] T039 [US1] Implement deleteTask action with optimistic removal in tasks store `resources/js/stores/tasks.js`
- [ ] T040 [US1] Enhance TaskModal.vue with all form fields (title, description, priority, due_date, assignee) in `resources/js/components/kanban/TaskModal.vue`
- [ ] T041 [US1] Enhance TaskDetailModal.vue with full task view and edit mode in `resources/js/components/kanban/TaskDetailModal.vue`
- [ ] T042 [US1] Add delete confirmation dialog to TaskDetailModal.vue
- [ ] T043 [US1] Wire TaskCard click to open TaskDetailModal in `resources/js/components/kanban/TaskCard.vue`
- [ ] T044 [US1] Add permission checks for edit/delete buttons based on TaskPolicy

**Checkpoint**: User Story 1 complete - task CRUD fully functional

---

## Phase 4: User Story 2 - Drag-and-Drop Task Movement (Priority: P1) 🎯 MVP

**Goal**: Drag tasks between columns to update their status visually

**Independent Test**: Drag a task from "To Do" to "In Progress", verify it stays after page refresh

### Implementation for User Story 2

- [ ] T045 [US2] Enhance TaskController@move with WIP limit validation and activity logging in `app/Http/Controllers/TaskController.php`
- [ ] T046 [US2] Implement moveTask action with optimistic update and rollback in tasks store `resources/js/stores/tasks.js`
- [ ] T047 [US2] Enhance useDragDrop composable with WIP limit check in `resources/js/composables/useDragDrop.js`
- [ ] T048 [US2] Add visual WIP limit indicator to KanbanColumn.vue in `resources/js/components/kanban/KanbanColumn.vue`
- [ ] T049 [US2] Show warning toast when WIP limit exceeded during drag
- [ ] T050 [US2] Implement reorderTasks action for within-column reordering in tasks store `resources/js/stores/tasks.js`
- [ ] T051 [US2] Add "Move to..." menu option on TaskCard.vue for mobile support in `resources/js/components/kanban/TaskCard.vue`
- [ ] T052 [US2] Implement column selection submenu in TaskCard actions menu
- [ ] T053 [US2] Add drag handle styling and visual feedback during drag

**Checkpoint**: User Story 2 complete - drag-and-drop fully functional

---

## Phase 5: User Story 3 - Subtask Management (Priority: P1) 🎯 MVP

**Goal**: Break down tasks into subtasks with progress tracking

**Independent Test**: Add subtasks to a task, check them off, verify progress bar updates on task card

### Implementation for User Story 3

- [ ] T054 [US3] Implement SubtaskController@index to list subtasks for a task in `app/Http/Controllers/SubtaskController.php`
- [ ] T055 [US3] Implement SubtaskController@store to create subtask with activity logging in `app/Http/Controllers/SubtaskController.php`
- [ ] T056 [US3] Implement SubtaskController@update to toggle completion and edit title in `app/Http/Controllers/SubtaskController.php`
- [ ] T057 [US3] Implement SubtaskController@destroy to delete subtask in `app/Http/Controllers/SubtaskController.php`
- [ ] T058 [US3] Implement SubtaskController@reorder for drag-to-reorder in `app/Http/Controllers/SubtaskController.php`
- [ ] T059 [P] [US3] Create SubtaskList.vue component with checkbox list in `resources/js/components/kanban/SubtaskList.vue`
- [ ] T060 [US3] Add subtask input field with Enter key submission in SubtaskList.vue
- [ ] T061 [US3] Implement subtask drag-to-reorder with drag handles in SubtaskList.vue
- [ ] T062 [US3] Add delete button on subtask hover in SubtaskList.vue
- [ ] T063 [US3] Implement optimistic toggle for subtask completion in SubtaskList.vue
- [ ] T064 [US3] Integrate SubtaskList into TaskDetailModal.vue
- [ ] T065 [US3] Add subtask progress indicator (2/5) and progress bar to TaskCard.vue in `resources/js/components/kanban/TaskCard.vue`

**Checkpoint**: User Story 3 complete - subtask management fully functional

---

## Phase 6: User Story 4 - Task Comments (Priority: P2)

**Goal**: Collaborate with team via task comments with 15-minute edit window

**Independent Test**: Add a comment, verify it shows with author and timestamp, edit within 15 minutes, verify edit lock after

### Implementation for User Story 4

- [ ] T066 [US4] Implement CommentController@index with pagination in `app/Http/Controllers/CommentController.php`
- [ ] T067 [US4] Implement CommentController@store with activity logging in `app/Http/Controllers/CommentController.php`
- [ ] T068 [US4] Implement CommentController@update with 15-min window enforcement in `app/Http/Controllers/CommentController.php`
- [ ] T069 [US4] Implement CommentController@destroy in `app/Http/Controllers/CommentController.php`
- [ ] T070 [US4] Implement fetchComments action in comments store `resources/js/stores/comments.js`
- [ ] T071 [US4] Implement addComment action with optimistic update in comments store `resources/js/stores/comments.js`
- [ ] T072 [US4] Implement updateComment action in comments store `resources/js/stores/comments.js`
- [ ] T073 [US4] Implement deleteComment action in comments store `resources/js/stores/comments.js`
- [ ] T074 [US4] Implement isEditable and timeUntilLocked in useCommentEditing composable `resources/js/composables/useCommentEditing.js`
- [ ] T075 [P] [US4] Create CommentSection.vue component with comment list in `resources/js/components/kanban/CommentSection.vue`
- [ ] T076 [US4] Add comment input textarea with submit button in CommentSection.vue
- [ ] T077 [US4] Show relative timestamps (5 min ago, 2 hours ago) on comments
- [ ] T078 [US4] Add edit/delete buttons that hide after 15-minute window
- [ ] T079 [US4] Show "Edited" indicator when comment has been modified
- [ ] T080 [US4] Implement inline comment editing mode in CommentSection.vue
- [ ] T081 [US4] Integrate CommentSection into TaskDetailModal.vue
- [ ] T082 [US4] Add comment count badge to TaskCard.vue

**Checkpoint**: User Story 4 complete - comments fully functional

---

## Phase 7: User Story 5 - Task Labels (Priority: P2)

**Goal**: Categorize tasks with colored labels for organization

**Independent Test**: Create a label, assign to a task, verify label appears on task card

### Implementation for User Story 5

- [ ] T083 [US5] Implement LabelController@index to list project labels in `app/Http/Controllers/LabelController.php`
- [ ] T084 [US5] Implement LabelController@store to create label in `app/Http/Controllers/LabelController.php`
- [ ] T085 [US5] Implement LabelController@update to edit label in `app/Http/Controllers/LabelController.php`
- [ ] T086 [US5] Implement LabelController@destroy to delete label (cascade from tasks) in `app/Http/Controllers/LabelController.php`
- [ ] T087 [US5] Implement TaskController@syncLabels to assign labels to task in `app/Http/Controllers/TaskController.php`
- [ ] T088 [US5] Implement fetchLabels action in labels store `resources/js/stores/labels.js`
- [ ] T089 [US5] Implement createLabel action in labels store `resources/js/stores/labels.js`
- [ ] T090 [US5] Implement updateLabel action in labels store `resources/js/stores/labels.js`
- [ ] T091 [US5] Implement deleteLabel action in labels store `resources/js/stores/labels.js`
- [ ] T092 [P] [US5] Create LabelManager.vue with preset color palette (12 colors) in `resources/js/components/kanban/LabelManager.vue`
- [ ] T093 [US5] Add create label form with name input and color swatches in LabelManager.vue
- [ ] T094 [US5] Add edit and delete actions for existing labels in LabelManager.vue
- [ ] T095 [P] [US5] Create LabelSelector.vue for assigning labels to tasks in `resources/js/components/kanban/LabelSelector.vue`
- [ ] T096 [US5] Integrate LabelSelector into TaskDetailModal.vue
- [ ] T097 [US5] Display up to 3 labels on TaskCard.vue with "+N" overflow indicator in `resources/js/components/kanban/TaskCard.vue`
- [ ] T098 [US5] Add label management section to project settings or board header

**Checkpoint**: User Story 5 complete - labels fully functional

---

## Phase 8: User Story 6 - Task Filtering & Search (Priority: P2)

**Goal**: Filter and search tasks to find specific items on busy boards

**Independent Test**: Search for "login" in search box, verify only matching tasks appear

### Implementation for User Story 6

- [ ] T099 [US6] Enhance TaskController@index with search and filter parameters in `app/Http/Controllers/TaskController.php`
- [ ] T100 [US6] Add filter by labels (comma-separated IDs) to TaskController@index
- [ ] T101 [US6] Add filter by assignee to TaskController@index
- [ ] T102 [US6] Add filter by priority to TaskController@index
- [ ] T103 [US6] Add filter by due date range (overdue, today, this_week) to TaskController@index
- [ ] T104 [P] [US6] Create FilterBar.vue component in `resources/js/components/kanban/FilterBar.vue`
- [ ] T105 [US6] Add search input with 300ms debounce in FilterBar.vue
- [ ] T106 [US6] Add label filter multiselect dropdown in FilterBar.vue
- [ ] T107 [US6] Add assignee filter dropdown in FilterBar.vue
- [ ] T108 [US6] Add priority filter checkboxes in FilterBar.vue
- [ ] T109 [US6] Add due date filter (overdue, due today, due this week) in FilterBar.vue
- [ ] T110 [US6] Add "Clear All Filters" button with active filter count badge
- [ ] T111 [US6] Implement URL query param sync for filters (useRoute, router.push)
- [ ] T112 [US6] Implement filter state restoration from URL on component mount
- [ ] T113 [US6] Enhance useTaskFiltering composable with URL sync in `resources/js/composables/useTaskFiltering.js`
- [ ] T114 [US6] Integrate FilterBar into BoardHeader.vue in `resources/js/components/kanban/BoardHeader.vue`
- [ ] T115 [US6] Show "No tasks found" empty state when filters return no results

**Checkpoint**: User Story 6 complete - filtering and search fully functional

---

## Phase 9: User Story 7 - Task Assignment (Priority: P2)

**Goal**: Assign tasks to team members for clear work distribution

**Independent Test**: Assign a user to a task, verify their avatar appears on task card

### Implementation for User Story 7

- [ ] T116 [US7] Enhance TaskResource to include assignee with avatar_url in `app/Http/Resources/TaskResource.php`
- [ ] T117 [US7] Add project members endpoint for assignee dropdown (use existing ProjectController@members or create)
- [ ] T118 [P] [US7] Create AssigneeSelector.vue dropdown component in `resources/js/components/kanban/AssigneeSelector.vue`
- [ ] T119 [US7] Show project members in AssigneeSelector dropdown
- [ ] T120 [US7] Add "Unassigned" option to AssigneeSelector
- [ ] T121 [US7] Integrate AssigneeSelector into TaskModal.vue
- [ ] T122 [US7] Integrate AssigneeSelector into TaskDetailModal.vue
- [ ] T123 [US7] Display assignee avatar on TaskCard.vue in `resources/js/components/kanban/TaskCard.vue`
- [ ] T124 [US7] Add "Assigned to me" quick filter option in FilterBar.vue
- [ ] T125 [US7] Implement assignTask action in tasks store `resources/js/stores/tasks.js`

**Checkpoint**: User Story 7 complete - task assignment fully functional

---

## Phase 10: User Story 8 - Due Date Management (Priority: P2)

**Goal**: Set and track due dates with visual urgency indicators

**Independent Test**: Set due date to tomorrow, verify orange indicator; set to past, verify red "Overdue"

### Implementation for User Story 8

- [ ] T126 [US8] Enhance TaskResource with formatted due date and urgency level in `app/Http/Resources/TaskResource.php`
- [ ] T127 [P] [US8] Create DatePicker.vue component in `resources/js/components/common/DatePicker.vue`
- [ ] T128 [US8] Integrate DatePicker into TaskModal.vue for due_date field
- [ ] T129 [US8] Integrate DatePicker into TaskDetailModal.vue
- [ ] T130 [US8] Display relative due date on TaskCard.vue (Today, Tomorrow, in 5 days, Overdue)
- [ ] T131 [US8] Style due date badge: red for overdue, orange for due soon (<=3 days), gray for future
- [ ] T132 [US8] Add due date sorting option to board (sort by due date ascending)
- [ ] T133 [US8] Log due date changes in activity trail

**Checkpoint**: User Story 8 complete - due date management fully functional

---

## Phase 11: User Story 9 - Activity Trail (Priority: P3)

**Goal**: View task activity history to understand changes

**Independent Test**: Make changes to a task, view activity log showing who did what and when

### Implementation for User Story 9

- [ ] T134 [US9] Implement ActivityController@index to list task activities in `app/Http/Controllers/ActivityController.php`
- [ ] T135 [US9] Implement ActivityController@projectActivities for project-wide activities in `app/Http/Controllers/ActivityController.php`
- [ ] T136 [US9] Create ActivityObserver to log all task-related events in `app/Observers/ActivityObserver.php`
- [ ] T137 [US9] Register ActivityObserver for Task, Subtask, Comment models in `app/Providers/AppServiceProvider.php`
- [ ] T138 [P] [US9] Create ActivityFeed.vue component in `resources/js/components/kanban/ActivityFeed.vue`
- [ ] T139 [US9] Display activity type with appropriate icon (created, moved, assigned, commented)
- [ ] T140 [US9] Show user avatar and relative timestamp for each activity
- [ ] T141 [US9] Add "Load more" pagination for activities
- [ ] T142 [US9] Integrate ActivityFeed into TaskDetailModal.vue (separate tab or section)
- [ ] T143 [US9] Format activity messages (e.g., "John moved task to In Progress - 5 min ago")

**Checkpoint**: User Story 9 complete - activity trail fully functional

---

## Phase 12: User Story 10 - Board Statistics (Priority: P3)

**Goal**: View board statistics for project progress overview

**Independent Test**: View stats section, verify counts match actual tasks on board

### Implementation for User Story 10

- [ ] T144 [US10] Create BoardController@stats endpoint for board statistics in `app/Http/Controllers/BoardController.php`
- [ ] T145 [US10] Calculate task counts per column, overdue count, completion percentage
- [ ] T146 [P] [US10] Create BoardStats.vue component in `resources/js/components/kanban/BoardStats.vue`
- [ ] T147 [US10] Display total tasks, in progress, completed counts in BoardStats.vue
- [ ] T148 [US10] Highlight overdue count in red when > 0
- [ ] T149 [US10] Show completion percentage with progress bar
- [ ] T150 [US10] Integrate BoardStats into BoardHeader.vue
- [ ] T151 [US10] Add refresh button to update stats

**Checkpoint**: User Story 10 complete - board statistics fully functional

---

## Phase 13: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T152 [P] Mobile responsive design for KanbanBoard at 768px breakpoint
- [ ] T153 [P] Mobile responsive design for TaskDetailModal at 768px breakpoint
- [ ] T154 [P] Mobile responsive design for FilterBar at 768px breakpoint
- [ ] T155 Add loading skeletons for task cards while fetching
- [ ] T156 Implement error boundary and retry logic for API failures
- [ ] T157 Add keyboard navigation (arrow keys between cards, Enter to open)
- [ ] T158 Performance optimization - lazy load comments and activities on modal open
- [ ] T159 Bundle size audit - ensure <50KB additional JS
- [ ] T160 Add "Refresh" button to BoardHeader with "Last updated" indicator
- [ ] T161 Run quickstart.md validation for all user flows
- [ ] T162 Final accessibility audit (ARIA labels, focus management)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-12)**: All depend on Foundational phase completion
  - User stories can proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Phase 13)**: Depends on MVP user stories (US1-US3) being complete

### User Story Dependencies

| Story | Priority | Depends On | Can Start After |
|-------|----------|------------|-----------------|
| US1 - Task CRUD | P1 | Foundational | Phase 2 |
| US2 - Drag-Drop | P1 | Foundational | Phase 2 |
| US3 - Subtasks | P1 | Foundational | Phase 2 |
| US4 - Comments | P2 | Foundational | Phase 2 |
| US5 - Labels | P2 | Foundational | Phase 2 |
| US6 - Filtering | P2 | US5 (for label filter) | Phase 7 |
| US7 - Assignment | P2 | Foundational | Phase 2 |
| US8 - Due Dates | P2 | Foundational | Phase 2 |
| US9 - Activity | P3 | US1 (activity logging) | Phase 3 |
| US10 - Stats | P3 | Foundational | Phase 2 |

### Within Each User Story

- Backend endpoints before frontend integration
- Store actions before component wiring
- Core implementation before optimizations
- Permission checks integrated with features

### Parallel Opportunities

**Phase 2 Parallel Tasks**:
```
T008 [P] CommentController
T009 [P] LabelController
T010 [P] ActivityController
T011-T016 [P] All Form Requests
T018 [P] LabelPolicy
T019-T022 [P] All Resources
```

**US1-US3 Parallel** (all P1 stories can start after Phase 2):
```
Developer A: US1 - Task CRUD (T031-T044)
Developer B: US2 - Drag-Drop (T045-T053)
Developer C: US3 - Subtasks (T054-T065)
```

**US4-US5-US7-US8 Parallel** (P2 stories without dependencies):
```
Developer A: US4 - Comments (T066-T082)
Developer B: US5 - Labels (T083-T098)
Developer C: US7 - Assignment (T116-T125)
Developer D: US8 - Due Dates (T126-T133)
```

---

## Parallel Example: MVP Sprint (US1-US3)

```bash
# Day 1: Foundation (sequential)
T001-T030: Complete setup and foundational tasks

# Day 2-3: US1 + US2 + US3 in parallel
Developer A:
  T031-T044 (Task CRUD)

Developer B:
  T045-T053 (Drag-Drop)

Developer C:
  T054-T065 (Subtasks)

# Day 4: Integration and testing
```

---

## Implementation Strategy

### MVP First (User Stories 1-3)

1. Complete Phase 1: Setup (T001-T006)
2. Complete Phase 2: Foundational (T007-T030) - CRITICAL
3. Complete Phase 3: US1 - Task CRUD (T031-T044)
4. Complete Phase 4: US2 - Drag-Drop (T045-T053)
5. Complete Phase 5: US3 - Subtasks (T054-T065)
6. **STOP and VALIDATE**: Test all MVP flows
7. Deploy/demo MVP

### Incremental Delivery

| Increment | User Stories | Deliverable |
|-----------|--------------|-------------|
| MVP | US1-US3 | Full task CRUD, drag-drop, subtasks |
| Release 2 | +US4-US5 | Comments and labels |
| Release 3 | +US6-US8 | Filtering, assignment, due dates |
| Release 4 | +US9-US10 | Activity trail and stats |

### Estimated Task Counts

| Phase | Tasks | Parallelizable |
|-------|-------|----------------|
| Setup | 6 | 4 |
| Foundational | 24 | 14 |
| US1 - Task CRUD | 14 | 1 |
| US2 - Drag-Drop | 9 | 0 |
| US3 - Subtasks | 12 | 1 |
| US4 - Comments | 17 | 1 |
| US5 - Labels | 16 | 2 |
| US6 - Filtering | 17 | 1 |
| US7 - Assignment | 10 | 1 |
| US8 - Due Dates | 8 | 1 |
| US9 - Activity | 10 | 1 |
| US10 - Stats | 8 | 1 |
| Polish | 11 | 3 |
| **Total** | **162** | **31** |

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- All API endpoints follow contracts/api.yaml specification
- Frontend follows existing glassmorphic dark theme design system
- Most backend models already exist - focus on controllers and frontend components
