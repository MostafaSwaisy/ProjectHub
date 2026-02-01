# Tasks: Modern UI Design System Integration

**Input**: Design documents from `/specs/001-modern-ui/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md (Phase 0), data-model.md (Phase 1), contracts/ (Phase 1)

**Tests**: Tests are OPTIONAL for this feature. Manual visual QA is the primary testing approach per spec.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- All tasks include exact file paths

## Path Conventions

This is a web application (Laravel + Vue.js):
- Frontend: `resources/js/`
- Backend: `app/` (no changes needed)
- Tests: `tests/unit/components/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Initialize design system foundation and shared components

- [ ] T001 Clone reference repository to temp-kanban-auth/ for design reference
- [ ] T002 [P] Create design system CSS file at resources/js/styles/design-system.css with CSS variables from reference
- [ ] T003 [P] Create animations CSS file at resources/js/styles/animations.css with keyframe definitions
- [ ] T004 [P] Create responsive CSS file at resources/js/styles/responsive.css with media queries
- [ ] T005 Import design system CSS files into resources/css/app.css
- [ ] T006 [P] Create shared components directory structure: resources/js/components/shared/
- [ ] T007 [P] Create kanban components directory structure: resources/js/components/kanban/
- [ ] T008 [P] Create composables directory if not exists: resources/js/composables/
- [ ] T009 [P] Create stores directory if not exists: resources/js/stores/
- [ ] T010 Add Inter font import to resources/css/app.css from Google Fonts

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared design system components that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T011 [P] Create Button.vue component in resources/js/components/shared/Button.vue with primary/secondary variants
- [ ] T012 [P] Create Input.vue component in resources/js/components/shared/Input.vue with focus states
- [ ] T013 [P] Create Modal.vue component in resources/js/components/shared/Modal.vue with glassmorphic styling
- [ ] T014 [P] Create Dropdown.vue component in resources/js/components/shared/Dropdown.vue with fade animation
- [ ] T015 [P] Create AnimatedBackground.vue component in resources/js/components/shared/AnimatedBackground.vue with floating orbs
- [ ] T016 [P] Create useAnimation.js composable in resources/js/composables/useAnimation.js for prefers-reduced-motion detection
- [ ] T017 [P] Create useResponsive.js composable in resources/js/composables/useResponsive.js for breakpoint detection
- [ ] T018 Update App.vue in resources/js/App.vue to apply dark theme background globally
- [ ] T019 Configure Tailwind CSS to extend with design system colors in tailwind.config.js

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Modern Authentication Experience (Priority: P1) 🎯 MVP

**Goal**: Upgrade all auth pages with modern design (login, register, forgot password, reset password)

**Independent Test**: Navigate through complete auth flow - register → login → forgot password. Verify glassmorphic effects, animated backgrounds, loading states, and password strength indicator.

### Implementation for User Story 1

- [ ] T020 [P] [US1] Upgrade Login.vue page in resources/js/pages/auth/Login.vue with AnimatedBackground and glassmorphic card
- [ ] T021 [P] [US1] Upgrade Register.vue page in resources/js/pages/auth/Register.vue with password strength indicator
- [ ] T022 [P] [US1] Create PasswordStrengthIndicator.vue component in resources/js/components/auth/PasswordStrengthIndicator.vue
- [ ] T023 [P] [US1] Upgrade ForgotPassword.vue page in resources/js/pages/auth/ForgotPassword.vue with consistent styling
- [ ] T024 [P] [US1] Upgrade ResetPassword.vue page in resources/js/pages/auth/ResetPassword.vue with consistent styling
- [ ] T025 [US1] Update AuthCard.vue component in resources/js/components/auth/AuthCard.vue with glassmorphic effect and gradient top border
- [ ] T026 [US1] Update SubmitButton.vue component in resources/js/components/auth/SubmitButton.vue with loading spinner animation
- [ ] T027 [US1] Update FormField.vue component in resources/js/components/auth/FormField.vue with focus glow effect
- [ ] T028 [US1] Update PasswordInput.vue component in resources/js/components/auth/PasswordInput.vue with show/hide toggle styling
- [ ] T029 [US1] Add ripple effect to button clicks across all auth pages
- [ ] T030 [US1] Add social login UI buttons (Google/GitHub) to Login and Register pages with hover effects
- [ ] T031 [US1] Add custom checkbox styling for "Remember me" on Login page
- [ ] T032 [US1] Add shake animation for validation errors using animations.css
- [ ] T033 [US1] Add page transition animations to router/index.js for auth routes (fade in/out 300ms)

