# Implementation Plan: Dashboard & Navigation System

**Branch**: `002-dashboard-navigation` | **Date**: 2026-02-03 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/002-dashboard-navigation/spec.md`

## Summary

This feature implements a complete Dashboard & Navigation System that fixes the "black screen after login" issue by adding:
- **Application Layout** with top navigation bar and collapsible sidebar for consistent navigation across authenticated pages
- **Real-time Dashboard** showing 4 statistics (projects, tasks, team members, overdue tasks), recent projects list, and optional activity feed
- **Projects Management** with create/view/delete capabilities and project cards showing completion metrics
- **Global Search** for finding projects and tasks with keyboard shortcuts (Cmd/Ctrl+K)
- **API Integration** connecting Vue frontend to Laravel backend with proper authentication and authorization

**Technical Approach**: Leverage existing Laravel 12 + Vue 3 + Pinia + Sanctum stack from Phase 1-6 (001-modern-ui). No database migrations required - all existing tables support this feature. Implement RESTful API endpoints with role-based authorization (instructor vs member), Pinia stores for state management, and reusable Vue components following the established glassmorphic dark theme design system.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0)
**Primary Dependencies**: Laravel Framework 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0
**Storage**: SQLite (existing schema, NO migrations needed)
**Testing**: PHPUnit 11.x (Laravel backend), Vitest 2.x (Vue frontend)
**Target Platform**: Modern web browsers (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
**Project Type**: Web application (Laravel backend + Vue SPA frontend)
**Performance Goals**:
- Dashboard load < 2 seconds on 3G
- API responses < 500ms
- Search results < 500ms
- Animations at 60fps
**Constraints**:
- No breaking changes to existing Phase 1-6 features
- Must work with existing database schema (no migrations)
- Bundle size: New components < 50KB gzipped
- 30-minute session timeout
- WCAG 2.1 Level A accessibility
**Scale/Scope**:
- Target: 25 projects max, 500 tasks max per user (small team scale)
- Expected concurrent users: Average 20-50, peak <100 (maintaining <500ms p95 response time under peak load)
- Database: SQLite suitable for this scale

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Status**: ✅ PASSED

No constitution file exists in the project (checked `.specify/constitution.md`), therefore no project-specific constraints to validate against. Default SpecKit best practices applied:
- Avoid over-engineering: Using existing stack, no new frameworks introduced
- Simple architecture: RESTful API with standard CRUD patterns, no microservices
- Minimal abstractions: Direct Eloquent models, no Repository pattern
- Clear boundaries: Feature self-contained in 002-dashboard-navigation branch

## Project Structure

### Documentation (this feature)

```text
specs/002-dashboard-navigation/
├── spec.md               # Feature specification with 5 user stories, 62 FRs
├── plan.md               # This file - implementation plan
├── research.md           # Phase 0: Technology decisions, patterns, architecture
├── data-model.md         # Phase 1: Entity definitions, Pinia stores, validation rules
├── quickstart.md         # Phase 1: Developer setup and workflow guide
├── contracts/            # Phase 1: API endpoint specifications
│   ├── dashboard-stats.md    # GET /api/dashboard/stats
│   ├── projects.md           # Projects CRUD endpoints
│   ├── search.md             # GET /api/search
│   └── activities.md         # GET /api/activities (optional)
├── checklists/
│   └── requirements.md   # Specification quality checklist (PASSED)
└── tasks.md              # Phase 2: NOT YET CREATED - run /speckit.tasks
```

### Source Code (repository root)

```text
# Web application structure (Laravel backend + Vue frontend)

# Backend (Laravel)
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php    # NEW: Dashboard stats endpoint
│   │   ├── ProjectController.php      # NEW: Projects CRUD with authorization
│   │   ├── SearchController.php       # NEW: Global search
│   │   └── ActivityController.php     # NEW (optional): Activity feed
│   └── Resources/
│       ├── ProjectResource.php        # NEW: Project JSON response formatting
│       ├── UserResource.php           # NEW: User summary formatting
│       └── ActivityResource.php       # NEW (optional): Activity formatting
├── Models/
│   ├── Project.php                    # EXISTING: No changes needed
│   ├── Task.php                       # EXISTING: No changes needed
│   ├── User.php                       # EXISTING: No changes needed
│   └── Activity.php                   # EXISTING: No changes needed
└── ...

routes/
└── api.php                            # MODIFIED: Add new dashboard, projects, search routes

