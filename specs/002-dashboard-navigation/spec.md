# Feature Specification: Dashboard & Navigation System

**Feature Branch**: `002-dashboard-navigation`
**Created**: 2026-02-03
**Status**: Draft
**Input**: Dashboard & Navigation System - Application layout with sidebar/navbar, real-time statistics dashboard, projects management, and global navigation for authenticated users

## Clarifications

### Session 2026-02-03

- Q: What should be the automatic session timeout policy for inactive users? → A: 30 minutes (industry standard balancing security and user convenience)
- Q: What are the permission differences between user roles? → A: Role-based - Project instructor (creator) has full control including delete, project members can view and edit but cannot delete projects
- Q: What are the expected maximum scale limits for dashboard performance optimization? → A: Small scale - 25 projects maximum and 500 tasks maximum per user (suitable for small teams and individual users)
- Q: What level of WCAG accessibility compliance is required? → A: WCAG 2.1 Level A (basic accessibility including keyboard navigation, alt text, and semantic HTML)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Application Layout & Navigation Access (Priority: P1) 🎯 MVP

As a logged-in user, I want to see a consistent navigation layout with a sidebar and top navigation bar when I access any authenticated page, so that I can easily navigate between different sections of the application and understand where I am.

**Why this priority**: This is the critical foundation that fixes the "black screen" issue. Without a layout and navigation system, users are trapped on the dashboard with no way to access other features. This is the minimum requirement to make the application usable after login.

**Independent Test**: Can be fully tested by logging in and verifying the navigation layout appears with functional sidebar links and top navbar. User can click sidebar items to navigate to different sections.

**Acceptance Scenarios**:

1. **Given** I am a logged-in user, **When** I land on the dashboard after login, **Then** I see a top navigation bar with the ProjectHub logo, search box, notifications icon, and my user avatar
2. **Given** I am viewing any authenticated page, **When** I look at the left side of the screen, **Then** I see a sidebar with navigation links (Dashboard, Projects, My Tasks, Team, Settings)
3. **Given** I am on the dashboard page, **When** I look at the sidebar, **Then** the Dashboard link is highlighted with an orange accent to indicate I'm on that page
4. **Given** I am viewing the sidebar, **When** I click on "Projects", **Then** I navigate to the projects list page and the Projects link becomes highlighted
5. **Given** I am on a mobile device (screen width < 768px), **When** I view the page, **Then** the sidebar is collapsed and I see a hamburger menu icon to toggle it
6. **Given** I am on mobile with sidebar collapsed, **When** I tap the hamburger menu, **Then** the sidebar slides in from the left with smooth animation
7. **Given** I am viewing the top navigation bar, **When** I click on my user avatar, **Then** a dropdown menu appears with options for Profile, Settings, and Logout
8. **Given** I am in the user avatar dropdown, **When** I click "Logout", **Then** I am logged out, my session is cleared, and I'm redirected to the login page

---

### User Story 2 - Real-Time Dashboard Statistics (Priority: P1) 🎯 MVP

As a project manager or team member, I want to see a dashboard with real-time statistics about my projects, tasks, team, and overdue items when I first log in, so that I can quickly understand the current state of my work and prioritize my actions.

**Why this priority**: The dashboard is the landing page after login. Users need immediate visibility into their work status. Without this, the dashboard page remains empty/black, providing no value. This is critical for first impression and daily workflow.

**Independent Test**: Can be fully tested by logging in with a user who has projects and tasks, verifying that all four statistic cards show accurate counts that match the database records.

**Acceptance Scenarios**:

