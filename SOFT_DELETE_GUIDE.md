# Soft Delete Feature - Quick Reference Guide

## What is Soft Delete?
Items are marked as deleted (deleted_at timestamp) but remain in the database. They're hidden from normal views but can be restored or permanently deleted.

## User Features

### Soft Delete (Hide Item)
- Click delete button on any item (task, board, column, comment, subtask)
- Item disappears from board immediately
- Activity logged with type 'deleted'

### Restore Item
- Navigate to Project → Trash tab
- Find the deleted item
- Click "Restore" button
- Item reappears in original location (or choose new parent if original is deleted)
- Activity logged with type 'restored'

### Permanently Delete Item
- In Trash tab, click "Permanently Delete" button
- **Note**: Only available if you're:
  - Project owner, OR
  - Task assignee (for tasks only)
- Confirm in modal: "This action cannot be undone"
- Item is permanently removed from database
- Activity logged with type 'force_deleted'

### View Trash
- Go to Project view
- Click "Trash" tab
- Filter by type: All, Tasks, Boards, Columns, Comments, Subtasks
- See deletion date and who deleted it

## Developer Reference

### API Endpoints

**View Trash**
```
GET /api/projects/{project}/trash?type=tasks&page=1&per_page=20
```

**Restore Item**
```
POST /api/projects/{project}/restore
Body: { type: "task", id: 123 }
```

**Force Delete Item**
```
DELETE /api/projects/{project}/force
Body: { type: "task", id: 123 }
```

### Database
All tables have:
- `deleted_at` (timestamp) - NULL if not deleted
- `deleted_by` (user_id) - Who deleted it

Query soft-deleted items:
```php
// Exclude deleted (default)
Task::all();

// Include deleted
Task::withTrashed()->all();

// Only deleted
Task::onlyTrashed()->all();
```

### Models with Cascade
- **Project** → deletes all Boards
- **Board** → deletes all Columns
- **Column** → deletes all Tasks
- **Task** → deletes all Subtasks and Comments

### Activity Log
All delete operations logged with:
- `type`: 'deleted' | 'restored' | 'force_deleted'
- `subject_type`: Model class
- `subject_id`: Item ID
- `data`: Array with item_type and item_title

Example:
```php
Activity::create([
    'user_id' => auth()->id(),
    'project_id' => $project->id,
    'type' => 'deleted',
    'subject_type' => 'App\Models\Task',
    'subject_id' => $task->id,
    'data' => ['item_type' => 'task', 'item_title' => 'Design homepage']
]);
```

### Authorization
- **Soft Delete**: Any project member
- **Restore**: Any project member
- **Force Delete**:
  - Project owner (any item)
  - Task assignee (their tasks only)
  - Returns 403 if unauthorized

## Common Scenarios

### I deleted something by mistake
1. Go to Project → Trash
2. Find the item
3. Click "Restore"
4. Done!

### I want to permanently remove an item
1. Go to Project → Trash
2. Find the item
3. Click "Permanently Delete"
4. Confirm: "This action cannot be undone"
5. Item is gone forever

### What happens when I delete a project?
All boards, columns, tasks, subtasks, and comments are soft-deleted together. You can restore the entire project from the workspace trash, which will restore all children that were deleted with it.

### Can I restore just a subtask?
Only if:
- The subtask's task still exists (not force-deleted)
- If the task was deleted too, it will be restored automatically when you restore the subtask

### What about comments on deleted tasks?
Comments are soft-deleted with their parent task. They'll be restored if you restore the task.

## Testing

### Manual Test Flow
```
1. Create task with subtasks
2. Delete task
3. Verify task not visible on board
4. Check trash - task appears
5. Restore task
6. Verify task back with subtasks
7. Delete again, force-delete
8. Verify permanently removed (even with withTrashed)
```

### Via Tinker
```php
php artisan tinker

// Create and delete
$task = Task::first();
$task->delete();

// Check soft delete
$task->trashed(); // true

// Check cascade
$task->subtasks()->count(); // 0 (excluded by SoftDeletes)
Subtask::withTrashed()->where('task_id', $task->id)->first()->deleted_at; // SET

// Restore
$task->restore();
$task->subtasks()->count(); // 1 (back)

// Force delete
$task->forceDelete();
Subtask::withTrashed()->where('task_id', $task->id)->count(); // 0 (gone)
```

## Architecture

### Key Files
- **Models**: `app/Models/` - All models use SoftDeletes
- **Traits**:
  - `app/Traits/HasSoftDeleteUser.php` - Auto-populate deleted_by
  - `app/Traits/HasCascadeSoftDeletes.php` - Handle cascade operations
- **Controller**: `app/Http/Controllers/TrashController.php`
- **Routes**: `routes/api.php` - Trash endpoints
- **Frontend Store**: `resources/js/stores/trash.js`
- **Frontend Component**: `resources/js/components/projects/TrashTab.vue`

### Cascade Flow (Soft Delete)
```
$project->delete()
  ↓ HasCascadeSoftDeletes listener
  ├→ Load boards
  └→ foreach board: $board->delete()
      ↓ HasCascadeSoftDeletes listener
      ├→ Load columns
      └→ foreach column: $column->delete()
          ↓ HasCascadeSoftDeletes listener
          ├→ Load tasks
          └→ foreach task: $task->delete()
              ↓ HasCascadeSoftDeletes listener
              ├→ Load subtasks → delete each
              └→ Load comments → delete each
```

## Troubleshooting

### "Item not found in trash" error
- Item was never deleted (check if it still exists on board)
- Item was force-deleted (permanently gone)
- You're looking in wrong project

### "Cannot restore: parent item has been deleted"
- The item's parent was force-deleted
- You need to select a new parent before restoring
- Modal will ask for new parent

### "Unauthorized to permanently delete"
- You're not the project owner
- For tasks: you're not the assignee
- Ask project owner to force-delete

### Deleted items still visible on board
- Hard refresh browser (Ctrl+Shift+R)
- Clear browser cache
- Check if deletion actually succeeded (check trash)

## Future Enhancements

Potential improvements not yet implemented:
- Scheduled auto-purge (delete soft-deleted items after 30 days)
- Bulk restore/delete from trash
- Trash search
- Export trash items
- Restore history (who restored what when)
