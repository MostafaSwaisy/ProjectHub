# Component Contracts: Modern UI Design System

**Feature**: Modern UI Design System Integration (001-modern-ui)
**Created**: 2026-02-01
**Phase**: Phase 1 - Design Artifacts

## Overview

This document defines the component interfaces (props, emits, slots, exposed methods) for all Vue components in the modern UI design system.

---

## 1. Shared Components (`resources/js/components/shared/`)

### 1.1 Button.vue

**Purpose**: Reusable button component with design system styling

**Props**:
```typescript
interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger'
  // default: 'primary'
  // Controls button color scheme and styling

  size?: 'sm' | 'md' | 'lg'
  // default: 'md'
  // Controls padding and font size

  loading?: boolean
  // default: false
  // Shows spinner and disables button

  disabled?: boolean
  // default: false
  // Disables button interaction

  fullWidth?: boolean
  // default: false
  // Makes button width 100%

  type?: 'button' | 'submit' | 'reset'
  // default: 'button'
  // HTML button type attribute
}
```

**Emits**:
```typescript
{
  click: (event: MouseEvent) => void
  // Emitted when button is clicked (if not disabled/loading)
}
```

**Slots**:
```typescript
{
  default: () => VNode[]
  // Button content (text, icons, etc.)

  icon?: () => VNode
  // Optional icon before button text
}
```

**Usage**:
```vue
<Button variant="primary" :loading="isSubmitting" @click="handleSubmit">
  Sign In
</Button>
```

---

### 1.2 Input.vue

**Purpose**: Reusable input field with design system styling and validation

**Props**:
```typescript
interface InputProps {
  modelValue: string | number
  // v-model binding

  type?: 'text' | 'email' | 'password' | 'number' | 'date' | 'search'
  // default: 'text'
  // HTML input type

  label?: string
  // Optional label text

  placeholder?: string
  // Placeholder text

  error?: string
  // Error message to display below input

  disabled?: boolean
  // default: false

  required?: boolean
  // default: false
  // Shows asterisk in label

  autocomplete?: string
  // HTML autocomplete attribute

  icon?: string
  // Icon name to display (left side)

  clearable?: boolean
  // default: false
  // Shows clear button when has value
}
```

**Emits**:
```typescript
{
  'update:modelValue': (value: string | number) => void
  // v-model update

  blur: (event: FocusEvent) => void
  focus: (event: FocusEvent) => void
  clear: () => void
  // When clear button clicked
}
```

**Slots**:
```typescript
{
  'prepend-icon'?: () => VNode
  // Custom icon before input

  'append-icon'?: () => VNode
  // Custom icon after input
}
```

**Usage**:
```vue
<Input
  v-model="form.email"
  type="email"
  label="Email Address"
  placeholder="you@example.com"
  :error="errors.email"
  required
/>
```

---

### 1.3 Modal.vue

**Purpose**: Reusable modal dialog with glassmorphic styling

**Props**:
```typescript
interface ModalProps {
  isOpen: boolean
  // Controls modal visibility

  title?: string
  // Modal header title

  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
  // default: 'md'
  // Modal width (sm: 400px, md: 600px, lg: 800px, xl: 1000px, full: 90vw)

  closeOnClickOutside?: boolean
  // default: true
  // Close modal when clicking backdrop

  closeOnEsc?: boolean
  // default: true
  // Close modal on ESC key press

  showClose?: boolean
  // default: true
  // Show X close button in header

  persistent?: boolean
  // default: false
  // Prevents closing (overrides closeOnClickOutside and closeOnEsc)
}
```

**Emits**:
```typescript
{
  close: () => void
  // Emitted when modal requests to close

  'update:isOpen': (value: boolean) => void
  // v-model:isOpen support
}
```

**Slots**:
```typescript
{
  default: () => VNode[]
  // Modal body content

  header?: () => VNode[]
  // Custom header (replaces title)

  footer?: () => VNode[]
  // Modal footer (typically for action buttons)
}
```

**Usage**:
```vue
<Modal
  v-model:isOpen="showTaskModal"
  title="Create Task"
  size="lg"
  @close="handleClose"
>
  <template #default>
    <TaskForm v-model="form" />
  </template>
  <template #footer>
    <Button variant="ghost" @click="handleClose">Cancel</Button>
    <Button variant="primary" @click="handleSave">Save</Button>
  </template>
</Modal>
```

---

### 1.4 Dropdown.vue

**Purpose**: Dropdown menu for actions and options