tests/Feature/
├── DashboardControllerTest.php        # NEW: Test dashboard stats calculations
├── ProjectControllerTest.php          # NEW: Test CRUD + authorization
└── SearchControllerTest.php           # NEW: Test search functionality

# Frontend (Vue 3)
resources/js/
├── layouts/
│   └── AppLayout.vue                  # NEW: Main authenticated layout wrapper
├── pages/
│   ├── Dashboard.vue                  # MODIFIED: Add real data (currently empty placeholder)
│   ├── ProjectsList.vue               # NEW (placeholder): Future dedicated projects page
│   ├── MyTasks.vue                    # NEW (placeholder): Future my tasks page
│   ├── Team.vue                       # NEW (placeholder): Future team page
│   └── Settings.vue                   # NEW (placeholder): Future settings page
├── components/
│   ├── layout/
│   │   ├── TopNavbar.vue              # NEW: Top navigation bar with logo, search, user menu
│   │   ├── Sidebar.vue                # NEW: Collapsible sidebar with nav links
│   │   ├── UserMenu.vue               # NEW: User dropdown menu with logout
│   │   └── GlobalSearch.vue           # NEW: Search input with results dropdown
│   ├── dashboard/
│   │   ├── DashboardStats.vue         # NEW: Statistics cards section
│   │   ├── StatCard.vue               # NEW: Individual statistic card
│   │   ├── RecentProjects.vue         # NEW: Projects list section
│   │   ├── ProjectCard.vue            # NEW: Individual project card
│   │   ├── ProjectModal.vue           # NEW: Create/edit project modal
│   │   └── ActivityFeed.vue           # NEW (optional): Activity feed
│   └── shared/
│       ├── Avatar.vue                 # EXISTING: Reuse from Phase 1-6
│       ├── Button.vue                 # EXISTING: Reuse from Phase 1-6
│       ├── Modal.vue                  # EXISTING: Reuse from Phase 1-6
│       └── Skeleton.vue               # EXISTING: Reuse from Phase 1-6
├── stores/
│   ├── layout.js                      # NEW: Sidebar collapse, active nav state
│   ├── dashboard.js                   # NEW: Dashboard statistics state
│   ├── projects.js                    # NEW: Projects list and CRUD actions
│   └── auth.js                        # EXISTING: No changes needed
├── router/
│   └── index.js                       # MODIFIED: Add dashboard, placeholder routes
└── composables/
    ├── useSearch.js                   # NEW: Search with debouncing
    └── useAvatar.js                   # NEW: Generate initials and colors

resources/css/
├── design-system.css                  # EXISTING: Reuse glassmorphic styles
└── animations.css                     # EXISTING: Reuse transitions

tests/
└── vitest/
    ├── stores/
    │   ├── layout.test.js             # NEW: Test layout store
    │   └── dashboard.test.js          # NEW: Test dashboard store
    └── components/
        ├── Sidebar.test.js            # NEW: Test sidebar toggle
        └── GlobalSearch.test.js       # NEW: Test search debouncing
