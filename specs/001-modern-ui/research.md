# Research: Modern UI Design System Integration

**Date**: 2026-02-01
**Feature**: Modern UI Design System Integration (001-modern-ui)
**Phase**: Phase 0 - Research & Technical Decisions

## Overview

This document captures technical research and decisions for integrating the modern UI design system from the reference Vue.js repository into ProjectHub's Laravel + Vue.js application.

**Reference Repository**: https://github.com/MostafaSwaisy/Kanban-Board-and-Auth-pages.git (cloned to `temp-kanban-auth/`)

---

## R1: Design System Token Extraction

**Goal**: Extract all design tokens from reference repository for use in ProjectHub

**Source**: `temp-kanban-auth/src/assets/styles.css`

### Color Tokens

#### Primary Colors (Orange)
```css
--orange-primary: #FF6B35
--orange-light: #FF8C5A
--orange-dark: #E55A2B
--orange-glow: rgba(255, 107, 53, 0.4)
```

**Usage**: Primary actions (buttons, links), focus states, highlights

#### Secondary Colors (Blue)
```css
--blue-primary: #2563EB
--blue-light: #3B82F6
--blue-dark: #1D4ED8
--blue-glow: rgba(37, 99, 235, 0.4)
```

**Usage**: Secondary actions, informational elements, status indicators

#### Neutral Colors (Black Scale)
```css
--black-primary: #0A0A0A     /* Main background */
--black-secondary: #1A1A1A   /* Secondary backgrounds */
--black-tertiary: #2A2A2A    /* Inputs, cards */
--black-card: #151515        /* Card backgrounds */
```

**Usage**: Dark theme backgrounds, surfaces, elevated elements

#### Text Colors
```css
--text-primary: #FFFFFF      /* Primary text */
--text-secondary: #A0A0A0    /* Secondary text, labels */
--text-muted: #666666        /* Muted text, placeholders */
```

**Usage**: Typography hierarchy, readability on dark backgrounds

#### Gradients
```css
--gradient-orange: linear-gradient(135deg, var(--orange-primary) 0%, var(--orange-dark) 100%)
--gradient-blue: linear-gradient(135deg, var(--blue-light) 0%, var(--blue-dark) 100%)
--gradient-dark: linear-gradient(180deg, var(--black-primary) 0%, var(--black-secondary) 100%)
--gradient-card: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%)
```

**Usage**: Buttons, cards, backgrounds with subtle depth

### Spacing & Layout Tokens

#### Border Radius
```css
--radius-sm: 8px      /* Small elements */
--radius-md: 12px     /* Inputs, buttons */
--radius-lg: 16px     /* Cards */
--radius-xl: 24px     /* Modals */
--radius-full: 9999px /* Pills, avatars */
```

#### Shadows
```css
--shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3)
--shadow-md: 0 4px 20px rgba(0, 0, 0, 0.4)
--shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.5)
--shadow-orange: 0 4px 30px var(--orange-glow)
--shadow-blue: 0 4px 30px var(--blue-glow)
```

**Usage**: Elevation hierarchy, hover states, modals

### Animation Tokens

#### Transitions
```css
--transition-fast: 0.15s ease      /* Micro-interactions */
--transition-normal: 0.3s ease     /* Standard UI changes */
--transition-slow: 0.5s ease       /* Major state changes */
--transition-bounce: 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)  /* Playful effects */
```

### Typography

**Font Family**: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif

**Font Smoothing**:
```css
-webkit-font-smoothing: antialiased
-moz-osx-font-smoothing: grayscale
```

**Line Height**: 1.6 (base)

### Decision: Tailwind CSS Integration Strategy

**Approach**: Hybrid CSS Variables + Tailwind Utilities

1. **Create** `resources/js/styles/design-system.css` with all CSS variables from reference
2. **Extend** Tailwind config (`tailwind.config.js`) to reference CSS variables for colors
3. **Use** CSS variables directly in components for consistency
4. **Use** Tailwind utilities for spacing, layout, responsive design

**Rationale**:
- CSS variables allow runtime theming and global updates
- Tailwind provides utility-first approach for rapid development
- Hybrid approach combines best of both worlds

