# Implementation Plan: Projects Management

**Branch**: `003-projects-management` | **Date**: 2026-02-04 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/003-projects-management/spec.md`

## Summary

Complete Projects Management System enabling full CRUD operations with project listing (grid/list views), filtering, sorting, search, archiving, team member management, and project duplication for authenticated users. Builds on existing Laravel + Vue 3 architecture with Pinia state management.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 11), JavaScript ES2022 (Vue 3.4.0)
**Primary Dependencies**: Laravel 11, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7
**Storage**: MySQL/SQLite via Laravel Eloquent ORM
**Testing**: PHPUnit (backend), Vitest (frontend)
**Target Platform**: Web (responsive, 768px mobile breakpoint)
**Project Type**: Web application (Laravel backend + Vue 3 SPA frontend)
**Performance Goals**: 2s page load, 500ms search response, 200ms filter/sort
**Constraints**: <50KB additional JS bundle, <2s API response, handle 100 projects/user
**Scale/Scope**: Single-tenant, up to 100 projects per user, 100+ team members per project

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Note**: Project constitution is using default template. No custom constraints defined.

| Gate | Status | Notes |
|------|--------|-------|
| Technology Stack | ✅ Pass | Laravel + Vue 3 matches existing stack |
| Testing Required | ✅ Pass | PHPUnit + Vitest configured |
| Code Organization | ✅ Pass | Following existing MVC patterns |

## Project Structure

### Documentation (this feature)

```text
specs/003-projects-management/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (OpenAPI specs)
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
# Backend (Laravel)
app/
├── Http/
│   ├── Controllers/
│   │   └── ProjectController.php    # NEW: Full CRUD + archive + members
│   ├── Requests/
│   │   ├── StoreProjectRequest.php  # NEW: Create validation
│   │   ├── UpdateProjectRequest.php # NEW: Update validation
│   │   └── AddProjectMemberRequest.php # NEW: Member validation
│   └── Resources/
│       └── ProjectResource.php      # UPDATE: Add archive, permissions
├── Models/
│   └── Project.php                  # UPDATE: Add is_archived, scopes
├── Policies/
│   └── ProjectPolicy.php            # UPDATE: Archive, member permissions
database/
└── migrations/
    └── YYYY_MM_DD_add_is_archived_to_projects.php  # NEW

# Frontend (Vue 3)
resources/js/
├── components/
│   └── projects/
│       ├── ProjectCard.vue          # NEW: Grid view card
│       ├── ProjectRow.vue           # NEW: List view row
│       ├── ProjectModal.vue         # NEW: Create/Edit modal
│       ├── ProjectFilters.vue       # NEW: Filter toolbar
│       ├── ProjectSearch.vue        # NEW: Search input
│       ├── TeamManagement.vue       # NEW: Member management
│       ├── DeleteConfirmModal.vue   # NEW: Delete confirmation
│       └── ArchiveToggle.vue        # NEW: Archive controls
├── composables/
│   └── useProjects.js               # NEW: Project operations
├── pages/projects/
│   └── ProjectsList.vue             # UPDATE: Full implementation
├── stores/
│   └── projects.js                  # NEW: Projects Pinia store
└── router/index.js                  # UPDATE: Add archived route param

routes/
└── api.php                          # UPDATE: Add project routes

tests/
├── Feature/
│   └── ProjectControllerTest.php    # NEW: API tests
└── Unit/
    └── ProjectPolicyTest.php        # NEW: Policy tests
```

**Structure Decision**: Web application pattern following existing Laravel MVC + Vue 3 SPA architecture. Backend handles API, frontend handles UI with Pinia state management.

## Complexity Tracking

> No violations requiring justification. Implementation follows existing patterns.

## Implementation Phases

### Phase 1: Core CRUD (P1 MVP)
- Database migration for `is_archived`
- ProjectController with index, store, show, update, destroy
- StoreProjectRequest, UpdateProjectRequest validation
- ProjectResource updates (permissions, archive status)
- ProjectPolicy updates (archive, edit permissions)
- Projects Pinia store
- ProjectsList.vue with grid/list toggle
- ProjectCard.vue, ProjectRow.vue components
- ProjectModal.vue (create/edit)
- Empty state component

### Phase 2: Archive & Delete (P1-P2)
- Archive/unarchive endpoints
- Delete with confirmation (type name for >10 tasks)
- DeleteConfirmModal.vue
- ArchiveToggle.vue
- Archived projects tab/filter
- Read-only mode for archived projects

### Phase 3: Filter, Sort, Search (P2)
- Filter dropdowns (status, role)
- Sort options (updated, created, title)
- Search with 300ms debounce
- URL param persistence
- ProjectFilters.vue, ProjectSearch.vue

### Phase 4: Team Management (P3)
- Member list endpoint
- Add/remove member endpoints
- Role change endpoint
- User search API
- TeamManagement.vue modal section
- Permission checks in frontend

### Phase 5: Duplicate (P4 Optional)
- Duplicate endpoint with options
- Include tasks vs structure only
- DuplicateProjectModal.vue

## Key Decisions

1. **Optimistic locking**: Use `updated_at` timestamp comparison for conflict detection
2. **Default member role**: Viewer (read-only by default, least privilege)
3. **Archive storage**: Boolean `is_archived` column (not separate table)
4. **Filter persistence**: URL params (shareable) + localStorage fallback
5. **Cascade delete**: Hard delete with confirmation, soft delete deferred to future
