# Dashboard Implementation Tasks - Modern UI Phase

**Issue**: Login redirect to `/dashboard` shows black/empty page
**Root Cause**: Dashboard.vue exists but only displays placeholder data with no navigation or real data fetching
**Database Status**: ✅ Schema is perfect - no changes needed!

---

## DATABASE ANALYSIS ✅

The existing database schema already supports ALL modern UI requirements:

### Existing Tables (Perfect for Modern UI):
- **projects** - title, description, instructor_id, timeline_status, budget_status
- **tasks** - column_id, title, description, assignee_id, priority, due_date, position
- **boards** - project_id, title
- **columns** - board_id, title, position, wip_limit (supports 6-column kanban)
- **labels** - project_id, name, color (perfect for colored badges)
- **task_labels** - many-to-many relationship
- **project_members** - project_id, user_id, role (team collaboration)
- **subtasks** - task_id, title, is_completed, position (progress bars)
- **users** - name, email, role_id (avatars and assignments)
- **activities** - user_id, project_id, type, subject_type, subject_id, data (activity tracking)

**VERDICT**: ✅ NO DATABASE MIGRATIONS NEEDED - Schema fully supports modern UI design!

---

## PHASE 8: Dashboard & Navigation (NEW)

### Issue Summary
The Dashboard.vue component exists with modern styling but:
1. Shows only placeholder data ("--") instead of real statistics
2. No navigation bar/sidebar to access projects
3. No way to create new projects
4. No project list view
5. Disconnected from the kanban board implementation

---

## Tasks

### T115 [P] Create AppLayout Component
**Priority**: CRITICAL (blocks all other dashboard work)
**File**: `resources/js/layouts/AppLayout.vue`
**Description**: Create a reusable app layout with:
- Top navigation bar with logo, search, notifications, user menu
- Sidebar with navigation links (Dashboard, Projects, Settings)
- Main content area with router-view
- Mobile-responsive collapsible sidebar
- Dark theme consistent with design system
- User avatar and name display in navigation
**Dependencies**: None
**Testing**: Navigate to dashboard and verify layout renders with sidebar and navbar

---

### T116 [P] Create Navigation Bar Component
**Priority**: CRITICAL
**File**: `resources/js/components/layout/NavigationBar.vue`
**Description**: Create top navigation bar with:
- Logo and app name (ProjectHub)
- Global search input with icon
- Notification bell icon with badge count
- User avatar dropdown menu (Profile, Settings, Logout)
- Glassmorphic background effect with backdrop blur
- Orange accent colors for active states
**Dependencies**: None
**Testing**: Verify dropdown menus work and logout redirects correctly

---

### T117 [P] Create Sidebar Navigation Component
**Priority**: CRITICAL
**File**: `resources/js/components/layout/Sidebar.vue`
**Description**: Create sidebar with navigation links:
- Dashboard icon + label
- Projects icon + label (with project count badge)
- My Tasks icon + label
- Team icon + label
- Settings icon + label
- Collapsible on mobile (hamburger menu)
- Active state highlighting with orange border
- Smooth expand/collapse animations
**Dependencies**: None
**Testing**: Click navigation links and verify routing works, test mobile collapse

---

### T118 [US5] Update Dashboard.vue to Fetch Real Data
**Priority**: CRITICAL
**File**: `resources/js/pages/Dashboard.vue`
**Description**: Update Dashboard component to:
- Fetch dashboard statistics from API endpoint
- Display total projects count (from projects table)
- Display active tasks count (tasks not in Done/Archived columns)
- Display team members count (distinct users from project_members)
- Display overdue tasks count (tasks with due_date < today and not Done/Archived)
- Add loading skeletons while data loads
- Add error handling with user-friendly messages
- Use existing design system card styling
**Dependencies**: T119 (API endpoint)
**Testing**: Login and verify dashboard shows real statistics

---

