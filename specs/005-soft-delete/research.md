# Research: Soft Delete Support

**Feature**: 005-soft-delete | **Date**: 2026-03-07

## Research Findings

### R-001: Laravel SoftDeletes Implementation Pattern

**Decision**: Use Laravel's built-in `Illuminate\Database\Eloquent\SoftDeletes` trait on all 10 target models.

**Rationale**: Laravel's SoftDeletes trait provides:
- Automatic `deleted_at` column handling via `$table->softDeletes()` migration helper
- Global scope that excludes soft-deleted records from all queries automatically
- Built-in `withTrashed()`, `onlyTrashed()`, `trashed()`, `restore()`, `forceDelete()` methods
- Model events: `trashed`, `restoring`, `restored`, `forceDeleting`, `forceDeleted`
- Route implicit binding support via `->withTrashed()` on routes
- Factory support via `->trashed()` state

**Alternatives considered**:
- Custom soft delete implementation: Rejected — duplicates existing framework capability
- Third-party packages (e.g., `dyrynda/laravel-cascade-soft-deletes`): Rejected — cascade logic is simple enough to implement manually via model observers or boot methods; avoids external dependency

### R-002: Custom `deleted_by` Column

**Decision**: Add a custom `deleted_by` nullable `foreignId` column alongside the standard `deleted_at`. Override the model's `delete()` method or use a model observer to auto-populate `deleted_by` with `auth()->id()`.

**Rationale**: Laravel's SoftDeletes trait only manages `deleted_at`. The `deleted_by` column is a custom addition required by the spec (FR-001, FR-012). Best approach is a reusable trait (`HasSoftDeleteUser`) that hooks into the `deleting` model event.

**Alternatives considered**:
- Derive "who deleted" from Activity log: Rejected by user (clarification Q2) — direct column is more reliable and faster to query
- Boot method on each model individually: Rejected — a shared trait is DRY

### R-003: Cascade Soft Deletes Strategy

**Decision**: Implement cascade soft deletes via a custom `HasCascadeSoftDeletes` trait that defines `$cascadeDeletes` relationships. On the `deleting` event, iterate relationships and call `delete()` on each child. On `restoring`, restore only children whose `deleted_at` matches the parent's.

**Rationale**: Laravel does not provide cascade soft deletes natively. The hierarchy is:
- Project → boards()
- Board → columns()
- Column → tasks()
- Task → subtasks(), comments()

The trait checks `$this->isForceDeleting()` to decide between cascade soft-delete and cascade force-delete.

**Alternatives considered**:
- Database-level CASCADE triggers: Rejected — SQLite has limited trigger support, and `deleted_at` is not a standard FK constraint
- `dyrynda/laravel-cascade-soft-deletes` package: Rejected — simple enough to build; avoids dependency
- Manual cascade in each controller: Rejected — duplicates logic, error-prone

### R-004: Cascade Restore Logic

**Decision**: On restore, only restore children whose `deleted_at` equals the parent's `deleted_at` (timestamp matching for same cascade batch). This prevents unintentionally restoring items that were independently deleted before the cascade.

**Rationale**: Confirmed by user in clarification Q3. Timestamp comparison is the simplest mechanism that doesn't require an additional `cascade_batch_id` column.

**Alternatives considered**:
- Restore all trashed children regardless: Rejected by user — would un-delete items user explicitly removed
- Add a `cascade_batch_id` column: Rejected — over-engineering; timestamp matching is sufficient for this use case

### R-005: ProjectMember Model & SoftDeletes Compatibility

**Decision**: `ProjectMember` extends `Model` (not `Pivot`), so it can safely use the `SoftDeletes` trait.

**Rationale**: Laravel docs explicitly warn that pivot models (`Pivot` class) cannot use SoftDeletes. Since `ProjectMember` is a standard Eloquent model, no compatibility issue exists.

### R-006: Route Binding for Soft-Deleted Models

**Decision**: Use `->withTrashed()` on restore and force-delete routes so Laravel's implicit model binding can resolve soft-deleted models.

**Rationale**: By default, implicit model binding excludes trashed models. Restore/force-delete endpoints need to resolve trashed models, so the `withTrashed()` route method is required.

### R-007: Trash API Architecture

**Decision**: Create a single `TrashController` with endpoints scoped to a project. It aggregates trashed items across all entity types (boards, columns, tasks, subtasks, comments) within a project.

**Rationale**: A single controller keeps the API surface clean and avoids scattering trash logic across 6 controllers. The trash view is a per-project concept.

**Alternatives considered**:
- Add `trashed()` scope endpoints to each existing controller: Rejected — fragments the trash view logic
- Global trash endpoint (not per-project): Rejected — spec requires per-project trash (FR-010)

### R-008: Frontend Trash Store

**Decision**: Create a new `trash.js` Pinia store dedicated to trash operations (fetch trashed items, restore, force-delete). Keep it separate from existing stores to avoid polluting active-data stores with trashed-data logic.

**Rationale**: Separation of concerns — the trash store deals with a fundamentally different data set (deleted items) and different UI (trash tab).