**Checkpoint**: Auth flow complete and independently testable. MVP deliverable.

---

## Phase 4: User Story 4 - Design System Consistency (Priority: P2)

**Goal**: Ensure consistent design system application across all existing pages

**Independent Test**: Navigate through entire application. Verify consistent colors, typography, button styles, spacing, and animations on all pages.

### Implementation for User Story 4

- [ ] T034 [P] [US4] Apply design system colors to all existing components in resources/js/components/
- [ ] T035 [P] [US4] Update button styles across all existing pages to use shared Button component
- [ ] T036 [P] [US4] Update input styles across all existing forms to use shared Input component
- [ ] T037 [P] [US4] Apply consistent typography scale (font sizes, weights, line heights) globally
- [ ] T038 [P] [US4] Apply consistent spacing scale (padding, margins) globally
- [ ] T039 [US4] Ensure all modals use shared Modal component with glassmorphic styling
- [ ] T040 [US4] Ensure all dropdowns use shared Dropdown component with fade animations
- [ ] T041 [US4] Add page transition animations to all route changes in router/index.js
- [ ] T042 [US4] Apply hover states (elevation + glow) to all interactive elements
- [ ] T043 [US4] Verify dark theme background applied to all pages consistently

**Checkpoint**: Design system fully integrated across application.

---

## Phase 5: User Story 2 - Project Management with Kanban Board (Priority: P2)

**Goal**: Build full-featured kanban board with drag-drop, task cards, labels, and modern UI

**Independent Test**: Create project, view kanban board, create tasks, drag between columns, edit tasks, apply labels, search/filter. Verify all animations and interactions work smoothly.

### Stores for User Story 2

- [ ] T044 [P] [US2] Create tasks.js Pinia store in resources/js/stores/tasks.js with CRUD actions
- [ ] T045 [P] [US2] Create kanban.js Pinia store in resources/js/stores/kanban.js with board state management

### Composables for User Story 2

- [ ] T046 [P] [US2] Create useDragDrop.js composable in resources/js/composables/useDragDrop.js using HTML5 drag API
- [ ] T047 [P] [US2] Create useTaskFiltering.js composable in resources/js/composables/useTaskFiltering.js for search functionality

### Core Kanban Components for User Story 2

- [ ] T048 [P] [US2] Create TaskCard.vue component in resources/js/components/kanban/TaskCard.vue with priority borders and hover effects
- [ ] T049 [P] [US2] Create KanbanColumn.vue component in resources/js/components/kanban/KanbanColumn.vue with colored status indicators
- [ ] T050 [P] [US2] Create BoardStats.vue component in resources/js/components/kanban/BoardStats.vue with task counts
- [ ] T051 [P] [US2] Create BoardHeader.vue component in resources/js/components/kanban/BoardHeader.vue with search box and "+ Add Task" button
- [ ] T052 [US2] Create KanbanBoard.vue container component in resources/js/components/kanban/KanbanBoard.vue integrating all board elements

### Task Modal Components for User Story 2

- [ ] T053 [P] [US2] Create TaskModal.vue component in resources/js/components/kanban/TaskModal.vue for create/edit with glassmorphic styling
- [ ] T054 [P] [US2] Create TaskDetailModal.vue component in resources/js/components/kanban/TaskDetailModal.vue for viewing task details
- [ ] T055 [P] [US2] Create LabelSelector.vue component in resources/js/components/kanban/LabelSelector.vue with color picker
- [ ] T056 [US2] Integrate TaskModal with form validation and label selection

### Task Card Features for User Story 2

