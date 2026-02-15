# Data Model: Projects Management

**Feature**: 003-projects-management
**Date**: 2026-02-04
**Status**: Complete

## Entity Overview

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│     User        │────<│  ProjectMember   │>────│    Project      │
└─────────────────┘     └──────────────────┘     └─────────────────┘
        │                                                │
        │                                                │
        └───────────────────┬────────────────────────────┘
                            │ instructor_id
                            ▼
                    ┌─────────────────┐
                    │     Board       │
                    └─────────────────┘
                            │
                            ▼
                    ┌─────────────────┐
                    │    Column       │
                    └─────────────────┘
                            │
                            ▼
                    ┌─────────────────┐
                    │     Task        │
                    └─────────────────┘
```

## Entities

### Project (Modified)

**Table**: `projects`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Unique identifier |
| title | varchar(100) | required, max:100 | Project title |
| description | text | nullable, max:500 | Project description |
| instructor_id | bigint | FK → users.id, cascade delete | Project owner |
| timeline_status | enum | 'behind', 'on_track', 'ahead', default: 'on_track' | Timeline status |
| budget_status | enum | 'over_budget', 'on_budget', 'under_budget', default: 'on_budget' | Budget status |
| is_archived | boolean | default: false | **NEW** Archive flag |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Last update timestamp |

**Indexes**:
- `instructor_id` (existing)
- `is_archived` (new - for filtering performance)

**Eloquent Scopes** (to add):
```php
// Active projects (not archived)
public function scopeActive($query) {
    return $query->where('is_archived', false);
}

// Archived projects only
public function scopeArchived($query) {
    return $query->where('is_archived', true);
}

// User's projects (owner or member)
public function scopeForUser($query, $userId) {
    return $query->where('instructor_id', $userId)
        ->orWhereHas('members', fn($q) => $q->where('user_id', $userId));
}
```

### ProjectMember (Existing)

**Table**: `project_members`

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Unique identifier |
| project_id | bigint | FK → projects.id, cascade delete | Associated project |
| user_id | bigint | FK → users.id, cascade delete | Team member user |
| role | enum | 'owner', 'editor', 'viewer', default: 'viewer' | Member role |
| created_at | timestamp | auto | When member was added |
| updated_at | timestamp | auto | Last update timestamp |

**Unique Constraint**: `(project_id, user_id)` - prevents duplicate memberships

**Note**: The 'owner' role in project_members is distinct from instructor_id. The instructor_id on projects table determines the true owner. The 'owner' role here is for display purposes only.

### User (Existing - Reference)

**Table**: `users`

Relevant fields for project management:
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| name | varchar | Display name |
| email | varchar | Email (used for search) |
| role_id | bigint | FK → roles.id |

### Board (Existing - Reference)

**Table**: `boards`

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | PK |
| project_id | bigint | FK → projects.id |
| title | varchar | Board title |

**Relationship**: Each project has one or more boards. Default board created on project creation.

## Validation Rules

### Project Creation

```php
// StoreProjectRequest.php
[
    'title' => ['required', 'string', 'max:100'],
    'description' => ['nullable', 'string', 'max:500'],
    'timeline_status' => ['nullable', 'in:On Track,At Risk,Ahead'],
    'budget_status' => ['nullable', 'in:Within Budget,Over Budget'],
]
```

### Project Update

```php
// UpdateProjectRequest.php
[
    'title' => ['sometimes', 'required', 'string', 'max:100'],
    'description' => ['nullable', 'string', 'max:500'],
    'timeline_status' => ['nullable', 'in:On Track,At Risk,Ahead'],
    'budget_status' => ['nullable', 'in:Within Budget,Over Budget'],
]
```

### Project Member

```php
// ProjectMemberRequest.php
[
    'user_id' => ['required', 'exists:users,id'],
    'role' => ['required', 'in:editor,viewer'],
]
```

## State Transitions

### Project Archive State

```
                    archive()
    ┌─────────┐  ─────────────>  ┌────────────┐
    │ Active  │                  │  Archived  │
    │is_archived│  <─────────────  │is_archived │
    │ = false │    unarchive()   │  = true    │
    └─────────┘                  └────────────┘
```

**Transition Rules**:
- Only instructor (owner) can archive/unarchive
- Archiving does NOT delete any data
- Archived projects are read-only (boards/tasks cannot be modified)
- Archived projects excluded from dashboard statistics
- Unarchiving restores full editability

### Project Member Role Changes

```
    ┌──────────┐
    │  Viewer  │ ←──────────────────────┐
    └────┬─────┘                        │
         │ promote()                    │ demote()
         ▼                              │
    ┌──────────┐                        │
    │  Editor  │ ───────────────────────┘
    └──────────┘
```

**Note**: Instructor role is NOT managed via project_members table. The project's `instructor_id` field determines ownership and cannot be transferred in this feature scope.

## Relationships Summary

| From | To | Type | Via |
|------|-----|------|-----|
| Project | User (owner) | belongsTo | instructor_id |
| Project | User (members) | belongsToMany | project_members |
| Project | Board | hasMany | project_id |
| Project | Task | hasManyThrough | Board → Column → Task |
| ProjectMember | Project | belongsTo | project_id |
| ProjectMember | User | belongsTo | user_id |

## Migration Required

```php
// xxxx_add_is_archived_to_projects_table.php
Schema::table('projects', function (Blueprint $table) {
    $table->boolean('is_archived')->default(false)->after('budget_status');
    $table->index('is_archived');
});
```

## Computed Properties

### Project Model

```php
// Task counts (computed at query time)
public function getActiveTaskCountAttribute() {
    return $this->tasks()
        ->whereHas('column', fn($q) => $q->where('title', '!=', 'Done'))
        ->count();
}

public function getTotalTaskCountAttribute() {
    return $this->tasks()->count();
}

public function getCompletedTaskCountAttribute() {
    return $this->tasks()
        ->whereHas('column', fn($q) => $q->where('title', 'Done'))
        ->count();
}

public function getProgressPercentageAttribute() {
    $total = $this->total_task_count;
    if ($total === 0) return 0;
    return round(($this->completed_task_count / $total) * 100);
}
```

## Query Patterns

### List Projects with Filters

```php
Project::query()
    ->forUser($userId)
    ->active() // or ->archived() based on filter
    ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%"))
    ->when($status, fn($q) => $q->where('timeline_status', $status))
    ->when($role === 'owner', fn($q) => $q->where('instructor_id', $userId))
    ->when($role === 'member', fn($q) => $q->where('instructor_id', '!=', $userId))
    ->with(['members:id,name,email', 'boards'])
    ->withCount(['tasks as active_task_count' => fn($q) => $q->whereHas('column', fn($c) => $c->where('title', '!=', 'Done'))])
    ->orderBy($sortField, $sortDirection)
    ->paginate(20);
```