1. **Given** I am a logged-in user with 5 projects, **When** I view the dashboard, **Then** I see a statistics card showing "Total Projects: 5"
2. **Given** I have 23 tasks that are not in Done or Archived status, **When** I view the dashboard, **Then** I see a statistics card showing "Active Tasks: 23"
3. **Given** I work with 8 distinct team members across all my projects, **When** I view the dashboard, **Then** I see a statistics card showing "Team Members: 8"
4. **Given** I have 3 tasks with due dates in the past that are not in Done or Archived status, **When** I view the dashboard, **Then** I see a statistics card showing "Overdue Tasks: 3" with a red highlight
5. **Given** I am a new user with no projects, **When** I view the dashboard, **Then** all statistics cards show "0" with an empty state message encouraging me to create my first project
6. **Given** I am viewing the dashboard statistics, **When** the page is loading data from the API, **Then** I see animated skeleton loaders in place of the numbers
7. **Given** the API fails to load statistics, **When** an error occurs, **Then** I see a user-friendly error message with a "Retry" button instead of the statistics
8. **Given** my project data changes (new task created, task completed), **When** I refresh the dashboard, **Then** the statistics update to reflect the latest counts

---

### User Story 3 - Projects List & Management (Priority: P2)

As a project manager, I want to see a list of my recent projects on the dashboard and be able to create new projects, so that I can quickly access my active work and start new initiatives.

**Why this priority**: After seeing statistics, users need to access their actual projects. This bridges the gap between overview and detailed work. It's the primary action users will take from the dashboard.

**Independent Test**: Can be fully tested by viewing the dashboard projects section, creating a new project via the modal, and verifying the new project appears in the list and database.

**Acceptance Scenarios**:

1. **Given** I have 5 projects in the system, **When** I scroll down on the dashboard, **Then** I see a "Recent Projects" section displaying my 5 most recently updated projects as cards
2. **Given** I am viewing a project card, **When** I look at the card, **Then** I see the project title, description (truncated to 100 characters), member avatars (up to 5 shown), timeline status badge, and task completion percentage
3. **Given** I am viewing a project card with "At Risk" timeline status, **When** I look at the status badge, **Then** I see an orange badge with the text "At Risk"
4. **Given** I am viewing the projects section, **When** I see the "+ New Project" button, **Then** it has prominent orange gradient styling matching the design system
5. **Given** I click the "+ New Project" button, **When** the modal opens, **Then** I see a glassmorphic modal with form fields for title (required), description (optional), timeline status dropdown, and budget status dropdown
6. **Given** I am creating a new project, **When** I fill in only the title and submit, **Then** the project is created with default values (timeline status: "On Track", budget status: "Within Budget")
7. **Given** I submit a valid project form, **When** the API successfully creates the project, **Then** the modal closes, I see a success notification, and the new project appears at the top of my projects list
8. **Given** I click on a project card, **When** I click anywhere on the card, **Then** I navigate to that project's kanban board view
9. **Given** I have no projects, **When** I view the dashboard, **Then** I see an empty state illustration with text "No projects yet" and a prominent "+ Create Your First Project" button
10. **Given** I am viewing project cards, **When** projects have team members, **Then** I see up to 5 member avatars displayed as circles with "+3 more" indicator if more than 5 members exist

---

### User Story 4 - Global Search (Priority: P3)

As a user working on multiple projects, I want to search for projects and tasks from anywhere in the application using the search box in the navigation bar, so that I can quickly find and navigate to specific items without browsing through lists.

**Why this priority**: While useful, search is not critical for MVP. Users can still navigate via sidebar and project lists. This is a productivity enhancement for users with many projects/tasks.

**Independent Test**: Can be fully tested by typing search queries into the navigation bar search box and verifying that relevant projects and tasks appear in the dropdown results.

**Acceptance Scenarios**:

