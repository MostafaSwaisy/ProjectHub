# Implementation Research: Dashboard & Navigation System

**Feature**: 002-dashboard-navigation
**Phase**: 0 (Research & Outline)
**Date**: 2026-02-03
**Related**: [spec.md](spec.md)

## Technology Stack Decision

### Core Framework Selection

**Decision**: Continue with existing Laravel 12 + Vue 3.4.0 + Pinia 2.2.0 + Sanctum 4.2 stack

**Rationale**:
- Phase 1-6 (001-modern-ui) already established this stack with working authentication and kanban board
- No need to introduce new technologies for standard dashboard/navigation features
- Design system CSS (glassmorphic effects, orange/blue theme) is already built and validated
- Sanctum authentication is working and requires no changes
- Zero learning curve for existing patterns

**Alternatives Considered**:
- React/Next.js migration: Rejected - would require rewriting all Phase 1-6 work
- Vue 2: Rejected - already on Vue 3 with Composition API benefits
- Nuxt.js: Rejected - unnecessary complexity for SPA with API backend

### State Management Pattern

**Decision**: Use Pinia stores for layout state, dashboard data, and projects list

**Rationale**:
- Pinia 2.2.0 already configured and used in Phase 1-6
- Composition API-friendly with TypeScript support
- Lightweight compared to Vuex (no mutations boilerplate)
- Natural fit for dashboard statistics caching and projects list management

**Store Architecture**:
```javascript
// stores/layout.js - Navigation and sidebar state
export const useLayoutStore = defineStore('layout', {
  state: () => ({
    sidebarCollapsed: false, // Persisted to localStorage
    currentRoute: null
  })
})

// stores/dashboard.js - Dashboard statistics
export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: { totalProjects: 0, activeTasks: 0, teamMembers: 0, overdueTasks: 0 },
    loading: false,
    error: null
  }),
  actions: {
    async fetchStats() { /* API call */ }
  }
})

// stores/projects.js - Projects list
export const useProjectsStore = defineStore('projects', {
  state: () => ({
    projects: [],
    loading: false
  }),
  actions: {
    async fetchProjects() { /* API call */ },
    async createProject(data) { /* API call */ }
  }
})
```

### Layout Architecture

**Decision**: Single AppLayout component wrapping all authenticated routes

**Rationale**:
- Vue Router supports layout-per-route via component composition
- Keeps layout logic centralized and DRY
- Easy to test layout in isolation
- Consistent with Vue 3 best practices

**Implementation Pattern**:
```vue
<!-- layouts/AppLayout.vue -->
<template>
  <div class="app-layout">
    <TopNavbar />
    <Sidebar />
    <main class="app-content">
      <slot /> <!-- Route component renders here -->
    </main>
  </div>
</template>
```

**Route Configuration**:
```javascript
// router/index.js
{
  path: '/dashboard',
  component: Dashboard,
  meta: { layout: 'app', requiresAuth: true }
}
```

### API Design Pattern

**Decision**: RESTful Laravel API endpoints with JSON:API-inspired responses

**Rationale**:
- Laravel API Resources provide consistent JSON formatting
- Existing Sanctum authentication middleware works seamlessly
- Standard HTTP verbs (GET, POST, PUT, DELETE) are intuitive
- Response structure matches Pinia store expectations from Phase 1-6

**Response Format**:
```json
{
  "data": {
    "stats": {
      "total_projects": 5,
      "active_tasks": 23,
      "team_members": 8,
      "overdue_tasks": 3
    }
  }
}
```

**Error Format**:
```json
{
  "error": {
    "message": "Failed to fetch dashboard statistics",
    "code": "DASHBOARD_FETCH_ERROR"
  }
}
```

### Search Implementation

**Decision**: SQL LIKE queries with database indexes, no full-text search engine

**Rationale**:
- Scale target is small (25 projects, 500 tasks max per user)
- SQL LIKE on indexed columns is sufficient for this scale
- Avoids complexity of Elasticsearch/Meilisearch setup
- Search limited to 20 results caps query complexity
- 300ms debouncing reduces API load

**SQL Pattern**:
```sql
SELECT * FROM projects
WHERE (title LIKE '%query%' OR description LIKE '%query%')
  AND (instructor_id = ? OR id IN (SELECT project_id FROM project_members WHERE user_id = ?))
LIMIT 10
```

**Indexes Required** (should already exist):
- `projects.instructor_id`
- `project_members.user_id`
- `tasks.column_id`
- Composite: `project_members(user_id, project_id)`

### Performance Optimization Strategy

**Decision**: Client-side caching with stale-while-revalidate pattern + Laravel cache