### T119 [US5] Create Dashboard API Endpoint
**Priority**: CRITICAL
**File**: `app/Http/Controllers/DashboardController.php`, `routes/api.php`
**Description**: Create API endpoint `/api/dashboard/stats` that returns:
```json
{
  "totalProjects": 5,
  "activeTasks": 23,
  "teamMembers": 8,
  "overdueTasks": 3
}
```
- Count projects where user is instructor or member
- Count tasks in non-Done/Archived columns
- Count distinct team members across user's projects
- Count overdue tasks (due_date < today, not Done/Archived)
- Require authentication (Sanctum middleware)
**Dependencies**: None
**Testing**: Test endpoint with Postman/curl, verify correct counts

---

### T120 [P] [US5] Create Projects List Component
**Priority**: HIGH
**File**: `resources/js/components/dashboard/ProjectsList.vue`
**Description**: Create component showing recent projects:
- Display 5 most recent projects as cards
- Show project title, description (truncated), member avatars
- Show timeline status badge (On Track/At Risk/Delayed)
- Show task completion percentage
- Click card to navigate to project kanban board
- "+ New Project" button with primary styling
- Grid layout (3 columns on desktop, 1 on mobile)
**Dependencies**: T121 (Projects store)
**Testing**: Verify projects display and clicking navigates to kanban

---

### T121 [P] [US5] Create Projects Pinia Store
**Priority**: HIGH
**File**: `resources/js/stores/projects.js`
**Description**: Create Pinia store for project management:
- State: projects list, current project, loading, error
- Actions: fetchProjects(), fetchProject(id), createProject(), updateProject(), deleteProject()
- Getters: getProjectById(id), recentProjects (last 5), projectsCount
- API calls to Laravel backend endpoints
- Handle loading and error states
**Dependencies**: T122 (Projects API)
**Testing**: Test all CRUD operations via store

---

### T122 [US5] Create Projects API Endpoints
**Priority**: HIGH
**File**: `app/Http/Controllers/ProjectController.php`, `routes/api.php`
**Description**: Create RESTful API endpoints:
- GET `/api/projects` - List user's projects (as instructor or member)
- GET `/api/projects/{id}` - Get project details with stats
- POST `/api/projects` - Create new project
- PUT `/api/projects/{id}` - Update project
- DELETE `/api/projects/{id}` - Delete project (soft delete?)
- Include project members and task counts in responses
- Require authentication and authorization checks
**Dependencies**: None
**Testing**: Test all endpoints with Postman, verify authorization

---

### T123 [US5] Add Projects Section to Dashboard
**Priority**: HIGH
**File**: `resources/js/pages/Dashboard.vue`
**Description**: Update Dashboard to include:
- "Recent Projects" heading below statistics cards
- ProjectsList component integration
- "+ New Project" modal trigger
- Empty state when no projects (with CTA to create first project)
- Smooth fade-in animation for projects list
**Dependencies**: T120 (ProjectsList component)
**Testing**: Verify projects section renders, create project flow works

---

### T124 [P] [US5] Create New Project Modal
**Priority**: HIGH
**File**: `resources/js/components/projects/ProjectModal.vue`
**Description**: Create modal for creating/editing projects:
- Title input field (required, max 100 chars)
- Description textarea (optional, max 500 chars)
- Timeline status dropdown (On Track/At Risk/Delayed)
- Budget status dropdown (Within Budget/Over Budget)
- Team members multi-select (search users)
- Glassmorphic modal styling
- Form validation with error messages
- Loading state on submit
- Success notification after creation
**Dependencies**: T125 (Users API for team selection)
**Testing**: Create project and verify it appears in projects list

---

### T125 [US5] Create Users API Endpoint for Team Selection
**Priority**: MEDIUM
**File**: `app/Http/Controllers/UserController.php`, `routes/api.php`
**Description**: Create endpoint for user search:
- GET `/api/users/search?q={query}` - Search users by name/email
- Return id, name, email, avatar_url
- Limit to 20 results
- Exclude current user from results (optional)
- Require authentication
**Dependencies**: None
**Testing**: Test search with various queries

---