1. **Given** I am on any authenticated page, **When** I look at the top navigation bar, **Then** I see a search input field with a magnifying glass icon and placeholder text "Search projects and tasks..."
2. **Given** I click into the search box, **When** I type "marketing", **Then** I see a dropdown appear below the search box showing projects and tasks matching "marketing"
3. **Given** I am viewing search results, **When** results are displayed, **Then** I see projects and tasks grouped separately with icons to distinguish them (folder icon for projects, checkbox icon for tasks)
4. **Given** I search for "bug fix", **When** the search returns 5 projects and 12 tasks, **Then** I see up to 10 results per type (10 projects, 10 tasks maximum) with a note indicating if more results exist
5. **Given** I am viewing search results, **When** I click on a project result, **Then** I navigate to that project's kanban board and the search dropdown closes
6. **Given** I am viewing search results, **When** I click on a task result, **Then** I navigate to the task's project kanban board with that task highlighted or in view
7. **Given** I am typing in the search box, **When** I pause typing for 300ms, **Then** the search API is called (debounced to avoid excessive API calls)
8. **Given** I search for something with no results, **When** the search returns empty, **Then** I see "No results found" message with a suggestion to try different keywords
9. **Given** I am on desktop, **When** I press Cmd+K (Mac) or Ctrl+K (Windows), **Then** the search box receives focus immediately for quick access

---

### User Story 5 - Activity Feed (Priority: P4) [Optional Enhancement]

As a team member, I want to see recent activities from my projects on the dashboard, so that I can stay informed about what my team is doing without checking each project individually.

**Why this priority**: This is a nice-to-have feature that enhances awareness but is not critical for core functionality. Users can still work effectively without seeing activity history.

**Independent Test**: Can be fully tested by performing actions (creating tasks, completing tasks, commenting) and verifying those activities appear in the dashboard activity feed.

**Acceptance Scenarios**:

1. **Given** I am viewing the dashboard, **When** I look below the projects section, **Then** I see an "Recent Activity" section displaying the last 10 activities from my projects
2. **Given** I am viewing an activity item, **When** I look at the activity, **Then** I see the user's avatar, name, action performed (created, updated, completed, commented), subject (task or project title), and relative timestamp
3. **Given** an activity happened 2 hours ago, **When** I view the timestamp, **Then** I see "2 hours ago" in human-readable format
4. **Given** I am viewing the activity feed, **When** I click on an activity item, **Then** I navigate to the related task or project
5. **Given** I have been viewing the dashboard for 30 seconds, **When** new activities occur, **Then** the feed automatically refreshes to show the new activities (optional auto-refresh)

---

### Edge Cases

- What happens when a user exceeds the expected scale limit (>25 projects)? Show only the 5 most recent on dashboard, provide "View All Projects" link to dedicated projects page, performance may degrade gracefully
- What happens when a project title is very long (200+ characters)? Truncate to 100 characters with ellipsis in card view
- What happens when a user has no avatar image? Display user's initials in a colored circle (e.g., "JD" for John Doe) using a generated background color
- What happens when the dashboard API times out or fails? Show skeleton loaders for 3 seconds, then display error state with "Retry" button and helpful error message
- What happens when a user is both instructor and member of the same project? Count the project once in statistics to avoid duplication
- What happens when creating a project with a duplicate title? Allow it - project titles don't need to be unique (users may have multiple "Q1 Marketing Campaign" projects)
- What happens when a user clicks multiple navigation links rapidly? Disable navigation during transitions to prevent race conditions and ensure smooth animations
- What happens when the sidebar is open on mobile and user rotates device to landscape? Automatically close sidebar on orientation change to maximize content area
- What happens when search API returns partial results due to database timeout? Display the partial results with a warning banner "Some results may be missing - try again"
- What happens when a user has no team members (working alone)? Display "Team Members: 1" (counting themselves) with their own avatar
- What happens when a project has 0% task completion (no tasks)? Show "No tasks yet" instead of "0%" to avoid confusion
- What happens when logout API call fails due to network issue? Clear local session/token anyway and redirect to login page (client-side logout)

## Requirements *(mandatory)*

### Functional Requirements

#### Application Layout & Navigation