**Implementation**:
```javascript
// tailwind.config.js
theme: {
  extend: {
    colors: {
      orange: {
        primary: 'var(--orange-primary)',
        light: 'var(--orange-light)',
        dark: 'var(--orange-dark)',
      },
      blue: {
        primary: 'var(--blue-primary)',
        light: 'var(--blue-light)',
        dark: 'var(--blue-dark)',
      },
      // ... etc
    }
  }
}
```

---

## R2: Component API Patterns

**Goal**: Analyze reference Vue.js components to understand prop patterns and composition

**Sources**:
- `temp-kanban-auth/src/components/TaskCard.vue`
- `temp-kanban-auth/src/views/Kanban.vue`

### Composition API Patterns Observed

#### TaskCard Component Structure

**Props Pattern**:
```javascript
const props = defineProps({
  task: {
    type: Object,     // Full task object
    required: true
  },
  labels: {
    type: Array,      // Available labels for rendering
    default: () => []
  }
})
```

**Events Pattern**:
```javascript
const emit = defineEmits([
  'dragstart',   // Drag & drop initiation
  'click',       // Card click for details
  'edit',        // Edit action from menu
  'delete',      // Delete action from menu
  'archive',     // Archive action from menu
  'duplicate',   // Duplicate action from menu
  'moveToTop'    // Priority reordering
])
```

**Reactive State Pattern**:
```javascript
const isDragging = ref(false)           // Local UI state
const showMenu = ref(false)             // Local UI state
const menuButton = ref(null)            // Template ref
const menuPosition = reactive({         // Complex reactive object
  position: 'fixed',
  top: '0px',
  left: '0px',
  zIndex: 9999
})
```

**Computed Properties Pattern**:
```javascript
const assignee = computed(() => {
  return props.task.assignee ? teamMembers[props.task.assignee] : null
})

const isOverdue = computed(() => {
  if (!props.task.dueDate || props.task.status === 'done') return false
  return new Date(props.task.dueDate) < new Date()
})

const formatDueDate = computed(() => {
  // Relative date formatting: "Today", "Tomorrow", "X days ago"
  // ...logic
})
```

### Kanban Board Data Structure

**Columns Configuration**:
```javascript
const columns = [
  { id: 'backlog', title: 'Backlog', color: '#6B7280' },
  { id: 'todo', title: 'To Do', color: '#3B82F6' },
  { id: 'in-progress', title: 'In Progress', color: '#FF6B35' },
  { id: 'review', title: 'Review', color: '#8B5CF6' },
  { id: 'done', title: 'Done', color: '#22C55E' },
  { id: 'archived', title: 'Archived', color: '#475569' }
]
```

**Task Object Structure** (from reference):
```javascript
{
  id: number,
  title: string,
  description: string,
  status: 'backlog' | 'todo' | 'in-progress' | 'review' | 'done' | 'archived',
  priority: 'low' | 'medium' | 'high' | 'urgent',
  dueDate: string,  // ISO 8601 date
  assignee: number, // User ID
  labels: string[], // Array of label IDs
  subtasks: Array<{ id, title, completed }>,
  archived: boolean
}
```

**Labels Configuration**:
```javascript
const availableLabels = ref([
  { id: 'bug', name: 'Bug', color: '#EF4444' },
  { id: 'feature', name: 'Feature', color: '#3B82F6' },
  { id: 'enhancement', name: 'Enhancement', color: '#8B5CF6' },
  { id: 'documentation', name: 'Docs', color: '#6B7280' },
  { id: 'design', name: 'Design', color: '#EC4899' },
  { id: 'backend', name: 'Backend', color: '#22C55E' },
  { id: 'frontend', name: 'Frontend', color: '#F59E0B' }
])
```

### Key Patterns for Adoption

1. **Props-Down, Events-Up**: Strict unidirectional data flow
2. **Composition API**: Use `ref()`, `reactive()`, `computed()` consistently
3. **Template Refs**: For DOM access (e.g., menu positioning)
4. **Computed for Derived State**: Date formatting, overdue detection
5. **Event Emitters**: Delegate actions to parent components
6. **Teleport for Modals/Menus**: Avoid z-index issues

---

## R3: Animation Implementation Strategy

**Goal**: Determine performant animation approach with accessibility support

### Animation Types in Reference

