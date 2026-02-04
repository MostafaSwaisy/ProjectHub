# Store Contracts: Modern UI Design System

**Feature**: Modern UI Design System Integration (001-modern-ui)
**Created**: 2026-02-01
**Phase**: Phase 1 - Design Artifacts

## Overview

This document defines the Pinia store schemas for state management in the modern UI design system. All stores use the Composition API style (`setup()` syntax).

---

## 1. Kanban Store

**File**: `resources/js/stores/kanban.js`

**Purpose**: Centralized state management for kanban board tasks, columns, and labels

### State

```typescript
interface KanbanState {
  // Task data
  tasks: Task[]
  // All tasks for current project

  labels: Label[]
  // Available labels for the project

  columns: Column[]
  // Kanban column configuration (static or loaded from API)

  // UI state
  isLoading: boolean
  // True when fetching tasks from API

  error: string | null
  // Error message from last failed operation

  currentProjectId: number | null
  // Currently loaded project ID

  // Filter state
  searchQuery: string
  // Current search/filter query

  selectedLabels: string[]
  // Label IDs to filter by

  selectedPriorities: string[]
  // Priority levels to filter by

  selectedAssignees: number[]
  // User IDs to filter by
}
```

### Getters

```typescript
interface KanbanGetters {
  // Task queries
  getTasksByColumn: (state: KanbanState) => (columnId: string) => Task[]
  // Returns tasks for a specific column, filtered by search/filters

  getTaskById: (state: KanbanState) => (taskId: number) => Task | undefined
  // Returns a single task by ID

  // Statistics
  stats: (state: KanbanState) => BoardStats
  // Computes total, completed, inProgress, overdue counts

  // Filtered tasks
  filteredTasks: (state: KanbanState) => Task[]
  // Tasks filtered by searchQuery, selectedLabels, selectedPriorities, selectedAssignees

  // Task by status
  tasksByStatus: (state: KanbanState) => Record<string, Task[]>
  // Tasks grouped by column ID

  // Loading state
  isInitialized: (state: KanbanState) => boolean
  // True if tasks have been loaded at least once
}
```

### Actions

```typescript
interface KanbanActions {
  // Data fetching
  fetchTasks(projectId: number): Promise<void>
  // GET /api/projects/{projectId}/tasks
  // Loads all tasks and updates state.tasks

  fetchLabels(projectId: number): Promise<void>
  // GET /api/projects/{projectId}/labels
  // Loads available labels

  // Task CRUD
  createTask(taskData: Partial<Task>): Promise<Task>
  // POST /api/tasks
  // Creates new task and adds to state.tasks

  updateTask(taskId: number, updates: Partial<Task>): Promise<Task>
  // PUT /api/tasks/{taskId}
  // Updates task and refreshes state

  deleteTask(taskId: number): Promise<void>
  // DELETE /api/tasks/{taskId}
  // Removes task from state.tasks

  // Specialized task actions
  moveTask(taskId: number, newColumnId: string, newPosition?: number): Promise<void>
  // PUT /api/tasks/{taskId}
  // Updates task status/column and position

  duplicateTask(taskId: number): Promise<Task>
  // POST /api/tasks (copies data from existing task)
  // Creates duplicate and adds to state.tasks

  archiveTask(taskId: number): Promise<void>
  // PUT /api/tasks/{taskId} with status='archived'
  // Moves task to archived column

  moveToTop(taskId: number): Promise<void>
  // PUT /api/tasks/{taskId} with position=0
  // Moves task to top of its column

  // Filter actions
  setSearchQuery(query: string): void
  // Updates state.searchQuery (client-side only)

  setLabelFilter(labelIds: string[]): void
  // Updates state.selectedLabels

  setPriorityFilter(priorities: string[]): void
  // Updates state.selectedPriorities

  setAssigneeFilter(assigneeIds: number[]): void
  // Updates state.selectedAssignees

  clearFilters(): void
  // Resets all filter state

  // Utility
  refreshTasks(): Promise<void>
  // Re-fetches tasks for current project

  reset(): void
  // Resets store to initial state
}
```