- **FR-001**: System MUST display a consistent application layout on all authenticated pages with a top navigation bar and left sidebar
- **FR-002**: System MUST show a top navigation bar containing the ProjectHub logo, global search input, notifications bell icon, and user avatar dropdown
- **FR-003**: System MUST display a sidebar with navigation links to Dashboard, Projects, My Tasks, Team, and Settings pages
- **FR-004**: System MUST highlight the current active page in the sidebar with an orange accent border or background
- **FR-005**: System MUST render the sidebar as collapsible on mobile devices (< 768px width) with a hamburger menu toggle button
- **FR-006**: System MUST animate sidebar transitions smoothly (slide in/out with 300ms duration)
- **FR-007**: System MUST display user avatar dropdown menu when clicking the avatar, showing user name, email, and menu items (Profile, Settings, Logout)
- **FR-008**: System MUST log users out when clicking "Logout", clearing authentication tokens/sessions and redirecting to login page
- **FR-009**: System MUST automatically expire user sessions after 30 minutes of inactivity, requiring re-authentication to continue
- **FR-010**: System MUST apply the existing dark theme design system (glassmorphic effects, orange/blue colors) to all layout components

#### Dashboard Statistics

- **FR-011**: System MUST display four statistic cards on the dashboard: Total Projects, Active Tasks, Team Members, and Overdue Tasks
- **FR-012**: System MUST calculate Total Projects as the count of projects where the user is the instructor or a project member
- **FR-013**: System MUST calculate Active Tasks as the count of tasks in columns other than "Done" or "Archived" for the user's projects
- **FR-014**: System MUST calculate Team Members as the distinct count of users who are members of any of the user's projects
- **FR-015**: System MUST calculate Overdue Tasks as the count of tasks with due_date < today's date and not in "Done" or "Archived" columns
- **FR-016**: System MUST highlight the Overdue Tasks card with red accent styling when count is greater than zero
- **FR-017**: System MUST display animated skeleton loaders while dashboard statistics are being fetched from the API
- **FR-018**: System MUST show user-friendly error messages (displayed within 3 seconds of failure) with a "Retry" button if dashboard API calls fail
- **FR-019**: System MUST display an empty state with "0" values and a call-to-action to create first project when user has no projects

#### Projects List & Management

- **FR-020**: System MUST display a "Recent Projects" section on the dashboard showing the 5 most recently updated projects
- **FR-021**: System MUST render each project as a card displaying title, description (truncated to 100 characters), timeline status badge, budget status badge, task completion percentage, and member avatars
- **FR-022**: System MUST show up to 5 member avatars per project card with a "+N more" indicator if more than 5 members exist
- **FR-023**: System MUST display timeline status badges with appropriate colors: "On Track" (green), "At Risk" (orange), "Delayed" (red)
- **FR-024**: System MUST calculate task completion percentage as (completed tasks / total tasks) × 100 for each project
- **FR-025**: System MUST display a prominent "+ New Project" button with orange gradient styling
- **FR-026**: System MUST open a glassmorphic modal when clicking "+ New Project" with form fields for project creation
- **FR-027**: System MUST require project title (max 100 characters) and allow optional description (max 500 characters)
- **FR-028**: System MUST provide dropdown selections for timeline status (On Track, At Risk, Delayed) and budget status (Within Budget, Over Budget)
- **FR-029**: System MUST validate project form and display inline error messages for validation failures
- **FR-030**: System MUST create the project via API call and refresh the projects list upon successful creation
- **FR-031**: System MUST navigate to the project's kanban board when clicking anywhere on a project card
- **FR-032**: System MUST display an empty state with illustration and "+ Create Your First Project" button when user has no projects

#### Global Search