### T126 [US5] Integrate Dashboard with AppLayout
**Priority**: CRITICAL
**File**: `resources/js/pages/Dashboard.vue`, `resources/js/router/index.js`
**Description**: Update Dashboard and router:
- Wrap Dashboard in AppLayout component
- Update router meta to specify layout: 'app'
- Add layout switching logic in App.vue or router
- Ensure sidebar navigation shows on dashboard
- Test transitions between auth pages (no layout) and app pages (with layout)
**Dependencies**: T115 (AppLayout)
**Testing**: Navigate from login to dashboard, verify layout appears

---

### T127 [US5] Update KanbanView to Use AppLayout
**Priority**: HIGH
**File**: `resources/js/pages/projects/KanbanView.vue`
**Description**: Wrap KanbanView in AppLayout:
- Use same AppLayout as Dashboard
- Update router meta for layout: 'app'
- Add breadcrumbs showing Project > Kanban Board
- Ensure sidebar navigation works
- Test navigation from Dashboard to Kanban and back
**Dependencies**: T115 (AppLayout)
**Testing**: Navigate to kanban from dashboard, verify layout persists

---

### T128 [US5] Create User Profile Dropdown Menu
**Priority**: MEDIUM
**File**: `resources/js/components/layout/UserMenu.vue`
**Description**: Create dropdown menu for user avatar:
- User name and email display
- Menu items: Profile, Settings, Preferences, Logout
- Avatar with fallback to initials (e.g., "JD" for John Doe)
- Smooth dropdown animation (fade + slide)
- Logout calls auth store logout() and redirects to login
- Click outside to close
**Dependencies**: T116 (NavigationBar)
**Testing**: Click avatar and verify menu, test logout

---

### T129 [P] [US5] Add Activity Feed to Dashboard
**Priority**: LOW (optional enhancement)
**File**: `resources/js/components/dashboard/ActivityFeed.vue`
**Description**: Create activity feed showing recent activities:
- Display last 10 activities from activities table
- Show activity type icon (created, updated, completed, commented)
- Show user avatar, name, action, and timestamp
- Relative time formatting (2 hours ago, yesterday)
- Link to related task/project
- Scrollable container with max height
- Auto-refresh every 30 seconds (optional)
**Dependencies**: T130 (Activities API)
**Testing**: Verify activities display and auto-refresh works

---

### T130 [US5] Create Activities API Endpoint
**Priority**: LOW
**File**: `app/Http/Controllers/ActivityController.php`, `routes/api.php`
**Description**: Create endpoint for recent activities:
- GET `/api/activities?limit=10` - Get recent activities for user's projects
- Include user details (name, avatar)
- Include subject details (task title, project title)
- Order by created_at DESC
- Require authentication
**Dependencies**: None
**Testing**: Test endpoint returns activities in correct format

---

### T131 [US5] Add Search Functionality to Navigation
**Priority**: MEDIUM
**File**: `resources/js/components/layout/GlobalSearch.vue`
**Description**: Implement global search in navbar:
- Search input with magnifying glass icon
- Search across projects and tasks
- Keyboard shortcut (Cmd/Ctrl + K) to focus search
- Dropdown results with project/task icons
- Click result to navigate to project/task
- Debounced search API calls (300ms delay)
- Empty state and loading state
**Dependencies**: T132 (Search API)
**Testing**: Search for projects/tasks and verify navigation

---

### T132 [US5] Create Global Search API Endpoint
**Priority**: MEDIUM
**File**: `app/Http/Controllers/SearchController.php`, `routes/api.php`
**Description**: Create search endpoint:
- GET `/api/search?q={query}` - Search projects and tasks
- Return projects matching title/description
- Return tasks matching title/description
- Limit to 10 results per type (20 total)
- Include project/task metadata for display
- Require authentication
**Dependencies**: None
**Testing**: Test search with various queries, verify relevance

---

