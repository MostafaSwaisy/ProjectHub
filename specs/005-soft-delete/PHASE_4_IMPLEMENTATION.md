# Phase 4: User Story 2 - View Deleted Items in Trash
## Complete Implementation (Tasks T024-T029)

### Overview
Successfully implemented a comprehensive trash management system for viewing and managing soft-deleted items across all project entity types (Tasks, Boards, Columns, Subtasks, Comments). The implementation includes both backend API and frontend UI with filtering, pagination, and item metadata.

---

## T024: TrashController Implementation
**File:** `app/Http/Controllers/TrashController.php`

### Features:
- `index()` method: GET /api/projects/{project}/trash
- Query parameters:
  - `?type=tasks|boards|columns|subtasks|comments` (optional)
  - `?page=1` (default: 1)
  - `?per_page=20` (default: 20, max: 100)
- Aggregates soft-deleted items from all entity types using `onlyTrashed()`
- Returns paginated results via TrashItemResource
- Authorization: Only shows items to project members
- Loads relationships for deleted_by user with each item

### Technical Details:
- Queries soft-deleted items separately for each entity type
- Merges results and sorts by deleted_at descending (newest first)
- Manual pagination on collections
- Eager loads relationships to prevent N+1 queries

---

## T025: TrashItemResource Implementation
**File:** `app/Http/Resources/TrashItemResource.php`

### Response Format:
```json
{
  "id": 1,
  "type": "task",
  "title": "Task title",
  "deleted_at": "2026-03-07T10:30:00Z",
  "deleted_by": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "parent": {
    "type": "column",
    "id": 5,
    "title": "In Progress",
    "exists": true
  }
}
```

### Features:
- Unified resource handling different entity types
- Includes deleted_by user information
- Shows parent entity with exists flag (for deleted parents)
- Parent relationships:
  - Task → Column (and Board)
  - Subtask → Task
  - Comment → Task
  - Column → Board
  - Board → Project

---

## T026: API Route Configuration
**File:** `routes/api.php`

### Route Added:
```php
// Trash Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('projects/{project}/trash', [TrashController::class, 'index'])->name('trash.index');
});
```

### Security:
- Protected with `auth:sanctum` middleware
- Authorization policy check in controller
- Only accessible to authenticated, project-member users

---

## T027: Pinia Trash Store Implementation
**File:** `resources/js/stores/trash.js`

### State:
- `items`: Array of trash items
- `loading`: Loading state
- `error`: Error messages
- `pagination`: { current_page, per_page, total, last_page }
- `activeFilter`: Current filter (null for all, or type name)

### Actions:
- `fetchTrashItems(projectId, type=null, page=1, perPage=20)`: Fetch trash items with optional filtering
- `setFilter(type)`: Update active filter
- `resetFilter()`: Clear filter
- `clearItems()`: Clear items from state

### Computed Properties (Getters):
- `filteredItems`: Items filtered by activeFilter
- `hasItems`: Boolean indicating if items exist
- `isEmpty`: Boolean indicating if no items

### Features:
- Full pagination support
- Type filtering (tasks, boards, columns, subtasks, comments)
- Error handling and state management

---

## T028: TrashTab.vue Component Implementation
**File:** `resources/js/components/projects/TrashTab.vue`

### Features:

