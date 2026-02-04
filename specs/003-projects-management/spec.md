# Feature Specification: Projects Management

**Feature Branch**: `003-projects-management`
**Created**: 2026-02-04
**Status**: Draft
**Input**: Complete Projects Management System - Full CRUD operations with project listing, filtering, sorting, archiving, team member management, and project duplication for authenticated users

## Clarifications

### Session 2026-02-04

- Q: Should archived projects be shown in the main list? → A: No, archived projects should be in a separate "Archived" tab/filter
- Q: Can archived projects be restored? → A: Yes, users can unarchive projects to restore them
- Q: What happens to tasks when a project is archived? → A: Tasks remain unchanged but are hidden from dashboard statistics
- Q: Should there be a confirmation dialog for delete? → A: Yes, with option to type project name for critical projects
- Q: Can project members be promoted to instructor? → A: Not in this phase - transfer ownership is out of scope
- Q: How should concurrent edit conflicts be detected and handled? → A: Optimistic locking with `updated_at` timestamp comparison
- Q: What should the default role be when adding a new team member? → A: Viewer (read-only by default, least privilege)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Projects List View (Priority: P1) 🎯 MVP

As a project manager, I want to see a comprehensive list of all my projects with key information at a glance, so that I can quickly find and access any project I'm working on.

**Why this priority**: Users need a central place to view and manage all their projects. The dashboard only shows 5 recent projects, which is insufficient for users with many projects.

**Independent Test**: Can be fully tested by logging in and navigating to the Projects page, verifying all projects are displayed with correct information.

**Acceptance Scenarios**:

1. **Given** I am a logged-in user with 10 projects, **When** I navigate to the Projects page, **Then** I see a list/grid of all 10 projects with title, description, status badges, and member avatars
2. **Given** I am viewing the projects list, **When** I look at each project card, **Then** I see the project title, truncated description (max 150 chars), timeline status badge, task progress bar, and up to 5 member avatars
3. **Given** I am viewing the projects list, **When** I click on the view toggle, **Then** I can switch between grid view (cards) and list view (rows)
4. **Given** I am viewing the projects list, **When** I look at the header, **Then** I see a count of total projects (e.g., "12 Projects")
5. **Given** I am viewing a project card, **When** I hover over it, **Then** I see a subtle elevation effect and quick action buttons appear (edit, archive, delete)
6. **Given** I click on a project card (not on action buttons), **When** the navigation completes, **Then** I am taken to the project's kanban board view
7. **Given** I have no projects, **When** I view the Projects page, **Then** I see an empty state with illustration and "Create Your First Project" call-to-action

---

### User Story 2 - Create New Project (Priority: P1) 🎯 MVP

As a project manager, I want to create a new project with all necessary details, so that I can organize my work and invite team members.

**Why this priority**: Creating projects is the fundamental action that enables all other project management features.

**Independent Test**: Can be fully tested by clicking "New Project", filling out the form, and verifying the project is created and appears in the list.

**Acceptance Scenarios**:

1. **Given** I am on the Projects page, **When** I click the "+ New Project" button, **Then** a modal/drawer opens with a project creation form
2. **Given** I am viewing the project creation form, **When** I see the fields, **Then** I see inputs for: Title (required), Description (optional), Timeline Status (dropdown), Budget Status (dropdown), and an optional color/icon selector
3. **Given** I have filled in the required fields, **When** I click "Create Project", **Then** the project is created and I see a success notification
4. **Given** project creation succeeds, **When** the modal closes, **Then** the new project appears at the top of my projects list
5. **Given** I submit the form with empty title, **When** validation runs, **Then** I see an error message "Title is required"
6. **Given** I click outside the modal or press Escape, **When** I have unsaved changes, **Then** I see a confirmation dialog asking if I want to discard changes
7. **Given** project creation is in progress, **When** the API call is executing, **Then** the submit button shows a loading spinner and is disabled
8. **Given** I create a new project, **When** it's created, **Then** a default board with standard columns (Backlog, To Do, In Progress, Review, Done) is automatically created

---

### User Story 3 - Edit Project Details (Priority: P1) 🎯 MVP

As a project manager, I want to edit my project's details, so that I can keep project information up to date as requirements change.

**Why this priority**: Projects evolve over time and users need to update details without recreating them.

**Independent Test**: Can be fully tested by editing a project's title and description, then verifying the changes persist.

**Acceptance Scenarios**:

