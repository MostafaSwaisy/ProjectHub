# Quickstart: Soft Delete Support

**Feature**: 005-soft-delete | **Date**: 2026-03-07

## Prerequisites

- PHP 8.2+, Composer installed
- Node.js 18+, npm installed
- SQLite database at `database/database.sqlite`

## Setup

```bash
# 1. Switch to feature branch
git checkout 005-soft-delete

# 2. Install dependencies
composer install
npm install

# 3. Run migrations (adds deleted_at + deleted_by columns)
php artisan migrate

# 4. Start development servers
php artisan serve &
npm run dev
```

## Key Files to Modify

### Backend (in order)

1. **Migration**: `database/migrations/YYYY_MM_DD_HHMMSS_add_soft_deletes_to_all_tables.php`
   - Adds `deleted_at` and `deleted_by` to 10 tables

2. **Traits**: `app/Traits/HasSoftDeleteUser.php`, `app/Traits/HasCascadeSoftDeletes.php`
   - Reusable traits for `deleted_by` auto-population and cascade behavior

3. **Models** (10 files in `app/Models/`):
   - Add `use SoftDeletes, HasSoftDeleteUser, HasCascadeSoftDeletes;`
   - Define `$cascadeDeletes` on parent models

4. **Controllers** (6 existing + 1 new):
   - Existing: Remove manual cascade delete code in `destroy()` methods (trait handles it)
   - New: `app/Http/Controllers/TrashController.php` — trash view, restore, force-delete

5. **Routes**: `routes/api.php`
   - Add trash, restore, and force-delete routes with `->withTrashed()`

6. **Resources**: `app/Http/Resources/TrashItemResource.php`
   - Unified resource for trash view items

### Frontend (in order)

1. **Store**: `resources/js/stores/trash.js`
   - Pinia store for fetching trash, restore, force-delete actions

2. **Component**: `resources/js/components/projects/TrashTab.vue`
   - Trash tab with filtering, restore/delete buttons

3. **Router**: Update project routes to include trash tab

4. **Existing stores**: Update `tasks.js`, `kanban.js` to handle restore events

## Verification

```bash
# Run migrations
php artisan migrate

# Verify soft delete works via tinker
php artisan tinker
>>> $task = App\Models\Task::first();
>>> $task->delete();
>>> $task->trashed(); // true
>>> App\Models\Task::withTrashed()->find($task->id); // found
>>> $task->restore();
>>> $task->trashed(); // false
```

## Architecture Decisions

- **Single migration**: All 10 tables modified in one migration for atomicity
- **Reusable traits**: `HasSoftDeleteUser` and `HasCascadeSoftDeletes` avoid code duplication
- **Single TrashController**: Aggregates all trash operations per project
- **Timestamp matching for cascade restore**: Children restored only if `deleted_at` matches parent's
- **`deleted_by` column**: Direct column (not derived from activity log) for fast trash view queries