- **FR-033**: System MUST display a search input field in the top navigation bar with placeholder text "Search projects and tasks..."
- **FR-034**: System MUST show a dropdown below the search field displaying search results when user types
- **FR-035**: System MUST debounce search API calls with 300ms delay to avoid excessive requests while user is typing
- **FR-036**: System MUST search across both project titles/descriptions and task titles/descriptions
- **FR-037**: System MUST display search results grouped by type (Projects, Tasks) with appropriate icons
- **FR-038**: System MUST limit search results to 10 items per type (10 projects max, 10 tasks max)
- **FR-039**: System MUST indicate if more results exist beyond the displayed limit
- **FR-040**: System MUST navigate to the project's kanban board when clicking a project search result
- **FR-041**: System MUST navigate to the task's project kanban board when clicking a task search result
- **FR-042**: System MUST display "No results found" message when search returns empty results
- **FR-043**: System MUST focus the search input when user presses Cmd+K (Mac) or Ctrl+K (Windows) keyboard shortcut

#### Accessibility

- **FR-044**: System MUST comply with WCAG 2.1 Level A accessibility standards including keyboard navigation for all interactive elements, semantic HTML structure, and alternative text for images
- **FR-045**: System MUST support full keyboard navigation allowing users to access all features without a mouse (Tab, Enter, Escape, Arrow keys)
- **FR-046**: System MUST provide appropriate ARIA labels and roles for screen reader compatibility on all interactive components

#### Activity Feed (Optional)

- **FR-047**: System SHOULD display a "Recent Activity" section on the dashboard showing the last 10 activities from user's projects
- **FR-048**: System SHOULD render each activity item with user avatar, name, action type (created, updated, completed, commented), subject (task/project title), and relative timestamp
- **FR-049**: System SHOULD format timestamps in relative human-readable format (e.g., "2 hours ago", "yesterday", "last week")
- **FR-050**: System SHOULD allow clicking activity items to navigate to the related task or project
- **FR-051**: System MAY auto-refresh the activity feed every 30 seconds to show new activities

#### API Endpoints

- **FR-052**: System MUST provide GET `/api/dashboard/stats` endpoint returning total projects, active tasks, team members, and overdue tasks counts
- **FR-053**: System MUST provide GET `/api/projects` endpoint returning list of user's projects with metadata (members, task counts, completion percentage)
- **FR-054**: System MUST provide GET `/api/projects/{id}` endpoint returning detailed project information
- **FR-055**: System MUST provide POST `/api/projects` endpoint for creating new projects
- **FR-056**: System MUST provide PUT `/api/projects/{id}` endpoint for updating existing projects
- **FR-057**: System MUST provide DELETE `/api/projects/{id}` endpoint for deleting projects
- **FR-058**: System MUST provide GET `/api/search?q={query}` endpoint searching projects and tasks, returning up to 20 total results
- **FR-059**: System MUST provide GET `/api/users/search?q={query}` endpoint for searching users by name/email (for team member selection)
- **FR-060**: System SHOULD provide GET `/api/activities?limit={n}` endpoint returning recent activities for user's projects
- **FR-061**: System MUST require authentication (Sanctum tokens) for all API endpoints
- **FR-062**: System MUST enforce role-based authorization rules where project instructors (creators) have full control (create, read, update, delete), while project members can view and edit projects but cannot delete them

### Key Entities

- **Dashboard Statistics**: Aggregate metrics representing the user's work overview including total project count (integer), active task count (integer), team member count (integer), and overdue task count (integer). Calculated in real-time from database queries.

- **Project Card**: Summary representation of a project displayed on the dashboard including project title (string), description excerpt (first 100 characters), timeline status (enum: On Track/At Risk/Delayed), budget status (enum: Within Budget/Over Budget), task completion percentage (0-100%), and list of member avatars (up to 5 displayed).

- **Navigation Layout**: Persistent application frame containing top navigation bar with logo, search box, notifications icon, and user menu, plus sidebar with page navigation links. The layout wraps all authenticated page content and maintains state across route changes.

- **Search Result**: Item returned from global search containing type (project or task), title, optional description snippet, and identifier for navigation. Results are grouped by type and limited to 10 per type.

