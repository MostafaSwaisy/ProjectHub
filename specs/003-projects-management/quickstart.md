# Quickstart: Projects Management

**Feature**: 003-projects-management
**Date**: 2026-02-04

## Prerequisites

Before implementing this feature, ensure:

1. **002-dashboard-navigation is complete** - Layout, sidebar, navigation working
2. **Authentication working** - Laravel Sanctum configured and tested
3. **Database seeded** - Test users and projects exist
4. **Development servers running**:
   - Laravel: `php artisan serve`
   - Vite: `npm run dev`

## Implementation Order

### Phase 1: Backend Foundation

#### Step 1: Database Migration

```bash
php artisan make:migration add_is_archived_to_projects_table
```

```php
// Migration content:
Schema::table('projects', function (Blueprint $table) {
    $table->boolean('is_archived')->default(false)->after('budget_status');
    $table->index('is_archived');
});
```

```bash
php artisan migrate
```

#### Step 2: Update Project Model

Add to `app/Models/Project.php`:

```php
protected $fillable = [
    'title',
    'description',
    'instructor_id',
    'timeline_status',
    'budget_status',
    'is_archived', // ADD THIS
];

// ADD SCOPES
public function scopeActive($query)
{
    return $query->where('is_archived', false);
}

public function scopeArchived($query)
{
    return $query->where('is_archived', true);
}

public function scopeForUser($query, $userId)
{
    return $query->where('instructor_id', $userId)
        ->orWhereHas('members', fn($q) => $q->where('user_id', $userId));
}
```

#### Step 3: Create ProjectController

```bash
php artisan make:controller ProjectController --api
```

Implement methods: `index`, `store`, `show`, `update`, `destroy`, `archive`, `unarchive`, `duplicate`

#### Step 4: Create Form Requests

```bash
php artisan make:request StoreProjectRequest
php artisan make:request UpdateProjectRequest
php artisan make:request ProjectMemberRequest
```

#### Step 5: Update ProjectPolicy

Add to `app/Policies/ProjectPolicy.php`:

```php
public function archive(User $user, Project $project): bool
{
    return $user->id === $project->instructor_id;
}

public function manageMembers(User $user, Project $project): bool
{
    return $user->id === $project->instructor_id;
}
```

#### Step 6: Register API Routes

Add to `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Existing routes...

    // Projects CRUD
    Route::apiResource('projects', ProjectController::class);

    // Project actions
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive']);
    Route::post('projects/{project}/unarchive', [ProjectController::class, 'unarchive']);
    Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate']);

    // Project members
    Route::get('projects/{project}/members', [ProjectController::class, 'members']);
    Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::put('projects/{project}/members/{user}', [ProjectController::class, 'updateMember']);
    Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

    // User search
    Route::get('users/search', [UserController::class, 'search']);
});
```

### Phase 2: Frontend Foundation

#### Step 1: Create Projects Store

Create `resources/js/stores/projects.js`:

```javascript
import { defineStore } from 'pinia';
import axios from 'axios';

export const useProjectsStore = defineStore('projects', {
    state: () => ({
        projects: [],
        loading: false,
        error: null,
        pagination: null,
        filters: {
            search: '',
            status: '',
            role: 'all',
            archived: false,
            sort: 'updated_at',
            order: 'desc',
        },
    }),

    getters: {
        hasProjects: (state) => state.projects.length > 0,
        activeProjects: (state) => state.projects.filter(p => !p.is_archived),
        archivedProjects: (state) => state.projects.filter(p => p.is_archived),
    },

    actions: {
        async fetchProjects() {
            this.loading = true;
            try {
                const response = await axios.get('/api/projects', {
                    params: this.filters,
                });
                this.projects = response.data.data;
                this.pagination = response.data.meta;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load projects';
            } finally {
                this.loading = false;
            }
        },

        // Add more actions: createProject, updateProject, deleteProject, etc.
    },
});
```

#### Step 2: Create Permission Composable

Create `resources/js/composables/useProjectPermissions.js`:

```javascript
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

export function useProjectPermissions(project) {
    const authStore = useAuthStore();

    const isOwner = computed(() =>
        project.value?.instructor_id === authStore.user?.id
    );

    const canEdit = computed(() => {
        if (!project.value) return false;
        if (isOwner.value) return true;
        const membership = project.value.members?.find(
            m => m.user_id === authStore.user?.id
        );
        return membership?.role === 'editor';
    });

    const canDelete = computed(() => isOwner.value);
    const canArchive = computed(() => isOwner.value);
    const canManageMembers = computed(() => isOwner.value);

    return {
        isOwner,
        canEdit,
        canDelete,
        canArchive,
        canManageMembers,
    };
}
```

#### Step 3: Create UI Components

Order of component creation:

1. `ProjectCard.vue` - Grid view card
2. `ProjectRow.vue` - List view row
3. `ProjectFilters.vue` - Filter toolbar
4. `ProjectSearch.vue` - Search input
5. `ProjectModal.vue` - Create/Edit form
6. `DeleteConfirmModal.vue` - Delete confirmation
7. `ArchiveConfirmModal.vue` - Archive confirmation
8. `TeamMemberManager.vue` - Team management

#### Step 4: Implement ProjectsList Page

Update `resources/js/pages/projects/ProjectsList.vue` with full implementation.

### Phase 3: Integration

#### Step 1: Update Dashboard Statistics

Modify `DashboardController::stats()` to exclude archived projects:

```php
$projects = Project::forUser($userId)->active()->get();
```

#### Step 2: Update Navigation

Ensure Projects navigation link is active when on projects routes.

#### Step 3: Test All Flows

1. List projects (grid/list toggle)
2. Create new project
3. Edit project details
4. Archive/unarchive project
5. Delete project with confirmation
6. Add/remove team members
7. Filter and sort
8. Search

## Testing Checklist

### Backend Tests

```bash
# Run specific tests
php artisan test --filter=ProjectControllerTest
```

- [ ] List projects returns user's projects only
- [ ] Create project sets correct instructor_id
- [ ] Create project creates default board
- [ ] Update project validates data
- [ ] Delete requires ownership
- [ ] Archive/unarchive updates is_archived
- [ ] Member operations require ownership

### Frontend Tests

```bash
npm run test
```

- [ ] Store fetches and caches projects
- [ ] Filters update URL params
- [ ] Search debounces correctly
- [ ] Modals open/close properly
- [ ] Form validation shows errors
- [ ] Permissions hide/show actions

## Common Issues

### Issue: Projects not loading

**Cause**: Missing auth token
**Fix**: Ensure axios default headers include Bearer token

### Issue: Cannot create project

**Cause**: User role doesn't have permission
**Fix**: Check user has 'instructor' or 'admin' role

### Issue: Archive not working

**Cause**: Missing is_archived column
**Fix**: Run `php artisan migrate`

### Issue: Filters not persisting

**Cause**: URL params not synced
**Fix**: Use `watchEffect` to sync filters with router query

## Key Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Controllers/ProjectController.php` | API endpoints |
| `app/Models/Project.php` | Model with scopes |
| `app/Policies/ProjectPolicy.php` | Authorization |
| `resources/js/stores/projects.js` | Pinia store |
| `resources/js/pages/projects/ProjectsList.vue` | Main page |
| `resources/js/components/projects/*.vue` | UI components |
