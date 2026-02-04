# Data Model: Modern UI Design System Integration

**Feature**: Modern UI Design System Integration (001-modern-ui)
**Created**: 2026-02-01
**Phase**: Phase 1 - Design Artifacts

## Overview

This document defines the data structures used by the modern UI components. Since this is a frontend-only integration with no backend changes, this focuses on:
1. **Component State Models** - Vue component reactive state
2. **Data Transformation Models** - Mapping between API responses and UI components
3. **UI State Models** - Ephemeral client-side state for interactions

---

## 1. Core Entities (Backend → Frontend Mapping)

### 1.1 Task

**Source**: Laravel API `/api/projects/{id}/tasks`

**API Response Structure** (from Laravel):
```typescript
interface TaskAPIResponse {
  id: number
  column_id: number
  title: string
  description: string | null
  assignee_id: number | null
  priority: 'low' | 'medium' | 'high' | 'urgent'
  due_date: string | null  // ISO 8601 date
  position: number
  created_at: string
  updated_at: string

  // Relationships (eager loaded)
  column: {
    id: number
    name: string  // "Backlog", "To Do", "In Progress", "Review", "Done", "Archived"
    color: string
    position: number
  }
  assignee: {
    id: number
    name: string
    email: string
    avatar: string | null
  } | null
  labels: Array<{
    id: number
    name: string
    color: string
  }>
  subtasks: Array<{
    id: number
    title: string
    is_completed: boolean
  }>

  // Computed attributes
  progress: number  // 0-100 percentage
  completed_subtask_count: number
  is_overdue: boolean
}
```

**Frontend Component Structure** (for Vue components):
```typescript
interface Task {
  id: number
  title: string
  description: string
  status: 'backlog' | 'todo' | 'in-progress' | 'review' | 'done' | 'archived'
  priority: 'low' | 'medium' | 'high' | 'urgent'
  dueDate: string | null  // ISO 8601
  assignee: {
    id: number
    name: string
    avatar: string  // URL or Dicebear generated
  } | null
  labels: Array<{
    id: string  // Stringified number for component keys
    name: string
    color: string  // Hex color
  }>
  subtasks: Array<{
    id: number
    title: string
    completed: boolean
  }>
  progress?: number  // 0-100, only if subtasks exist
  isOverdue?: boolean
  position: number
}
```

**Transformation Function**: `mapTaskFromAPI()` in `resources/js/composables/useTaskMapping.js`

**Field Mappings**:
| API Field | Component Field | Transformation |
|-----------|----------------|----------------|
| `id` | `id` | Direct |
| `title` | `title` | Direct |
| `description` | `description` | Direct (null → empty string) |
| `column.name` | `status` | Lowercase, replace spaces with hyphens |
| `priority` | `priority` | Direct |
| `due_date` | `dueDate` | Direct |
| `assignee` | `assignee` | Extract id, name, avatar (generate if null) |
| `labels` | `labels` | Map array, stringify IDs |
| `subtasks` | `subtasks` | Rename `is_completed` → `completed` |
| `progress` | `progress` | Direct (if > 0) |
| `is_overdue` | `isOverdue` | Direct |
| `position` | `position` | Direct |

---

### 1.2 Column

**Source**: Hardcoded configuration (based on existing Column model)

**Structure**:
```typescript
interface Column {
  id: string  // 'backlog', 'todo', 'in-progress', 'review', 'done', 'archived'
  title: string  // Display name
  color: string  // Hex color for indicator dot
  tasks: Task[]  // Filtered tasks for this column
}
```

**Default Configuration**:
```javascript
const columns = [
  { id: 'backlog', title: 'Backlog', color: '#6B7280' },      // Gray
  { id: 'todo', title: 'To Do', color: '#3B82F6' },           // Blue
  { id: 'in-progress', title: 'In Progress', color: '#FF6B35' }, // Orange
  { id: 'review', title: 'Review', color: '#8B5CF6' },        // Purple
  { id: 'done', title: 'Done', color: '#22C55E' },            // Green
  { id: 'archived', title: 'Archived', color: '#475569' }     // Dark gray
]
```

**Usage**: Static configuration in Kanban.vue, tasks are filtered client-side by status

---

### 1.3 Label

**Source**: Laravel API `/api/labels` or eager loaded with tasks

**API Response**:
```typescript
interface LabelAPIResponse {
  id: number
  name: string
  color: string  // Hex color
  created_at: string
  updated_at: string
}
```

**Frontend Structure**:
```typescript
interface Label {
  id: string  // Stringified for v-for keys
  name: string
  color: string  // Hex color
}
```