1. **Given** I am viewing a project card, **When** I click the edit button (pencil icon), **Then** an edit modal/drawer opens pre-filled with the project's current data
2. **Given** I am in the edit modal, **When** I change the title and click "Save", **Then** the project is updated and I see a success notification
3. **Given** I am editing a project, **When** I click "Cancel" or press Escape, **Then** the modal closes without saving changes
4. **Given** I am a project member (not instructor), **When** I try to edit the project, **Then** I can edit title, description, and status but cannot delete or transfer ownership
5. **Given** I update the timeline status to "At Risk", **When** I save, **Then** the project card shows an orange "At Risk" badge
6. **Given** I clear the title field, **When** I try to save, **Then** I see a validation error and the save is prevented
7. **Given** another user has updated the project (detected via `updated_at` timestamp mismatch), **When** I try to save my changes, **Then** I see a conflict warning with option to overwrite (force save) or refresh (reload latest data and discard my changes)

---

### User Story 4 - Delete Project (Priority: P1) 🎯 MVP

As a project instructor (owner), I want to delete projects I no longer need, so that I can keep my workspace organized and remove clutter.

**Why this priority**: Users need the ability to remove projects they no longer need, but this must be protected to prevent accidental data loss.

**Independent Test**: Can be fully tested by deleting a project and verifying it no longer appears in the list and all related data is removed.

**Acceptance Scenarios**:

1. **Given** I am the project instructor, **When** I click the delete button on a project, **Then** I see a confirmation dialog warning about permanent deletion
2. **Given** I see the delete confirmation, **When** the project has more than 10 tasks, **Then** I must type the project name to confirm deletion
3. **Given** I confirm the deletion, **When** the delete completes, **Then** the project and all its boards, columns, tasks, and comments are permanently deleted
4. **Given** I am a project member (not instructor), **When** I try to delete the project, **Then** I see an error message "Only the project owner can delete this project"
5. **Given** delete is in progress, **When** the API call is executing, **Then** the confirm button shows a loading spinner
6. **Given** deletion fails, **When** the error occurs, **Then** I see an error message and the project remains intact
7. **Given** I click "Cancel" on the confirmation dialog, **When** the dialog closes, **Then** the project is not deleted

---

### User Story 5 - Archive/Unarchive Project (Priority: P2)

As a project manager, I want to archive completed or inactive projects without deleting them, so that I can declutter my active workspace while preserving project history.

**Why this priority**: Archiving is a safer alternative to deletion for projects that may need to be referenced later.

**Independent Test**: Can be fully tested by archiving a project, verifying it moves to the archived section, and unarchiving it.

**Acceptance Scenarios**:

1. **Given** I am viewing a project card, **When** I click the archive button, **Then** I see a confirmation dialog "Archive this project?"
2. **Given** I confirm archiving, **When** the action completes, **Then** the project disappears from the main list and moves to the "Archived" section
3. **Given** I am viewing the archived projects, **When** I see an archived project card, **Then** it has a visual indicator (gray overlay or badge) showing it's archived
4. **Given** I am viewing an archived project, **When** I click "Unarchive", **Then** the project returns to the active projects list
5. **Given** I view the Projects page, **When** I look at the tabs/filters, **Then** I see "Active" and "Archived" options with counts
6. **Given** a project is archived, **When** dashboard statistics are calculated, **Then** archived project tasks are NOT included in active or overdue counts
7. **Given** I try to access an archived project's kanban board, **When** I click on it, **Then** I can view the board in read-only mode with an "Unarchive to edit" button
8. **Given** I am a project member (not instructor), **When** I try to archive the project, **Then** I see an error "Only the project owner can archive this project"

---

### User Story 6 - Filter and Sort Projects (Priority: P2)

As a user with many projects, I want to filter and sort my projects, so that I can quickly find specific projects or organize them by different criteria.

**Why this priority**: As users accumulate projects, finding specific ones becomes difficult without filtering and sorting capabilities.

**Independent Test**: Can be fully tested by applying various filters and sorting options, then verifying the list updates correctly.

**Acceptance Scenarios**:

1. **Given** I am on the Projects page, **When** I look at the toolbar, **Then** I see filter dropdowns for Status and Role, and a sort dropdown
2. **Given** I select "At Risk" from the status filter, **When** the filter applies, **Then** I only see projects with "At Risk" timeline status
3. **Given** I select "My Projects" from the role filter, **When** the filter applies, **Then** I only see projects where I am the instructor (owner)
4. **Given** I select "Member Of" from the role filter, **When** the filter applies, **Then** I only see projects where I am a member but not the instructor
5. **Given** I select "Last Updated" from sort options, **When** sorting applies, **Then** projects are ordered by most recently updated first
6. **Given** I select "Title A-Z" from sort options, **When** sorting applies, **Then** projects are ordered alphabetically by title
7. **Given** I have active filters, **When** I click "Clear Filters", **Then** all filters are reset and all projects are shown
8. **Given** I apply filters, **When** I refresh the page, **Then** my filter selections are preserved (via URL params or localStorage)