**Props**:
```typescript
interface DropdownProps {
  position?: 'bottom-left' | 'bottom-right' | 'top-left' | 'top-right'
  // default: 'bottom-right'
  // Dropdown menu position relative to trigger

  disabled?: boolean
  // default: false
}
```

**Emits**:
```typescript
{
  open: () => void
  close: () => void
}
```

**Slots**:
```typescript
{
  trigger: () => VNode
  // Element that triggers dropdown (usually a button)

  default: () => VNode[]
  // Dropdown menu content (typically DropdownItem components)
}
```

**Usage**:
```vue
<Dropdown position="bottom-right">
  <template #trigger>
    <button class="three-dots-btn">⋮</button>
  </template>
  <template #default>
    <DropdownItem @click="handleEdit">Edit</DropdownItem>
    <DropdownItem @click="handleDuplicate">Duplicate</DropdownItem>
    <DropdownItem @click="handleArchive">Archive</DropdownItem>
    <DropdownItem variant="danger" @click="handleDelete">Delete</DropdownItem>
  </template>
</Dropdown>
```

---

### 1.5 AnimatedBackground.vue

**Purpose**: Animated background orbs for auth pages

**Props**:
```typescript
interface AnimatedBackgroundProps {
  orbCount?: number
  // default: 3
  // Number of animated orbs

  colors?: string[]
  // default: ['#FF6B35', '#2563EB', '#8B5CF6']
  // Array of hex colors for orbs
}
```

**Emits**: None

**Slots**: None

**Usage**:
```vue
<AnimatedBackground :orb-count="3" />
```

---

## 2. Kanban Components (`resources/js/components/kanban/`)

### 2.1 KanbanBoard.vue

**Purpose**: Main kanban board container with columns

**Props**:
```typescript
interface KanbanBoardProps {
  projectId: number
  // Required: Project ID to load tasks for
}
```

**Emits**:
```typescript
{
  taskCreated: (task: Task) => void
  taskUpdated: (task: Task) => void
  taskDeleted: (taskId: number) => void
}
```

**Slots**: None

**Exposed Methods** (via `defineExpose`):
```typescript
{
  refreshTasks: () => Promise<void>
  // Reload tasks from API
}
```

**Usage**:
```vue
<KanbanBoard :project-id="projectId" @task-created="handleTaskCreated" />
```

---

### 2.2 KanbanColumn.vue

**Purpose**: Single column in kanban board

**Props**:
```typescript
interface KanbanColumnProps {
  column: {
    id: string
    title: string
    color: string
  }
  // Required: Column configuration

  tasks: Task[]
  // Required: Tasks to display in this column

  isDragOver?: boolean
  // default: false
  // Highlights column when dragging over
}
```

**Emits**:
```typescript
{
  drop: (event: DragEvent, columnId: string) => void
  // Emitted when task is dropped on column

  dragOver: (event: DragEvent, columnId: string) => void
  // Emitted when dragging over column

  dragLeave: (event: DragEvent) => void
  // Emitted when drag leaves column
}
```

**Slots**:
```typescript
{
  header?: () => VNode[]
  // Custom column header (replaces default title + count)
}
```

**Usage**:
```vue
<KanbanColumn
  :column="column"
  :tasks="filteredTasks"
  :is-drag-over="dragOverColumn === column.id"
  @drop="handleDrop"
  @drag-over="handleDragOver"
/>
```

---

### 2.3 TaskCard.vue

**Purpose**: Individual task card in kanban column

**Props**:
```typescript
interface TaskCardProps {
  task: Task
  // Required: Full task object

  labels: Label[]
  // Required: Available labels for rendering badges

  isDragging?: boolean
  // default: false
  // Applied when this card is being dragged
}
```

**Emits**:
```typescript
{
  dragstart: (event: DragEvent, task: Task) => void
  // Emitted when drag starts

  dragend: (event: DragEvent) => void
  // Emitted when drag ends

  click: (task: Task) => void
  // Emitted when card is clicked (opens detail modal)

  edit: (task: Task) => void
  // Emitted from dropdown menu

  delete: (taskId: number) => void
  // Emitted from dropdown menu

  archive: (taskId: number) => void
  // Emitted from dropdown menu

  duplicate: (taskId: number) => void
  // Emitted from dropdown menu

  moveToTop: (taskId: number) => void
  // Emitted from dropdown menu
}
```

**Slots**: None

