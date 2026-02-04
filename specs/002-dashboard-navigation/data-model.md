# Data Model: Dashboard & Navigation System

**Feature**: 002-dashboard-navigation
**Phase**: 1 (Design & Contracts)
**Date**: 2026-02-03
**Related**: [spec.md](spec.md) | [research.md](research.md)

## Overview

This feature uses the **existing database schema** from Phase 1-6. No new tables or migrations are required. This document defines the logical entities and their relationships as they appear in API responses and frontend stores.

## Entity Definitions

### 1. Dashboard Statistics

**Purpose**: Aggregate metrics representing the user's work overview

**Structure**:
```typescript
interface DashboardStatistics {
  total_projects: number      // Count of projects where user is instructor or member
  active_tasks: number         // Count of tasks not in "Done" or "Archived" columns
  team_members: number         // Distinct count of users in user's projects
  overdue_tasks: number        // Count of tasks with due_date < today and not done
}
```

**Calculation Rules**:
- `total_projects`: COUNT(DISTINCT projects) WHERE instructor_id = user.id OR project has user as member
- `active_tasks`: COUNT(tasks) WHERE task.column.title NOT IN ('Done', 'Archived') AND task belongs to user's projects
- `team_members`: COUNT(DISTINCT project_members.user_id) WHERE project in user's projects (excludes the user themselves)
- `overdue_tasks`: COUNT(tasks) WHERE due_date < CURRENT_DATE AND column NOT IN ('Done', 'Archived') AND task belongs to user's projects

**Database Tables Used**:
- `projects` (instructor_id)
- `project_members` (user_id, project_id)
- `tasks` (column_id, due_date)
- `columns` (title, board_id)
- `boards` (project_id)

**API Endpoint**: `GET /api/dashboard/stats`

**Example Response**:
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

---

### 2. Project Card

**Purpose**: Summary representation of a project for display in lists

**Structure**:
```typescript
interface ProjectCard {
  id: number
  title: string                     // Max 100 chars
  description: string | null        // Truncated to 100 chars in UI
  timeline_status: TimelineStatus   // 'On Track' | 'At Risk' | 'Delayed'
  budget_status: BudgetStatus       // 'Within Budget' | 'Over Budget'
  created_at: string                // ISO 8601 timestamp
  updated_at: string                // ISO 8601 timestamp
  instructor: UserSummary           // Project owner
  members: UserSummary[]            // Up to 5 displayed in UI
  total_members: number             // Total count including instructor
  task_completion: TaskCompletion   // Statistics about tasks
}

interface UserSummary {
  id: number
  name: string
  email: string
  avatar_url: string | null         // If null, generate initials
}

interface TaskCompletion {
  total: number                     // Total tasks in project
  completed: number                 // Tasks in "Done" column
  percentage: number                // (completed / total) * 100, or 0 if no tasks
}

enum TimelineStatus {
  ON_TRACK = 'On Track',
  AT_RISK = 'At Risk',
  DELAYED = 'Delayed'
}

enum BudgetStatus {
  WITHIN_BUDGET = 'Within Budget',
  OVER_BUDGET = 'Over Budget'
}
```

**Calculation Rules**:
- `task_completion.total`: COUNT(tasks) WHERE task.board.project_id = project.id
- `task_completion.completed`: COUNT(tasks) WHERE task.board.project_id = project.id AND task.column.title = 'Done'
- `task_completion.percentage`: (completed / total * 100) OR 0 if total === 0
- `total_members`: COUNT(project_members) + 1 (instructor)
- `members`: First 5 members by join date (for UI display)

**Database Tables Used**:
- `projects` (id, title, description, timeline_status, budget_status, instructor_id, created_at, updated_at)
- `project_members` (project_id, user_id, created_at)
- `users` (id, name, email, avatar_url)
- `boards` (project_id)
- `columns` (board_id, title)
- `tasks` (column_id)

**API Endpoints**:
- `GET /api/projects` - List user's projects
- `GET /api/projects/{id}` - Get single project details

**Example Response**:
```json
{
  "data": {
    "projects": [
      {
        "id": 42,
        "title": "Q1 Marketing Campaign",
        "description": "Launch social media ads for new product line targeting millennials",
        "timeline_status": "At Risk",
        "budget_status": "Within Budget",
        "created_at": "2026-01-15T10:30:00Z",
        "updated_at": "2026-02-03T14:22:00Z",
        "instructor": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "avatar_url": "https://example.com/avatars/john.jpg"
        },
        "members": [
          {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "avatar_url": null
          },
          {
            "id": 3,
            "name": "Bob Johnson",
            "email": "bob@example.com",
            "avatar_url": "https://example.com/avatars/bob.jpg"
          }
        ],
        "total_members": 3,
        "task_completion": {
          "total": 15,
          "completed": 8,
          "percentage": 53.33
        }
      }
    ]
  }
}
```