- [ ] T057 [US2] Add priority indicator (colored left border) to TaskCard based on priority level
- [ ] T058 [US2] Add pulse animation to urgent priority tasks in TaskCard
- [ ] T059 [US2] Add label badges display (max 3 with "+N more") to TaskCard
- [ ] T060 [US2] Add due date display with relative formatting (Today/Tomorrow/X days ago) to TaskCard
- [ ] T061 [US2] Add overdue/due-soon highlighting (red/orange) to TaskCard
- [ ] T062 [US2] Add assignee avatar and name display to TaskCard
- [ ] T063 [US2] Add three-dot menu with dropdown options (Edit, Duplicate, Archive, Delete) to TaskCard
- [ ] T064 [US2] Add task ID display in monospace font to TaskCard
- [ ] T065 [US2] Add subtask progress bar to TaskCard when subtasks exist

### Kanban Board Integration for User Story 2

- [ ] T066 [US2] Implement drag-drop functionality between columns using useDragDrop composable
- [ ] T067 [US2] Implement search/filter functionality in BoardHeader using useTaskFiltering composable
- [ ] T068 [US2] Implement board statistics calculation in BoardStats (total, completed, in progress, overdue)
- [ ] T069 [US2] Connect KanbanBoard to tasks store for data fetching and updates
- [ ] T070 [US2] Handle task creation via "+ Add Task" button opening TaskModal
- [ ] T071 [US2] Handle task editing via TaskCard menu "Edit" option
- [ ] T072 [US2] Handle task deletion via TaskCard menu "Delete" option with confirmation
- [ ] T073 [US2] Handle task duplication via TaskCard menu "Duplicate" option
- [ ] T074 [US2] Handle task archiving via TaskCard menu "Archive" option
- [ ] T075 [US2] Handle "Move to Top" functionality in TaskCard menu
- [ ] T076 [US2] Add smooth transitions when tasks move between columns
- [ ] T077 [US2] Add smooth transitions when filtering tasks via search

### Page Integration for User Story 2

- [ ] T078 [US2] Create KanbanView.vue page in resources/js/pages/projects/KanbanView.vue
- [ ] T079 [US2] Add kanban route to resources/js/router/index.js for /projects/:id/kanban
- [ ] T080 [US2] Integrate KanbanBoard component into KanbanView page with project data

### Edge Cases for User Story 2

- [ ] T081 [US2] Handle long task titles (100+ chars) with ellipsis truncation after 2 lines
- [ ] T082 [US2] Handle 50+ tasks in single column with max height and smooth scrolling
- [ ] T083 [US2] Handle drag outside board area with cancel animation returning card to origin
- [ ] T084 [US2] Handle label overflow (show first 3 labels + "+N more" indicator)
- [ ] T085 [US2] Add cleanup for ongoing animations when user navigates away

**Checkpoint**: Kanban board fully functional and independently testable.

---

## Phase 6: User Story 3 - Responsive Mobile Experience (Priority: P3)

**Goal**: Optimize all pages for mobile and tablet devices

**Independent Test**: Access application on mobile device (<768px) and tablet (768-1024px). Test auth pages adapt to single column, kanban columns scroll horizontally, modals go full-screen, touch interactions work smoothly.

### Implementation for User Story 3

- [ ] T086 [P] [US3] Add mobile breakpoint styles to responsive.css for auth pages (single column, centered cards)
- [ ] T087 [P] [US3] Add mobile breakpoint styles to responsive.css for kanban board (horizontal scroll)
- [ ] T088 [P] [US3] Add tablet breakpoint styles to responsive.css for kanban board (2-3 columns visible)
- [ ] T089 [P] [US3] Update Modal component to go full-screen on mobile devices
- [ ] T090 [P] [US3] Ensure touch targets are minimum 44x44px across all interactive elements
- [ ] T091 [US3] Add touch feedback styles for buttons and cards (active/tap states instead of hover)
- [ ] T092 [US3] Implement long-press to initiate drag on mobile in useDragDrop composable
- [ ] T093 [US3] Add "Move to..." menu alternative for mobile task relocation (no drag required)
- [ ] T094 [US3] Test and adjust spacing/padding for mobile keyboards on modals
- [ ] T095 [US3] Add horizontal scroll indicators for kanban columns on mobile
- [ ] T096 [US3] Optimize touch scrolling performance for task lists
- [ ] T097 [US3] Add minimum screen width check (320px) with informative message if below