**Rationale**:
- Dashboard stats change infrequently (minutes/hours, not seconds)
- 5-minute server-side cache per user reduces database load
- Pinia stores cache data for component re-renders
- Skeleton loaders provide instant perceived performance

**Caching Strategy**:
```php
// Laravel Controller
Cache::remember("dashboard.stats.{$userId}", 300, function() {
    return [
        'total_projects' => ...,
        'active_tasks' => ...,
        // ...
    ];
});
```

## Component Architecture

### Layout Components (New)

1. **AppLayout.vue** - Top-level authenticated layout wrapper
   - Manages sidebar collapse state
   - Provides layout structure for all authenticated pages
   - Loads TopNavbar and Sidebar components

2. **TopNavbar.vue** - Top navigation bar
   - Logo (links to /dashboard)
   - GlobalSearch component
   - Notifications icon (placeholder for future)
   - UserMenu component

3. **Sidebar.vue** - Collapsible sidebar navigation
   - Navigation links with active state highlighting
   - Mobile hamburger toggle
   - Smooth slide animations

4. **UserMenu.vue** - Dropdown menu from avatar
   - User info display
   - Profile/Settings links (placeholder pages)
   - Logout action

5. **GlobalSearch.vue** - Search input with results dropdown
   - Debounced search API calls
   - Keyboard shortcut (Cmd/Ctrl+K)
   - Results grouped by type (projects, tasks)

### Dashboard Components (New)

1. **Dashboard.vue** - Main dashboard page
   - Loads DashboardStats, RecentProjects, ActivityFeed
   - Coordinates data fetching from stores

2. **DashboardStats.vue** - Four statistic cards
   - Skeleton loaders during fetch
   - Error state with retry
   - Empty state for new users

3. **StatCard.vue** - Reusable statistic card
   - Label, value, optional icon
   - Conditional styling (e.g., red for overdue tasks > 0)

4. **RecentProjects.vue** - Projects list section
   - Displays 5 most recent projects
   - "+ New Project" button
   - Empty state with CTA

5. **ProjectCard.vue** - Individual project summary card
   - Title, description (truncated 100 chars)
   - Status badges (timeline, budget)
   - Member avatars (max 5 shown)
   - Task completion percentage
   - Click to navigate to kanban

6. **ProjectModal.vue** - Create/edit project modal
   - Form with title, description, dropdowns
   - Validation with inline errors
   - Glassmorphic modal styling from design system

7. **ActivityFeed.vue** - Recent activity list (optional)
   - Activity items with avatars
   - Relative timestamps
   - Click to navigate to subject

### Shared Components (Reused from Phase 1-6)

- **Button** - Already exists in design system
- **Input** - Text input with validation
- **Modal** - Base modal wrapper (glassmorphic)
- **Avatar** - User avatar with fallback to initials
- **Badge** - Status badges (timeline, budget, labels)
- **Skeleton** - Loading placeholders

## Integration Points

### With Existing Auth System (Phase 3)

- Login flow remains unchanged: `POST /api/auth/login` → returns Sanctum token
- After successful login, redirect to `/dashboard` (already working)
- Dashboard now renders with AppLayout instead of black screen
- Logout clears Sanctum token and Pinia stores

### With Existing Kanban Board (Phase 5)

- Navigation from Dashboard → Project Card click → `/projects/:id/kanban`
- Kanban route uses same AppLayout wrapper for consistent navigation
- Kanban stores (useKanbanStore, useTaskStore) continue working independently
- No changes required to kanban components

### With Existing Design System (Phase 1-2)

- All new components import existing CSS:
  - `design-system.css` - CSS variables, colors, glassmorphic classes
  - `animations.css` - Transitions, keyframes
- Use existing utility classes:
  - `.glass-card`, `.glass-button`
  - `.gradient-primary`, `.gradient-secondary`
  - `.animation-fade-in`, `.animation-slide-up`
- Orange/blue color scheme maintained via CSS variables

### With Existing Mobile Responsive (Phase 6)

- AppLayout components use existing breakpoints:
  - Mobile: < 768px (sidebar collapsed by default)
  - Tablet: 768px - 1024px
  - Desktop: > 1024px
- Touch event handling for sidebar swipe
- Mobile-first grid for dashboard cards

## Security Considerations

### Authentication & Authorization

**Pattern**: Laravel Sanctum middleware + role-based checks

**Existing Protection** (from Phase 3):
- All `/api/*` routes protected by `auth:sanctum` middleware
- Frontend stores Sanctum token in localStorage
- Axios interceptor adds token to headers

**New Requirements**:
- Dashboard stats endpoint: Check user is authenticated
- Projects endpoints: Check user is instructor or project member
- Delete project: Check user is instructor only (not just member)
- Search: Only return projects/tasks user has access to