### Usage Example

```typescript
import { useKanbanStore } from '@/stores/kanban'

const kanbanStore = useKanbanStore()

// Load tasks
await kanbanStore.fetchTasks(projectId)

// Get tasks for a column
const todoTasks = kanbanStore.getTasksByColumn('todo')

// Create task
await kanbanStore.createTask({
  title: 'New task',
  description: 'Task description',
  priority: 'high',
  status: 'todo'
})

// Move task
await kanbanStore.moveTask(taskId, 'in-progress')

// Filter tasks
kanbanStore.setSearchQuery('bug')
kanbanStore.setLabelFilter(['bug', 'urgent'])

// Get filtered results
const filtered = kanbanStore.filteredTasks
```

---

## 2. Tasks Store (Alternative/Simplified)

**File**: `resources/js/stores/tasks.js`

**Purpose**: Simplified store focused only on task data (if kanban store is too complex)

### State

```typescript
interface TasksState {
  tasks: Task[]
  isLoading: boolean
  error: string | null
}
```

### Getters

```typescript
interface TasksGetters {
  getTaskById: (state: TasksState) => (id: number) => Task | undefined
  taskCount: (state: TasksState) => number
}
```

### Actions

```typescript
interface TasksActions {
  fetchTasks(projectId: number): Promise<void>
  addTask(task: Task): void  // Optimistic update
  updateTask(taskId: number, updates: Partial<Task>): void
  removeTask(taskId: number): void
}
```

**Note**: This is a simpler alternative if the full kanban store is overkill. Choose one approach, not both.

---

## 3. Auth Store

**File**: `resources/js/stores/auth.js`

**Purpose**: User authentication state management

### State

```typescript
interface AuthState {
  user: {
    id: number
    name: string
    email: string
    avatar: string | null
  } | null
  // Current authenticated user

  isAuthenticated: boolean
  // True if user is logged in

  isLoading: boolean
  // True during auth operations

  error: string | null
  // Last authentication error
}
```

### Getters

```typescript
interface AuthGetters {
  currentUser: (state: AuthState) => User | null
  // Returns current user or null

  isLoggedIn: (state: AuthState) => boolean
  // Alias for isAuthenticated

  userInitials: (state: AuthState) => string
  // Returns user initials for avatar (e.g., "JD" for "John Doe")
}
```

### Actions

```typescript
interface AuthActions {
  // Authentication
  login(credentials: { email: string, password: string, remember: boolean }): Promise<void>
  // POST /api/login
  // Sets user and isAuthenticated on success

  register(data: { name: string, email: string, password: string, password_confirmation: string }): Promise<void>
  // POST /api/register
  // Registers and logs in user

  logout(): Promise<void>
  // POST /api/logout
  // Clears user state

  // Session management
  fetchUser(): Promise<void>
  // GET /api/user
  // Loads current user (for page refresh)

  forgotPassword(email: string): Promise<void>
  // POST /api/forgot-password
  // Sends reset link email

  resetPassword(data: { token: string, email: string, password: string, password_confirmation: string }): Promise<void>
  // POST /api/reset-password
  // Resets password with token

  // Utility
  clearError(): void
  // Clears error state
}
```

### Usage Example

```typescript
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Login
await authStore.login({
  email: 'user@example.com',
  password: 'password123',
  remember: true
})

// Check auth status
if (authStore.isAuthenticated) {
  console.log(`Welcome, ${authStore.currentUser.name}`)
}

// Logout
await authStore.logout()
```

---

## 4. UI Store (Optional)

**File**: `resources/js/stores/ui.js`

**Purpose**: Global UI state (modals, notifications, theme preferences)

### State

```typescript
interface UIState {
  // Modal state
  modals: {
    taskCreate: boolean
    taskEdit: boolean
    taskDetail: boolean
  }

  // Notification/toast state
  notifications: Array<{
    id: string
    type: 'success' | 'error' | 'warning' | 'info'
    message: string
    duration: number  // milliseconds
  }>

  // Sidebar state
  sidebarCollapsed: boolean

  // Theme preferences
  preferences: {
    reducedMotion: boolean
    theme: 'dark' | 'light'  // Currently only dark supported
  }
}
```