```

**Structure Decision**: Selected **Option 2: Web application** structure because this is a Laravel backend with Vue SPA frontend. The project follows standard Laravel + Vue separation with backend in `app/` and frontend in `resources/js/`. This matches the existing structure from Phase 1-6 (001-modern-ui) and requires no restructuring.

**Key Directories**:
- `app/Http/Controllers/` - New API controllers for dashboard, projects, search
- `resources/js/layouts/` - New AppLayout component for authenticated pages
- `resources/js/components/layout/` - New navigation components (navbar, sidebar)
- `resources/js/components/dashboard/` - New dashboard-specific components
- `resources/js/stores/` - New Pinia stores for layout, dashboard, projects state
- `specs/002-dashboard-navigation/` - All planning artifacts for this feature

## Implementation Phases

### Phase 0: Research & Outline ✅ COMPLETE

**Artifacts Created**:
- [research.md](research.md) - Technology stack decisions, component architecture, integration patterns, security considerations, testing strategy

**Key Decisions Documented**:
- Continue with Laravel 12 + Vue 3 + Pinia stack (no new tech)
- Use Pinia stores for layout/dashboard/projects state management
- Single AppLayout component wrapping all authenticated routes
- RESTful API with JSON responses (no GraphQL)
- SQL LIKE queries for search (no full-text engine needed at this scale)
- Client-side caching with stale-while-revalidate + 5-min server cache
- 30-minute session timeout via Laravel config
- Role-based authorization: instructor (full control) vs member (view/edit only)

### Phase 1: Design & Contracts ✅ COMPLETE

**Artifacts Created**:
- [data-model.md](data-model.md) - 7 entity definitions with TypeScript interfaces, Pinia store structures, validation rules
- [contracts/dashboard-stats.md](contracts/dashboard-stats.md) - Dashboard statistics endpoint contract
- [contracts/projects.md](contracts/projects.md) - Projects CRUD endpoints with authorization
- [contracts/search.md](contracts/search.md) - Global search endpoint with debouncing
- [contracts/activities.md](contracts/activities.md) - Activity feed endpoint (optional P4)
- [quickstart.md](quickstart.md) - Developer setup guide with step-by-step implementation workflow

**Entities Defined**:
1. Dashboard Statistics - Aggregate metrics (projects, tasks, team members, overdue)
2. Project Card - Summary with completion percentage, members, status badges
3. Project Create/Update Request - Validation rules and constraints
4. Search Result - Projects and tasks with match highlighting
5. Navigation Layout State - Sidebar collapse, active route tracking
6. Activity Item - User actions with relative timestamps (optional)
7. User Avatar - Image URL or generated initials with colors

**API Contracts**:
- `GET /api/dashboard/stats` - Returns 4 statistics, 5-min cache, <100ms target
- `GET /api/projects` - List user's projects with task completion
- `GET /api/projects/{id}` - Single project details with authorization check
- `POST /api/projects` - Create project (user becomes instructor)
- `PUT /api/projects/{id}` - Update project (instructor or member)
- `DELETE /api/projects/{id}` - Delete project (instructor only)
- `GET /api/search?q={query}` - Search projects/tasks, max 20 results, 300ms debounce
- `GET /api/activities?limit={n}` - Recent activities (optional, max 50)

### Phase 2: Task Breakdown ⏳ PENDING

**Status**: NOT YET STARTED - Requires `/speckit.tasks` command

**Expected Output**: `tasks.md` with dependency-ordered implementation tasks organized by:
1. Backend API Implementation (controllers, resources, routes, tests)
2. Pinia Store Implementation (layout, dashboard, projects)
3. Layout Components (AppLayout, TopNavbar, Sidebar, UserMenu, GlobalSearch)
4. Dashboard Components (DashboardStats, StatCard, RecentProjects, ProjectCard, ProjectModal)
5. Router Configuration (add new routes, update meta)
6. Integration Testing (full flow, regression tests on Phase 1-6)
7. Mobile Responsive Adjustments (sidebar collapse, touch events)
8. Accessibility (keyboard navigation, ARIA labels, screen reader testing)

**Estimated Task Count**: 40-60 tasks based on component count and API endpoints

### Phase 3: Implementation ⏳ PENDING

**Status**: NOT YET STARTED - Requires `/speckit.implement` command after tasks.md exists

**Execution Flow**:
1. Backend first: Controllers → Resources → Routes → Tests
2. Frontend stores: Layout → Dashboard → Projects
3. Layout components: AppLayout → TopNavbar → Sidebar → UserMenu → GlobalSearch
4. Dashboard page: Modify existing Dashboard.vue → Add DashboardStats → RecentProjects
5. Project components: ProjectCard → ProjectModal
6. Optional: ActivityFeed (P4 priority)
7. Integration testing: Full flow, regression, cross-browser
8. Performance validation: Load times, API response times

### Phase 4: Validation ⏳ PENDING

**Testing Requirements**:
- Backend: 15+ PHPUnit feature tests (dashboard stats accuracy, authorization, search)
- Frontend: 10+ Vitest unit tests (stores, components, debouncing)
- Manual QA: All 5 user stories with acceptance scenarios
- Regression: Verify Phase 1-6 features still work (auth, kanban)
- Performance: Dashboard < 2s, API < 500ms, search < 500ms
- Accessibility: WCAG 2.1 Level A compliance (keyboard nav, ARIA)
- Cross-browser: Chrome, Firefox, Safari, Edge on desktop + mobile

**Success Criteria Validation** (from spec.md):
- SC-001: Dashboard loads in < 2 seconds ✓
- SC-002: Statistics are accurate with zero counting errors ✓
- SC-003: Navigation between pages in < 1 second ✓
- SC-004: Create project and see it in list within 30 seconds ✓
- SC-005: Search returns results within 500ms ✓
- SC-006: No regression on existing features ✓
- SC-007: Login → dashboard → kanban flow < 10 seconds ✓
- SC-008: Mobile sidebar works smoothly ✓
- SC-009: Skeleton loaders < 100ms, data < 2 seconds ✓
- SC-010: Search finds all relevant items at scale (25 projects, 500 tasks) ✓
- SC-011: Logout clears session in < 1 second ✓
- SC-012: Current page identifiable within 2 seconds ✓

## Critical Dependencies

### External Dependencies (Already Installed)
- Laravel Framework 12.x - Backend API framework
- Laravel Sanctum 4.2 - API authentication
- Vue 3.4.0 - Frontend framework
- Vue Router 4.3.0 - Client-side routing
- Pinia 2.2.0 - State management
- Axios 1.11.0 - HTTP client
- Tailwind CSS 4.0.0 - Styling
- Vite 7.0.7 - Build tool
- PHPUnit 11.x - Backend testing
- Vitest 2.x - Frontend testing

### Internal Dependencies (Must Be Complete)
- **Phase 1**: Design System (glassmorphic effects, CSS variables, colors) ✅
- **Phase 2**: Component Library (Button, Modal, Avatar, Skeleton) ✅
- **Phase 3**: Authentication System (login, register, token management) ✅
- **Phase 4**: Database Schema (projects, tasks, boards, columns, users, activities) ✅
- **Phase 5**: Kanban Board (must continue working after adding layout) ✅
- **Phase 6**: Mobile Responsive Patterns (breakpoints, touch events) ✅

**Blocker Check**: All Phase 1-6 dependencies are complete (001-modern-ui feature). No blockers to proceed.

## Risk Mitigation

| Risk | Impact | Mitigation Strategy | Status |
|------|--------|---------------------|--------|
| API performance with large datasets | High | Database indexes + 5-min cache + pagination | Planned |
| Navigation state management bugs | Medium | Pinia store + localStorage + route metadata | Planned |
| Search slow/irrelevant results | Medium | Indexed columns + 20 result limit + debouncing | Planned |
| Mobile sidebar UX issues | Medium | Standard patterns + click-outside + overlay | Planned |
| Scope creep from enhancements | High | Clear MVP (P1 only) + documented out-of-scope | Documented |
| Kanban integration breaks | High | Layout wrapper + regression tests + full flow testing | Planned |
| Session timeout UX confusion | Low | Clear messaging + auto-redirect to login | Spec updated |
| Authorization bypass vulnerability | High | Server-side checks in every controller action | Planned |

## Implementation Metrics

**Estimated Effort**:
- Backend API: 8-12 hours (controllers, tests)
- Frontend Stores: 4-6 hours (Pinia stores)
- Layout Components: 8-10 hours (navbar, sidebar, search)
- Dashboard Components: 10-14 hours (stats, projects, cards)
- Integration & Testing: 8-12 hours (QA, regression, performance)
- **Total: 38-54 hours** (5-7 days for full-time developer)

**Complexity Score**:
- Backend: Medium (standard CRUD with authorization)
- Frontend: Medium (new layout system, multiple components)
- Integration: Low (existing stack, no new tech)
- Testing: Medium (multiple flows, authorization cases)

**Lines of Code Estimate**:
- Backend: ~800 lines (controllers + tests)
- Frontend: ~1,500 lines (components + stores + tests)
- **Total: ~2,300 lines**

## Next Steps

1. **Run `/speckit.tasks`** - Generate detailed task breakdown in `tasks.md`
2. **Review and approve tasks** - Validate task order and estimates
3. **Run `/speckit.implement`** - Execute implementation from `tasks.md`
4. **Manual QA** - Test all 5 user stories with acceptance scenarios
5. **Performance validation** - Verify success criteria (SC-001 to SC-012)
6. **Code review** - Submit PR with checklist
7. **Merge to main** - Deploy to production after approval

## Related Documentation

- [Specification](spec.md) - Full requirements with 62 functional requirements
- [Research](research.md) - Technology decisions and architectural patterns
- [Data Model](data-model.md) - Entity definitions and Pinia store structures
- [API Contracts](contracts/) - Endpoint specifications with examples
- [Quickstart Guide](quickstart.md) - Developer setup and implementation workflow
- [Requirements Checklist](checklists/requirements.md) - Specification quality validation (PASSED)

---

**Plan Status**: ✅ COMPLETE - Ready for Phase 2 (Task Breakdown)
**Last Updated**: 2026-02-03
**Next Command**: `/speckit.tasks` to generate implementation tasks
