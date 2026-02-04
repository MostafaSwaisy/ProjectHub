# Implementation Tasks: Dashboard & Navigation System

**Feature**: 002-dashboard-navigation
**Branch**: `002-dashboard-navigation`
**Created**: 2026-02-03
**Related**: [spec.md](spec.md) | [plan.md](plan.md) | [data-model.md](data-model.md)

## Overview

This task breakdown implements the Dashboard & Navigation System in user story order, enabling independent development and testing of each feature increment. Each phase represents a complete, testable slice of functionality.

**Total Tasks**: 111 (109 core + 2 optional: T051a, T109a)
**MVP Tasks** (US1 + US2): 46 tasks (Phases 1-4 + critical polish T107-T109)
**Enhancement Tasks** (US3-US5): 48 tasks (Phases 5-7)
**Polish Tasks**: 15 tasks (Phase 8: T095-T109)

## Task Format

```
- [ ] T### [P] [Story] Description with file path
```

- **T###**: Sequential task ID
- **[P]**: Parallelizable (can be done simultaneously with other [P] tasks)
- **[Story]**: User story label (e.g., [US1], [US2])
- **Description**: Clear action with exact file path

## Implementation Strategy

**MVP-First Approach**: Implement US1 (Layout) + US2 (Dashboard Stats) first for immediate value. This fixes the "black screen" issue and provides core functionality. US3-US5 are progressive enhancements that can be added iteratively.

**Parallel Execution**: Tasks marked [P] can be done simultaneously by different team members or in any order without conflicts.

---

## Phase 1: Setup & Prerequisites

**Goal**: Verify environment and dependencies are ready for implementation.

- [ ] T001 Verify Laravel 12 and PHP 8.2+ are installed and working via `php artisan --version`
- [ ] T002 Verify Node.js 18+ and npm are installed via `node --version` and `npm --version`
- [ ] T003 Verify Vue 3.4.0, Pinia 2.2.0, Vue Router 4.3.0 are installed via `npm list vue pinia vue-router`
- [ ] T004 Verify Laravel Sanctum 4.2 authentication is working by testing login flow
- [ ] T005 Verify existing database has required tables (projects, tasks, boards, columns, users, activities) via `php artisan tinker` and query verification
- [ ] T006 Verify existing design system CSS files exist in resources/css/ (design-system.css, animations.css)
- [ ] T007 Verify Phase 1-6 kanban board is functional by navigating to /projects/1/kanban

---

## Phase 2: Foundational Infrastructure

**Goal**: Build shared components and utilities needed by multiple user stories.

### API Resources (Backend)

- [ ] T008 [P] Create ProjectResource in app/Http/Resources/ProjectResource.php for consistent project JSON formatting with instructor, members, and task completion
- [ ] T009 [P] Create UserResource in app/Http/Resources/UserResource.php for user summary JSON formatting (id, name, email, avatar_url)

### Frontend Utilities

- [ ] T010 [P] Create useAvatar composable in resources/js/composables/useAvatar.js with getInitials() and getBgColor() functions per data-model.md

### Route Setup

- [ ] T011 Update resources/js/router/index.js to add meta.requiresAuth to dashboard route and prepare for layout wrapper integration

---

## Phase 3: User Story 1 - Application Layout & Navigation (P1 MVP) 🎯

**Goal**: Fix "black screen" issue by implementing consistent navigation layout with sidebar and top navbar.

**Independent Test Criteria**: Log in and verify navigation layout appears with functional sidebar links. Click sidebar items to navigate between sections. Test mobile sidebar toggle.

### Backend: Placeholder Routes

- [ ] T012 [P] [US1] Add placeholder GET routes for /projects, /tasks, /team, /settings in routes/web.php returning simple Inertia pages

### Frontend: Layout Store

- [ ] T013 [US1] Create layout Pinia store in resources/js/stores/layout.js with state (sidebarCollapsed, currentRoute, isMobile) per data-model.md:533-592
- [ ] T014 [US1] Implement layout store actions: toggleSidebar(), initializeLayout(), persistSidebarState() with localStorage integration
- [ ] T015 [US1] Implement layout store getter: activeNavItem computed from currentRoute