**Authorization Pattern** (Laravel Controller):
```php
// DashboardController.php
public function stats(Request $request) {
    $user = $request->user(); // Sanctum provides authenticated user

    $stats = [
        'total_projects' => Project::where('instructor_id', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->count(),
        // ...
    ];

    return response()->json(['data' => ['stats' => $stats]]);
}

// ProjectController.php
public function destroy(Request $request, $id) {
    $project = Project::findOrFail($id);

    // Only project instructor can delete
    if ($project->instructor_id !== $request->user()->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $project->delete();
    return response()->json(['message' => 'Project deleted']);
}
```

### Session Management

**Decision**: 30-minute inactivity timeout (from clarifications)

**Implementation**:
- Laravel session config: `SESSION_LIFETIME=30`
- Sanctum token expiration: Use Laravel's default stateless tokens
- Frontend: No automatic refresh required (user re-authenticates on 401)
- On 401 response: Clear stores, redirect to /auth/login

**Axios Interceptor** (extends existing from Phase 3):
```javascript
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Session expired
      useAuthStore().logout() // Clear stores
      router.push('/auth/login')
    }
    return Promise.reject(error)
  }
)
```

### XSS Prevention

**Pattern**: Vue 3 automatic escaping + DOMPurify for rich text

**Existing Protection**:
- Vue 3 templates automatically escape `{{ }}` interpolations
- Never use `v-html` unless content is sanitized

**New Risk Areas**:
- Project descriptions: May contain user HTML
- Task descriptions: May contain user HTML
- Activity feed messages: May contain user-generated text

**Mitigation**:
- Store project/task descriptions as plain text only
- Display with Vue's automatic escaping
- If rich text needed later: Add DOMPurify sanitization

### CSRF Protection

**Pattern**: Laravel Sanctum SPA authentication

**Existing Protection** (from Phase 3):
- Sanctum provides CSRF cookie for same-origin requests
- Axios automatically includes CSRF token in headers

**Requirements**:
- All state-changing requests (POST, PUT, DELETE) include CSRF token
- Token refreshed on page load via `/sanctum/csrf-cookie`

## Testing Strategy

### Frontend Testing

**Tools**: Vitest (already configured in Phase 1-6)

**Unit Tests** (New):
- `AppLayout.vue`: Sidebar toggle, active route highlighting
- `GlobalSearch.vue`: Debouncing, keyboard shortcuts
- `DashboardStats.vue`: Loading states, error handling
- `ProjectCard.vue`: Status badge colors, member avatar rendering
- Pinia stores: `layout.js`, `dashboard.js`, `projects.js` actions

**Integration Tests** (New):
- Navigation flow: Dashboard → Projects → Kanban → Back
- Project creation: Modal open → Form fill → Submit → List update
- Search flow: Type → Debounce → Results → Navigate
- Logout: Click logout → Stores cleared → Redirect

### Backend Testing

**Tools**: PHPUnit (Laravel default)

**Feature Tests** (New):
- `DashboardControllerTest`: Stats calculation accuracy
- `ProjectControllerTest`: CRUD operations, authorization
- `SearchControllerTest`: Query relevance, access control

**Test Cases**:
```php
// Test dashboard stats accuracy
public function test_dashboard_stats_counts_user_projects_correctly() {
    $user = User::factory()->create();
    $projects = Project::factory()->count(5)->create(['instructor_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/dashboard/stats');

    $response->assertJson([
        'data' => ['stats' => ['total_projects' => 5]]
    ]);
}

// Test authorization: members cannot delete projects
public function test_project_member_cannot_delete_project() {
    $instructor = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['instructor_id' => $instructor->id]);
    $project->members()->attach($member->id);

    $response = $this->actingAs($member)->deleteJson("/api/projects/{$project->id}");

    $response->assertStatus(403);
    $this->assertDatabaseHas('projects', ['id' => $project->id]); // Not deleted
}
```

### Regression Testing

**Critical Paths** (Must not break):
- Phase 3: Login → Dashboard (was broken, now fixed)
- Phase 5: Dashboard → Kanban board → Task CRUD
- Phase 6: Mobile sidebar → Navigation

## Database Queries Analysis

### Dashboard Statistics Query

**Requirement**: Calculate 4 statistics from existing tables

**Queries** (optimized with indexes):