**Computed Properties** (internal):
```typescript
{
  assignee: User | null
  isOverdue: boolean
  formatDueDate: string  // "Today", "Tomorrow", "3 days ago"
  visibleLabels: Label[]  // First 3 labels
  remainingLabelsCount: number  // For "+N more" badge
  priorityClass: string  // CSS class for left border color
  showProgress: boolean  // Has subtasks
}
```

**Usage**:
```vue
<TaskCard
  :task="task"
  :labels="availableLabels"
  :is-dragging="draggedTask?.id === task.id"
  @click="openTaskDetail"
  @edit="openEditModal"
  @delete="handleDelete"
  @archive="handleArchive"
/>
```

---

### 2.4 TaskModal.vue

**Purpose**: Modal for creating/editing tasks

**Props**:
```typescript
interface TaskModalProps {
  task: Task | null
  // null = create mode, Task = edit mode

  isOpen: boolean
  // Controls modal visibility

  availableLabels: Label[]
  // Required: Labels for label picker

  teamMembers: User[]
  // Required: Users for assignee dropdown
}
```

**Emits**:
```typescript
{
  close: () => void
  // Emitted when modal should close

  save: (taskData: Partial<Task>) => Promise<void>
  // Emitted when save button clicked (parent handles API call)

  'update:isOpen': (value: boolean) => void
  // v-model:isOpen support
}
```

**Slots**: None

**Internal State**:
```typescript
{
  form: {
    title: string
    description: string
    priority: 'low' | 'medium' | 'high' | 'urgent'
    dueDate: string | null
    assigneeId: number | null
    labelIds: string[]
    subtasks: Array<{ title: string, completed: boolean }>
  }
  isSubmitting: boolean
  errors: Record<string, string>
}
```

**Usage**:
```vue
<TaskModal
  v-model:isOpen="showModal"
  :task="editingTask"
  :available-labels="labels"
  :team-members="teamMembers"
  @save="handleSaveTask"
  @close="handleCloseModal"
/>
```

---

### 2.5 BoardHeader.vue

**Purpose**: Kanban board header with stats and search

**Props**:
```typescript
interface BoardHeaderProps {
  stats: {
    total: number
    completed: number
    inProgress: number
    overdue: number
  }
  // Required: Board statistics

  searchQuery?: string
  // v-model binding for search input
}
```

**Emits**:
```typescript
{
  'update:searchQuery': (value: string) => void
  // v-model:searchQuery support

  addTask: () => void
  // Emitted when "+ Add Task" button clicked

  refresh: () => void
  // Emitted when refresh button clicked
}
```

**Slots**: None

**Usage**:
```vue
<BoardHeader
  v-model:search-query="searchQuery"
  :stats="boardStats"
  @add-task="openCreateModal"
  @refresh="refreshTasks"
/>
```

---

### 2.6 BoardStats.vue

**Purpose**: Display board statistics with color-coded values

**Props**:
```typescript
interface BoardStatsProps {
  stats: {
    total: number
    completed: number
    inProgress: number
    overdue: number
  }
  // Required: Statistics to display
}
```

**Emits**: None

**Slots**: None

**Usage**:
```vue
<BoardStats :stats="boardStats" />
```

---

## 3. Auth Components (`resources/js/pages/auth/`)

### 3.1 Login.vue

**Purpose**: Login page with modern UI

**Props**: None (page component)

**Emits**: None

**Internal State**:
```typescript
{
  form: {
    email: string
    password: string
    remember: boolean
  }
  isSubmitting: boolean
  errors: Record<string, string>
}
```

**Methods**:
```typescript
{
  handleSubmit: () => Promise<void>
  // Submits login form to API
}
```

---

### 3.2 Register.vue

**Purpose**: Registration page with password strength indicator

**Props**: None (page component)

**Emits**: None

**Internal State**:
```typescript
{
  form: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }
  isSubmitting: boolean
  errors: Record<string, string>
  passwordStrength: {
    score: number  // 0-4
    label: string  // "Weak", "Fair", "Good", "Strong"
    color: string  // CSS color
  }
}
```

**Computed Properties**:
```typescript
{
  passwordStrength: {
    score: number
    label: string
    color: string
  }
  // Calculated from form.password length and complexity
}
```

---

### 3.3 ForgotPassword.vue

**Purpose**: Password reset request page

**Props**: None (page component)

**Emits**: None

**Internal State**:
```typescript
{
  form: {
    email: string
  }
  isSubmitting: boolean
  errors: Record<string, string>
  successMessage: string | null
}
```

---

### 3.4 ResetPassword.vue

