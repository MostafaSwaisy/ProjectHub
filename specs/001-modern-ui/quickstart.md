# Quickstart Guide: Modern UI Design System

**Feature**: Modern UI Design System Integration (001-modern-ui)
**Created**: 2026-02-01
**Phase**: Phase 1 - Design Artifacts

## Overview

This guide provides step-by-step instructions for developing, testing, and validating the modern UI design system integration.

---

## 1. Prerequisites

### Required Software

- **Node.js**: v18.0.0 or higher
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **PostgreSQL**: 14+ (existing database)
- **Git**: For version control

### Browser Requirements

**Development/Testing Browsers**:
- Chrome 90+ (primary development browser)
- Firefox 88+
- Safari 14+
- Edge 90+

**Mobile Testing** (via DevTools or real devices):
- iOS Safari 14+
- Android Chrome 90+

---

## 2. Development Setup

### Step 1: Checkout Feature Branch

```bash
# Ensure you're on the feature branch
git checkout 001-modern-ui

# Verify branch
git branch --show-current
# Output: 001-modern-ui
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies (if needed)
composer install

# Install Node.js dependencies
npm install

# Verify key packages
npm list vue @vitejs/plugin-vue pinia vue-router
```

**Expected Versions**:
- `vue`: ^3.4.0
- `pinia`: ^2.2.0
- `vue-router`: ^4.3.0
- `vite`: ^7.0.7
- `tailwindcss`: ^4.0.0

### Step 3: Environment Configuration

```bash
# Copy .env if needed
cp .env.example .env

# Verify database connection
php artisan config:clear
php artisan migrate:status
```

**Key Environment Variables**:
```env
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_DATABASE=projecthub
VITE_APP_URL="${APP_URL}"
```

### Step 4: Database Seeding (Optional)

```bash
# Seed test data for development
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=TaskSeeder
php artisan db:seed --class=LabelSeeder

# Verify data
php artisan tinker
>>> \App\Models\Task::count()
>>> \App\Models\Label::count()
```

---

## 3. Running the Development Environment

### Terminal 1: Laravel Backend

```bash
# Start Laravel development server
php artisan serve

# Output: Server running on [http://localhost:8000]
```

### Terminal 2: Vite Frontend

```bash
# Start Vite dev server with HMR
npm run dev

# Output:
#   VITE v7.0.7  ready in 234 ms
#   ➜  Local:   http://localhost:5173/
#   ➜  Network: use --host to expose
```

### Accessing the Application

**URL**: http://localhost:8000

The Laravel app will proxy Vite assets automatically via `@vite` directive in Blade templates.

---

## 4. Development Workflow

### File Organization

```text
resources/js/
├── components/
│   ├── shared/              # NEW: Design system components
│   │   ├── Button.vue
│   │   ├── Input.vue
│   │   ├── Modal.vue
│   │   ├── Dropdown.vue
│   │   └── AnimatedBackground.vue
│   ├── kanban/              # NEW: Kanban board components
│   │   ├── KanbanBoard.vue
│   │   ├── KanbanColumn.vue
│   │   ├── TaskCard.vue
│   │   ├── TaskModal.vue
│   │   ├── BoardHeader.vue
│   │   └── BoardStats.vue
│   └── auth/                # MODIFIED: Existing auth components
├── composables/             # NEW: Reusable logic
│   ├── useDragDrop.js
│   ├── useAnimation.js
│   ├── useResponsive.js
│   └── useTaskMapping.js
├── stores/                  # NEW: Pinia stores
│   ├── kanban.js
│   └── auth.js
├── pages/                   # MODIFIED: Page components
│   ├── auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── ForgotPassword.vue
│   │   └── ResetPassword.vue
│   └── projects/
│       └── KanbanView.vue   # NEW
├── styles/                  # NEW: Design system CSS
│   ├── design-system.css
│   ├── animations.css
│   └── responsive.css
├── api/                     # NEW: API wrappers
│   ├── kanban.js
│   └── auth.js
├── utils/                   # NEW: Helper functions
│   └── dateHelpers.js
├── types/                   # NEW: TypeScript types
│   ├── index.ts
│   ├── models.ts
│   └── components.ts
├── router/index.js          # MODIFIED: Add routes
└── App.vue                  # MODIFIED: Global styles
```

