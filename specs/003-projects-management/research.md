# Research: Projects Management

**Feature**: 003-projects-management
**Date**: 2026-02-04
**Status**: Complete

## Research Tasks

### 1. Database Schema for Archive Feature

**Decision**: Add `is_archived` boolean column to existing `projects` table

**Rationale**:
- Simple boolean flag is sufficient for active/archived state
- Minimal migration impact - single column addition
- Easy to query with Eloquent scopes (`whereArchived()`, `whereActive()`)
- No need for separate archive table as we're not tracking archive history

**Alternatives Considered**:
- Separate `archived_projects` table: Rejected - unnecessary complexity, harder to unarchive
- `archived_at` timestamp: Rejected - boolean is simpler, timestamp adds no value for our use case
- Soft deletes for archive: Rejected - soft deletes serve different purpose (recovery), mixing concepts is confusing

### 2. Project Duplication Strategy

**Decision**: Server-side duplication with transaction and selective copying

**Rationale**:
- Database transaction ensures atomicity - no partial duplicates
- Server handles the heavy lifting, reducing client complexity
- Can selectively copy boards/columns/tasks based on user preference
- Team members intentionally NOT copied (as per spec)

**Alternatives Considered**:
- Client-side duplication via multiple API calls: Rejected - race conditions, partial failures
- Queue-based async duplication: Rejected - over-engineering for expected data sizes

### 3. Filter/Sort State Persistence

**Decision**: URL query parameters as primary, localStorage as backup

**Rationale**:
- URL params enable shareable links with filters applied
- Browser back/forward navigation works naturally
- localStorage fallback for returning users
- Vue Router handles query param reactivity well

**Alternatives Considered**:
- localStorage only: Rejected - can't share filtered views
- Server-side storage: Rejected - over-engineering, adds API complexity

### 4. Team Member Search Implementation

**Decision**: Server-side search with debounced API calls

**Rationale**:
- Security - client shouldn't have full user list
- Scalability - works regardless of user count
- 300ms debounce prevents excessive API calls
- Returns paginated results (first 10 matches)

**Alternatives Considered**:
- Client-side filtering: Rejected - security concern, performance with many users
- Real-time search (no debounce): Rejected - unnecessary API load

### 5. Modal vs Page for Create/Edit

**Decision**: Modal dialogs for create/edit forms

**Rationale**:
- Consistent with existing UX patterns in the app
- User maintains context of projects list
- Faster perceived performance (no page navigation)
- Simpler state management (single page component)

**Alternatives Considered**:
- Dedicated pages for create/edit: Rejected - breaks user flow, feels heavier
- Drawer/sidebar: Rejected - less space for form fields on mobile

### 6. Cascade Delete Implementation

**Decision**: Database-level cascade delete with application confirmation

**Rationale**:
- Foreign key constraints already set up with `onDelete('cascade')`
- Application layer handles confirmation UX
- Single DELETE query handles all related data
- No orphaned records possible

**Alternatives Considered**:
- Application-level cascade: Rejected - slower, risk of orphaned data
- Soft delete first, then hard delete: Rejected - complicates queries unnecessarily

### 7. Permission Checking Architecture

**Decision**: Laravel Policy + Vue composable for consistent permission checks

**Rationale**:
- Laravel Policy centralizes backend authorization
- Vue composable (`useProjectPermissions`) provides reactive frontend checks
- Same permission logic in both layers prevents UI/API mismatches
- Existing `ProjectPolicy.php` can be extended

**Alternatives Considered**:
- Frontend-only checks: Rejected - security risk, API must enforce
- Inline permission checks: Rejected - code duplication, hard to maintain

### 8. Grid/List View Toggle

**Decision**: CSS-based toggle with single component

**Rationale**:
- Same data, different visual presentation
- CSS Grid/Flexbox handles layout switching efficiently
- Single component reduces code duplication
- View preference stored in localStorage

**Alternatives Considered**:
- Separate components for grid/list: Rejected - code duplication
- Server-side rendering per view: Rejected - unnecessary complexity

## Technology Best Practices

### Laravel Best Practices Applied

1. **Form Request Validation**: Use `StoreProjectRequest` and `UpdateProjectRequest` for validation
2. **API Resources**: Extend `ProjectResource` for consistent JSON transformation
3. **Eloquent Scopes**: Add `scopeActive()`, `scopeArchived()` for reusable queries
4. **Policy Authorization**: Extend `ProjectPolicy` for archive/member operations
5. **Service Layer**: Extract business logic to `ProjectService` for testability

### Vue 3 Best Practices Applied

1. **Composition API**: Use `<script setup>` syntax throughout
2. **Pinia Store**: Centralize projects state in dedicated store
3. **Composables**: Extract reusable logic (permissions, filters) into composables
4. **storeToRefs**: Maintain reactivity when destructuring store state
5. **computed**: Use for derived state (filtered projects, hasProjects)

### API Design Best Practices Applied

1. **RESTful Endpoints**: Follow standard REST conventions
2. **Pagination**: Return paginated results for list endpoint
3. **Filtering**: Support query params for server-side filtering
4. **Error Responses**: Consistent error format with appropriate HTTP codes
5. **Resource Loading**: Include related data via `?include=members,board`

## Integration Patterns

### Dashboard Integration

- Archived projects MUST be excluded from dashboard statistics
- Modify existing `DashboardController::stats()` to filter by `is_archived = false`
- No UI changes to dashboard required

### Kanban Board Integration

- Project archive status affects board editability
- Archived project boards are read-only (view permission only)
- Unarchive action enables editing again

## Open Questions Resolved

| Question | Resolution |
|----------|------------|
| Should is_archived default to false? | Yes, new projects are active by default |
| How to handle 404 for archived projects? | Show project but indicate archived status |
| Should search include archived? | No, search only active by default, separate archived tab |
| Max team members per project? | No hard limit, paginate display at 20 |