---

### 3. Project Create/Update Request

**Purpose**: Data structure for creating or updating projects

**Structure**:
```typescript
interface ProjectCreateRequest {
  title: string                     // Required, max 100 chars
  description?: string | null       // Optional, max 500 chars
  timeline_status?: TimelineStatus  // Optional, default: 'On Track'
  budget_status?: BudgetStatus      // Optional, default: 'Within Budget'
}

interface ProjectUpdateRequest {
  title?: string                    // Optional, max 100 chars
  description?: string | null       // Optional, max 500 chars
  timeline_status?: TimelineStatus  // Optional
  budget_status?: BudgetStatus      // Optional
}
```

**Validation Rules**:
- `title`: Required on create, 1-100 characters, no HTML
- `description`: Optional, max 500 characters, plain text only
- `timeline_status`: Must be one of: 'On Track', 'At Risk', 'Delayed'
- `budget_status`: Must be one of: 'Within Budget', 'Over Budget'

**API Endpoints**:
- `POST /api/projects` - Create new project
- `PUT /api/projects/{id}` - Update existing project

**Example Request** (Create):
```json
{
  "title": "Website Redesign",
  "description": "Modernize company website with new branding and improved UX",
  "timeline_status": "On Track",
  "budget_status": "Within Budget"
}
```

**Example Response** (Success):
```json
{
  "data": {
    "project": {
      "id": 43,
      "title": "Website Redesign",
      "description": "Modernize company website with new branding and improved UX",
      "timeline_status": "On Track",
      "budget_status": "Within Budget",
      "created_at": "2026-02-03T15:30:00Z",
      "updated_at": "2026-02-03T15:30:00Z",
      "instructor": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": null
      },
      "members": [],
      "total_members": 1,
      "task_completion": {
        "total": 0,
        "completed": 0,
        "percentage": 0
      }
    }
  }
}
```

---

### 4. Search Result

**Purpose**: Item returned from global search across projects and tasks

**Structure**:
```typescript
interface SearchResponse {
  projects: SearchResultItem[]      // Max 10 items
  tasks: SearchResultItem[]         // Max 10 items
  has_more_projects: boolean        // True if > 10 projects match
  has_more_tasks: boolean           // True if > 10 tasks match
}

interface SearchResultItem {
  id: number
  type: 'project' | 'task'
  title: string
  description: string | null        // Snippet showing match context
  project_id: number | null         // Null for project results, set for task results
  project_title: string | null      // Set for task results
  match_in: 'title' | 'description' // Where the search term matched
}
```

**Search Behavior**:
- Search query: min 2 characters
- Debounce: 300ms on frontend
- Search scope: Projects and tasks where user is instructor or member
- Match pattern: SQL LIKE '%query%' on title and description
- Result limit: 10 projects + 10 tasks maximum
- Ordering: Exact title match first, then partial title, then description matches

**Database Tables Used**:
- `projects` (id, title, description, instructor_id)
- `project_members` (project_id, user_id)
- `tasks` (id, title, description, column_id)
- `columns` (board_id)
- `boards` (project_id)

**API Endpoint**: `GET /api/search?q={query}`

**Example Response**:
```json
{
  "data": {
    "results": {
      "projects": [
        {
          "id": 42,
          "type": "project",
          "title": "Marketing Campaign",
          "description": "Social media marketing for new product launch",
          "project_id": null,
          "project_title": null,
          "match_in": "title"
        }
      ],
      "tasks": [
        {
          "id": 158,
          "type": "task",
          "title": "Create marketing copy",
          "description": "Write compelling ad copy for Facebook ads",
          "project_id": 42,
          "project_title": "Marketing Campaign",
          "match_in": "title"
        }
      ],
      "has_more_projects": false,
      "has_more_tasks": true
    }
  }
}
```

---

### 5. Navigation Layout State

**Purpose**: Frontend state management for layout and navigation