### Development Cycle

1. **Make Changes**: Edit Vue components, CSS, or composables
2. **Hot Reload**: Vite automatically reloads changes (HMR)
3. **Manual Refresh**: Hard refresh (Cmd+Shift+R / Ctrl+Shift+R) if HMR fails
4. **Check Console**: Verify no Vue warnings or errors
5. **Test Feature**: Manually test the updated functionality
6. **Commit**: `git add . && git commit -m "feat: description"`

---

## 5. Testing Strategy

### 5.1 Manual Testing Checklist

#### Auth Pages Testing

**Login Page** (`/login`):
- [ ] Page loads with dark theme and animated background
- [ ] Email and password inputs have glassmorphic styling
- [ ] Focus states show orange glow effect
- [ ] "Remember me" checkbox has custom styling
- [ ] Form validation shows inline errors
- [ ] Submit button shows loading spinner when clicked
- [ ] Social login buttons (Google, GitHub) have hover effects
- [ ] Page transitions smoothly when navigating to register
- [ ] Animation respects `prefers-reduced-motion`

**Register Page** (`/register`):
- [ ] Password strength indicator appears when typing password
- [ ] Strength indicator shows correct color (red/yellow/green)
- [ ] Password confirmation validation works
- [ ] Name, email, password fields validate correctly
- [ ] Form submission works and redirects on success

**Forgot Password** (`/forgot-password`):
- [ ] Email input validates format
- [ ] Success message appears after submission
- [ ] Consistent design with other auth pages

**Reset Password** (`/reset-password`):
- [ ] Page loads with token from email link
- [ ] New password and confirmation fields work
- [ ] Password strength indicator shows
- [ ] Submission updates password successfully

#### Kanban Board Testing

**Board Loading** (`/projects/{id}/kanban`):
- [ ] Board loads with 6 columns (Backlog, To Do, In Progress, Review, Done, Archived)
- [ ] Each column has colored indicator dot
- [ ] Board statistics show correct counts
- [ ] Task cards render with proper styling
- [ ] Loading state shows during data fetch

**Task Cards**:
- [ ] Priority indicated by left border color (gray/blue/orange/red)
- [ ] Urgent tasks have pulse animation
- [ ] Labels display as colored badges (max 3 visible + "+N more")
- [ ] Due date shows relative formatting ("Today", "Tomorrow", etc.)
- [ ] Overdue tasks highlighted in red
- [ ] Assignee avatar and name display
- [ ] Subtasks show progress bar
- [ ] Hover shows elevation and glow effect
- [ ] Three-dot menu appears on hover

**Drag and Drop**:
- [ ] Task cards draggable between columns
- [ ] Smooth drag animation with visual feedback
- [ ] Drop updates task status via API
- [ ] Column highlights when dragging over
- [ ] Drag cancels if dropped outside board

**Task Modal**:
- [ ] "+" button opens create modal
- [ ] Modal has glassmorphic background blur
- [ ] All form fields work (title, description, priority, due date, assignee, labels)
- [ ] Label picker allows multi-select
- [ ] Subtask list can be added/removed
- [ ] Save button creates/updates task
- [ ] Close button and ESC key close modal
- [ ] Click outside closes modal (unless unsaved changes)

**Search and Filter**:
- [ ] Search box filters tasks in real-time
- [ ] Filtered tasks animate smoothly
- [ ] No results message shows when search returns empty

**Task Actions** (from dropdown menu):
- [ ] Edit opens modal with task data pre-filled
- [ ] Duplicate creates copy of task
- [ ] Move to Top reorders task in column
- [ ] Archive moves task to Archived column
- [ ] Delete removes task (with confirmation)

