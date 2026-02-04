# Implementation Plan: Modern UI Design System Integration

**Branch**: `001-modern-ui` | **Date**: 2026-02-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-modern-ui/spec.md`

## Summary

Integrate a modern UI design system into ProjectHub's existing Laravel + Vue.js application. The implementation will upgrade auth pages (login, register, forgot/reset password) and create a full-featured kanban board with drag-drop functionality, task management, and modern visual effects. The design features a dark theme with orange/blue color scheme, glassmorphic effects, animated backgrounds, and responsive layouts. All existing backend functionality remains unchanged - this is purely a frontend visual upgrade.

**Key Technical Approach**:
- Adapt reference Vue.js components from [Kanban-Board-and-Auth-pages](https://github.com/MostafaSwaisy/Kanban-Board-and-Auth-pages.git) repository
- Maintain Laravel backend API compatibility (no backend changes required)
- Use CSS variables for design system tokens
- Implement component-based architecture matching existing Vue 3 + Vue Router structure
- Progressive enhancement: desktop-first, then mobile responsive
- Performance-conscious: respect `prefers-reduced-motion`, lazy-load animations

## Technical Context

**Language/Version**: JavaScript ES2022, Vue.js 3.4.0
**Primary Dependencies**: Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7
**Storage**: PostgreSQL (existing, unchanged - task data already exists)
**Testing**: Vitest 1.0.0, @vue/test-utils 2.4.0
**Target Platform**: Modern browsers (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
**Project Type**: Web application (Laravel backend + Vue.js SPA frontend)
**Performance Goals**: Auth pages <2s load, Kanban renders 100 tasks <3s, 60fps animations
**Constraints**: No backend changes, no DB migrations, bundle size CSS <100KB + JS <200KB (gzipped)
**Scale/Scope**: 4 auth pages + 1 kanban board, ~15 new Vue components, 1 global CSS design system

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Note**: No constitution file exists for this project. Standard Laravel/Vue.js best practices apply:
- ✅ Component-based architecture (Vue SFC)
- ✅ Separation of concerns (components, stores, composables)
- ✅ CSS scoping (scoped styles in Vue components)
- ✅ Accessibility standards (WCAG 2.1 AA minimum)
- ✅ Browser compatibility via PostCSS/Autoprefixer

## Project Structure

### Documentation (this feature)

```text
specs/001-modern-ui/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output (design system research)
├── data-model.md        # Phase 1 output (component state models)
├── quickstart.md        # Phase 1 output (development & testing guide)
├── contracts/           # Phase 1 output (component APIs)
│   ├── components.md    # Component prop interfaces
│   └── stores.md        # Pinia store schemas
├── checklists/
│   └── requirements.md  # Spec validation checklist
└── tasks.md             # Phase 2 output (/speckit.tasks - not created yet)
```

### Source Code (repository root)

```text
resources/js/
├── components/
│   ├── auth/              # Existing (to be upgraded)
│   ├── kanban/            # NEW
│   │   ├── KanbanBoard.vue
│   │   ├── KanbanColumn.vue
│   │   ├── TaskCard.vue
│   │   ├── TaskModal.vue
│   │   ├── BoardHeader.vue
│   │   └── BoardStats.vue
│   └── shared/            # NEW (design system)
│       ├── Button.vue
│       ├── Input.vue
│       ├── Modal.vue
│       ├── Dropdown.vue
│       └── AnimatedBackground.vue
├── composables/
│   ├── useDragDrop.js     # NEW
│   ├── useAnimation.js    # NEW
│   └── useResponsive.js   # NEW
├── stores/
│   ├── tasks.js           # NEW
│   └── kanban.js          # NEW
├── pages/
│   ├── auth/              # Existing (upgrade)
│   └── projects/
│       └── KanbanView.vue # NEW
├── styles/
│   ├── design-system.css  # NEW
│   ├── animations.css     # NEW
│   └── responsive.css     # NEW
├── router/index.js        # Existing (add routes)
└── App.vue                # Existing (may update)

tests/unit/components/     # NEW
└── *.test.js              # Component tests

temp-kanban-auth/          # Reference (temporary)
```

**Structure Decision**: Web application structure. Frontend in `resources/js/`, backend unchanged in `app/`. All work focuses on frontend Vue components. Reference repository kept temporarily for design guidance.

## Complexity Tracking

No Constitution violations - standard Vue.js/Laravel architecture applies.

---

# Phase 0: Research & Technical Decisions

## Research Tasks

### R1: Design System Token Extraction
Extract all CSS variables from `temp-kanban-auth/src/assets/styles.css` → document colors, spacing, typography, shadows in `research.md`

### R2: Component API Patterns
Analyze reference Vue components to understand prop/event patterns → document in `research.md`

### R3: Animation Implementation Strategy
Determine animation approach with `prefers-reduced-motion` support → document in `research.md`

### R4: Drag-Drop Library Selection
Choose: VueDraggable Next vs HTML5 native vs Pragmatic Drag and Drop → document in `research.md`

### R5: Data Structure Audit
Verify Laravel backend returns required fields (priorities, labels, etc.) → document mapping needed in `research.md`

### R6: Tailwind CSS Integration
Plan how to integrate CSS variables with existing Tailwind 4.0 → document in `research.md`

---

# Phase 1: Design Artifacts

Will generate:
- `data-model.md` - Component state interfaces
- `contracts/components.md` - Component props/events
- `contracts/stores.md` - Pinia store schemas
- `quickstart.md` - Dev setup & testing guide

---

# Phase 2: Implementation Strategy

*Detailed tasks via `/speckit.tasks`. High-level strategy:*

## Order (By User Story Priority)

1. **Setup** - Design system CSS, shared components
2. **US1 (P1)** - Modern Authentication pages
3. **US4 (P2)** - Design System Consistency
4. **US2 (P2)** - Kanban Board
5. **US3 (P3)** - Responsive Mobile
6. **Polish** - Performance, accessibility, cross-browser

## Parallel Opportunities

Within each phase, different components/files can be developed in parallel.

## Testing Strategy

- Manual visual QA (primary)
- Component unit tests (recommended)
- Cross-browser testing
- Accessibility audit

**Plan Status**: ✅ Ready for research phase