**Structure** (Pinia Store):
```typescript
interface LayoutState {
  sidebarCollapsed: boolean         // True if sidebar is collapsed
  currentRoute: string              // Current route path (e.g., '/dashboard')
  isMobile: boolean                 // True if window.innerWidth < 768px
}

interface LayoutActions {
  toggleSidebar(): void             // Toggle sidebar collapse state
  setSidebarCollapsed(value: boolean): void
  setCurrentRoute(path: string): void
  initializeLayout(): void          // Load from localStorage
  persistSidebarState(): void       // Save to localStorage
}
```

**Persistence**:
- `sidebarCollapsed` saved to localStorage as `layout.sidebarCollapsed`
- Restored on app initialization
- Auto-collapse on mobile (<768px) regardless of saved state

**Computed Properties**:
- `activeNavItem`: Derived from `currentRoute` (e.g., '/dashboard' → 'dashboard')
- `showSidebar`: On mobile, show only if NOT collapsed; on desktop, always show

**Example State**:
```javascript
{
  sidebarCollapsed: false,
  currentRoute: '/dashboard',
  isMobile: false
}
```

---

### 6. Activity Item

**Purpose**: Record of a user action in the system (optional feature)

**Structure**:
```typescript
interface ActivityItem {
  id: number
  user: UserSummary                 // Actor who performed the action
  action: ActivityAction            // Type of action
  subject_type: 'project' | 'task'  // What was acted upon
  subject_id: number                // ID of the project or task
  subject_title: string             // Title for display
  data: Record<string, any> | null  // Additional context (e.g., old/new values)
  created_at: string                // ISO 8601 timestamp
  relative_time: string             // Human-readable (e.g., "2 hours ago")
}

enum ActivityAction {
  CREATED = 'created',
  UPDATED = 'updated',
  COMPLETED = 'completed',
  COMMENTED = 'commented',
  DELETED = 'deleted'
}
```

**Display Format**:
- "{user.name} {action} {subject_type} "{subject_title}" {relative_time}"
- Example: "Jane Smith completed task "Write documentation" 2 hours ago"

**Database Tables Used**:
- `activities` (id, user_id, action, subject_type, subject_id, data, created_at)
- `users` (id, name, email, avatar_url)
- `projects` or `tasks` (for subject_title)

**API Endpoint**: `GET /api/activities?limit={n}`

**Example Response**:
```json
{
  "data": {
    "activities": [
      {
        "id": 1024,
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com",
          "avatar_url": null
        },
        "action": "completed",
        "subject_type": "task",
        "subject_id": 158,
        "subject_title": "Write documentation",
        "data": null,
        "created_at": "2026-02-03T13:15:00Z",
        "relative_time": "2 hours ago"
      },
      {
        "id": 1023,
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "avatar_url": "https://example.com/avatars/john.jpg"
        },
        "action": "created",
        "subject_type": "project",
        "subject_id": 43,
        "subject_title": "Website Redesign",
        "data": null,
        "created_at": "2026-02-03T10:30:00Z",
        "relative_time": "5 hours ago"
      }
    ]
  }
}
```

---

### 7. User Avatar

**Purpose**: Visual representation of a user in UI components

**Structure**:
```typescript
interface UserAvatar {
  id: number
  name: string
  avatar_url: string | null
  initials: string                  // Computed: first letter of first + last name
  bg_color: string                  // Computed: consistent color based on user ID
}
```

**Generation Rules**:
- If `avatar_url` is set: Display image
- If `avatar_url` is null:
  - Extract initials from name (e.g., "John Doe" → "JD")
  - Generate background color from user ID hash (consistent per user)
  - Use color palette from design system (avoid orange/blue to prevent confusion with UI accent colors)

**Frontend Utility**:
```javascript
// utils/avatar.js
export function getInitials(name) {
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0][0].toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

export function getBgColor(userId) {
  const colors = ['#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#3B82F6', '#6366F1']
  return colors[userId % colors.length]
}
```

**Example Rendering**:
```vue
<!-- Avatar.vue -->
<div v-if="user.avatar_url" class="avatar">
  <img :src="user.avatar_url" :alt="user.name" />
</div>
<div v-else class="avatar avatar-initials" :style="{ backgroundColor: getBgColor(user.id) }">
  {{ getInitials(user.name) }}
</div>
```

---

## Entity Relationships