### Frontend: Layout Components

- [ ] T016 [US1] Create AppLayout component in resources/js/layouts/AppLayout.vue as main authenticated layout wrapper with slot for page content
- [ ] T017 [US1] Create Sidebar component in resources/js/components/layout/Sidebar.vue with nav links (Dashboard, Projects, My Tasks, Team, Settings) and active highlighting
- [ ] T018 [US1] Implement Sidebar mobile collapse functionality (<768px) with hamburger menu toggle, slide-in animation (300ms), and click-outside-to-close behavior
- [ ] T019 [P] [US1] Create TopNavbar component in resources/js/components/layout/TopNavbar.vue with ProjectHub logo, search box placeholder, notifications icon placeholder, and user avatar
- [ ] T020 [US1] Create UserMenu component in resources/js/components/layout/UserMenu.vue as dropdown menu with Profile, Settings, and Logout options
- [ ] T021 [US1] Implement logout functionality in UserMenu calling auth store logout() and redirecting to /auth/login

### Frontend: Router Integration

- [ ] T022 [US1] Update Dashboard.vue in resources/js/pages/Dashboard.vue to use AppLayout wrapper component
- [ ] T023 [US1] Update router/index.js to wrap all authenticated routes with AppLayout component
- [ ] T024 [US1] Add router navigation guard to sync currentRoute with layout store on route change

### Frontend: Styling

- [ ] T025 [US1] Style AppLayout, Sidebar, TopNavbar, UserMenu components using existing design-system.css glassmorphic classes and orange/blue color scheme

### Testing & Validation

- [ ] T026 [US1] Test US1 Acceptance Scenario 1-4: Verify layout appears after login, sidebar shows links, active page highlighted, navigation works
- [ ] T027 [US1] Test US1 Acceptance Scenario 5-6: Verify mobile sidebar collapses, hamburger menu toggles, slide-in animation works
- [ ] T028 [US1] Test US1 Acceptance Scenario 7-8: Verify user avatar dropdown shows, logout clears session and redirects to login

---

## Phase 4: User Story 2 - Real-Time Dashboard Statistics (P1 MVP) 🎯

**Goal**: Display real-time dashboard statistics (projects, tasks, team members, overdue) to provide immediate work visibility.

**Independent Test Criteria**: Log in with user who has projects/tasks. Verify all four statistic cards show accurate counts matching database records.

### Backend: Dashboard Stats API

- [ ] T029 [US2] Create DashboardController in app/Http/Controllers/DashboardController.php
- [ ] T030 [US2] Implement DashboardController@stats method calculating 4 statistics per contracts/dashboard-stats.md with 5-minute cache
- [ ] T031 [US2] Add GET /api/dashboard/stats route in routes/api.php with auth:sanctum middleware
- [ ] T032 [P] [US2] Create DashboardControllerTest in tests/Feature/DashboardControllerTest.php with tests for accurate counts, caching, and authorization

### Frontend: Dashboard Store

- [ ] T033 [US2] Create dashboard Pinia store in resources/js/stores/dashboard.js with state (stats, loading, error) per data-model.md:595-632
- [ ] T034 [US2] Implement dashboard store fetchStats() action calling GET /api/dashboard/stats with error handling and retry()

### Frontend: Dashboard Components

- [ ] T035 [US2] Create StatCard component in resources/js/components/dashboard/StatCard.vue displaying label, value, optional icon, and conditional red highlight for overdue > 0
- [ ] T036 [US2] Create DashboardStats component in resources/js/components/dashboard/DashboardStats.vue rendering 4 StatCard components with skeleton loaders during loading
- [ ] T037 [US2] Update Dashboard.vue to include DashboardStats component and call dashboardStore.fetchStats() on mount
- [ ] T038 [US2] Implement skeleton loader animation in DashboardStats showing during API fetch (<100ms initial render)
- [ ] T039 [US2] Implement error state in DashboardStats with user-friendly message and "Retry" button
- [ ] T040 [US2] Implement empty state in DashboardStats when user has no projects showing "0" values with CTA to create first project