### T133 [US5] Add Notifications Bell to Navigation
**Priority**: LOW (optional enhancement)
**File**: `resources/js/components/layout/NotificationsBell.vue`
**Description**: Create notifications dropdown:
- Bell icon with unread count badge
- Dropdown showing recent notifications
- Notification types: task assigned, due soon, commented, etc.
- Mark as read functionality
- Link to notification source (task/project)
- Empty state when no notifications
**Dependencies**: T134 (Notifications API)
**Testing**: Verify notifications appear and mark as read works

---

### T134 [US5] Create Notifications API Endpoints
**Priority**: LOW
**File**: `app/Http/Controllers/NotificationController.php`, `routes/api.php`
**Description**: Create notification endpoints:
- GET `/api/notifications` - Get user notifications
- POST `/api/notifications/{id}/read` - Mark notification as read
- GET `/api/notifications/unread-count` - Get unread count
- Include notification type, data, created_at
- Require authentication
**Dependencies**: None
**Testing**: Test endpoints with Postman

---

### T135 [US5] Create Empty State Components
**Priority**: MEDIUM
**File**: `resources/js/components/shared/EmptyState.vue`
**Description**: Create reusable empty state component:
- Icon/illustration placeholder
- Heading and description text
- Optional CTA button
- Props: icon, heading, description, buttonText, buttonAction
- Consistent styling with design system
- Use for: no projects, no tasks, no search results, etc.
**Dependencies**: None
**Testing**: Test in various scenarios (empty dashboard, empty search)

---

### T136 [US5] Add Loading Skeletons for Dashboard
**Priority**: MEDIUM
**File**: `resources/js/components/shared/LoadingSkeleton.vue`
**Description**: Create loading skeleton component:
- Animated shimmer effect
- Props: type (card, list, table), count
- Match actual component dimensions
- Use for: dashboard stats, projects list, activities feed
- Smooth transition from skeleton to real content
**Dependencies**: None
**Testing**: Test with slow API responses, verify smooth transition

---

### T137 [US5] Update Router with Layout Logic
**Priority**: CRITICAL
**File**: `resources/js/router/index.js`, `resources/js/App.vue`
**Description**: Add layout switching logic:
- Auth routes use no layout (full-screen pages)
- App routes use AppLayout (with sidebar/navbar)
- Route meta: `layout: 'auth' | 'app' | 'none'`
- Dynamic component rendering in App.vue based on layout
- Ensure transitions work between different layouts
**Dependencies**: T115 (AppLayout), T116 (NavigationBar), T117 (Sidebar)
**Testing**: Test navigation between auth and app routes

---

### T138 [US5] Add Logout Functionality
**Priority**: HIGH
**File**: `resources/js/composables/useAuth.js`, `app/Http/Controllers/AuthController.php`
**Description**: Implement logout:
- Frontend: Add logout() method to useAuth composable
- Clear token from localStorage
- Clear user state in auth store
- API: POST `/api/auth/logout` - Invalidate token/session
- Redirect to login page after logout
- Show success message
**Dependencies**: T128 (UserMenu)
**Testing**: Logout from user menu and verify redirect to login

---

### T139 [US5] Test Dashboard with Real Data
**Priority**: HIGH
**File**: Manual testing + seed data
**Description**: Comprehensive testing:
- Create database seeder with sample data (5 projects, 20 tasks, 3 users)
- Run seeder: `php artisan db:seed --class=ModernUIDashboardSeeder`
- Login and verify all dashboard features work:
  - Statistics show correct counts
  - Projects list displays correctly
  - Activity feed shows recent activities
  - Navigation works between dashboard and kanban
  - Search finds projects and tasks
- Test with different user roles (instructor vs member)
**Dependencies**: All dashboard tasks complete
**Testing**: Full manual QA of dashboard features

---

### T140 [US5] Update Documentation
**Priority**: LOW
**File**: `README.md`, `specs/001-modern-ui/implementation-status.md`
**Description**: Document Phase 8 implementation:
- Update README with dashboard features
- Add screenshots of dashboard
- Document API endpoints in API docs
- Update implementation status report
- Add notes about layout system
**Dependencies**: All dashboard tasks complete
**Testing**: Review documentation for accuracy