#### Design System Consistency

- [ ] Orange (#FF6B35) used for primary actions
- [ ] Blue (#2563EB) used for secondary actions
- [ ] Dark theme (#0A0A0A) applied consistently
- [ ] Inter font family loads correctly
- [ ] Border radius consistent (8px/12px/16px/24px)
- [ ] Transitions smooth (150ms/300ms/500ms)
- [ ] Glassmorphic effects work (backdrop blur)
- [ ] Hover states show glow shadows
- [ ] Buttons have gradient sweep animation

#### Responsive Design

**Mobile (<768px)**:
- [ ] Auth pages stack vertically
- [ ] Kanban columns scroll horizontally
- [ ] Task cards remain readable
- [ ] Modals go full-screen
- [ ] Touch targets minimum 44x44px
- [ ] Drag-drop works on touch devices

**Tablet (768px-1024px)**:
- [ ] Kanban shows 2-3 columns at once
- [ ] Auth pages scale appropriately
- [ ] Modals sized for tablet

**Desktop (>1024px)**:
- [ ] All 6 kanban columns visible
- [ ] Optimal spacing and layout
- [ ] Hover effects work smoothly

---

### 5.2 Browser Testing Matrix

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Auth Pages | ✅ | ✅ | ✅ | ✅ |
| Kanban Board | ✅ | ✅ | ✅ | ✅ |
| Drag & Drop | ✅ | ✅ | ✅ | ✅ |
| Animations | ✅ | ✅ | ✅ | ✅ |
| Glassmorphic | ✅ | ✅ | ⚠️* | ✅ |

*Safari 14+ supports `backdrop-filter`, older versions fallback to solid background

### Testing Commands

```bash
# Run in each browser's DevTools
# Check for console errors
# Test with different screen sizes (DevTools responsive mode)
# Test with "Reduce Motion" enabled (System Preferences/Settings)
```

---

### 5.3 Automated Testing (Optional)

**Component Tests** (Vitest + @vue/test-utils):

```bash
# Run component tests
npm run test

# Run with coverage
npm run test:coverage

# Watch mode
npm run test:watch
```

**Example Test** (`tests/unit/components/TaskCard.test.js`):

```javascript
import { mount } from '@vue/test-utils'
import TaskCard from '@/components/kanban/TaskCard.vue'

describe('TaskCard', () => {
  const mockTask = {
    id: 1,
    title: 'Test Task',
    priority: 'high',
    dueDate: '2026-02-05',
    labels: [{ id: '1', name: 'Bug', color: '#EF4444' }]
  }

  it('renders task title', () => {
    const wrapper = mount(TaskCard, {
      props: { task: mockTask, labels: [] }
    })
    expect(wrapper.text()).toContain('Test Task')
  })

  it('applies priority class', () => {
    const wrapper = mount(TaskCard, {
      props: { task: mockTask, labels: [] }
    })
    expect(wrapper.classes()).toContain('priority-high')
  })

  it('emits click event', async () => {
    const wrapper = mount(TaskCard, {
      props: { task: mockTask, labels: [] }
    })
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })
})
```

**E2E Tests** (Playwright - if configured):

```bash
# Run E2E tests
npm run test:e2e

# Run in headed mode (see browser)
npm run test:e2e:headed
```

---

## 6. Performance Testing

### Lighthouse Audit

```bash
# Install Lighthouse CLI
npm install -g lighthouse

# Run audit on auth page
lighthouse http://localhost:8000/login --view

# Run audit on kanban board
lighthouse http://localhost:8000/projects/1/kanban --view
```

**Target Scores**:
- Performance: >90
- Accessibility: >95
- Best Practices: >90

### Performance Budgets

**Success Criteria from spec.md**:
- ✅ Auth pages load in <2s
- ✅ Kanban board with 100 tasks renders in <3s
- ✅ Drag operations at 60fps (16ms per frame)
- ✅ Modal animations complete in 300ms
- ✅ Interactive elements respond in <100ms

**Measuring Performance**:

```javascript
// In DevTools Console
// Measure kanban load time
performance.mark('kanban-start')
// ... wait for page to load ...
performance.mark('kanban-end')
performance.measure('kanban-load', 'kanban-start', 'kanban-end')
console.log(performance.getEntriesByName('kanban-load')[0].duration)
// Target: <3000ms

// Measure drag performance
// DevTools > Performance > Record > Drag task > Stop
// Check for 60fps (no dropped frames)
```

---

## 7. Accessibility Testing

### Manual Accessibility Checks

**Keyboard Navigation**:
- [ ] Tab through all interactive elements
- [ ] Enter/Space activates buttons
- [ ] ESC closes modals and dropdowns
- [ ] Arrow keys navigate dropdowns (if implemented)

**Screen Reader Testing** (VoiceOver/NVDA/JAWS):
- [ ] Form labels announced correctly
- [ ] Button purposes clear
- [ ] Error messages announced
- [ ] Task card content readable

**Color Contrast**:
```bash
# Check contrast ratios
# Use DevTools Accessibility inspector
# Or: https://webaim.org/resources/contrastchecker/

# Target: 4.5:1 minimum for normal text (WCAG AA)
```

**Reduced Motion**:
```bash
# Enable in OS settings:
# macOS: System Preferences > Accessibility > Display > Reduce motion
# Windows: Settings > Ease of Access > Display > Show animations

# Verify animations disabled when preference enabled
```

---

## 8. Common Issues & Troubleshooting

### Issue: Vite HMR not working

**Symptoms**: Changes not reflecting, must hard refresh

**Solution**:
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Restart dev server
npm run dev
```

### Issue: CSS variables not applying

**Symptoms**: Colors appear wrong or default

**Solution**:
1. Check `design-system.css` is imported in `resources/css/app.css`
2. Verify import order (CSS variables before Tailwind)
3. Hard refresh browser (Cmd+Shift+R)

### Issue: Drag and drop not working

**Symptoms**: Task cards not draggable

**Solution**:
1. Check `draggable="true"` attribute on task cards
2. Verify `useDragDrop` composable imported
3. Check browser console for errors
4. Test in different browser (may be browser-specific issue)

### Issue: Animations laggy

**Symptoms**: Choppy animations, low FPS

**Solution**:
1. Check Chrome DevTools > Performance for bottlenecks
2. Verify using GPU-accelerated properties (transform, opacity)
3. Reduce blur radius on glassmorphic effects
4. Check for unnecessary re-renders (Vue DevTools)

### Issue: 404 on API requests

**Symptoms**: Kanban board fails to load tasks

**Solution**:
1. Verify Laravel server running (`php artisan serve`)
2. Check API routes: `php artisan route:list | grep api`
3. Verify CORS settings in `config/cors.php`
4. Check network tab for actual error response

---

## 9. Validation Checklist

Before marking feature complete, verify all success criteria from `spec.md`:

### Functional Requirements

- [ ] **FR-001 to FR-009**: All auth pages implemented with modern UI
- [ ] **FR-010 to FR-030**: Kanban board with all features working
- [ ] **FR-031 to FR-040**: Design system consistently applied
- [ ] **FR-041 to FR-046**: Responsive design on all screen sizes

### Success Criteria

- [ ] **SC-001**: Login completes in <10s
- [ ] **SC-002**: Auth pages load in <2s
- [ ] **SC-003**: Drag operations at 60fps
- [ ] **SC-004**: 100 tasks render in <3s
- [ ] **SC-005**: Interactive elements respond in <100ms
- [ ] **SC-006**: Modals animate in 300ms
- [ ] **SC-007**: Zero regression in auth functionality
- [ ] **SC-008**: Zero regression in task management
- [ ] **SC-009**: Usable on 320px width
- [ ] **SC-010**: 4.5:1 contrast ratio achieved
- [ ] **SC-011**: Task creation in <30s
- [ ] **SC-012**: No frame drops on 60Hz displays

### User Stories

- [ ] **US1 (P1)**: Modern auth pages fully functional
- [ ] **US2 (P2)**: Kanban board with drag-drop working
- [ ] **US3 (P3)**: Responsive on mobile/tablet/desktop
- [ ] **US4 (P2)**: Design system consistent across all pages

---

## 10. Deployment Preparation

### Build for Production

```bash
# Create optimized production build
npm run build

# Output: dist/ folder with compiled assets
```

### Verify Production Build

```bash
# Serve production build locally
npm run preview

# Or use Laravel's built-in server
php artisan serve --env=production
```

### Performance Check

```bash
# Run Lighthouse on production build
lighthouse http://localhost:8000/login --view

# Check bundle sizes
du -sh public/build/*

# Target: CSS <100KB, JS <200KB (gzipped)
```

---

## 11. Git Workflow

### Committing Changes

```bash
# Stage changes
git add resources/js/components/kanban/
git add resources/js/styles/

# Commit with conventional commit message
git commit -m "feat(kanban): add drag-drop functionality"

# Commit types:
# feat: New feature
# fix: Bug fix
# style: CSS/design changes
# refactor: Code restructuring
# test: Adding tests
# docs: Documentation
```

### Pushing to Remote

```bash
# Push feature branch
git push origin 001-modern-ui

# Create pull request on GitHub/GitLab
# Link to spec.md and tasks.md in PR description
```

---

## 12. Demo Scenarios

### Scenario 1: New User Registration Flow

1. Navigate to `/register`
2. Fill in name, email, password
3. Watch password strength indicator update
4. Submit form
5. Verify redirect to dashboard

### Scenario 2: Kanban Task Management

1. Navigate to `/projects/1/kanban`
2. View board with existing tasks
3. Click "+ Add Task" button
4. Fill task details in modal
5. Save and verify card appears
6. Drag task from "To Do" to "In Progress"
7. Click task card to view details
8. Click three-dot menu and duplicate task
9. Verify duplicate appears

### Scenario 3: Mobile Experience

1. Open DevTools responsive mode
2. Set viewport to iPhone 14 Pro (390x844)
3. Navigate through auth pages
4. View kanban board with horizontal scroll
5. Test touch interactions
6. Verify modals go full-screen

---

## 13. Additional Resources

### Reference Repository

**URL**: https://github.com/MostafaSwaisy/Kanban-Board-and-Auth-pages.git

**Local Clone**: `temp-kanban-auth/` (in project root)

**Usage**: Visual reference for design fidelity

### Documentation

- **Spec**: `specs/001-modern-ui/spec.md`
- **Plan**: `specs/001-modern-ui/plan.md`
- **Tasks**: `specs/001-modern-ui/tasks.md`
- **Research**: `specs/001-modern-ui/research.md`
- **Data Model**: `specs/001-modern-ui/data-model.md`
- **Contracts**: `specs/001-modern-ui/contracts/`

### External Docs

- Vue 3: https://vuejs.org/guide/
- Pinia: https://pinia.vuejs.org/
- Tailwind CSS: https://tailwindcss.com/docs
- Vite: https://vitejs.dev/guide/

---

## Summary

**Quick Commands**:
```bash
# Start development
php artisan serve    # Terminal 1
npm run dev          # Terminal 2

# Run tests
npm run test

# Build for production
npm run build

# Performance audit
lighthouse http://localhost:8000/login --view
```

**Key Files to Watch**:
- `resources/js/app.js` - App entry point
- `resources/js/router/index.js` - Route definitions
- `resources/js/styles/*.css` - Design system
- `resources/js/stores/*.js` - State management

**Success Indicators**:
- ✅ All manual tests pass
- ✅ Performance budgets met
- ✅ Accessibility checks pass
- ✅ Responsive on all screen sizes
- ✅ Zero console errors/warnings

**Ready for Implementation**: Phase 1 complete ✅