**Predefined Labels** (if creating new labels):
```javascript
const defaultLabels = [
  { id: 'bug', name: 'Bug', color: '#EF4444' },          // Red
  { id: 'feature', name: 'Feature', color: '#3B82F6' },  // Blue
  { id: 'enhancement', name: 'Enhancement', color: '#8B5CF6' }, // Purple
  { id: 'documentation', name: 'Docs', color: '#6B7280' }, // Gray
  { id: 'design', name: 'Design', color: '#EC4899' },    // Pink
  { id: 'backend', name: 'Backend', color: '#22C55E' },  // Green
  { id: 'frontend', name: 'Frontend', color: '#F59E0B' } // Amber
]
```

---

### 1.4 User (Team Member)

**Source**: Laravel API (eager loaded with tasks or `/api/users`)

**API Response**:
```typescript
interface UserAPIResponse {
  id: number
  name: string
  email: string
  avatar: string | null
}
```

**Frontend Structure**:
```typescript
interface User {
  id: number
  name: string
  avatar: string  // URL or generated avatar
}
```

**Avatar Generation** (if null):
```javascript
function generateAvatar(name: string): string {
  return `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(name)}`
}
```

---

### 1.5 Board Statistics

**Source**: Computed from tasks array client-side

**Structure**:
```typescript
interface BoardStats {
  total: number       // Total tasks (excluding archived)
  completed: number   // Tasks in "done" status
  inProgress: number  // Tasks in "in-progress" status
  overdue: number     // Tasks with isOverdue = true
}
```

**Computation**:
```javascript
const stats = computed(() => ({
  total: tasks.value.filter(t => t.status !== 'archived').length,
  completed: tasks.value.filter(t => t.status === 'done').length,
  inProgress: tasks.value.filter(t => t.status === 'in-progress').length,
  overdue: tasks.value.filter(t => t.isOverdue).length
}))
```

---

## 2. Component State Models

### 2.1 KanbanBoard Component State

**File**: `resources/js/pages/projects/KanbanView.vue`

**State Structure**:
```typescript
interface KanbanBoardState {
  // Data
  tasks: Ref<Task[]>
  columns: Column[]
  availableLabels: Ref<Label[]>
  teamMembers: Ref<User[]>

  // UI State
  isLoading: Ref<boolean>
  searchQuery: Ref<string>
  selectedTask: Ref<Task | null>  // For task detail modal
  showTaskModal: Ref<boolean>
  editingTask: Ref<Task | null>

  // Drag & Drop State
  draggedTask: Ref<Task | null>
  dragOverColumn: Ref<string | null>

  // Computed
  filteredTasks: ComputedRef<Task[]>  // Filtered by search
  boardStats: ComputedRef<BoardStats>
}
```

**Pinia Store** (`resources/js/stores/kanban.js`):
```typescript
interface KanbanStore {
  // State
  tasks: Task[]
  labels: Label[]
  columns: Column[]

  // Getters
  getTasksByColumn: (columnId: string) => Task[]
  getTaskById: (taskId: number) => Task | undefined
  stats: BoardStats

  // Actions
  fetchTasks(projectId: number): Promise<void>
  createTask(task: Partial<Task>): Promise<void>
  updateTask(taskId: number, updates: Partial<Task>): Promise<void>
  deleteTask(taskId: number): Promise<void>
  moveTask(taskId: number, newColumnId: string): Promise<void>
  duplicateTask(taskId: number): Promise<void>
  archiveTask(taskId: number): Promise<void>
}
```

---

### 2.2 TaskCard Component State

**File**: `resources/js/components/kanban/TaskCard.vue`

**Props**:
```typescript
interface TaskCardProps {
  task: Task           // Required
  labels: Label[]      // For rendering label badges
  isDragging?: boolean // From parent drag state
}
```

**Local State**:
```typescript
interface TaskCardLocalState {
  showMenu: Ref<boolean>
  menuPosition: Reactive<{
    position: string  // 'fixed'
    top: string      // '0px'
    left: string     // '0px'
    zIndex: number   // 9999
  }>
  menuButton: Ref<HTMLElement | null>  // Template ref
}
```

**Emits**:
```typescript
interface TaskCardEmits {
  dragstart: (event: DragEvent, task: Task) => void
  click: (task: Task) => void
  edit: (task: Task) => void
  delete: (taskId: number) => void
  archive: (taskId: number) => void
  duplicate: (taskId: number) => void
  moveToTop: (taskId: number) => void
}
```

**Computed Properties**:
```typescript
interface TaskCardComputed {
  assignee: ComputedRef<User | null>
  isOverdue: ComputedRef<boolean>
  formatDueDate: ComputedRef<string>  // "Today", "Tomorrow", "3 days ago"
  visibleLabels: ComputedRef<Label[]>  // First 3 labels
  remainingLabelsCount: ComputedRef<number>  // For "+N more"
  priorityClass: ComputedRef<string>  // CSS class for border color
  showProgress: ComputedRef<boolean>  // Has subtasks
}
```

