# Tasks: Projects Management

**Input**: Design documents from `/specs/003-projects-management/`
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

**Purpose**: Project initialization and database schema updates

- [X] T001 Create database migration for is_archived column in `database/migrations/2026_02_04_add_is_archived_to_projects_table.php`
- [X] T002 Run migration to add is_archived column with index
- [X] T003 [P] Create projects Pinia store skeleton in `resources/js/stores/projects.js`
- [X] T004 [P] Create useProjectPermissions composable in `resources/js/composables/useProjectPermissions.js`
- [X] T005 [P] Create projects components directory structure at `resources/js/components/projects/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T006 Update Project model with is_archived fillable, scopes (active, archived, forUser) in `app/Models/Project.php`
- [x] T007 [P] Create StoreProjectRequest validation in `app/Http/Requests/StoreProjectRequest.php`
- [x] T008 [P] Create UpdateProjectRequest validation in `app/Http/Requests/UpdateProjectRequest.php`
- [x] T009 Create ProjectController with constructor and base structure in `app/Http/Controllers/ProjectController.php`
- [x] T010 Update ProjectPolicy with edit, archive, manageMembers methods in `app/Policies/ProjectPolicy.php`
- [x] T011 Register project API routes (apiResource + custom actions) in `routes/api.php`
- [x] T012 Update ProjectResource with is_archived, permissions, task counts in `app/Http/Resources/ProjectResource.php`

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Projects List View (Priority: P1) 🎯 MVP

**Goal**: Display all user's projects in grid/list view with key information

**Independent Test**: Login, navigate to /projects, verify all projects displayed with title, description, badges, avatars

### Implementation for User Story 1

- [x] T013 [US1] Implement ProjectController@index with filters, pagination, sorting in `app/Http/Controllers/ProjectController.php`
- [x] T014 [P] [US1] Create ProjectCard.vue component with status badges, progress bar, avatars in `resources/js/components/projects/ProjectCard.vue`
- [x] T015 [P] [US1] Create ProjectRow.vue component for list view in `resources/js/components/projects/ProjectRow.vue`
- [x] T016 [P] [US1] Create EmptyState.vue component with CTA in `resources/js/components/projects/EmptyState.vue`
- [x] T017 [US1] Implement fetchProjects action in projects store in `resources/js/stores/projects.js`
- [x] T018 [US1] Implement ProjectsList.vue page with grid/list toggle, project count header in `resources/js/pages/projects/ProjectsList.vue`
- [x] T019 [US1] Add view preference persistence (localStorage) in ProjectsList.vue
- [x] T020 [US1] Implement hover state with quick action buttons on ProjectCard.vue
- [x] T021 [US1] Wire project card click to navigate to kanban board route

**Checkpoint**: User Story 1 complete - projects list view fully functional

---

## Phase 4: User Story 2 - Create New Project (Priority: P1) 🎯 MVP

**Goal**: Enable users to create new projects with default board

**Independent Test**: Click "New Project", fill form, submit, verify project appears in list with default board

### Implementation for User Story 2

- [x] T022 [US2] Implement ProjectController@store with default board creation in `app/Http/Controllers/ProjectController.php`
- [x] T023 [P] [US2] Create ProjectModal.vue component with form fields in `resources/js/components/projects/ProjectModal.vue`
- [x] T024 [US2] Implement createProject action in projects store in `resources/js/stores/projects.js`
- [x] T025 [US2] Add form validation (title required, max lengths) in ProjectModal.vue
- [x] T026 [US2] Implement loading state and disabled button during submission
- [x] T027 [US2] Add success notification after project creation
- [x] T028 [US2] Implement unsaved changes confirmation dialog on modal close
- [x] T029 [US2] Add "New Project" button to ProjectsList.vue toolbar
- [x] T030 [US2] Optimistically add new project to list without page refresh

**Checkpoint**: User Story 2 complete - project creation fully functional

---

## Phase 5: User Story 3 - Edit Project Details (Priority: P1) 🎯 MVP

**Goal**: Allow editing project title, description, and status fields

**Independent Test**: Click edit on project, change title, save, verify changes persist

### Implementation for User Story 3

- [x] T031 [US3] Implement ProjectController@show to return project details in `app/Http/Controllers/ProjectController.php`
- [x] T032 [US3] Implement ProjectController@update with validation in `app/Http/Controllers/ProjectController.php`
- [x] T033 [US3] Add edit mode to ProjectModal.vue with pre-populated data
- [x] T034 [US3] Implement updateProject action in projects store in `resources/js/stores/projects.js`
- [x] T035 [US3] Implement optimistic locking with updated_at comparison in update endpoint
- [x] T036 [US3] Create ConflictModal.vue for concurrent edit warnings in `resources/js/components/projects/ConflictModal.vue`
- [x] T037 [US3] Wire edit button on ProjectCard to open modal in edit mode
- [x] T038 [US3] Update project in list without page refresh after save
- [x] T039 [US3] Apply permission check - editors can edit, viewers cannot

**Checkpoint**: User Story 3 complete - project editing fully functional

---

## Phase 6: User Story 4 - Delete Project (Priority: P1) 🎯 MVP

**Goal**: Enable project owners to delete projects with confirmation

**Independent Test**: Click delete on owned project, confirm, verify project removed from list

### Implementation for User Story 4

- [x] T040 [US4] Implement ProjectController@destroy with cascade delete in `app/Http/Controllers/ProjectController.php`
- [x] T041 [US4] Add task count check for confirmation requirement in destroy method
- [x] T042 [P] [US4] Create DeleteConfirmModal.vue with name-typing for critical projects in `resources/js/components/projects/DeleteConfirmModal.vue`
- [x] T043 [US4] Implement deleteProject action in projects store in `resources/js/stores/projects.js`
- [x] T044 [US4] Wire delete button on ProjectCard to open confirmation modal
- [x] T045 [US4] Apply permission check - only instructor can delete
- [x] T046 [US4] Show error message if non-owner attempts delete
- [x] T047 [US4] Remove project from list optimistically after deletion

**Checkpoint**: User Story 4 complete - project deletion fully functional

---

## Phase 7: User Story 5 - Archive/Unarchive Project (Priority: P2)

**Goal**: Allow archiving projects to hide from active list without deletion

**Independent Test**: Archive project, verify moves to archived tab, unarchive, verify returns to active

### Implementation for User Story 5

- [ ] T048 [US5] Implement ProjectController@archive endpoint in `app/Http/Controllers/ProjectController.php`
- [ ] T049 [US5] Implement ProjectController@unarchive endpoint in `app/Http/Controllers/ProjectController.php`
- [ ] T050 [P] [US5] Create ArchiveConfirmModal.vue in `resources/js/components/projects/ArchiveConfirmModal.vue`
- [ ] T051 [US5] Implement archiveProject and unarchiveProject actions in projects store
- [ ] T052 [US5] Add Active/Archived tabs to ProjectsList.vue toolbar
- [ ] T053 [US5] Style archived project cards with gray overlay indicator
- [ ] T054 [US5] Update DashboardController to exclude archived projects from stats in `app/Http/Controllers/DashboardController.php`
- [ ] T055 [US5] Implement read-only mode for archived project kanban boards
- [ ] T056 [US5] Apply permission check - only instructor can archive/unarchive

**Checkpoint**: User Story 5 complete - archive functionality fully functional

---

## Phase 8: User Story 6 - Filter and Sort Projects (Priority: P2)

**Goal**: Enable filtering by status/role and sorting by various criteria

**Independent Test**: Apply status filter, verify list updates, apply sort, verify order changes

### Implementation for User Story 6

- [ ] T057 [US6] Add filter parameters (status, role, sort, order) to ProjectController@index
- [ ] T058 [P] [US6] Create ProjectFilters.vue with status dropdown in `resources/js/components/projects/ProjectFilters.vue`
- [ ] T059 [US6] Add role filter dropdown (All, My Projects, Member Of) to ProjectFilters.vue
- [ ] T060 [US6] Add sort dropdown (Last Updated, Created, Title A-Z, Title Z-A) to ProjectFilters.vue
- [ ] T061 [US6] Implement filter state in projects store with URL param sync
- [ ] T062 [US6] Add "Clear Filters" button functionality
- [ ] T063 [US6] Persist filter state to localStorage as fallback
- [ ] T064 [US6] Wire filters to refetch projects with debounce

**Checkpoint**: User Story 6 complete - filter and sort fully functional

---

## Phase 9: User Story 7 - Search Projects (Priority: P2)

**Goal**: Enable text search across project titles and descriptions

**Independent Test**: Type search query, verify matching projects shown within 500ms

### Implementation for User Story 7

- [ ] T065 [US7] Add search parameter to ProjectController@index with LIKE query
- [ ] T066 [P] [US7] Create ProjectSearch.vue with input and clear button in `resources/js/components/projects/ProjectSearch.vue`
- [ ] T067 [US7] Implement 300ms debounced search in projects store
- [ ] T068 [US7] Add search state to URL params for shareability
- [ ] T069 [US7] Show "No projects found" state when search returns empty
- [ ] T070 [US7] Implement Escape key to clear search and blur input
- [ ] T071 [US7] Integrate search with active filters (AND logic)

**Checkpoint**: User Story 7 complete - search fully functional

---

## Phase 10: User Story 8 - Manage Project Team Members (Priority: P3)

**Goal**: Allow project owners to add, remove, and change member roles

**Independent Test**: Add member to project, verify they appear in list, change role, remove member

### Implementation for User Story 8

- [ ] T072 [US8] Implement ProjectController@members to list project members in `app/Http/Controllers/ProjectController.php`
- [ ] T073 [US8] Implement ProjectController@addMember with default Viewer role in `app/Http/Controllers/ProjectController.php`
- [ ] T074 [US8] Implement ProjectController@updateMember for role changes in `app/Http/Controllers/ProjectController.php`
- [ ] T075 [US8] Implement ProjectController@removeMember with confirmation in `app/Http/Controllers/ProjectController.php`
- [ ] T076 [P] [US8] Create AddProjectMemberRequest validation in `app/Http/Requests/AddProjectMemberRequest.php`
- [ ] T077 [US8] Create UserController@search for finding users by name/email in `app/Http/Controllers/UserController.php`
- [ ] T078 [P] [US8] Create TeamManagement.vue component in `resources/js/components/projects/TeamManagement.vue`
- [ ] T079 [US8] Implement user search with debounce in TeamManagement.vue
- [ ] T080 [US8] Implement member list with role dropdown and remove button
- [ ] T081 [US8] Add team management section to ProjectModal.vue edit mode
- [ ] T082 [US8] Apply permission check - only instructor can manage members
- [ ] T083 [US8] Paginate member list for projects with 20+ members

**Checkpoint**: User Story 8 complete - team management fully functional

---

## Phase 11: User Story 9 - Duplicate Project (Priority: P4) [Optional]

**Goal**: Allow duplicating projects with optional task copying

**Independent Test**: Duplicate project with "structure only", verify new project created with boards/columns

### Implementation for User Story 9

- [ ] T084 [US9] Implement ProjectController@duplicate with transaction in `app/Http/Controllers/ProjectController.php`
- [ ] T085 [US9] Add include_tasks option to duplicate boards, columns, and optionally tasks
- [ ] T086 [P] [US9] Create DuplicateProjectModal.vue with name input and options in `resources/js/components/projects/DuplicateProjectModal.vue`
- [ ] T087 [US9] Implement duplicateProject action in projects store
- [ ] T088 [US9] Add "Duplicate" option to project card actions menu
- [ ] T089 [US9] Handle duplication failure with rollback and error message
- [ ] T090 [US9] Set current user as instructor (owner) of duplicated project

**Checkpoint**: User Story 9 complete - project duplication fully functional

---

## Phase 12: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T091 [P] Mobile responsive design for ProjectsList at 768px breakpoint
- [ ] T092 [P] Mobile responsive design for ProjectModal at 768px breakpoint
- [ ] T093 [P] Mobile responsive design for ProjectCard grid/list toggle
- [ ] T094 Add loading skeletons for project list while fetching
- [ ] T095 Implement error boundary and retry logic for API failures
- [ ] T096 Add keyboard navigation (arrow keys in list, Enter to open)
- [ ] T097 Performance optimization - lazy load project avatars
- [ ] T098 Bundle size audit - ensure <50KB additional JS
- [ ] T099 Run quickstart.md validation for all user flows
- [ ] T100 Final accessibility audit (ARIA labels, focus management)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-11)**: All depend on Foundational phase completion
  - User stories can proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3 → P4)
- **Polish (Phase 12)**: Depends on MVP user stories (US1-US4) being complete

### User Story Dependencies

| Story | Priority | Depends On | Can Start After |
|-------|----------|------------|-----------------|
| US1 - List View | P1 | Foundational | Phase 2 |
| US2 - Create | P1 | Foundational | Phase 2 |
| US3 - Edit | P1 | US1 (for modal reuse) | Phase 3 |
| US4 - Delete | P1 | US1 (for list integration) | Phase 3 |
| US5 - Archive | P2 | US1 (for tab integration) | Phase 3 |
| US6 - Filter/Sort | P2 | US1 (for toolbar) | Phase 3 |
| US7 - Search | P2 | US1, US6 (toolbar) | Phase 8 |
| US8 - Team | P3 | US3 (modal integration) | Phase 5 |
| US9 - Duplicate | P4 | US2 (create logic) | Phase 4 |

### Within Each User Story

- Backend endpoints before frontend integration
- Store actions before component wiring
- Core implementation before optimizations
- Permission checks integrated with features

### Parallel Opportunities

**Phase 2 Parallel Tasks**:
```
T007 [P] StoreProjectRequest
T008 [P] UpdateProjectRequest
```

**US1 Parallel Tasks** (after T013):
```
T014 [P] ProjectCard.vue
T015 [P] ProjectRow.vue
T016 [P] EmptyState.vue
```

**Cross-Story Parallel** (after Phase 2):
- Developer A: US1 + US2 (core list and create)
- Developer B: US3 + US4 (edit and delete)
- Then merge for US5-US9

---

## Parallel Example: MVP Sprint (US1-US4)

```bash
# Day 1: Foundation (sequential)
T001-T012: Complete setup and foundational tasks