---

## Dependencies & Execution Order

### Phase 8 Dependencies:

**CRITICAL PATH (Must be done first):**
1. T115 (AppLayout) → T137 (Router Layout Logic) → T126 (Dashboard Integration)
2. T116 (NavigationBar) + T117 (Sidebar) → T115 (used in AppLayout)
3. T119 (Dashboard API) → T118 (Dashboard Data Fetch)
4. T122 (Projects API) → T121 (Projects Store) → T120 (Projects List)

**Can Run in Parallel After Critical Path:**
- T124 (Project Modal) + T125 (Users API)
- T128 (User Menu) - depends on T116
- T131 (Global Search) + T132 (Search API)
- T133 (Notifications) + T134 (Notifications API) - Optional
- T129 (Activity Feed) + T130 (Activities API) - Optional
- T135 (Empty States) + T136 (Loading Skeletons)

**Final Steps:**
- T127 (KanbanView Layout) - after T115
- T138 (Logout) - after T128
- T139 (Testing with Real Data)
- T140 (Documentation)

---

## Task Count Summary

**Phase 8: Dashboard & Navigation**
- CRITICAL: 8 tasks (T115-T119, T126, T137, T138)
- HIGH: 5 tasks (T120-T124, T127)
- MEDIUM: 5 tasks (T125, T128, T131-T132, T135-T136)
- LOW: 4 tasks (T129-T130, T133-T134, T140)

**Total: 26 new tasks** for complete dashboard implementation

---

## MVP for Dashboard (Minimum Viable Dashboard)

To quickly fix the "black screen" issue, implement in this order:

1. **T115** - Create AppLayout (navbar + sidebar + content area)
2. **T116** - Create NavigationBar (logo + user menu)
3. **T117** - Create Sidebar (navigation links)
4. **T119** - Create Dashboard API (statistics endpoint)
5. **T118** - Update Dashboard.vue (fetch real data)
6. **T137** - Update Router (layout switching)
7. **T126** - Integrate Dashboard with AppLayout
8. **T122** - Create Projects API (list projects)
9. **T121** - Create Projects Store
10. **T120** - Create Projects List component
11. **T123** - Add Projects Section to Dashboard

**MVP = 11 tasks** to get a functional dashboard with navigation and real data

After MVP, you can add:
- Project creation modal (T124-T125)
- Search functionality (T131-T132)
- Activity feed (T129-T130)
- Notifications (T133-T134)
- Polish (T135-T136, T139-T140)

---

## Database Migration Required?

**NO DATABASE CHANGES NEEDED** ✅

The existing schema perfectly supports all dashboard and modern UI features:
- projects table has all needed fields
- tasks table has priority, due_date, assignee_id, column_id
- labels table has color field for colored badges
- project_members table supports team collaboration
- subtasks table supports progress bars
- activities table ready for activity feed

**Only API endpoints and frontend components need to be created!**

---

## Notes

- All routes should be protected with authentication middleware
- Use Sanctum tokens for API authentication
- Follow Laravel API Resource pattern for consistent responses
- Use Pinia stores for all state management
- Follow existing design system CSS variables
- Ensure mobile responsiveness for all new components
- Add proper error handling and loading states
- Use TypeScript definitions if project uses TypeScript
- Follow Vue 3 Composition API patterns
- Test with real data before marking complete

---

## Summary

The dashboard issue is straightforward:
1. **Dashboard.vue exists but shows placeholder data**
2. **No navigation layout to access projects/kanban**
3. **No API endpoints to fetch real data**

Solution:
1. Create AppLayout with sidebar and navbar
2. Create API endpoints for dashboard stats and projects
3. Fetch and display real data
4. Add project creation and navigation

**Database is perfect - no migrations needed!** All required tables and relationships already exist.

**Recommended approach**: Start with MVP (11 tasks) to quickly fix the black screen, then add enhancements.