---

### User Story 7 - Search Projects (Priority: P2)

As a user, I want to search for projects by name or description, so that I can quickly find a specific project without scrolling through the entire list.

**Why this priority**: Search is essential for users with many projects who need to find specific ones quickly.

**Independent Test**: Can be fully tested by typing search queries and verifying matching projects are shown.

**Acceptance Scenarios**:

1. **Given** I am on the Projects page, **When** I look at the toolbar, **Then** I see a search input with placeholder "Search projects..."
2. **Given** I type "marketing" in the search box, **When** the search executes (debounced 300ms), **Then** I see only projects with "marketing" in title or description
3. **Given** I search for "xyz123" (no matches), **When** results are empty, **Then** I see "No projects found matching your search"
4. **Given** I have search text entered, **When** I click the X button in the search field, **Then** the search is cleared and all projects are shown
5. **Given** I search while filters are active, **When** results are shown, **Then** search works within the filtered results (search AND filter)
6. **Given** I search for partial word "mark", **When** results are shown, **Then** projects with "marketing" or "bookmark" in title/description appear
7. **Given** I press Escape while in the search field, **When** the action triggers, **Then** the search is cleared and focus leaves the input

---

### User Story 8 - Manage Project Team Members (Priority: P3)

As a project instructor, I want to add and remove team members from my project, so that I can control who has access and collaborate effectively.

**Why this priority**: Team collaboration is important but can be implemented after core CRUD operations are working.

**Independent Test**: Can be fully tested by adding a member to a project, verifying they appear in the members list, and removing them.

**Acceptance Scenarios**:

1. **Given** I am on the project edit modal, **When** I click "Manage Team", **Then** I see a section showing current team members with their roles
2. **Given** I am in the team management section, **When** I click "Add Member", **Then** I see a search input to find users by name or email
3. **Given** I search for a user, **When** I click on their name in results, **Then** they are added to the project as a "Viewer" role (read-only by default)
4. **Given** I view a team member, **When** I see their entry, **Then** I see their avatar, name, email, and role (Instructor/Editor/Viewer)
5. **Given** I am the project instructor, **When** I click the remove button next to a member, **Then** they are removed from the project after confirmation
6. **Given** I am a project member, **When** I try to add/remove members, **Then** I see an error "Only the project owner can manage team members"
7. **Given** I change a member's role from Editor to Viewer, **When** I save, **Then** their permissions are updated and they can only view (not edit)
8. **Given** I add a member, **When** they next log in, **Then** they see the project in their projects list

---

### User Story 9 - Duplicate Project (Priority: P4) [Optional Enhancement]

As a project manager, I want to duplicate an existing project as a template, so that I can quickly create similar projects without starting from scratch.

**Why this priority**: Nice-to-have feature that improves efficiency but is not critical for MVP.

**Independent Test**: Can be fully tested by duplicating a project and verifying the copy has all the same settings and structure.

**Acceptance Scenarios**:

1. **Given** I am viewing a project, **When** I click "Duplicate" from the actions menu, **Then** I see a dialog asking for the new project name
2. **Given** I enter a name and confirm, **When** duplication completes, **Then** a new project is created with the same description, status, and board structure
3. **Given** I duplicate a project, **When** I choose "Include tasks", **Then** all tasks are copied to the new project (without assignees)
4. **Given** I duplicate a project, **When** I choose "Structure only", **Then** only boards and columns are copied (no tasks)
5. **Given** duplication completes, **When** I view the new project, **Then** I am the instructor (owner) regardless of original project ownership
6. **Given** the original project has team members, **When** I duplicate, **Then** team members are NOT copied (I can add them manually)

---

### Edge Cases

- What happens when a user is removed from a project while viewing it? → Show notification "You have been removed from this project" and redirect to Projects page
- What happens when viewing a deleted project via direct URL? → Show 404 page with "Project not found" and link back to projects list
- What happens with very long project titles (200+ chars)? → Prevent input > 100 chars with character counter showing remaining
- What happens when trying to archive the last active project? → Allow it - users can have zero active projects
- What happens when a project has 100+ team members? → Paginate member list, show first 20 with "Load more" button
- What happens when duplicate API call fails mid-way? → Roll back any created project, show error message
- What happens when filter returns zero results? → Show "No projects match your filters" with clear filters button
- What happens when user leaves Projects page with unsaved modal data? → Show browser's beforeunload warning

