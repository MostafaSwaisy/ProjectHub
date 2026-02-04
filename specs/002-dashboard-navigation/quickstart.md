# Quickstart Guide: Dashboard & Navigation System

**Feature**: 002-dashboard-navigation
**Branch**: `002-dashboard-navigation`
**Prerequisites**: Phase 1-6 (001-modern-ui) must be complete

## Overview

This guide provides step-by-step instructions for implementing the Dashboard & Navigation System feature. Follow the sections in order for a smooth development experience.

## Prerequisites

### Required Knowledge
- Laravel 12 (Controllers, Resources, Eloquent)
- Vue 3 Composition API
- Pinia state management
- Vue Router configuration
- Tailwind CSS (existing design system)

### Environment Setup
- PHP 8.2+ installed
- Node.js 18+ and npm installed
- Database configured (SQLite by default)
- Laravel Sanctum authentication working from Phase 3

### Verify Existing Setup
```bash
# Check Laravel version
php artisan --version
# Should show: Laravel Framework 12.x.x

# Check if auth system works
php artisan tinker
>>> User::count()
# Should return number of users (>0 if Phase 3 complete)

# Check frontend dependencies
npm list vue vue-router pinia
# Should show installed versions

# Check if existing routes work
php artisan route:list | grep dashboard
# Should show /dashboard route (currently returns black screen - we'll fix this)
```

## Development Workflow

### Step 1: Checkout Branch

```bash
# Branch should already exist from /speckit.specify command
git checkout 002-dashboard-navigation

# Verify you're on the right branch
git branch --show-current
# Output: 002-dashboard-navigation

# Pull latest if needed
git pull origin 002-dashboard-navigation
```

### Step 2: Review Specification & Contracts

**Read these files in order**:
1. [spec.md](spec.md) - Requirements and user stories
2. [research.md](research.md) - Technology decisions and patterns
3. [data-model.md](data-model.md) - Entity definitions and stores
4. [contracts/](contracts/) - API endpoint specifications
   - dashboard-stats.md
   - projects.md
   - search.md
   - activities.md (optional)

**Key Information to Extract**:
- User stories prioritization (P1 = MVP, P2-P4 = enhancements)
- Functional requirements (FR-001 to FR-062)
- Success criteria for testing
- API request/response formats

### Step 3: Backend Implementation

#### 3.1 Create Controllers

```bash
# Generate controllers
php artisan make:controller DashboardController
php artisan make:controller ProjectController --resource
php artisan make:controller SearchController
php artisan make:controller ActivityController  # Optional
```

**Files created**:
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/ProjectController.php`
- `app/Http/Controllers/SearchController.php`
- `app/Http/Controllers/ActivityController.php`

**Implement methods** according to [API contracts](contracts/):
- `DashboardController@stats` - Dashboard statistics
- `ProjectController` - CRUD operations with authorization
- `SearchController@search` - Global search
- `ActivityController@index` - Activity feed (optional)

#### 3.2 Create API Resources (Optional but Recommended)

```bash
# Generate Laravel API resources for consistent JSON formatting
php artisan make:resource ProjectResource
php artisan make:resource UserResource
php artisan make:resource ActivityResource  # Optional
```

**Example ProjectResource**:
```php
// app/Http/Resources/ProjectResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'timeline_status' => $this->timeline_status,
            'budget_status' => $this->budget_status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'total_members' => $this->members_count ?? $this->members->count() + 1,
            'task_completion' => [
                'total' => $this->tasks_count ?? 0,
                'completed' => $this->completed_tasks_count ?? 0,
                'percentage' => $this->calculateCompletionPercentage()
            ]
        ];
    }

    private function calculateCompletionPercentage()
    {
        $total = $this->tasks_count ?? 0;
        $completed = $this->completed_tasks_count ?? 0;
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}
```

#### 3.3 Add API Routes

```bash
# Edit routes/api.php
```

```php
// routes/api.php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ActivityController;

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Search
    Route::get('/search', [SearchController::class, 'search']);

    // Activities (optional)
    Route::get('/activities', [ActivityController::class, 'index']);
});
```

#### 3.4 Test Backend API

```bash
# Start Laravel server
php artisan serve

# In another terminal, test endpoints (requires authentication token)
# First, login to get token (assuming Phase 3 auth works)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Copy the token from response, then test dashboard stats
curl http://localhost:8000/api/dashboard/stats \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Should return:
# {"data":{"stats":{"total_projects":0,"active_tasks":0,"team_members":0,"overdue_tasks":0}}}
```

### Step 4: Frontend Implementation

#### 4.1 Create Pinia Stores

```bash
# Create store files
```

**File**: `resources/js/stores/layout.js`
```javascript
import { defineStore } from 'pinia'