### Testing & Validation

- [ ] T041 [US2] Test US2 Acceptance Scenario 1-4: Verify statistics cards show correct counts (projects, active tasks, team members, overdue with red highlight)
- [ ] T042 [US2] Test US2 Acceptance Scenario 5: Verify new user with no projects sees "0" values and empty state message
- [ ] T043 [US2] Test US2 Acceptance Scenario 6-8: Verify skeleton loaders during fetch, error state with retry button, statistics update on refresh

---

## Phase 5: User Story 3 - Projects List & Management (P2)

**Goal**: Display recent projects on dashboard and enable project creation to bridge gap between overview and detailed work.

**Independent Test Criteria**: View dashboard projects section. Create new project via modal. Verify project appears in list and database.

### Backend: Projects API

- [ ] T044 [US3] Create ProjectController in app/Http/Controllers/ProjectController.php with resource methods (index, show, store, update, destroy)
- [ ] T045 [US3] Implement ProjectController@index returning user's projects with instructor/members, task completion, ordered by updated_at DESC per contracts/projects.md
- [ ] T046 [US3] Implement ProjectController@show with authorization check (instructor or member only) per contracts/projects.md
- [ ] T047 [US3] Implement ProjectController@store creating project with user as instructor, validation (title required, max 100 chars) per contracts/projects.md
- [ ] T048 [US3] Implement ProjectController@update with authorization check (instructor or member can edit) per contracts/projects.md
- [ ] T049 [US3] Implement ProjectController@destroy with authorization check (instructor only can delete) per contracts/projects.md
- [ ] T050 [US3] Add Projects API routes in routes/api.php: GET /api/projects, GET /api/projects/{id}, POST /api/projects, PUT /api/projects/{id}, DELETE /api/projects/{id} with auth:sanctum
- [ ] T051 [P] [US3] Create ProjectControllerTest in tests/Feature/ProjectControllerTest.php testing CRUD operations, authorization (member cannot delete), and validation
- [ ] T051a [P] [US3] **[OPTIONAL]** Create UserSearchController in app/Http/Controllers/UserSearchController.php and implement GET /api/users/search?q={query} endpoint per FR-059 for team member selection (required if ProjectModal adds member assignment feature)

### Frontend: Projects Store

- [ ] T052 [US3] Create projects Pinia store in resources/js/stores/projects.js with state (projects, loading, error) per data-model.md:637-692
- [ ] T053 [US3] Implement projects store actions: fetchProjects(), createProject(data), deleteProject(id) calling respective API endpoints
- [ ] T054 [US3] Implement projects store getter: recentProjects returning first 5 projects

### Frontend: Projects Components

- [ ] T055 [US3] Create ProjectCard component in resources/js/components/dashboard/ProjectCard.vue displaying title, description (truncated 100 chars), status badges, member avatars (max 5), task completion percentage
- [ ] T056 [US3] Implement ProjectCard click handler navigating to /projects/{id}/kanban on card click
- [ ] T057 [US3] Create RecentProjects component in resources/js/components/dashboard/RecentProjects.vue rendering 5 most recent ProjectCard components
- [ ] T058 [US3] Add "+ New Project" button in RecentProjects with orange gradient styling opening ProjectModal
- [ ] T059 [US3] Create ProjectModal component in resources/js/components/dashboard/ProjectModal.vue with form fields (title required, description optional, timeline/budget dropdowns)
- [ ] T060 [US3] Implement ProjectModal form validation (title 1-100 chars, description max 500 chars) with inline error messages
- [ ] T061 [US3] Implement ProjectModal submit handler calling projectsStore.createProject(), closing modal on success, showing success notification
- [ ] T062 [US3] Implement RecentProjects empty state with illustration, "No projects yet" text, and "+ Create Your First Project" button
- [ ] T063 [US3] Update Dashboard.vue to include RecentProjects component below DashboardStats and call projectsStore.fetchProjects() on mount

### Testing & Validation