# Day 2-3: US1 + US2 in parallel
Developer A:
  T013, T014, T015, T016, T017, T018, T019, T020, T021

Developer B:
  T022, T023, T024, T025, T026, T027, T028, T029, T030

# Day 4: US3 + US4 in parallel
Developer A:
  T031-T039 (Edit)

Developer B:
  T040-T047 (Delete)

# Day 5: Integration and testing
```

---

## Implementation Strategy

### MVP First (User Stories 1-4)

1. Complete Phase 1: Setup (T001-T005)
2. Complete Phase 2: Foundational (T006-T012) - CRITICAL
3. Complete Phase 3: US1 - Projects List (T013-T021)
4. Complete Phase 4: US2 - Create Project (T022-T030)
5. Complete Phase 5: US3 - Edit Project (T031-T039)
6. Complete Phase 6: US4 - Delete Project (T040-T047)
7. **STOP and VALIDATE**: Test all MVP flows
8. Deploy/demo MVP

### Incremental Delivery

| Increment | User Stories | Deliverable |
|-----------|--------------|-------------|
| MVP | US1-US4 | Full CRUD for projects |
| Release 2 | +US5-US7 | Archive, filter, search |
| Release 3 | +US8 | Team management |
| Release 4 | +US9 | Project duplication |

### Estimated Task Counts

| Phase | Tasks | Parallelizable |
|-------|-------|----------------|
| Setup | 5 | 3 |
| Foundational | 7 | 2 |
| US1 - List | 9 | 3 |
| US2 - Create | 9 | 1 |
| US3 - Edit | 9 | 0 |
| US4 - Delete | 8 | 1 |
| US5 - Archive | 9 | 1 |
| US6 - Filter/Sort | 8 | 1 |
| US7 - Search | 7 | 1 |
| US8 - Team | 12 | 2 |
| US9 - Duplicate | 7 | 1 |
| Polish | 10 | 4 |
| **Total** | **100** | **20** |

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- All API endpoints follow contracts/api.yaml specification
- Frontend follows existing glassmorphic dark theme design system