## Requirements *(mandatory)*

### Functional Requirements

#### Projects List

- **FR-001**: System MUST display all user's projects (as instructor or member) on the Projects page
- **FR-002**: System MUST show each project with: title, description excerpt (150 chars max), timeline status badge, budget status badge, task progress bar, and up to 5 member avatars
- **FR-003**: System MUST provide toggle between grid view (cards) and list view (table rows)
- **FR-004**: System MUST display total project count in the page header
- **FR-005**: System MUST show hover state on project cards with quick action buttons (edit, archive, delete)
- **FR-006**: System MUST navigate to project kanban board when clicking the project card
- **FR-007**: System MUST display empty state with CTA when user has no projects

#### Create Project

- **FR-008**: System MUST provide "New Project" button prominently displayed on Projects page
- **FR-009**: System MUST open modal/drawer with form fields: title (required), description, timeline status, budget status
- **FR-010**: System MUST validate title is required and max 100 characters
- **FR-011**: System MUST validate description max 500 characters
- **FR-012**: System MUST create project with authenticated user as instructor (owner)
- **FR-013**: System MUST automatically create default board with columns: Backlog, To Do, In Progress, Review, Done
- **FR-014**: System MUST show success notification after project creation
- **FR-015**: System MUST add new project to list without full page refresh

#### Edit Project

- **FR-016**: System MUST allow editing project title, description, timeline status, and budget status
- **FR-017**: System MUST pre-populate edit form with current project values
- **FR-018**: System MUST allow instructors AND members to edit project details
- **FR-019**: System MUST validate edited data with same rules as creation
- **FR-020**: System MUST update project list item without full page refresh
- **FR-021**: System MUST show success notification after update

#### Delete Project

- **FR-022**: System MUST only allow project instructor (owner) to delete projects
- **FR-023**: System MUST show confirmation dialog before deletion
- **FR-024**: System MUST require typing project name for projects with >10 tasks
- **FR-025**: System MUST cascade delete all boards, columns, tasks, comments, and labels
- **FR-026**: System MUST remove project from list without full page refresh
- **FR-027**: System MUST show success notification after deletion

#### Archive Project

- **FR-028**: System MUST only allow project instructor to archive/unarchive projects
- **FR-029**: System MUST move archived projects to separate "Archived" section
- **FR-030**: System MUST exclude archived projects from dashboard statistics
- **FR-031**: System MUST allow viewing archived project boards in read-only mode
- **FR-032**: System MUST allow unarchiving to restore project to active state
- **FR-033**: System MUST show archived indicator on archived project cards
- **FR-034**: System MUST show archive/active counts in filter tabs

#### Filter and Sort

- **FR-035**: System MUST provide status filter (All, On Track, At Risk, Delayed)
- **FR-036**: System MUST provide role filter (All, My Projects, Member Of)
- **FR-037**: System MUST provide sort options (Last Updated, Created Date, Title A-Z, Title Z-A)
- **FR-038**: System MUST persist filter/sort state across page refreshes
- **FR-039**: System MUST provide "Clear Filters" button to reset all filters

#### Search

- **FR-040**: System MUST provide search input to filter projects
- **FR-041**: System MUST search across project titles and descriptions
- **FR-042**: System MUST debounce search with 300ms delay
- **FR-043**: System MUST show "No results" state when search returns empty
- **FR-044**: System MUST allow clearing search with X button or Escape key

#### Team Management

- **FR-045**: System MUST display team members list with avatar, name, email, and role
- **FR-046**: System MUST allow instructor to add members by searching users
- **FR-047**: System MUST allow instructor to remove members with confirmation
- **FR-048**: System MUST allow instructor to change member roles (Editor, Viewer)
- **FR-049**: System MUST prevent non-instructors from modifying team membership

#### API Endpoints

- **FR-050**: System MUST provide GET `/api/projects` with filter, sort, search, and pagination params
- **FR-051**: System MUST provide POST `/api/projects` for creating projects
- **FR-052**: System MUST provide PUT `/api/projects/{id}` for updating projects
- **FR-053**: System MUST provide DELETE `/api/projects/{id}` for deleting projects
- **FR-054**: System MUST provide POST `/api/projects/{id}/archive` for archiving
- **FR-055**: System MUST provide POST `/api/projects/{id}/unarchive` for unarchiving
- **FR-056**: System MUST provide GET `/api/projects/{id}/members` for listing members
- **FR-057**: System MUST provide POST `/api/projects/{id}/members` for adding members
- **FR-058**: System MUST provide DELETE `/api/projects/{id}/members/{userId}` for removing members
- **FR-059**: System MUST provide POST `/api/projects/{id}/duplicate` for duplicating projects