**Checkpoint**: Application fully responsive and mobile-friendly.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final optimizations, accessibility, and cross-browser compatibility

- [ ] T098 [P] Add loading skeletons for kanban board initial load in KanbanBoard.vue
- [ ] T099 [P] Optimize bundle size - ensure CSS <100KB and JS <200KB (gzipped)
- [ ] T100 [P] Add accessibility attributes (ARIA labels, roles) to all interactive elements
- [ ] T101 [P] Test with screen reader and fix any accessibility issues
- [ ] T102 [P] Verify color contrast ratios meet WCAG 2.1 AA (4.5:1 minimum)
- [ ] T103 [P] Add keyboard navigation support (Tab, Enter, Esc) for modals and forms
- [ ] T104 [P] Test backdrop-filter fallback for browsers without support (add @supports query)
- [ ] T105 [P] Test on Firefox 88+ and verify all animations work
- [ ] T106 [P] Test on Safari 14+ and verify webkit prefixes work
- [ ] T107 [P] Test on Edge 90+ and verify full compatibility
- [ ] T108 Test prefers-reduced-motion by enabling in OS settings and verifying animations disable
- [ ] T109 Profile performance with Chrome DevTools - verify 60fps animations
- [ ] T110 Profile initial load time - verify auth pages <2s, kanban <3s with 100 tasks
- [ ] T111 Clean up and remove temp-kanban-auth reference repository
- [ ] T112 Update project README with new UI features documentation
- [ ] T113 Create visual QA checklist document for manual testing
- [ ] T114 Run full regression test on existing functionality to ensure zero breakage

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-6)**: All depend on Foundational phase completion
  - **US1 (P1)** can start first (highest priority) - No dependencies on other stories
  - **US4 (P2)** should follow US1 to apply consistency early
  - **US2 (P2)** can run parallel with US4 or after (independent of US1/US4)
  - **US3 (P3)** depends on US1, US2, US4 being complete (applies responsive to all pages)
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational - No dependencies
- **User Story 4 (P2)**: Can start after US1 - Applies consistency to auth pages from US1
- **User Story 2 (P2)**: Can start after Foundational - Independent (new feature)
- **User Story 3 (P3)**: Depends on US1, US2, US4 - Makes all pages responsive

### Within Each User Story

- Setup tasks before implementation
- Stores/composables before components that use them
- Base components before container components
- Core functionality before edge cases
- Each story independently testable at checkpoint

### Parallel Opportunities

- **Phase 1 Setup**: All tasks T002-T010 can run in parallel (different files)
- **Phase 2 Foundational**: All tasks T011-T017 can run in parallel (different components)
- **US1 Implementation**: T020-T024 (pages), T022 (component) can run in parallel
- **US4 Implementation**: T034-T038 can run in parallel (different concerns)
- **US2 Stores/Composables**: T044-T047 can run in parallel
- **US2 Core Components**: T048-T051 can run in parallel
- **US2 Modal Components**: T053-T055 can run in parallel
- **US3 Implementation**: T086-T090 can run in parallel (different breakpoints)
- **Phase 7 Polish**: Most tasks T098-T107 can run in parallel (different concerns)

---

## Parallel Example: User Story 1

```bash
# Launch all page upgrades for User Story 1 together:
Task: "[US1] Upgrade Login.vue page in resources/js/pages/auth/Login.vue"
Task: "[US1] Upgrade Register.vue page in resources/js/pages/auth/Register.vue"
Task: "[US1] Create PasswordStrengthIndicator.vue in resources/js/components/auth/PasswordStrengthIndicator.vue"
Task: "[US1] Upgrade ForgotPassword.vue page in resources/js/pages/auth/ForgotPassword.vue"
Task: "[US1] Upgrade ResetPassword.vue page in resources/js/pages/auth/ResetPassword.vue"

# These can all be developed simultaneously as they are different files
```