export const useLayoutStore = defineStore('layout', {
  state: () => ({
    sidebarCollapsed: false,
    currentRoute: '/',
    isMobile: false
  }),

  getters: {
    activeNavItem: (state) => {
      // See data-model.md for full implementation
    }
  },

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      localStorage.setItem('layout.sidebarCollapsed', this.sidebarCollapsed)
    },

    initializeLayout() {
      const saved = localStorage.getItem('layout.sidebarCollapsed')
      if (saved) this.sidebarCollapsed = JSON.parse(saved)
    }
  }
})
```

**File**: `resources/js/stores/dashboard.js`
```javascript
import { defineStore } from 'pinia'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: { total_projects: 0, active_tasks: 0, team_members: 0, overdue_tasks: 0 },
    loading: false,
    error: null
  }),

  actions: {
    async fetchStats() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/api/dashboard/stats')
        this.stats = response.data.data.stats
      } catch (err) {
        this.error = err.response?.data?.error?.message || 'Failed to load statistics'
      } finally {
        this.loading = false
      }
    }
  }
})
```

**File**: `resources/js/stores/projects.js`
```javascript
import { defineStore } from 'pinia'
import axios from 'axios'

export const useProjectsStore = defineStore('projects', {
  state: () => ({
    projects: [],
    loading: false,
    error: null
  }),

  getters: {
    recentProjects: (state) => state.projects.slice(0, 5)
  },

  actions: {
    async fetchProjects() {
      this.loading = true
      try {
        const response = await axios.get('/api/projects')
        this.projects = response.data.data.projects
      } catch (err) {
        this.error = err.response?.data?.error?.message || 'Failed to load projects'
      } finally {
        this.loading = false
      }
    },

    async createProject(data) {
      const response = await axios.post('/api/projects', data)
      this.projects.unshift(response.data.data.project)
      return response.data.data.project
    }
  }
})
```

#### 4.2 Create Layout Components

**Directory Structure**:
```
resources/js/
├── layouts/
│   └── AppLayout.vue          # Main authenticated layout
├── components/
│   ├── layout/
│   │   ├── TopNavbar.vue      # Top navigation bar
│   │   ├── Sidebar.vue        # Collapsible sidebar
│   │   ├── UserMenu.vue       # User dropdown menu
│   │   └── GlobalSearch.vue   # Search with dropdown results
```

**File**: `resources/js/layouts/AppLayout.vue`
```vue
<template>
  <div class="app-layout min-h-screen bg-dark-900">
    <TopNavbar />
    <Sidebar />
    <main :class="['app-content transition-all duration-300', {
      'ml-64': !layoutStore.sidebarCollapsed && !layoutStore.isMobile,
      'ml-0': layoutStore.sidebarCollapsed || layoutStore.isMobile
    }]">
      <div class="container mx-auto px-4 py-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useLayoutStore } from '@/stores/layout'
import TopNavbar from '@/components/layout/TopNavbar.vue'
import Sidebar from '@/components/layout/Sidebar.vue'

const layoutStore = useLayoutStore()

onMounted(() => {
  layoutStore.initializeLayout()
})
</script>
```

**See** [research.md](research.md) for complete component architecture and implementation patterns.

#### 4.3 Create Dashboard Components

**Directory Structure**:
```
resources/js/
├── pages/
│   └── Dashboard.vue          # Main dashboard page
├── components/
│   ├── dashboard/
│   │   ├── DashboardStats.vue     # Statistics cards section
│   │   ├── StatCard.vue           # Individual statistic card
│   │   ├── RecentProjects.vue     # Projects list section
│   │   ├── ProjectCard.vue        # Individual project card
│   │   ├── ProjectModal.vue       # Create/edit project modal
│   │   └── ActivityFeed.vue       # Activity feed (optional)
```

**File**: `resources/js/pages/Dashboard.vue`
```vue
<template>
  <AppLayout>
    <div class="dashboard-page">
      <h1 class="text-3xl font-bold text-white mb-6">Dashboard</h1>

      <DashboardStats />
      <RecentProjects class="mt-8" />
      <ActivityFeed v-if="showActivityFeed" class="mt-8" />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useProjectsStore } from '@/stores/projects'
import AppLayout from '@/layouts/AppLayout.vue'
import DashboardStats from '@/components/dashboard/DashboardStats.vue'
import RecentProjects from '@/components/dashboard/RecentProjects.vue'
import ActivityFeed from '@/components/dashboard/ActivityFeed.vue'