- [ ] T064 [US3] Test US3 Acceptance Scenario 1-3: Verify 5 recent projects displayed as cards with correct data, status badges colored correctly (green/orange/red)
- [ ] T065 [US3] Test US3 Acceptance Scenario 4-7: Verify "+ New Project" button styled correctly, modal opens with form fields, project created with defaults, modal closes and project appears
- [ ] T066 [US3] Test US3 Acceptance Scenario 8-10: Verify clicking project card navigates to kanban, empty state shows for no projects, member avatars display with "+N more"

---

## Phase 6: User Story 4 - Global Search (P3)

**Goal**: Enable searching for projects and tasks from navigation bar for quick navigation without browsing lists.

**Independent Test Criteria**: Type search queries into navigation bar. Verify relevant projects/tasks appear in dropdown results. Test keyboard shortcut (Cmd/Ctrl+K).

### Backend: Search API

- [ ] T067 [US4] Create SearchController in app/Http/Controllers/SearchController.php
- [ ] T068 [US4] Implement SearchController@search method with SQL LIKE queries on projects/tasks titles and descriptions, limiting to 10 per type per contracts/search.md
- [ ] T069 [US4] Add GET /api/search?q={query} route in routes/api.php with auth:sanctum middleware and validation (min 2 chars)
- [ ] T070 [P] [US4] Create SearchControllerTest in tests/Feature/SearchControllerTest.php testing search results, authorization (only accessible projects/tasks), query validation

### Frontend: Search Composable

- [ ] T071 [US4] Create useSearch composable in resources/js/composables/useSearch.js with debounced search (300ms), loading, error, results state

### Frontend: Search Component

- [ ] T072 [US4] Create GlobalSearch component in resources/js/components/layout/GlobalSearch.vue with search input, magnifying glass icon, placeholder "Search projects and tasks..."
- [ ] T073 [US4] Implement GlobalSearch debounced search calling useSearch composable on input change (300ms delay)
- [ ] T074 [US4] Implement GlobalSearch results dropdown appearing below input when typing, grouped by Projects/Tasks with folder/checkbox icons
- [ ] T075 [US4] Implement GlobalSearch keyboard shortcut: Cmd+K (Mac) / Ctrl+K (Windows) focusing search input
- [ ] T076 [US4] Implement GlobalSearch result click handlers: project → /projects/{id}/kanban, task → /projects/{project_id}/kanban?highlight={task_id}
- [ ] T077 [US4] Implement GlobalSearch "No results found" message when search returns empty with suggestion to try different keywords
- [ ] T078 [US4] Implement GlobalSearch result limit indicator showing "+N more" when has_more_projects or has_more_tasks is true
- [ ] T079 [US4] Update TopNavbar to include GlobalSearch component between logo and user avatar

### Testing & Validation

- [ ] T080 [US4] Test US4 Acceptance Scenario 1-4: Verify search input visible, dropdown appears when typing, results grouped by type with icons, limit to 10 per type
- [ ] T081 [US4] Test US4 Acceptance Scenario 5-6: Verify clicking project result navigates to kanban, clicking task result navigates to task's kanban
- [ ] T082 [US4] Test US4 Acceptance Scenario 7-9: Verify debouncing (300ms delay), "No results" message, Cmd/Ctrl+K keyboard shortcut focuses input

---

## Phase 7: User Story 5 - Activity Feed (P4 Optional) [OPTIONAL]

**Goal**: Display recent project activities on dashboard for team awareness without checking each project individually.

**Independent Test Criteria**: Perform actions (create/complete tasks). Verify activities appear in dashboard feed with correct formatting.

**Note**: This entire phase is optional and can be skipped for MVP delivery.

### Backend: Activity API

- [ ] T083 [P] [US5] Create ActivityResource in app/Http/Resources/ActivityResource.php formatting activity JSON with user, action, subject, relative time
- [ ] T084 [US5] Create ActivityController in app/Http/Controllers/ActivityController.php
- [ ] T085 [US5] Implement ActivityController@index returning last N activities for user's projects with relative timestamps per contracts/activities.md
- [ ] T086 [US5] Add GET /api/activities?limit={n} route in routes/api.php with auth:sanctum middleware and validation (limit 1-50)

