# Research: Kanban Board & Task Management

**Feature**: 004-kanban-task-management
**Date**: 2026-02-05
**Status**: Complete

## Executive Summary

This research consolidates findings from codebase exploration to resolve technical decisions for the Kanban task management feature. The existing infrastructure is substantial - most backend models, controllers, and basic frontend components already exist. This feature focuses on completing UI components and adding advanced functionality.

---

## Research Areas

### 1. Existing Infrastructure Assessment

**Question**: What is already implemented vs. what needs to be built?

**Findings**:

| Component | Status | Notes |
|-----------|--------|-------|
| Task Model | EXISTS | Full relationships, progress calculation |
| Board Model | EXISTS | Auto-creates 5 default columns |
| Column Model | EXISTS | WIP limit support |
| Subtask Model | EXISTS | Position, completion status |
| Comment Model | EXISTS | Threading support (parent_id) |
| Label Model | EXISTS | Color support, project-scoped |
| Activity Model | EXISTS | Polymorphic, JSON data field |
| TaskController | EXISTS | CRUD + move + WIP limit enforcement |
| BoardController | EXISTS | CRUD with eager loading |
| SubtaskController | MISSING | Need full CRUD endpoints |
| CommentController | MISSING | Need CRUD with 15-min edit window |
| LabelController | MISSING | Need CRUD for project labels |
| ActivityController | MISSING | Need read-only listing |
| KanbanBoard.vue | EXISTS | Basic board layout |
| KanbanColumn.vue | EXISTS | Drag-drop zones |
| TaskCard.vue | EXISTS | Priority colors, menu actions |
| TaskModal.vue | EXISTS | Create/edit form |
| TaskDetailModal.vue | EXISTS | Basic view - needs enhancement |
| SubtaskList.vue | MISSING | Subtask management UI |
| CommentSection.vue | MISSING | Comment display/input UI |
| ActivityFeed.vue | MISSING | Activity log display |
| LabelManager.vue | MISSING | Project label CRUD UI |
| FilterBar.vue | MISSING | Advanced filter UI with URL sync |

**Decision**: Build missing controllers (Subtask, Comment, Label, Activity) and frontend components. Enhance existing TaskDetailModal with subtasks, comments, and activity sections.

**Rationale**: Leveraging existing infrastructure minimizes duplication and ensures consistency with established patterns.

---

### 2. Drag-and-Drop Implementation

**Question**: What drag-and-drop approach is best for Vue 3 Kanban boards?

**Findings**:
- Native HTML5 Drag and Drop API is already implemented in existing components
- `useDragDrop.js` composable exists with draggable/droppable bindings
- Kanban store tracks: `draggedTaskId`, `draggedFromColumn`, `draggedOverColumn`
- Optimistic updates with rollback pattern already established

**Decision**: Continue using native HTML5 Drag and Drop with existing composable.

**Rationale**: Already implemented and working. Adding a library (vue-draggable, dnd-kit) would introduce unnecessary complexity.

**Alternatives Considered**:
- vue-draggable: More features but adds dependency
- dnd-kit: Powerful but heavyweight for our needs

---

### 3. Comment Edit Window Implementation

**Question**: How to implement the 15-minute edit window for comments?

**Findings**:
- Comment model has `created_at` timestamp
- No `edited_at` timestamp currently stored (spec requires showing "edited" indicator)
- Laravel Carbon makes time comparison trivial

**Decision**:
1. Add `edited_at` nullable timestamp to comments table (if not present)
2. CommentPolicy checks: `$comment->created_at->addMinutes(15)->isFuture()`
3. Frontend shows "Edited" badge when `edited_at !== null`
4. Edit/delete buttons hidden after 15 minutes (client-side)
5. Backend enforces 15-minute window (server-side validation)

**Rationale**: Server-side enforcement is critical for security. Client-side hiding improves UX by not showing options that would fail.

---

### 4. Real-time Updates Strategy

**Question**: How to handle multiple users viewing the same board?

**Findings**:
- Spec explicitly lists "WebSocket-based instant updates" as OUT OF SCOPE
- Current implementation uses standard Axios requests
- No Laravel Echo or Pusher configuration present

**Decision**: Use manual refresh or polling-based updates initially.

**Implementation**:
1. Add "Refresh" button to board header
2. Optional: Add 30-second auto-poll for board data (configurable)
3. Show "Last updated: X minutes ago" indicator

**Rationale**: Matches spec's out-of-scope declaration. Real-time can be added later without breaking changes.

---

### 5. Filter URL Persistence

**Question**: How to persist filter state in URL for sharing?

**Findings**:
- Vue Router 4 supports query params via `useRoute()` and `router.push()`
- Similar pattern implemented in ProjectsList for project filtering
- Filters: search, labels[], assignees[], priorities[], due_date_range