- **Activity Item**: Record of a user action in the system including actor (user who performed action), action type (created/updated/completed/commented), subject (task or project affected), timestamp (when it occurred), and optional additional data. Used to populate the activity feed.

- **User Avatar**: Visual representation of a user displayed in navigation and project cards, consisting of either an image URL (if provided) or generated initials with colored background (e.g., "JD" on blue circle). Used throughout the UI for user identification.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can access the dashboard after login and see the application layout with navigation within 2 seconds on standard broadband connections
- **SC-002**: Dashboard statistics accurately reflect current database state with zero counting errors (no duplicate projects, correct task states)
- **SC-003**: Users can navigate between any two pages (Dashboard, Projects, Kanban) in under 1 second with smooth transitions
- **SC-004**: Users can create a new project and see it appear in their projects list within 30 seconds from clicking "+ New Project"
- **SC-005**: Global search returns relevant results within 500ms of the user stopping typing
- **SC-006**: 100% of existing functionality (authentication, kanban board from Phase 1-6) continues working without regression
- **SC-007**: Users can complete the flow of login → view dashboard → navigate to project kanban board in under 10 seconds
- **SC-008**: Mobile users (< 768px screens) can access all navigation features with the collapsible sidebar working smoothly
- **SC-009**: Dashboard loads with skeleton placeholders immediately (< 100ms) and populates with real data within 2 seconds
- **SC-010**: Search feature finds projects/tasks across the expected scale (up to 25 projects + 500 tasks per user) with accurate results (no missing relevant items)
- **SC-011**: Logout successfully clears user session and redirects to login page in under 1 second
- **SC-012**: Users can identify their current page location within 2 seconds of viewing any authenticated page (clear visual indicator in sidebar)

## Scope & Boundaries *(mandatory)*

### In Scope

- Application layout component with top navigation bar and collapsible sidebar
- Dashboard page with four real-time statistics cards
- Recent projects list (5 most recent) on dashboard
- Project creation modal with form validation
- Navigation between Dashboard, Projects page (if created), and project Kanban boards
- User avatar dropdown menu with profile and logout options
- Global search functionality for projects and tasks
- Loading states with skeleton animations
- Error handling with user-friendly messages and retry capabilities
- Mobile-responsive layout with collapsible sidebar
- Integration with existing modern UI design system (dark theme, glassmorphic effects, orange/blue colors)
- API endpoints for dashboard stats, projects CRUD, search, and user lookup
- Activity feed display (optional enhancement)
- Breadcrumbs for nested pages
- Keyboard shortcuts (Cmd/Ctrl+K for search)

### Out of Scope

- Notifications system implementation (bell icon is placeholder for future feature)
- User profile page editing (profile menu link goes to placeholder for now)
- Settings page implementation (settings menu link goes to placeholder)
- "My Tasks" page showing user's assigned tasks across all projects
- Team management page showing all team members
- Advanced project filtering or sorting beyond "most recent"
- Project archiving or soft delete functionality
- Project sharing or permission management
- Calendar view of tasks/projects
- Gantt chart or timeline views
- File attachments to projects
- Project templates or duplication
- Export projects/tasks data
- Real-time collaborative features (live updates when other users make changes)
- Email notifications for project activities
- Integration with third-party services (Slack, GitHub, etc.)
- Advanced search filters (search by date, assignee, priority, etc.)
- Search history or saved searches
- Bulk operations on projects (delete multiple, move multiple)
- Project analytics or reporting dashboards beyond basic task completion percentage
- Time tracking for projects or tasks
- Budget management or financial tracking (budget status is just a label)
- Custom fields for projects
- Multi-language support (i18n) for new components

## Assumptions *(mandatory)*