### Frontend: Activity Component

- [ ] T087 [US5] Create ActivityFeed component in resources/js/components/dashboard/ActivityFeed.vue displaying last 10 activities from user's projects
- [ ] T088 [US5] Implement ActivityFeed fetching activities from GET /api/activities?limit=10 on mount
- [ ] T089 [US5] Implement ActivityFeed rendering each activity with user avatar, name, action (created/updated/completed), subject title, relative timestamp ("2 hours ago")
- [ ] T090 [US5] Implement ActivityFeed click handler navigating to related project/task when clicking activity item
- [ ] T091 [US5] Implement ActivityFeed optional auto-refresh every 30 seconds to show new activities
- [ ] T092 [US5] Update Dashboard.vue to conditionally include ActivityFeed component below RecentProjects (only if P4 implemented)

### Testing & Validation

- [ ] T093 [US5] Test US5 Acceptance Scenario 1-4: Verify "Recent Activity" section shows 10 activities, displays user info + action + subject + timestamp, clicking navigates to task/project
- [ ] T094 [US5] Test US5 Acceptance Scenario 5: Verify optional auto-refresh updates feed with new activities after 30 seconds

---

## Phase 8: Polish & Cross-Cutting Concerns

**Goal**: Finalize accessibility, performance, and integration with existing features.

### Accessibility (WCAG 2.1 Level A)

- [ ] T095 Implement keyboard navigation for all interactive elements (Tab, Enter, Escape, Arrow keys) in Sidebar, TopNavbar, UserMenu, GlobalSearch, ProjectModal
- [ ] T096 Add ARIA labels and roles to Sidebar nav links, GlobalSearch input, UserMenu dropdown, ProjectModal form fields
- [ ] T097 Add semantic HTML structure to all components (nav, main, aside, article, section)
- [ ] T098 Add alt text to all images (user avatars, empty state illustrations)

### Mobile Responsive

- [ ] T099 Test mobile sidebar collapse/expand on screen width < 768px across iOS Safari and Chrome Android
- [ ] T100 Test mobile overlay semi-transparent background when sidebar open preventing interaction with content
- [ ] T101 Test mobile sidebar auto-close after navigation to avoid covering new page content
- [ ] T102 Test mobile orientation change auto-closing sidebar on landscape to maximize content area

### Performance Optimization

- [ ] T103 Verify dashboard loads in < 2 seconds on 3G connection (test with Chrome DevTools network throttling)
- [ ] T104 Verify API responses < 500ms for /api/dashboard/stats, /api/projects, /api/search (test with Laravel Telescope)
- [ ] T105 Verify search returns results within 500ms of stopping typing (test debouncing with console.time)
- [ ] T106 Verify animations run at 60fps (sidebar slide, skeleton loaders) - test with Chrome DevTools Performance tab

### Integration & Regression

- [ ] T107 Test full flow: Login → Dashboard → Click project → Kanban board → Back to dashboard verifying no broken links or layout issues
- [ ] T108 Verify existing kanban board from Phase 5 continues working after adding AppLayout wrapper (drag tasks, edit, delete)
- [ ] T109 Verify existing authentication from Phase 3 continues working (login, register, logout, session timeout after 30 minutes)
- [ ] T109a **[OPTIONAL]** Create comprehensive automated E2E regression test suite in tests/e2e/ covering all Phase 1-6 features (auth, kanban, design system) to ensure 100% regression coverage per SC-006

---

## Dependencies & Parallel Execution

### User Story Dependencies (Sequential)

```
Phase 1 (Setup) → Phase 2 (Foundational) → Phase 3 (US1) → Phase 4 (US2)
                                              ↓
                                           Phase 5 (US3)
                                              ↓
                                           Phase 6 (US4)
                                              ↓
                                           Phase 7 (US5) [Optional]
                                              ↓
                                           Phase 8 (Polish)
```

**Critical Path for MVP**:
Phase 1 → Phase 2 → Phase 3 (US1) → Phase 4 (US2) → Phase 8 (Subset: T107-T109)