const dashboardStore = useDashboardStore()
const projectsStore = useProjectsStore()

// Optional: Only show activity feed for P4 implementation
const showActivityFeed = computed(() => false) // Change to true when implementing P4

onMounted(() => {
  dashboardStore.fetchStats()
  projectsStore.fetchProjects()
})
</script>
```

#### 4.4 Update Router

```javascript
// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '@/pages/Dashboard.vue'
import { useAuthStore } from '@/stores/auth'

const routes = [
  // ... existing auth routes ...

  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },

  // Placeholder routes for sidebar links
  {
    path: '/projects',
    name: 'projects',
    component: () => import('@/pages/ProjectsList.vue'), // Create this later
    meta: { requiresAuth: true }
  },
  {
    path: '/tasks',
    name: 'my-tasks',
    component: () => import('@/pages/MyTasks.vue'), // Create this later
    meta: { requiresAuth: true }
  },
  {
    path: '/team',
    name: 'team',
    component: () => import('@/pages/Team.vue'), // Create this later
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/pages/Settings.vue'), // Create this later
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard (should already exist from Phase 3)
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/auth/login')
  } else {
    next()
  }
})

export default router
```

#### 4.5 Build and Test Frontend

```bash
# Install dependencies (if not done)
npm install

# Run development server with hot reload
npm run dev

# In browser, navigate to http://localhost:5173
# Login with existing user
# Should redirect to /dashboard and see:
# - Top navbar with logo, search, user avatar
# - Sidebar with navigation links
# - Dashboard statistics cards (with real data!)
# - Recent projects list
```

### Step 5: Testing

#### 5.1 Backend Feature Tests

```bash
# Create test files
php artisan make:test DashboardControllerTest
php artisan make:test ProjectControllerTest
php artisan make:test SearchControllerTest
```

**Run tests**:
```bash
# Run all feature tests
php artisan test

# Run specific test class
php artisan test --filter=DashboardControllerTest

# Run with coverage (optional)
php artisan test --coverage
```

#### 5.2 Frontend Unit Tests (Vitest)

```bash
# Run frontend tests
npm run test

# Run with watch mode
npm run test:watch