### Getters

```typescript
interface UIGetters {
  activeNotifications: (state: UIState) => Notification[]
  isAnyModalOpen: (state: UIState) => boolean
}
```

### Actions

```typescript
interface UIActions {
  // Modal management
  openModal(modalName: keyof UIState['modals']): void
  closeModal(modalName: keyof UIState['modals']): void
  closeAllModals(): void

  // Notification management
  showNotification(notification: { type: string, message: string, duration?: number }): string
  // Returns notification ID

  dismissNotification(id: string): void

  // Preferences
  toggleSidebar(): void
  setReducedMotion(value: boolean): void
  savePreferences(): void  // Persist to localStorage
  loadPreferences(): void  // Load from localStorage
}
```

### Usage Example

```typescript
import { useUIStore } from '@/stores/ui'

const uiStore = useUIStore()

// Show notification
uiStore.showNotification({
  type: 'success',
  message: 'Task created successfully',
  duration: 3000
})

// Open modal
uiStore.openModal('taskCreate')

// Toggle sidebar
uiStore.toggleSidebar()
```

---

## 5. Labels Store (Optional)

**File**: `resources/js/stores/labels.js`

**Purpose**: Separate store for label management (if labels need CRUD operations)

### State

```typescript
interface LabelsState {
  labels: Label[]
  isLoading: boolean
  error: string | null
}
```

### Getters

```typescript
interface LabelsGetters {
  getLabelById: (state: LabelsState) => (id: string) => Label | undefined
  sortedLabels: (state: LabelsState) => Label[]  // Sorted alphabetically
}
```

### Actions

```typescript
interface LabelsActions {
  fetchLabels(projectId: number): Promise<void>
  // GET /api/projects/{projectId}/labels

  createLabel(labelData: { name: string, color: string }): Promise<Label>
  // POST /api/labels

  updateLabel(labelId: string, updates: Partial<Label>): Promise<Label>
  // PUT /api/labels/{labelId}

  deleteLabel(labelId: string): Promise<void>
  // DELETE /api/labels/{labelId}
}
```

**Note**: Only create this if labels need full CRUD. Otherwise, keep labels in kanban store.

---

## 6. Store Composition Patterns

### Pattern 1: Using Multiple Stores in a Component

```vue
<script setup>
import { useKanbanStore } from '@/stores/kanban'
import { useAuthStore } from '@/stores/auth'
import { useUIStore } from '@/stores/ui'

const kanbanStore = useKanbanStore()
const authStore = useAuthStore()
const uiStore = useUIStore()

// Access state across stores
const currentUser = computed(() => authStore.currentUser)
const tasks = computed(() => kanbanStore.tasks)

async function createTask(taskData) {
  try {
    await kanbanStore.createTask(taskData)
    uiStore.showNotification({ type: 'success', message: 'Task created!' })
  } catch (error) {
    uiStore.showNotification({ type: 'error', message: error.message })
  }
}
</script>
```

### Pattern 2: Store-to-Store Communication

```typescript
// kanban.js
import { useAuthStore } from './auth'

export const useKanbanStore = defineStore('kanban', () => {
  async function createTask(taskData) {
    const authStore = useAuthStore()

    // Automatically set assignee to current user if not specified
    if (!taskData.assigneeId && authStore.currentUser) {
      taskData.assigneeId = authStore.currentUser.id
    }

    // ...rest of create logic
  }

  return { createTask }
})
```

### Pattern 3: Persisting Store State

```typescript
// kanban.js with localStorage persistence
import { watch } from 'vue'

export const useKanbanStore = defineStore('kanban', () => {
  const searchQuery = ref('')
  const selectedLabels = ref([])

  // Load from localStorage on init
  const savedFilters = localStorage.getItem('kanban_filters')
  if (savedFilters) {
    const parsed = JSON.parse(savedFilters)
    searchQuery.value = parsed.searchQuery || ''
    selectedLabels.value = parsed.selectedLabels || []
  }

  // Save to localStorage on change
  watch([searchQuery, selectedLabels], () => {
    localStorage.setItem('kanban_filters', JSON.stringify({
      searchQuery: searchQuery.value,
      selectedLabels: selectedLabels.value
    }))
  })

  return { searchQuery, selectedLabels }
})
```