#### 1. Background Orbs (Continuous)
```css
@keyframes orbFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.05); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}
.orb {
  animation: orbFloat 15s ease-in-out infinite;
  filter: blur(60px);  /* Heavy blur - potential performance cost */
}
```

**Performance Consideration**: `filter: blur()` is GPU-intensive. Use sparingly.

#### 2. Card Slide-Up (Enter Animation)
```css
@keyframes cardSlideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.auth-card {
  animation: cardSlideUp 0.6s ease-out;
}
```

**Implementation**: Use Vue `<Transition>` component for mount/unmount

#### 3. Pulse Animation (Urgent Tasks)
```css
@keyframes urgentPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
  50% { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
}
.task-card.priority-urgent {
  animation: urgentPulse 2s ease-in-out infinite;
}
```

**Optimization**: Animate `box-shadow` only (not `transform`/`opacity` for compositing)

### Accessibility: `prefers-reduced-motion`

**Strategy**: Disable all animations when user prefers reduced motion

**Implementation**:
```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Composable**: Create `useAnimation.js` to detect preference:
```javascript
import { ref, onMounted } from 'vue'

export function useAnimation() {
  const prefersReducedMotion = ref(false)

  onMounted(() => {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
    prefersReducedMotion.value = mediaQuery.matches

    mediaQuery.addEventListener('change', (e) => {
      prefersReducedMotion.value = e.matches
    })
  })

  return { prefersReducedMotion }
}
```

### Performance Optimization

**GPU-Accelerated Properties** (use these):
- `transform: translate()`, `scale()`, `rotate()`
- `opacity`

**Avoid** (triggers layout/paint):
- `width`, `height`, `top`, `left`, `right`, `bottom`
- `margin`, `padding`
- `filter` (use sparingly, prefer `backdrop-filter` for glassmorphism)

**Use `will-change`** sparingly for elements that will animate:
```css
.task-card:hover {
  will-change: transform, box-shadow;
}
```

### Vue Transition Components

**For Page Transitions**:
```vue
<router-view v-slot="{ Component }">
  <transition name="fade" mode="out-in">
    <component :is="Component" />
  </transition>
</router-view>
```

**For List Transitions** (task cards):
```vue
<TransitionGroup name="task-list" tag="div">
  <TaskCard v-for="task in tasks" :key="task.id" />
</TransitionGroup>
```

**CSS**:
```css
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
```

### Decision: Animation Strategy

1. **Use CSS animations** for continuous effects (orbs, pulse)
2. **Use Vue Transition** for enter/leave animations (modals, pages)
3. **Use TransitionGroup** for list reordering (task cards)
4. **Implement `prefers-reduced-motion`** globally via media query + composable
5. **Optimize with GPU properties** (transform, opacity only)
6. **Use `will-change` judiciously** on hover states

**Files**:
- `resources/js/styles/animations.css` - Keyframe definitions
- `resources/js/composables/useAnimation.js` - Reduced motion detection

---

## R4: Drag-Drop Library Selection

**Goal**: Choose drag-drop solution for kanban board that works with Vue 3

### Options Evaluated

#### Option 1: HTML5 Native Drag and Drop API ⭐ **SELECTED**

**Reference Implementation**: Uses native HTML5 API

**Pros**:
- ✅ Zero dependencies (no bundle size increase)
- ✅ Native browser support (all modern browsers)
- ✅ Works with Vue 3 Composition API
- ✅ Reference design already implements this successfully
- ✅ Good touch device support with polyfills

**Cons**:
- ⚠️ Requires manual event handling
- ⚠️ Touch devices need additional work (long-press to initiate)

**Implementation Pattern** (from reference):
```javascript
const draggedTask = ref(null)

const onDragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
}

const onDrop = (event, columnId) => {
  if (draggedTask.value) {
    draggedTask.value.status = columnId
    // API call to update backend
    draggedTask.value = null
  }
}
```

**HTML**:
```html
<div
  draggable="true"
  @dragstart="onDragStart($event, task)"
  @dragover.prevent
  @drop="onDrop($event, columnId)"