1. The existing database schema (projects, tasks, boards, columns, labels, task_labels, project_members, subtasks, users, activities tables) is complete and requires no migrations
2. The Laravel backend API framework is already set up with Sanctum for authentication
3. The Vue 3 frontend with Pinia stores and Vue Router is already configured and working
4. The modern UI design system from Phase 1-6 (CSS variables, design-system.css, animations.css) is complete and can be reused
5. User authentication is working via the existing login system and returns valid Sanctum tokens
6. The existing kanban board implementation from Phase 5 (User Story 2) is complete and functional
7. The projects table has columns: id, title, description, instructor_id (project creator/owner with full permissions), timeline_status, budget_status, created_at, updated_at
8. The tasks table has columns: id, column_id, title, description, assignee_id, priority, due_date, position, created_at, updated_at
9. The columns table has a title field that can be used to identify "Done" and "Archived" columns by name
10. User avatars can be generated from user initials if no avatar image exists (using a frontend utility)
11. The project_members table correctly links users to projects with a role field
12. The activities table schema supports tracking project and task activities with type, subject_type, subject_id, and data fields
13. API responses return JSON format consistent with Pinia store expectations
14. The application runs in modern browsers with JavaScript enabled (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
15. Users have stable internet connections for API calls (3G minimum: 384kbps download, <500ms latency; or better)
16. The existing router setup supports route metadata for layout switching
17. Laravel API resources or similar patterns are used for consistent JSON responses
18. CORS is properly configured for frontend-backend communication
19. The application has permission to use localStorage for caching user preferences
20. Due dates in the tasks table are stored as DATE type and can be compared with today's date
21. Expected data volume is capped at 25 projects and 500 tasks maximum per user (small scale target for performance optimization)

## Dependencies *(mandatory)*

### External Dependencies

- Laravel backend framework (assumed already installed and configured)
- Sanctum package for API authentication (assumed already installed)
- Vue 3 and Vue Router for frontend routing (assumed already installed)
- Pinia for state management (assumed already installed)
- Axios or similar HTTP client for API calls (assumed already available)
- Google Fonts for Inter font family (already imported in Phase 1)

### Internal Dependencies

- Phase 1-6 of Modern UI implementation must be complete (design system, kanban board, auth pages, mobile responsive)
- Existing authentication system must be functional (login/register from Phase 3)
- Existing database schema must be in place with all required tables
- Existing kanban board components from Phase 5 must be working

### Team Dependencies

- Backend developer approval for API endpoint contracts (request/response formats)
- Product owner approval for dashboard metrics calculations (what counts as "active" task)
- QA testing for navigation flows and API integrations across all browsers
- Design approval for layout component structure (sidebar width, navbar height, etc.)

## Technical Constraints *(if applicable)*

1. Must maintain compatibility with existing Laravel + Vue 3 + Pinia + Sanctum stack
2. Cannot introduce breaking changes to existing API endpoints used by kanban board
3. Must work with existing database schema without requiring migrations
4. Must integrate seamlessly with Phase 1-6 modern UI components and design system
5. Performance budget: Dashboard page must load (layout + data) within 2 seconds on 3G connection
6. Bundle size: New components should not add more than 50KB to JavaScript bundle (gzipped)
7. Must support browsers with JavaScript enabled (no server-side-only rendering required)
8. Must respect existing authentication and authorization framework (Sanctum middleware)
9. Cannot use third-party UI component libraries that conflict with custom design system
10. Must support touch events for mobile sidebar toggle and navigation
11. Must gracefully handle API failures with proper error states (no white screens of death)
12. Must avoid localStorage quota issues (limit cached data to < 5MB)
13. Search API must handle queries efficiently without full-text search engine (use SQL LIKE queries unless Elasticsearch/Meilisearch already exists)
14. Must work within Laravel rate limiting constraints (default 60 requests per minute per user)

## Risks & Mitigations *(if applicable)*

### Risk 1: API Performance with Large Datasets
**Risk**: Dashboard statistics queries may become slow for users with hundreds of projects or thousands of tasks, causing poor perceived performance.

**Impact**: High - Slow dashboard loads create frustration and poor first impression after login.

**Mitigation**:
- Implement database indexing on foreign keys (user_id, project_id, column_id, due_date)
- Use efficient SQL queries with proper JOIN optimization
- Cache dashboard statistics for 5 minutes per user using Laravel cache
- Implement pagination for projects list (5 recent shown, but API supports more)
- Add loading skeletons to provide immediate visual feedback while data loads
- Profile API endpoints with Laravel Telescope or similar tool during development

### Risk 2: Navigation State Management Complexity
**Risk**: Managing active navigation state, sidebar toggle state, and layout persistence across route changes could introduce bugs or inconsistent behavior.

**Impact**: Medium - Users might see flickering sidebars, wrong active links, or layout issues during navigation.

**Mitigation**:
- Use Pinia store for centralized layout state management
- Store sidebar collapse state in localStorage to persist across sessions
- Use Vue Router route metadata to determine active navigation item
- Implement navigation guards to ensure layout state is set before rendering
- Test all navigation flows thoroughly (page to page, page refresh, back/forward buttons)
- Use CSS transitions for smooth visual feedback during state changes

### Risk 3: Search Performance and Relevance
**Risk**: Global search using SQL LIKE queries may be slow or produce irrelevant results, especially with many projects/tasks or complex search terms.

**Impact**: Medium - Users may find search feature unusable if results are slow or inaccurate.

**Mitigation**:
- Implement search on indexed columns (title, description)
- Limit search results to 20 items total to cap query complexity
- Use debouncing (300ms) to reduce API calls while typing
- Consider basic relevance scoring (exact match in title > partial match in title > match in description)
- Add search result highlighting to show why items matched
- Document that full-text search engine (Elasticsearch) is out of scope but could be added later
- Provide clear "No results" feedback to set user expectations

### Risk 4: Mobile Sidebar UX Issues
**Risk**: Collapsible sidebar on mobile may conflict with swipe gestures, cover important content, or have confusing open/close behavior.

**Impact**: Medium - Mobile users are important for modern web apps and poor mobile UX harms adoption.

**Mitigation**:
- Use standard hamburger menu icon universally understood by users
- Implement click-outside-to-close behavior for mobile sidebar
- Add semi-transparent overlay when sidebar is open to focus attention
- Disable body scroll when sidebar is open to prevent awkward double-scrolling
- Auto-close sidebar after navigation to avoid covering new page content
- Test on actual mobile devices (iOS Safari, Chrome Android) not just browser simulation
- Follow mobile-first responsive design patterns from Phase 6

### Risk 5: Scope Creep from Enhanced Features
**Risk**: Stakeholders may request additional dashboard features (charts, graphs, more statistics, customizable widgets) once they see the basic dashboard working.

**Impact**: High - Could significantly delay delivery and expand effort beyond initial estimate.

**Mitigation**:
- Establish clear MVP definition (P1 user stories only) before starting implementation
- Document out-of-scope items explicitly in this specification
- Use feature flags or phases to control rollout (P1 first, then P2, etc.)
- Set expectations that enhanced features (activity feed, notifications) are post-MVP
- Create backlog items for future enhancements but don't commit to timeline
- Focus on delivering working navigation and basic dashboard first, iterate later

### Risk 6: Integration Issues with Existing Kanban Board
**Risk**: Navigation from dashboard to kanban board may break existing kanban functionality or cause routing conflicts.

**Impact**: High - Would regress existing working features from Phase 5, blocking release.

**Mitigation**:
- Review existing kanban routes before implementing navigation
- Use layout wrapper that doesn't interfere with kanban component
- Test full flow: login → dashboard → click project → kanban board → back to dashboard
- Ensure kanban board stores and composables continue working with layout wrapper
- Run regression tests on all Phase 5 kanban features after integration
- Keep layout component simple and non-invasive (container only, no business logic)

## Open Questions *(if applicable)*

None - All requirements are sufficiently clear for implementation to proceed. Any clarifications needed during development can be addressed through normal development workflow.