**US1 blocks**: US2, US3, US4, US5 (all need layout to function, but US2-US5 are independent of each other after US1 complete)
**US2 independent of**: US3, US4, US5 (can be done in parallel after US1)
**US3 independent of**: US4, US5 (can be done in parallel after US1+US2)
**US4 independent of**: US5 (can be done in parallel after US1)
**US5 independent of**: All others (optional enhancement)

### Parallel Execution Examples

**Within Phase 2 (Foundational)**:
- T008 (ProjectResource) + T009 (UserResource) + T010 (useAvatar composable) can be done simultaneously

**Within Phase 3 (US1)**:
- T012 (placeholder routes) + T019 (TopNavbar) can start before T016 (AppLayout) is complete

**Within Phase 4 (US2)**:
- T032 (DashboardControllerTest) can be done in parallel with T033-T034 (dashboard store)

**Within Phase 5 (US3)**:
- T051 (ProjectControllerTest) can be done in parallel with T052-T054 (projects store)

**Across User Stories (After US1 Complete)**:
- US2 (T029-T043) + US3 (T044-T066) can be developed in parallel by different team members
- US4 (T067-T082) can be developed in parallel with US2+US3

---

## Task Summary by Phase

| Phase | User Story | Tasks | MVP? | Parallelizable |
|-------|------------|-------|------|----------------|
| Phase 1 | Setup | T001-T007 (7) | Yes | 0 |
| Phase 2 | Foundational | T008-T011 (4) | Yes | 3 |
| Phase 3 | US1 (P1) | T012-T028 (17) | Yes | 2 |
| Phase 4 | US2 (P1) | T029-T043 (15) | Yes | 2 |
| Phase 5 | US3 (P2) | T044-T066 (23) | No | 2 |
| Phase 6 | US4 (P3) | T067-T082 (16) | No | 2 |
| Phase 7 | US5 (P4) | T083-T094 (12) | No | 2 |
| Phase 8 | Polish | T095-T109 (15) | Partial | 0 |
| **Total** | **All** | **T001-T109 (109)** | **43 MVP** | **13 total** |

---

## MVP Scope Recommendation

**Minimum Viable Product** = Phase 1 + Phase 2 + Phase 3 (US1) + Phase 4 (US2) + Phase 8 (T107-T109)

**Total MVP Tasks**: 7 + 4 + 17 + 15 + 3 = **46 tasks**

**Rationale**:
- US1 fixes the "black screen" issue and enables navigation
- US2 provides immediate value with dashboard statistics
- Together they deliver a functional, usable application post-login
- US3-US5 are progressive enhancements that can be added iteratively based on user feedback

**Estimated MVP Effort**: 20-30 hours (2-4 days full-time)

---

## Validation Checklist

After completing all tasks, validate against success criteria from spec.md:

- [ ] SC-001: Dashboard loads with layout within 2 seconds
- [ ] SC-002: Statistics accurately reflect database state with zero errors
- [ ] SC-003: Navigation between pages < 1 second
- [ ] SC-004: Create project flow < 30 seconds
- [ ] SC-005: Search returns results within 500ms
- [ ] SC-006: No regression on existing features (auth, kanban)
- [ ] SC-007: Login → dashboard → kanban flow < 10 seconds
- [ ] SC-008: Mobile sidebar works smoothly
- [ ] SC-009: Skeleton loaders < 100ms, data < 2 seconds
- [ ] SC-010: Search finds all relevant items at scale (25 projects, 500 tasks)
- [ ] SC-011: Logout clears session in < 1 second
- [ ] SC-012: Current page identifiable within 2 seconds (sidebar highlighting)

---

## Next Steps

1. **Review this task breakdown** - Confirm all user stories are covered and task order makes sense
2. **Assign tasks to team members** - Use [P] markers to identify parallel work opportunities
3. **Run `/speckit.implement`** - Execute implementation based on this task list
4. **Track progress** - Mark tasks complete as they're finished
5. **Test incrementally** - Validate each user story independently before moving to next

**Ready to implement!** 🚀