---

## Parallel Example: User Story 2

```bash
# Launch stores and composables together (US2 foundation):
Task: "[US2] Create tasks.js Pinia store"
Task: "[US2] Create kanban.js Pinia store"
Task: "[US2] Create useDragDrop.js composable"
Task: "[US2] Create useTaskFiltering.js composable"

# Then launch all core kanban components together:
Task: "[US2] Create TaskCard.vue component"
Task: "[US2] Create KanbanColumn.vue component"
Task: "[US2] Create BoardStats.vue component"
Task: "[US2] Create BoardHeader.vue component"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T010)
2. Complete Phase 2: Foundational (T011-T019) - CRITICAL blocker
3. Complete Phase 3: User Story 1 (T020-T033)
4. **STOP and VALIDATE**: Test complete auth flow independently
5. Deploy/demo modern authentication UI
6. **MVP DELIVERED** ✅

### Incremental Delivery

1. **Foundation**: Setup + Foundational → Design system ready
2. **Sprint 1**: Add User Story 1 → Test independently → Deploy (MVP - Modern Auth)
3. **Sprint 2**: Add User Story 4 → Test consistency → Deploy (Consistent Design)
4. **Sprint 3**: Add User Story 2 → Test kanban → Deploy (Full Kanban Board)
5. **Sprint 4**: Add User Story 3 → Test mobile → Deploy (Mobile Optimized)
6. **Sprint 5**: Polish phase → Final QA → Deploy (Production Ready)

Each increment adds value without breaking previous features.

### Parallel Team Strategy

With multiple developers after Foundational phase completes:

- **Developer A**: User Story 1 (Auth pages) - T020-T033
- **Developer B**: User Story 2 Setup (Stores/Composables) - T044-T047
- **Developer B**: User Story 2 Components - T048-T056 (after stores done)
- **Developer C**: User Story 4 (Design consistency) - T034-T043 (can start after US1)
- **Developer D**: Polish tasks - T098-T107 (can run parallel with US3)

Then converge for User Story 3 (responsive) which touches all pages.

---

## Task Count Summary

- **Phase 1 Setup**: 10 tasks
- **Phase 2 Foundational**: 9 tasks (BLOCKING)
- **Phase 3 US1 (P1)**: 14 tasks - **MVP**
- **Phase 4 US4 (P2)**: 10 tasks
- **Phase 5 US2 (P2)**: 37 tasks (largest - full kanban feature)
- **Phase 6 US3 (P3)**: 12 tasks
- **Phase 7 Polish**: 17 tasks

**Total: 109 tasks**

---

## Format Validation ✅

All tasks follow required format:
- ✅ Checkbox: `- [ ]`
- ✅ Task ID: Sequential T001-T114
- ✅ [P] marker: Present on parallelizable tasks
- ✅ [Story] label: Present on all user story tasks (US1, US2, US3, US4)
- ✅ Description: Clear action with exact file path
- ✅ Phase organization: Setup → Foundational → User Stories (by priority) → Polish

---

## Notes

- **No backend changes**: All tasks are frontend-only (resources/js/)
- **No database migrations**: Assumes task data structure already exists
- **Tests optional**: Spec doesn't mandate TDD, manual QA is primary approach
- **Reference repo**: temp-kanban-auth/ used for design reference only, removed in T111
- **Performance budgets**: Monitored in T109-T110 (60fps animations, <2-3s load times)
- **Accessibility**: WCAG 2.1 AA compliance checked in T100-T103
- **Browser support**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ (T104-T107)
- **Design system**: CSS variables enable easy theming and consistency
- **Component reuse**: Shared components (Button, Input, Modal) used across all pages
- **Independent stories**: Each user story deliverable and testable on its own

**Estimated MVP Delivery** (US1 only): ~33 tasks (Setup + Foundational + US1)
**Estimated Full Delivery** (all stories): 109 tasks total