### Key Entities

- **Project**: Core entity with id, title, description, instructor_id, timeline_status, budget_status, is_archived, created_at, updated_at
- **Project Card**: Visual representation showing title, description excerpt, status badges, progress bar, member avatars, and action buttons
- **Project Member**: Association between user and project with role (instructor/editor/viewer)
- **Archive State**: Boolean flag on project indicating active/archived status

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can view complete list of their projects within 2 seconds of page load
- **SC-002**: Users can create a new project and see it in the list within 5 seconds
- **SC-003**: Users can edit project details and see changes reflected immediately
- **SC-004**: Users can archive/unarchive projects with status change reflected within 1 second
- **SC-005**: Users can delete projects with confirmation in under 3 seconds
- **SC-006**: Search returns results within 500ms of user stopping typing
- **SC-007**: Filter and sort apply changes within 200ms
- **SC-008**: Team member management operations complete within 2 seconds
- **SC-009**: 100% of existing kanban functionality continues working (no regression)
- **SC-010**: Empty states provide clear guidance for users with no projects

## Scope & Boundaries *(mandatory)*

### In Scope

- Projects list page with grid/list views
- Create project modal with form validation
- Edit project modal with pre-populated data
- Delete project with confirmation dialog
- Archive/unarchive project functionality
- Filter by status and role
- Sort by various criteria
- Search projects by title/description
- Team member management (add, remove, change role)
- View toggle persistence
- Filter/sort state persistence
- Empty states for all scenarios
- Loading states and error handling
- Mobile responsive design

### Out of Scope

- Project templates library
- Project import/export
- Project analytics dashboard
- Gantt chart view
- Calendar view
- Time tracking
- Budget tracking beyond status label
- File attachments to projects
- Project comments/discussions
- Project milestones
- Project tags/categories
- Bulk project operations
- Project sharing via public links
- Project permissions beyond owner/editor/viewer
- Notifications for project changes
- Project activity history page
- Custom fields for projects

## Assumptions *(mandatory)*

1. The existing Project model has an `is_archived` field or can be added via migration
2. Laravel Sanctum authentication is working and provides user context
3. The existing design system (glassmorphic effects, dark theme) is available
4. Vue 3, Pinia, Vue Router are configured and working
5. The Projects API contracts from 002-dashboard-navigation are the starting point
6. Database supports soft deletes or we use hard deletes with cascade
7. User search API exists for finding users by name/email
8. Project member roles are stored in pivot table with role field

## Dependencies *(mandatory)*

### External Dependencies

- Laravel framework with Sanctum authentication
- Vue 3 with Pinia and Vue Router
- Existing design system CSS variables
- Database with projects, project_members, users tables

### Internal Dependencies

- 002-dashboard-navigation must be complete (layout, navigation)
- User authentication system must be working
- Dashboard statistics API must handle archived projects correctly

## Technical Constraints *(if applicable)*

1. Must use existing dark theme design system
2. Must be mobile responsive (breakpoint 768px)
3. Must handle up to 100 projects per user performantly
4. Must not exceed 50KB additional JS bundle size
5. API calls must complete within 2 seconds
6. Must gracefully handle network failures

## Risks & Mitigations *(if applicable)*

### Risk 1: Cascade Delete Data Loss
**Risk**: Deleting a project permanently removes all associated data (boards, tasks, comments)
**Impact**: High - Users may accidentally lose important data
**Mitigation**:
- Require typing project name for projects with many tasks
- Show count of items that will be deleted in confirmation
- Consider soft delete with 30-day recovery period (future enhancement)

### Risk 2: Archive Feature Complexity
**Risk**: Archive feature adds complexity to queries and UI state management
**Impact**: Medium - Could slow down development or introduce bugs
**Mitigation**:
- Add is_archived column to projects table
- Use simple boolean flag rather than separate archive table
- Create reusable scopes for active/archived filtering

### Risk 3: Team Permission Complexity
**Risk**: Role-based permissions (instructor/editor/viewer) may be difficult to enforce consistently
**Impact**: Medium - Security issues or confusing UX
**Mitigation**:
- Centralize permission checks in Laravel Policy class
- Create frontend permission helpers in composable
- Test permission scenarios thoroughly

## Open Questions *(if applicable)*

1. Should we support drag-and-drop reordering of projects? → Defer to future enhancement
2. Should archived projects count toward any limits? → No, archive is free
3. Should we send email notifications when added to/removed from projects? → Out of scope for now
4. Should duplicated projects have a "Duplicated from X" reference? → Nice to have, defer