**Decision**:
1. Serialize filters to URL query params on change
2. Read filters from URL on component mount
3. Use `watchEffect` to sync store state with URL
4. Debounce URL updates to avoid excessive history entries

**URL Format**:
```
/projects/{id}/kanban?search=login&labels=1,3&assignee=5&priority=high,critical&due=overdue
```

**Rationale**: Standard web pattern, enables sharing filtered views, works with browser back/forward.

---

### 6. Activity Logging Strategy

**Question**: What events should trigger activity logging?

**Findings**:
- Activity model exists with: user_id, project_id, type, subject_type, subject_id, data (JSON)
- Types specified in spec: created, status_change, assignment_change, comment_added

**Decision**: Log these events via Laravel model observers:

| Event | Type | Subject | Data |
|-------|------|---------|------|
| Task created | `task.created` | Task | `{title}` |
| Task moved | `task.moved` | Task | `{from_column, to_column}` |
| Task assigned | `task.assigned` | Task | `{assignee_id, assignee_name}` |
| Task due date set | `task.due_date_changed` | Task | `{old_date, new_date}` |
| Comment added | `comment.created` | Comment | `{excerpt}` |
| Subtask completed | `subtask.completed` | Subtask | `{title}` |

**Implementation**: Create `ActivityObserver` or use model events in Task/Comment models.

**Rationale**: Centralized logging ensures consistency. JSON data field allows flexible event metadata.

---

### 7. Mobile Touch Support

**Question**: How to support task movement on mobile devices?

**Findings**:
- Spec requires: "Given I am on a mobile device, When I tap a task and select 'Move to...', Then I can choose a destination column from a menu"
- TaskCard.vue already has dropdown menu with "Move to..." option
- Current implementation: submenu with column list

**Decision**: Enhance existing "Move to..." menu:
1. Show column names as submenu items
2. Disable current column option
3. Check WIP limits before allowing move
4. Show warning if target column at WIP limit

**Rationale**: Menu-based approach is more reliable than touch drag on mobile. Already partially implemented.

---

### 8. Subtask Reordering

**Question**: How to implement subtask drag-to-reorder?

**Findings**:
- Subtask model has `position` field
- No current reordering UI implemented
- Similar pattern exists for column reordering (not implemented yet)

**Decision**:
1. Use simple drag handles with native drag-drop
2. On drop: call `PATCH /tasks/{task}/subtasks/reorder` with new order
3. Backend: transaction to update all positions atomically

**Rationale**: Consistent with task drag-drop approach. Simple implementation sufficient for list reordering.

---

### 9. Label Color Picker

**Question**: What UI component for color selection?

**Findings**:
- Labels require color (stored as hex value)
- No existing color picker in codebase
- Tailwind provides preset color palette

**Decision**: Use preset color palette (12-16 colors) instead of free-form picker:
- Predefined colors: red, orange, yellow, green, teal, blue, indigo, purple, pink, gray
- Displayed as clickable color swatches
- Ensures consistent, accessible colors

**Rationale**: Simpler UX, ensures colors work well with dark theme, no external library needed.

---

### 10. Performance Optimization

**Question**: How to ensure board loads 100 tasks within 3 seconds?

**Findings**:
- Current TaskController uses eager loading: `with(['assignee', 'labels', 'subtasks'])`
- BoardController loads full hierarchy: `with(['columns.tasks.assignee', 'columns.tasks.labels'])`
- Pagination not currently used for board view

**Decision**:
1. Keep eager loading for relationships
2. Add `withCount('subtasks', 'comments')` for counts without full load
3. Lazy load comments/activity only when task detail modal opens
4. Consider virtual scrolling if columns exceed 50 tasks (future enhancement)

**Rationale**: Eager loading prevents N+1 queries. Lazy loading detail data reduces initial payload.

---

## Technology Decisions Summary

| Area | Decision | Rationale |
|------|----------|-----------|
| Drag-Drop | Native HTML5 + existing composable | Already implemented, works well |
| Comment Editing | 15-min window enforced server-side | Security requirement |
| Real-time | Manual refresh / polling | Spec out-of-scope |
| URL Persistence | Vue Router query params | Standard pattern |
| Activity Logging | Model observers | Centralized, consistent |
| Mobile Move | Menu-based selection | More reliable than touch drag |
| Subtask Reorder | Drag handles + batch update | Simple, consistent |
| Color Picker | Preset palette (12 colors) | Simpler UX, no library |
| Performance | Eager load + lazy detail | Balance speed and payload |

---

## Open Questions (None)

All technical decisions have been made. Ready for Phase 1 design.