#### Filter Section
- Six filter buttons: All, Tasks, Boards, Columns, Subtasks, Comments
- Active state styling with Indigo primary color (#667eea)
- Responsive button layout

#### Display States
1. **Loading State**: Spinner with "Loading trash items..." message
2. **Empty State**: Icon with "No deleted items" message
3. **Items List**: Detailed trash item cards

#### Trash Item Display
Each item shows:
- **Type Icon**: Color-coded icon matching entity type
  - Task: Blue (#3b82f6)
  - Board: Purple (#a855f7)
  - Column: Pink (#ec4899)
  - Subtask: Green (#22c55e)
  - Comment: Orange (#f97316)
- **Title**: Item title (truncated for long text)
- **Type Badge**: Small label indicating entity type
- **Metadata**: Deletion timestamp (relative time) and deleter name
- **Parent Reference**: Shows parent entity with exists status
- **Action Buttons**: Restore (green) and Delete (red) buttons (placeholder for Phase 5)

#### Pagination
- Previous/Next buttons
- Page info display (Page X of Y)
- Disabled state when at first/last page

### Styling
- Dark theme consistent with application design
- Responsive design for mobile
- CSS variables from design system
- Hover states and transitions
- Relative time formatting using dayjs

---

## T029: Trash Tab Integration in Project View
**File:** `resources/js/pages/projects/KanbanView.vue`

### Changes:

#### Tab Navigation
Added three tabs to project view:
1. **Boards**: Main kanban board view (existing)
2. **Members**: Placeholder for members management
3. **Trash**: New trash management tab

#### Tab Structure
- Tab buttons with icons and labels
- Active state styling with bottom border accent
- Smooth transitions between tabs
- Mobile-responsive horizontal scroll

#### Integration
- Imported TrashTab component
- Added `activeTab` ref to track current tab
- TrashTab component receives `projectId` as prop
- Tab content conditionally renders based on `activeTab` value

### Styling
- Consistent with application design system
- Tab navigation bar with subtle border
- Icon and text labels in each tab button
- Mobile-optimized with smaller text and icons

---

## Model Updates

All models now include the following additions:

### Task, Board, Column, Subtask, and Comment Models
```php
// In fillable array
'deleted_by',

// New relationship
public function deletedBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'deleted_by');
}
```

---

## Database Schema

The existing migration `2026_03_07_000000_add_soft_deletes_to_all_tables.php` already includes:
- `deleted_at` timestamp column for soft delete tracking
- `deleted_by` unsigned big integer column to track who deleted the item
- Foreign key constraint on deleted_by → users.id
- Index on deleted_at for efficient queries

---

## API Usage Examples

### Fetch All Trash Items
```
GET /api/projects/1/trash
```

### Fetch Only Deleted Tasks
```
GET /api/projects/1/trash?type=tasks
```

### Fetch with Pagination
```
GET /api/projects/1/trash?page=2&per_page=50
```

### Fetch Deleted Boards (Page 1)
```
GET /api/projects/1/trash?type=boards&page=1&per_page=20
```

---

## Key Files Created/Modified

### Created Files:
1. `app/Http/Controllers/TrashController.php` - 6.3 KB
2. `app/Http/Resources/TrashItemResource.php` - 2.8 KB
3. `resources/js/stores/trash.js` - 2.8 KB
4. `resources/js/components/projects/TrashTab.vue` - 17 KB

### Modified Files:
1. `routes/api.php` - Added TrashController import and trash routes
2. `resources/js/pages/projects/KanbanView.vue` - Added tabs with TrashTab integration
3. Model files (Task, Board, Column, Subtask, Comment) - Added deletedBy() relationships and 'deleted_by' to fillable

---

## Key Features Implemented

### Backend
- Aggregates soft-deleted items from 5 entity types
- Efficient eager loading of relationships
- Authorization checks per project
- Pagination support with configurable per_page
- Metadata tracking (deleted_at, deleted_by)
- Parent entity tracking with exists status

### Frontend
- Filterable trash view with 6 filter options
- Visual entity type differentiation with icons and colors
- Pagination controls
- Empty state messaging
- Loading state feedback
- Responsive design for mobile and desktop
- Relative time display (e.g., "2 hours ago")
- Action buttons ready for Phase 5 (restore/permanent delete)

### Security
- Soft deletes prevent accidental data loss
- User tracking for audit trail
- Authorization check (project membership)
- Read-only trash view (actions in Phase 5)

---

## Phase 5 Integration Points

The following are prepared for Phase 5 implementation:
- Restore buttons in TrashTab (will call restore endpoint)
- Permanent delete buttons (will call permanent delete endpoint)
- Restore and delete API endpoints in TrashController
- Success/error notifications for operations
- Confirmation modals for destructive operations

---

## Summary

This implementation provides a complete trash management system that:
1. Displays all soft-deleted items across the project
2. Organizes items by type with filtering
3. Shows deletion metadata (who deleted, when)
4. Tracks parent entities for context
5. Provides pagination for large trash lists
6. Maintains security and authorization
7. Follows application design patterns and styling

All components are fully functional and ready for Phase 5 restore/delete operations.