# Run with UI
npm run test:ui
```

**Test files to create**:
- `resources/js/stores/__tests__/layout.test.js`
- `resources/js/stores/__tests__/dashboard.test.js`
- `resources/js/components/__tests__/Sidebar.test.js`

#### 5.3 Manual QA Checklist

**User Story 1 (Layout & Navigation)**:
- [ ] After login, dashboard shows top navbar and sidebar
- [ ] Sidebar links are visible (Dashboard, Projects, My Tasks, Team, Settings)
- [ ] Current page (Dashboard) is highlighted in sidebar
- [ ] Click Projects link → navigates to projects page
- [ ] Click user avatar → dropdown menu appears
- [ ] Click Logout → logs out and redirects to login
- [ ] Mobile (<768px): sidebar is collapsed, hamburger menu visible
- [ ] Mobile: tap hamburger → sidebar slides in
- [ ] Mobile: tap outside sidebar → sidebar closes

**User Story 2 (Dashboard Statistics)**:
- [ ] Dashboard shows 4 statistic cards
- [ ] Total Projects shows correct count
- [ ] Active Tasks shows correct count (excludes Done/Archived)
- [ ] Team Members shows correct distinct count
- [ ] Overdue Tasks shows correct count with red highlight if > 0
- [ ] New user with no projects sees "0" and empty state message
- [ ] Skeleton loaders show during data fetch
- [ ] Error state with "Retry" button if API fails

**User Story 3 (Projects Management)**:
- [ ] Recent Projects section shows up to 5 projects
- [ ] Project cards show title, description, status badges, avatars
- [ ] Task completion percentage calculates correctly
- [ ] Click "+ New Project" → modal opens
- [ ] Fill project form and submit → project created
- [ ] New project appears at top of list
- [ ] Click project card → navigates to kanban board
- [ ] User with no projects sees empty state with CTA button

**User Story 4 (Global Search)**:
- [ ] Search box visible in top navbar
- [ ] Type query (min 2 chars) → dropdown appears with results
- [ ] Results grouped by Projects and Tasks
- [ ] Click project result → navigates to kanban
- [ ] Click task result → navigates to task's project kanban
- [ ] Press Cmd+K (Mac) or Ctrl+K (Windows) → search box focuses
- [ ] No results message displays when search returns empty

**User Story 5 (Activity Feed)** - Optional:
- [ ] Activity feed shows below projects section
- [ ] Activities display with avatar, action, subject, timestamp
- [ ] Relative timestamps formatted correctly ("2 hours ago")
- [ ] Click activity → navigates to related project/task

### Step 6: Integration with Existing Features

#### 6.1 Verify Kanban Board Integration

```bash
# Test flow: Dashboard → Project Card → Kanban Board
# 1. Login → /dashboard (new layout should appear)
# 2. Click on a project card
# 3. Should navigate to /projects/{id}/kanban
# 4. Kanban board should load with same layout wrapper
# 5. Verify all kanban features still work (drag tasks, edit, etc.)
```

**If kanban breaks**: Check that kanban route uses AppLayout or adjust router setup

#### 6.2 Verify Auth Integration

```bash
# Test flow: Login → Dashboard → Logout
# 1. Go to /auth/login
# 2. Submit valid credentials
# 3. Should redirect to /dashboard (no longer black screen!)
# 4. Click user avatar → Logout
# 5. Should clear session and redirect to /auth/login
```

### Step 7: Performance Validation

#### 7.1 Check Dashboard Load Time

```javascript
// Add to Dashboard.vue for testing
onMounted(async () => {
  const start = performance.now()

  await Promise.all([
    dashboardStore.fetchStats(),
    projectsStore.fetchProjects()
  ])

  const end = performance.now()
  console.log(`Dashboard loaded in ${(end - start).toFixed(2)}ms`)
  // Should be < 2000ms per success criteria SC-001
})
```

#### 7.2 Check Search Performance

```javascript
// In GlobalSearch.vue, log search time
const performSearch = async (query) => {
  const start = performance.now()
  const response = await axios.get('/api/search', { params: { q: query } })
  const end = performance.now()
  console.log(`Search completed in ${(end - start).toFixed(2)}ms`)
  // Should be < 500ms per success criteria SC-005
}
```

## Common Issues & Solutions

### Issue: "Unauthenticated" errors on API calls

**Solution**: Verify Sanctum token is being sent
```javascript
// Check axios interceptor in resources/js/app.js
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})
```

### Issue: Dashboard shows "0" for all stats despite having projects

**Solution**: Check authorization queries
```php
// In DashboardController, verify query includes both instructor and members
$projectIds = Project::where('instructor_id', $user->id)
    ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
    ->pluck('id');
```

### Issue: Sidebar doesn't collapse on mobile

**Solution**: Check isMobile detection in layout store
```javascript
// In stores/layout.js
updateIsMobile() {
  this.isMobile = window.innerWidth < 768
  if (this.isMobile) {
    this.sidebarCollapsed = true
  }
}
```

### Issue: Search returns no results

**Solution**: Verify search indexes and LIKE query
```sql
-- Check if indexes exist
SHOW INDEXES FROM projects;
SHOW INDEXES FROM tasks;

-- Test SQL query directly
SELECT * FROM projects WHERE title LIKE '%test%' LIMIT 10;
```

## Next Steps

After completing this quickstart:

1. **Run Full Test Suite**:
   ```bash
   php artisan test
   npm run test
   ```

2. **Generate Task Breakdown**:
   ```bash
   # Run speckit.tasks command to generate detailed implementation tasks
   /speckit.tasks
   ```

3. **Code Review**: Submit PR for review with checklist:
   - [ ] All user stories (P1-P4) implemented
   - [ ] Backend tests passing
   - [ ] Frontend tests passing
   - [ ] No console errors
   - [ ] Mobile responsive
   - [ ] Accessibility (keyboard navigation, ARIA labels)
   - [ ] No regression on Phase 1-6 features

4. **Deployment Preparation**:
   - Run production build: `npm run build`
   - Clear Laravel cache: `php artisan cache:clear`
   - Test on staging environment

## Reference Documentation

- [Specification](spec.md) - Full requirements
- [Research](research.md) - Technology decisions
- [Data Model](data-model.md) - Entity definitions
- [API Contracts](contracts/) - Endpoint specifications
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Vue 3 Docs](https://vuejs.org/)
- [Pinia Docs](https://pinia.vuejs.org/)
- [Tailwind CSS Docs](https://tailwindcss.com/)

## Support

If you encounter issues:
1. Check [Common Issues](#common-issues--solutions) section above
2. Review API contracts in `contracts/` directory
3. Consult research.md for architectural patterns
4. Check Phase 1-6 implementation for existing patterns

Happy coding! 🚀