---

### 2.3 TaskModal Component State

**File**: `resources/js/components/kanban/TaskModal.vue`

**Props**:
```typescript
interface TaskModalProps {
  task: Task | null    // null = create new, object = edit existing
  isOpen: boolean
  availableLabels: Label[]
  teamMembers: User[]
}
```

**Local State**:
```typescript
interface TaskModalState {
  // Form fields (v-model)
  form: Reactive<{
    title: string
    description: string
    priority: 'low' | 'medium' | 'high' | 'urgent'
    dueDate: string | null
    assigneeId: number | null
    labelIds: string[]
    subtasks: Array<{ title: string, completed: boolean }>
  }>

  // UI state
  isSubmitting: Ref<boolean>
  errors: Ref<Record<string, string>>
  showLabelPicker: Ref<boolean>
}
```

**Emits**:
```typescript
interface TaskModalEmits {
  close: () => void
  save: (task: Partial<Task>) => Promise<void>
}
```

---

### 2.4 Auth Page Component State

**Files**:
- `resources/js/pages/auth/Login.vue`
- `resources/js/pages/auth/Register.vue`
- `resources/js/pages/auth/ForgotPassword.vue`
- `resources/js/pages/auth/ResetPassword.vue`

**Login State**:
```typescript
interface LoginState {
  form: Reactive<{
    email: string
    password: string
    remember: boolean
  }>
  isSubmitting: Ref<boolean>
  errors: Ref<Record<string, string>>
}
```

**Register State**:
```typescript
interface RegisterState {
  form: Reactive<{
    name: string
    email: string
    password: string
    password_confirmation: string
  }>
  isSubmitting: Ref<boolean>
  errors: Ref<Record<string, string>>
  passwordStrength: ComputedRef<{
    score: number  // 0-4
    label: string  // "Weak", "Fair", "Good", "Strong"
    color: string  // CSS color
  }>
}
```

---

## 3. UI State Models (Ephemeral)

### 3.1 Drag & Drop State

**Composable**: `resources/js/composables/useDragDrop.js`

```typescript
interface DragDropState {
  draggedTask: Ref<Task | null>
  dragOverColumn: Ref<string | null>
  isDragging: ComputedRef<boolean>
}

interface DragDropHandlers {
  handleDragStart: (event: DragEvent, task: Task) => void
  handleDragEnd: (event: DragEvent) => void
  handleDragOver: (event: DragEvent, columnId: string) => void
  handleDrop: (event: DragEvent, columnId: string, onMove: (taskId: number, newColumn: string) => void) => void
}
```

---

### 3.2 Animation State

**Composable**: `resources/js/composables/useAnimation.js`

```typescript
interface AnimationState {
  prefersReducedMotion: Ref<boolean>
  shouldAnimate: ComputedRef<boolean>
}

interface AnimationHelpers {
  getTransitionDuration: (type: 'fast' | 'normal' | 'slow') => string
  shouldPlayAnimation: (animationType: 'continuous' | 'triggered') => boolean
}
```

---

### 3.3 Responsive State

**Composable**: `resources/js/composables/useResponsive.js`

```typescript
interface ResponsiveState {
  screenWidth: Ref<number>
  isMobile: ComputedRef<boolean>      // < 768px
  isTablet: ComputedRef<boolean>      // 768px - 1024px
  isDesktop: ComputedRef<boolean>     // > 1024px
  breakpoint: ComputedRef<'mobile' | 'tablet' | 'desktop'>
}
```

---

## 4. Data Transformation Layer

### 4.1 Task Mapping

**Composable**: `resources/js/composables/useTaskMapping.js`

**Functions**:
```typescript
// API → Component
function mapTaskFromAPI(apiTask: TaskAPIResponse): Task

// Component → API (for updates)
function mapTaskToAPI(task: Task): Partial<TaskAPIResponse>

// Batch mapping
function mapTasksFromAPI(apiTasks: TaskAPIResponse[]): Task[]
```

**Implementation Details**:
- Normalize column names to status IDs (slugify)
- Generate avatars if missing
- Stringify label IDs for component keys
- Rename subtask field `is_completed` → `completed`
- Compute `isOverdue` client-side if not provided

---

### 4.2 Date Formatting

**Helper**: `resources/js/utils/dateHelpers.js`

```typescript
interface DateHelpers {
  formatDueDate: (date: string | null) => string
  // "Today", "Tomorrow", "Yesterday", "In 3 days", "3 days ago", "Jan 15"

  isOverdue: (dueDate: string | null, status: string) => boolean
  // Check if past due (excluding "done" status)

  isDueSoon: (dueDate: string | null) => boolean
  // Within 2 days
}
```

