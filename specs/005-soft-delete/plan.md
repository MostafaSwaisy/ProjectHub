# Implementation Plan: Soft Delete Support

**Branch**: `005-soft-delete` | **Date**: 2026-03-07 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/005-soft-delete/spec.md`

## Summary

Add soft delete support across all models in ProjectHub. Instead of permanently removing records, all delete operations will set a `deleted_at` timestamp and `deleted_by` user ID, preserving data for recovery. The implementation includes cascade soft deletes/restores through the entity hierarchy (Project > Board > Column > Task > Subtask/Comment), a trash tab in the project view for browsing/restoring/permanently-deleting items, and activity logging for all delete-related actions.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0)
**Primary Dependencies**: Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7
**Storage**: SQLite via Laravel Eloquent ORM
**Testing**: Laravel PHPUnit (backend), manual testing (frontend)
**Target Platform**: Web application (browser)
**Project Type**: Web (backend + frontend)
**Performance Goals**: Trash view loads within 2 seconds for up to 500 deleted items; cascade operations complete in a single request
**Constraints**: SQLite single-writer constraint — cascade operations must use transactions; no foreign key cascade deletes (handled in application code)
**Scale/Scope**: 10 model types, 6 controllers to update, 1 new controller (TrashController), 1 new Vue page, 1 new Pinia store

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Constitution is an unpopulated template — no project-specific principles defined. Gate passes trivially. No violations to track.

## Project Structure

### Documentation (this feature)

```text
specs/005-soft-delete/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   └── api-contracts.md # REST API contracts
├── checklists/
│   └── requirements.md  # Quality checklist
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Models/              # 13 models — add SoftDeletes trait to 10
│   ├── Http/
│   │   ├── Controllers/     # 6 existing + 1 new (TrashController)
│   │   ├── Resources/       # Existing task resources + new TrashResource
│   │   └── Requests/        # Existing requests (no changes needed)
│   └── Traits/              # New: HasCascadeSoftDeletes trait
├── database/
│   └── migrations/          # 1 new migration for deleted_at + deleted_by columns
├── routes/
│   └── api.php              # Add restore, force-delete, and trash endpoints
└── tests/

frontend/
├── resources/js/
│   ├── components/
│   │   ├── projects/        # Add TrashTab.vue
│   │   └── shared/          # Reuse ConfirmDialog, Modal
│   ├── pages/
│   │   └── projects/        # Existing project pages (add trash route)
│   └── stores/
│       └── trash.js         # New Pinia store for trash operations
```

**Structure Decision**: Web application layout (backend/ + frontend/) — matches existing repository structure. All changes extend existing directories; no new top-level directories needed.

## Complexity Tracking

No constitution violations to justify.