>
```

#### Option 2: VueDraggable Next (SortableJS)

**Package**: `vuedraggable@next` (v4.x for Vue 3)

**Pros**:
- ✅ Mature library (SortableJS wrapper)
- ✅ Vue 3 support
- ✅ Touch device support built-in
- ✅ Smooth animations out of the box

**Cons**:
- ❌ Adds ~50KB to bundle (gzipped)
- ❌ Additional dependency to maintain
- ❌ Overkill for our use case (simple column-to-column dragging)

#### Option 3: Pragmatic Drag and Drop (Atlassian)

**Package**: `@atlaskit/pragmatic-drag-and-drop`

**Pros**:
- ✅ Modern, performant
- ✅ Framework-agnostic (works with Vue)
- ✅ Accessibility built-in

**Cons**:
- ❌ Newer library (less battle-tested)
- ❌ Adds dependency
- ❌ More complex than needed for our use case

### Decision: HTML5 Native Drag and Drop

**Rationale**:
1. Reference implementation proves it works
2. Zero bundle size impact
3. Good browser support
4. Simpler codebase (no library abstraction)
5. Easy to add touch support via composable

**Implementation Plan**:
- Create `useDragDrop.js` composable
- Wrap native HTML5 API with Vue reactivity
- Add touch device support (long-press detection)
- Add visual feedback during drag

**Touch Support Strategy**:
- Detect long-press (500ms) to initiate drag on mobile
- Provide alternative "Move to..." menu for mobile users
- Use CSS active states instead of hover on touch devices

**Composable Structure**:
```javascript
// resources/js/composables/useDragDrop.js
export function useDragDrop() {
  const draggedTask = ref(null)
  const dragOverColumn = ref(null)

  const handleDragStart = (event, task) => {
    draggedTask.value = task
    event.dataTransfer.effectAllowed = 'move'
    event.target.classList.add('dragging')
  }

  const handleDragEnd = (event) => {
    event.target.classList.remove('dragging')
    draggedTask.value = null
    dragOverColumn.value = null
  }

  const handleDragOver = (event, columnId) => {
    event.preventDefault()
    dragOverColumn.value = columnId
  }

  const handleDrop = (event, columnId, onMove) => {
    event.preventDefault()
    if (draggedTask.value && onMove) {
      onMove(draggedTask.value.id, columnId)
    }
    dragOverColumn.value = null
  }

  return {
    draggedTask,
    dragOverColumn,
    handleDragStart,
    handleDragEnd,
    handleDragOver,
    handleDrop
  }
}
```

---

## R5: Data Structure Audit

**Goal**: Verify Laravel backend provides necessary data for kanban UI

**Sources**:
- `app/Models/Task.php`
- `app/Models/Project.php`

### Existing Task Model Fields

**Database Fields** (from `$fillable`):
```php
- column_id      // ✅ Maps to kanban column/status
- title          // ✅ Task title
- description    // ✅ Task description
- assignee_id    // ✅ User assignment
- priority       // ✅ Priority level
- due_date       // ✅ Due date
- position       // ✅ Ordering within column
```

**Relationships**:
```php
- column()       // BelongsTo Column (status)
- assignee()     // BelongsTo User
- subtasks()     // HasMany Subtask (for progress bar)
- labels()       // BelongsToMany Label (for badges)
- comments()     // HasMany Comment
- activities()   // HasMany Activity
```

**Computed Attributes**:
```php
- progress                  // ✅ Subtask completion percentage
- completed_subtask_count   // ✅ For progress display
- isOverdue()               // ✅ Overdue detection
```

### Data Mapping: Reference → ProjectHub

| Reference Field | ProjectHub Field | Mapping Notes |
|----------------|------------------|---------------|
| `id` | `id` | ✅ Direct match |
| `title` | `title` | ✅ Direct match |
| `description` | `description` | ✅ Direct match |
| `status` | `column.name` or `column_id` | ⚠️ Needs mapping (string → column relation) |
| `priority` | `priority` | ✅ Assumed compatible (low/medium/high/urgent) |
| `dueDate` | `due_date` | ✅ Direct match (date format) |
| `assignee` | `assignee` relationship | ✅ Object with id, name, avatar |
| `labels` | `labels` relationship | ✅ Array of label objects |
| `subtasks` | `subtasks` relationship | ✅ For progress bar |
| `archived` | N/A | ❌ **MISSING** - Need to add or use column status |

### Data Compatibility Analysis

✅ **COMPATIBLE**:
- Task title, description, priority, due date
- Assignee relationship (returns User object with name)
- Labels relationship (many-to-many)
- Subtasks for progress display
- Overdue detection (computed method)

⚠️ **REQUIRES MAPPING**:
- **Status/Column**: Reference uses string status ('backlog', 'todo', etc.), ProjectHub uses Column relationship
  - **Solution**: In frontend, map `column.name` or `column_id` to status strings
  - Or: API endpoint should return status as string for Vue components

❌ **MISSING**:
- **Archived field**: Reference has `task.archived` boolean
  - **Solution 1**: Add "Archived" as a column in database
  - **Solution 2**: Use a specific column for archived tasks
  - **Decision**: Use Solution 2 (create "Archived" column, no DB changes needed)

### Frontend Data Transformation

Create a helper to transform API response:

```javascript
// resources/js/composables/useTaskMapping.js
export function mapTaskFromAPI(task) {
  return {
    id: task.id,
    title: task.title,
    description: task.description,
    status: task.column?.name?.toLowerCase().replace(/\s+/g, '-') || 'todo',
    priority: task.priority || 'medium',
    dueDate: task.due_date,
    assignee: task.assignee ? {
      id: task.assignee.id,
      name: task.assignee.name,
      avatar: task.assignee.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${task.assignee.name}`
    } : null,
    labels: task.labels?.map(l => ({
      id: l.id.toString(),
      name: l.name,
      color: l.color
    })) || [],
    subtasks: task.subtasks?.map(s => ({
      id: s.id,
      title: s.title,
      completed: s.is_completed
    })) || []
  }
}
```

### Decision: Data Compatibility

**Status**: ✅ **COMPATIBLE** with frontend mapping layer

**Actions Required**:
1. Create data mapping composable (`useTaskMapping.js`)
2. Transform API responses to match reference component structure
3. No backend changes needed
4. Use existing Column model for status management

**Assumptions Validated**:
- ✅ Task data structure exists
- ✅ Relationships (assignee, labels, subtasks) exist
- ✅ Priority field exists
- ✅ Due date handling exists

---

## R6: Tailwind CSS Integration

**Goal**: Plan integration of design system CSS variables with existing Tailwind CSS 4.0

**Current Config**: `tailwind.config.js` (reviewed)

### Current Tailwind Setup

**Existing Colors**:
- `primary` (indigo scale) - used for auth pages
- `success`, `error`, `warning` - semantic colors

**Existing Extensions**:
- Typography presets (pageHeading, sectionHeading, label, input, caption)
- Custom spacing (formGap, formSection)
- Custom border radius (form)
- Min height/width for touch targets (WCAG compliance)

### Integration Strategy

**Approach**: Extend Tailwind config to reference CSS variables

**Step 1**: Add design system colors to Tailwind config
```javascript
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        // Keep existing colors for backward compatibility
        primary: { /* existing */ },

        // Add new design system colors
        orange: {
          primary: 'rgb(255, 107, 53)',      // --orange-primary
          light: 'rgb(255, 140, 90)',        // --orange-light
          dark: 'rgb(229, 90, 43)',          // --orange-dark
          DEFAULT: 'rgb(255, 107, 53)',
        },
        blue: {
          primary: 'rgb(37, 99, 235)',       // --blue-primary
          light: 'rgb(59, 130, 246)',        // --blue-light
          dark: 'rgb(29, 78, 216)',          // --blue-dark
          DEFAULT: 'rgb(37, 99, 235)',
        },
        dark: {
          primary: 'rgb(10, 10, 10)',        // --black-primary
          secondary: 'rgb(26, 26, 26)',      // --black-secondary
          tertiary: 'rgb(42, 42, 42)',       // --black-tertiary
          card: 'rgb(21, 21, 21)',           // --black-card
        }
      },
      borderRadius: {
        sm: '8px',       // --radius-sm
        md: '12px',      // --radius-md (maps to default)
        lg: '16px',      // --radius-lg
        xl: '24px',      // --radius-xl
        // Keep existing 'form' for backward compatibility
      },
      boxShadow: {
        sm: '0 2px 8px rgba(0, 0, 0, 0.3)',
        md: '0 4px 20px rgba(0, 0, 0, 0.4)',
        lg: '0 8px 40px rgba(0, 0, 0, 0.5)',
        orange: '0 4px 30px rgba(255, 107, 53, 0.4)',
        blue: '0 4px 30px rgba(37, 99, 235, 0.4)',
      },
      transitionDuration: {
        fast: '150ms',
        DEFAULT: '300ms',
        slow: '500ms',
      }
    }
  }
}
```

**Step 2**: Use CSS variables in `design-system.css` for runtime flexibility
```css
:root {
  --orange-primary: #FF6B35;
  /* ... all other variables */
}
```

**Step 3**: Apply design system globally
```css
/* resources/js/styles/design-system.css */
body {
  background: var(--black-primary);
  color: var(--text-primary);
  font-family: 'Inter', sans-serif;
}
```

**Step 4**: Import order in `resources/css/app.css`
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import '../js/styles/design-system.css';
@import '../js/styles/animations.css';
@import '../js/styles/responsive.css';
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### Usage Patterns

**In Components** - Use CSS variables directly:
```vue
<style scoped>
.custom-button {
  background: var(--gradient-orange);
  box-shadow: var(--shadow-orange);
}
</style>
```

**With Tailwind Classes** - Use extended colors:
```vue
<button class="bg-orange-primary text-white rounded-lg shadow-orange">
  Submit
</button>
```

**Hybrid Approach**:
```vue
<div
  class="rounded-xl p-6"  <!-- Tailwind utilities -->
  :style="{
    background: 'var(--gradient-card)',  <!-- CSS variable -->
    backdropFilter: 'blur(20px)'          <!-- CSS property -->
  }"
>
```

### Decision: Hybrid CSS Architecture

**Layer 1**: CSS Variables (design-system.css)
- Define all tokens as CSS custom properties
- Enable runtime theming
- Single source of truth

**Layer 2**: Tailwind Config (tailwind.config.js)
- Reference design system values
- Provide utility classes
- Maintain backward compatibility

**Layer 3**: Component Styles (scoped)
- Use CSS variables for complex effects (gradients, glassmorphism)
- Use Tailwind utilities for spacing, layout
- Keep specificity low

**Benefits**:
- ✅ Consistent design tokens
- ✅ Utility-first workflow
- ✅ Runtime theming possible
- ✅ Backward compatible with existing Tailwind usage
- ✅ Easy to update globally (change CSS variable once)

---

## Summary of Decisions

| Research Area | Decision | Rationale |
|--------------|----------|-----------|
| **R1: Design Tokens** | Extract to CSS variables + extend Tailwind | Single source of truth, runtime flexibility |
| **R2: Component Patterns** | Use Composition API, props-down/events-up | Matches Vue 3 best practices, reference design |
| **R3: Animations** | CSS animations + Vue Transition + `prefers-reduced-motion` | Performant, accessible, GPU-optimized |
| **R4: Drag-Drop** | HTML5 Native Drag & Drop API | Zero dependencies, proven in reference, good browser support |
| **R5: Data Mapping** | Frontend mapping layer, no backend changes | Compatible with existing API, no DB migrations |
| **R6: Tailwind Integration** | Hybrid CSS variables + Tailwind utilities | Best of both worlds, backward compatible |

---

## Next Steps

1. ✅ **Phase 0 Complete**: All research documented
2. 🔄 **Phase 1**: Create design artifacts (data-model.md, contracts/, quickstart.md)
3. ⏭️ **Phase 2**: Begin implementation (tasks.md generated)

---

## Files to Create

Based on research, these files will be created in Phase 1 (Setup):

1. `resources/js/styles/design-system.css` - All CSS variables
2. `resources/js/styles/animations.css` - Keyframe definitions
3. `resources/js/styles/responsive.css` - Media queries
4. `resources/js/composables/useAnimation.js` - Reduced motion detection
5. `resources/js/composables/useDragDrop.js` - Drag & drop logic
6. `resources/js/composables/useResponsive.js` - Breakpoint detection
7. `resources/js/composables/useTaskMapping.js` - API data transformation

Update:
- `tailwind.config.js` - Extend with design system colors
- `resources/css/app.css` - Import design system CSS

**Research Phase Complete** ✅