```sql
-- Total Projects (instructor + member)
SELECT COUNT(DISTINCT p.id)
FROM projects p
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE p.instructor_id = ? OR pm.user_id = ?

-- Active Tasks (not in Done/Archived columns)
SELECT COUNT(t.id)
FROM tasks t
JOIN columns c ON t.column_id = c.id
JOIN boards b ON c.board_id = b.id
JOIN projects p ON b.project_id = p.id
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE (p.instructor_id = ? OR pm.user_id = ?)
  AND c.title NOT IN ('Done', 'Archived')

-- Team Members (distinct across all user's projects)
SELECT COUNT(DISTINCT pm.user_id)
FROM project_members pm
WHERE pm.project_id IN (
    SELECT p.id FROM projects p
    WHERE p.instructor_id = ?
    UNION
    SELECT pm2.project_id FROM project_members pm2 WHERE pm2.user_id = ?
)

-- Overdue Tasks (due_date < today, not Done/Archived)
SELECT COUNT(t.id)
FROM tasks t
JOIN columns c ON t.column_id = c.id
JOIN boards b ON c.board_id = b.id
JOIN projects p ON b.project_id = p.id
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE (p.instructor_id = ? OR pm.user_id = ?)
  AND t.due_date < CURRENT_DATE
  AND c.title NOT IN ('Done', 'Archived')
```

**Optimization Notes**:
- Use subquery for user's projects list (DRY)
- Index on `projects.instructor_id`, `project_members.user_id`, `tasks.due_date`
- Cache results for 5 minutes per user
- Estimated execution time: < 100ms on SQLite with indexes

### Recent Projects Query

**Requirement**: Fetch 5 most recently updated projects with metadata

```sql
SELECT
    p.*,
    (SELECT COUNT(*) FROM tasks t
     JOIN columns c ON t.column_id = c.id
     JOIN boards b ON c.board_id = b.id
     WHERE b.project_id = p.id) as total_tasks,
    (SELECT COUNT(*) FROM tasks t
     JOIN columns c ON t.column_id = c.id
     JOIN boards b ON c.board_id = b.id
     WHERE b.project_id = p.id AND c.title = 'Done') as completed_tasks
FROM projects p
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE p.instructor_id = ? OR pm.user_id = ?
ORDER BY p.updated_at DESC
LIMIT 5
```

**Frontend Calculation**:
- Completion % = (completed_tasks / total_tasks) × 100
- Member avatars: Separate query or eager load `project.members.user`

## Migration Plan

### Phase 0: Research ✅ (This Document)

**Status**: Complete
**Artifacts**: research.md

### Phase 1: Design & Contracts

**Tasks**:
- [x] Create data-model.md defining entities (Dashboard Statistics, Project Card, etc.)
- [x] Create API contracts in contracts/ directory
- [x] Create quickstart.md for developer setup

**Artifacts**: data-model.md, contracts/, quickstart.md

### Phase 2: Task Breakdown (via /speckit.tasks)

**Tasks**:
- Generate tasks.md with dependency-ordered implementation tasks
- Organize by component (Layout, Dashboard, API, Testing)
- Estimate effort and identify blockers

**Artifacts**: tasks.md

### Phase 3: Implementation (via /speckit.implement)

**Execution Order**:
1. Backend API endpoints (dashboard stats, projects CRUD, search)
2. Pinia stores (layout, dashboard, projects)
3. Layout components (AppLayout, Sidebar, TopNavbar)
4. Dashboard page and components
5. Integration testing
6. Mobile responsive adjustments

### Phase 4: Validation

**Testing**:
- Run frontend unit tests (Vitest)
- Run backend feature tests (PHPUnit)
- Manual QA on all user stories
- Regression testing on Phase 1-6 features
- Cross-browser testing (Chrome, Firefox, Safari, Edge)
- Mobile device testing (iOS Safari, Chrome Android)

## Risk Mitigation Tracking

| Risk | Mitigation | Status |
|------|------------|--------|
| API Performance with large datasets | Database indexes + 5-min cache + pagination | Planned |
| Navigation state management bugs | Pinia store + localStorage + route metadata | Planned |
| Search slow/irrelevant | Indexed columns + 20 result limit + debouncing | Planned |
| Mobile sidebar UX issues | Standard patterns + click-outside + overlay | Planned |
| Scope creep from enhancements | Clear MVP (P1 only) + document out-of-scope | Documented |
| Kanban integration breaks | Layout wrapper + regression tests + full flow testing | Planned |

## Open Questions for Development

None at this stage. All architectural decisions are documented above. Implementation can proceed directly to Phase 1 (Design & Contracts).

## References

- [Spec](spec.md) - Feature specification with requirements
- [Plan](plan.md) - High-level implementation plan
- Phase 1-6 Implementation: `specs/001-modern-ui/` (design system, auth, kanban)
- Vue 3 Docs: https://vuejs.org/guide/
- Pinia Docs: https://pinia.vuejs.org/
- Laravel API Resources: https://laravel.com/docs/12.x/eloquent-resources
- Sanctum Docs: https://laravel.com/docs/12.x/sanctum