---

## 5. Validation Rules

### 5.1 Task Form Validation

```typescript
interface TaskValidationRules {
  title: {
    required: true
    minLength: 1
    maxLength: 255
  }
  description: {
    required: false
    maxLength: 5000
  }
  priority: {
    required: true
    enum: ['low', 'medium', 'high', 'urgent']
  }
  dueDate: {
    required: false
    format: 'YYYY-MM-DD'
    minDate: 'today'  // Optional: prevent past dates
  }
  assigneeId: {
    required: false
    type: 'number'
  }
  labelIds: {
    required: false
    type: 'array'
    items: 'string'
  }
}
```

### 5.2 Auth Form Validation

**Login**:
```typescript
{
  email: { required: true, email: true },
  password: { required: true, minLength: 8 }
}
```

**Register**:
```typescript
{
  name: { required: true, minLength: 2, maxLength: 255 },
  email: { required: true, email: true },
  password: { required: true, minLength: 8 },
  password_confirmation: { required: true, sameAs: 'password' }
}
```

---

## 6. Local Storage Schema

### 6.1 User Preferences

**Key**: `projecthub_preferences`

```typescript
interface UserPreferences {
  theme: 'dark' | 'light'  // Currently only dark supported
  reducedMotion: boolean   // Override system preference
  kanbanView: {
    columnWidths: Record<string, number>  // Saved column widths
    hiddenColumns: string[]  // Collapsed columns
  }
}
```

**Usage**: Restore user's kanban board preferences on page load

---

## 7. API Endpoints (No Changes)

**Existing endpoints used by components**:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/projects/{id}/tasks` | Fetch all tasks for kanban board |
| POST | `/api/tasks` | Create new task |
| PUT | `/api/tasks/{id}` | Update task (including status/column change) |
| DELETE | `/api/tasks/{id}` | Delete task |
| GET | `/api/labels` | Fetch available labels |
| GET | `/api/users` | Fetch team members for assignee dropdown |

**No new endpoints needed** - all data is available through existing API

---

## 8. Data Flow Diagram

```
┌─────────────────┐
│  Laravel API    │
│  (Existing)     │
└────────┬────────┘
         │
         │ JSON Response
         ▼
┌─────────────────────────┐
│  useTaskMapping.js      │
│  (Data Transformation)  │
└────────┬────────────────┘
         │
         │ Normalized Task[]
         ▼
┌─────────────────────────┐
│  Pinia Store (kanban)   │
│  (State Management)     │
└────────┬────────────────┘
         │
         │ Reactive State
         ▼
┌─────────────────────────┐
│  Vue Components         │
│  (KanbanBoard, TaskCard)│
└─────────────────────────┘
         │
         │ User Actions
         ▼
┌─────────────────────────┐
│  API Calls (Axios)      │
└────────┬────────────────┘
         │
         │ Update Request
         ▼
     Laravel API
```

---

## 9. Component Data Dependencies

```
KanbanView.vue
├── Requires: tasks[], labels[], teamMembers[]
├── Provides: draggedTask, selectedTask
└── Children:
    ├── BoardHeader.vue
    │   ├── Requires: stats, searchQuery
    │   └── Emits: search, addTask
    ├── BoardStats.vue
    │   └── Requires: stats
    ├── KanbanColumn.vue (×6)
    │   ├── Requires: column, tasks[], dragOverColumn
    │   ├── Emits: drop, dragOver
    │   └── Children:
    │       └── TaskCard.vue (×N)
    │           ├── Requires: task, labels[], isDragging
    │           └── Emits: dragstart, click, edit, delete, archive, duplicate
    └── TaskModal.vue
        ├── Requires: task, isOpen, labels[], teamMembers[]
        └── Emits: close, save
```

---

## Summary

**Data Architecture Principles**:
1. ✅ **No Backend Changes** - All data available through existing API
2. ✅ **Frontend Mapping Layer** - Transform API responses to match UI needs
3. ✅ **Pinia for State Management** - Centralized task/label/user data
4. ✅ **Composables for Reusable Logic** - Drag-drop, animations, responsive
5. ✅ **Props-Down, Events-Up** - Unidirectional data flow
6. ✅ **Computed Properties** - Derived state (stats, filtering, formatting)
7. ✅ **Local Storage for Preferences** - Persist UI customizations

**Key Files to Create**:
- `resources/js/composables/useTaskMapping.js` - Data transformation
- `resources/js/stores/kanban.js` - Pinia store
- `resources/js/utils/dateHelpers.js` - Date formatting utilities

**Next Phase**: Generate contracts/ with detailed component prop interfaces