---

## 7. Store Initialization

**File**: `resources/js/app.js`

```typescript
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)
app.mount('#app')
```

**Initialize auth on app load**:

```typescript
// resources/js/router/index.js
import { useAuthStore } from '@/stores/auth'

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Load user on first navigation
  if (!authStore.isAuthenticated && to.meta.requiresAuth) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      return next({ name: 'login' })
    }
  }

  next()
})
```

---

## 8. API Integration Layer

**File**: `resources/js/api/kanban.js`

**Purpose**: Axios wrappers for kanban API calls (used by stores)

```typescript
import axios from 'axios'

export const kanbanAPI = {
  // Tasks
  getTasks(projectId: number) {
    return axios.get(`/api/projects/${projectId}/tasks`)
  },

  createTask(taskData: Partial<Task>) {
    return axios.post('/api/tasks', taskData)
  },

  updateTask(taskId: number, updates: Partial<Task>) {
    return axios.put(`/api/tasks/${taskId}`, updates)
  },

  deleteTask(taskId: number) {
    return axios.delete(`/api/tasks/${taskId}`)
  },

  // Labels
  getLabels(projectId: number) {
    return axios.get(`/api/projects/${projectId}/labels`)
  },

  createLabel(labelData: { name: string, color: string }) {
    return axios.post('/api/labels', labelData)
  }
}
```

**File**: `resources/js/api/auth.js`

```typescript
import axios from 'axios'

export const authAPI = {
  login(credentials: { email: string, password: string, remember: boolean }) {
    return axios.post('/api/login', credentials)
  },

  register(data: RegisterData) {
    return axios.post('/api/register', data)
  },

  logout() {
    return axios.post('/api/logout')
  },

  getUser() {
    return axios.get('/api/user')
  },

  forgotPassword(email: string) {
    return axios.post('/api/forgot-password', { email })
  },

  resetPassword(data: ResetPasswordData) {
    return axios.post('/api/reset-password', data)
  }
}
```

---

## 9. Store Testing Examples

### Testing Kanban Store

```typescript
// tests/unit/stores/kanban.test.js
import { setActivePinia, createPinia } from 'pinia'
import { useKanbanStore } from '@/stores/kanban'
import { vi } from 'vitest'

describe('Kanban Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('fetches tasks successfully', async () => {
    const store = useKanbanStore()
    const mockTasks = [{ id: 1, title: 'Test Task' }]

    vi.spyOn(kanbanAPI, 'getTasks').mockResolvedValue({ data: mockTasks })

    await store.fetchTasks(1)

    expect(store.tasks).toEqual(mockTasks)
    expect(store.isLoading).toBe(false)
  })

  it('filters tasks by search query', () => {
    const store = useKanbanStore()
    store.tasks = [
      { id: 1, title: 'Fix bug' },
      { id: 2, title: 'Add feature' }
    ]

    store.setSearchQuery('bug')

    expect(store.filteredTasks).toHaveLength(1)
    expect(store.filteredTasks[0].title).toBe('Fix bug')
  })
})
```

---

## Summary

**Recommended Stores**:
1. ✅ **kanban** - Core store (tasks, columns, labels, filters)
2. ✅ **auth** - User authentication
3. ⚠️ **ui** - Optional (global UI state, notifications)
4. ❌ **tasks** - Skip (use kanban store instead)
5. ❌ **labels** - Skip (keep in kanban store unless CRUD needed)

**Key Decisions**:
- Use Composition API style (`setup()` with `ref()`, `computed()`)
- Centralize API calls in `/api/*.js` modules
- Use TypeScript interfaces for type safety
- Implement optimistic updates for better UX
- Persist filters/preferences to localStorage
- Use Pinia devtools for debugging

**Next**: Create `quickstart.md` with development and testing guide