```
User (existing)
  ├── 1:N → Projects (as instructor)
  ├── N:M → Projects (as member via project_members)
  └── 1:N → Activities (as actor)

Project (existing)
  ├── N:1 → User (instructor_id)
  ├── N:M → Users (members via project_members)
  ├── 1:N → Boards
  └── 1:N → Activities (as subject)

Board (existing)
  ├── N:1 → Project
  └── 1:N → Columns

Column (existing)
  ├── N:1 → Board
  └── 1:N → Tasks

Task (existing)
  ├── N:1 → Column
  ├── N:1 → User (assignee_id)
  └── 1:N → Activities (as subject)

Activity (existing)
  ├── N:1 → User (actor)
  └── N:1 → Project OR Task (polymorphic subject)

Dashboard Statistics (computed)
  └── Aggregates from Projects, Tasks, Project Members

Search Result (computed)
  └── Queries Projects and Tasks
```

## Frontend Store Structure

### Layout Store (stores/layout.js)

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
      const path = state.currentRoute
      if (path === '/dashboard') return 'dashboard'
      if (path.startsWith('/projects')) return 'projects'
      if (path.startsWith('/tasks')) return 'tasks'
      if (path.startsWith('/team')) return 'team'
      if (path.startsWith('/settings')) return 'settings'
      return null
    }
  },

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      this.persistSidebarState()
    },

    setSidebarCollapsed(value) {
      this.sidebarCollapsed = value
      this.persistSidebarState()
    },

    setCurrentRoute(path) {
      this.currentRoute = path
    },

    initializeLayout() {
      const saved = localStorage.getItem('layout.sidebarCollapsed')
      if (saved !== null) {
        this.sidebarCollapsed = JSON.parse(saved)
      }
      this.updateIsMobile()
      window.addEventListener('resize', this.updateIsMobile)
    },

    updateIsMobile() {
      this.isMobile = window.innerWidth < 768
      if (this.isMobile) {
        this.sidebarCollapsed = true // Auto-collapse on mobile
      }
    },

    persistSidebarState() {
      localStorage.setItem('layout.sidebarCollapsed', JSON.stringify(this.sidebarCollapsed))
    }
  }
})
```

### Dashboard Store (stores/dashboard.js)

```javascript
import { defineStore } from 'pinia'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: {
      total_projects: 0,
      active_tasks: 0,
      team_members: 0,
      overdue_tasks: 0
    },
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
        console.error('Dashboard stats error:', err)
      } finally {
        this.loading = false
      }
    },

    retry() {
      this.fetchStats()
    }
  }
})
```

### Projects Store (stores/projects.js)

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
      this.error = null
      try {
        const response = await axios.get('/api/projects')
        this.projects = response.data.data.projects
      } catch (err) {
        this.error = err.response?.data?.error?.message || 'Failed to load projects'
        console.error('Projects fetch error:', err)
      } finally {
        this.loading = false
      }
    },

    async createProject(data) {
      try {
        const response = await axios.post('/api/projects', data)
        const newProject = response.data.data.project
        this.projects.unshift(newProject) // Add to beginning of list
        return newProject
      } catch (err) {
        const message = err.response?.data?.error?.message || 'Failed to create project'
        console.error('Project create error:', err)
        throw new Error(message)
      }
    },

    async deleteProject(projectId) {
      try {
        await axios.delete(`/api/projects/${projectId}`)
        this.projects = this.projects.filter(p => p.id !== projectId)
      } catch (err) {
        const message = err.response?.data?.error?.message || 'Failed to delete project'
        console.error('Project delete error:', err)
        throw new Error(message)
      }
    }
  }
})
```

## Validation Rules Summary

| Field | Create | Update | Constraints |
|-------|--------|--------|-------------|
| Project title | Required | Optional | 1-100 chars, plain text |
| Project description | Optional | Optional | Max 500 chars, plain text |
| Timeline status | Optional (default: 'On Track') | Optional | Enum: On Track, At Risk, Delayed |
| Budget status | Optional (default: 'Within Budget') | Optional | Enum: Within Budget, Over Budget |
| Search query | Required | N/A | Min 2 chars, max 100 chars |
| Activity limit | Optional (default: 10) | N/A | Integer, max 50 |

## Notes

1. **No database migrations required** - All entities map to existing schema from Phase 1-6
2. **Caching strategy** - Dashboard stats cached for 5 minutes per user on backend
3. **Authorization** - All queries scoped to user's projects (instructor or member)
4. **Soft deletes** - Out of scope; projects are hard-deleted when instructor deletes
5. **Timestamps** - All API responses use ISO 8601 format (e.g., "2026-02-03T15:30:00Z")
6. **Pagination** - Dashboard shows 5 recent projects; full list requires dedicated Projects page (future)
7. **Activity feed** - Optional feature (Priority P4); may be excluded from MVP