**Purpose**: Password reset page (from email link)

**Props**: None (page component, uses route params)

**Route Params**:
```typescript
{
  token: string  // Reset token from email
  email: string  // Email from query string
}
```

**Internal State**:
```typescript
{
  form: {
    password: string
    password_confirmation: string
  }
  isSubmitting: boolean
  errors: Record<string, string>
}
```

---

## 4. Composable Return Types

### 4.1 useDragDrop()

**Returns**:
```typescript
{
  draggedTask: Ref<Task | null>
  dragOverColumn: Ref<string | null>
  isDragging: ComputedRef<boolean>

  handleDragStart: (event: DragEvent, task: Task) => void
  handleDragEnd: (event: DragEvent) => void
  handleDragOver: (event: DragEvent, columnId: string) => void
  handleDrop: (event: DragEvent, columnId: string, onMove: (taskId: number, newColumn: string) => void) => void
}
```

---

### 4.2 useAnimation()

**Returns**:
```typescript
{
  prefersReducedMotion: Ref<boolean>
  shouldAnimate: ComputedRef<boolean>

  getTransitionDuration: (type: 'fast' | 'normal' | 'slow') => string
  shouldPlayAnimation: (type: 'continuous' | 'triggered') => boolean
}
```

---

### 4.3 useResponsive()

**Returns**:
```typescript
{
  screenWidth: Ref<number>
  isMobile: ComputedRef<boolean>      // < 768px
  isTablet: ComputedRef<boolean>      // 768px - 1024px
  isDesktop: ComputedRef<boolean>     // > 1024px
  breakpoint: ComputedRef<'mobile' | 'tablet' | 'desktop'>
}
```

---

### 4.4 useTaskMapping()

**Returns**:
```typescript
{
  mapTaskFromAPI: (apiTask: TaskAPIResponse) => Task
  mapTaskToAPI: (task: Task) => Partial<TaskAPIResponse>
  mapTasksFromAPI: (apiTasks: TaskAPIResponse[]) => Task[]
}
```

---

## 5. Component Communication Patterns

### Pattern 1: Parent → Child (Props)

```vue
<!-- Parent -->
<TaskCard :task="task" :labels="labels" />

<!-- Child receives props -->
const props = defineProps<TaskCardProps>()
```

### Pattern 2: Child → Parent (Events)

```vue
<!-- Child emits -->
const emit = defineEmits<TaskCardEmits>()
emit('delete', taskId)

<!-- Parent handles -->
<TaskCard @delete="handleDelete" />
```

### Pattern 3: v-model Binding

```vue
<!-- Parent -->
<Input v-model="form.email" />

<!-- Child implements -->
const props = defineProps<{ modelValue: string }>()
const emit = defineEmits<{ 'update:modelValue': [value: string] }>()
```

### Pattern 4: Provide/Inject (Global State)

```typescript
// Parent provides
provide('kanbanStore', kanbanStore)

// Child injects
const kanbanStore = inject('kanbanStore')
```

---

## 6. TypeScript Type Exports

**File**: `resources/js/types/index.ts`

```typescript
// Re-export all types for easy import
export type {
  Task,
  Column,
  Label,
  User,
  BoardStats,
  TaskAPIResponse,
  UserAPIResponse,
  LabelAPIResponse
} from './models'

export type {
  ButtonProps,
  InputProps,
  ModalProps,
  DropdownProps,
  TaskCardProps,
  TaskModalProps,
  KanbanBoardProps,
  KanbanColumnProps,
  BoardHeaderProps,
  BoardStatsProps
} from './components'

export type {
  DragDropState,
  AnimationState,
  ResponsiveState
} from './composables'
```

---

## Summary

**Total Components**: 14
- **Shared**: 5 (Button, Input, Modal, Dropdown, AnimatedBackground)
- **Kanban**: 6 (KanbanBoard, KanbanColumn, TaskCard, TaskModal, BoardHeader, BoardStats)
- **Auth Pages**: 4 (Login, Register, ForgotPassword, ResetPassword)
- **Composables**: 4 (useDragDrop, useAnimation, useResponsive, useTaskMapping)

**Key Patterns**:
- ✅ Props-down, Events-up for data flow
- ✅ v-model for two-way binding
- ✅ TypeScript interfaces for type safety
- ✅ Composition API with `<script setup>`
- ✅ Slots for flexible composition
- ✅ defineExpose for parent access to child methods

**Next**: Create `contracts/stores.md` with Pinia store schemas
