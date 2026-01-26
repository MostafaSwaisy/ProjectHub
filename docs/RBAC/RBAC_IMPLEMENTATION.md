# RBAC (Role-Based Access Control) Implementation Guide

This document describes the Role-Based Access Control system implemented for ProjectHub.

## Components Created

### 1. Middleware: RoleMiddleware

**Location:** `app/Http/Middleware/RoleMiddleware.php`

This middleware checks if the authenticated user has one of the required roles.

#### Usage in Routes:
```php
Route::middleware('auth:sanctum', 'role:admin,instructor')->group(function () {
    Route::post('/projects', [ProjectController::class, 'store']);
});

Route::middleware('auth:sanctum', 'role:instructor')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

#### How it works:
- Checks if user is authenticated
- Retrieves user's role from the database
- Compares against allowed roles passed as parameters
- Returns 403 Forbidden if unauthorized
- Returns 401 Unauthorized if not authenticated

---

### 2. Policies

#### ProjectPolicy (`app/Policies/ProjectPolicy.php`)

Controls access to Project resources.

**Methods:**

- **view(User $user, Project $project)** - Returns true if:
  - User is admin (can view all projects)
  - User is instructor AND owns the project
  - User is student AND is a member of the project

- **create(User $user)** - Returns true if:
  - User is instructor or admin

- **update(User $user, Project $project)** - Returns true if:
  - User is admin (can update all projects)
  - User is instructor AND owns the project

- **delete(User $user, Project $project)** - Returns true if:
  - User is admin (can delete all projects)
  - User is instructor AND owns the project

#### TaskPolicy (`app/Policies/TaskPolicy.php`)

Controls access to Task resources. Inherits project-level permissions.

**Methods:**

- **view(User $user, Task $task)** - Returns true if:
  - User is admin (can view all tasks)
  - User is instructor AND owns the project containing the task
  - User is member of the project containing the task

- **create(User $user, Column $column)** - Returns true if:
  - User is admin
  - User is instructor AND owns the project
  - User is member of the project

- **update(User $user, Task $task)** - Returns true if:
  - User is admin
  - User is instructor AND owns the project
  - User is the task assignee

- **delete(User $user, Task $task)** - Returns true if:
  - User is admin
  - User is instructor AND owns the project
  - User is the task assignee

#### UserPolicy (`app/Policies/UserPolicy.php`)

Controls access to User/Profile resources.

**Methods:**

- **view(User $user, User $target)** - Returns true if:
  - User is admin (can view all users)
  - User is viewing their own profile
  - User is instructor AND target is a student in one of their projects

- **viewProfile(User $user, User $target)** - Same as view()

- **update(User $user, User $target)** - Returns true if:
  - User is admin (can update all users)
  - User is updating their own profile

- **create(User $user)** - Returns true if:
  - User is admin

- **delete(User $user, User $target)** - Returns true if:
  - User is admin

---

### 3. AuthServiceProvider (`app/Providers/AuthServiceProvider.php`)

Registers all policies with Laravel's authorization system.

**Configured Policies:**
- `Project::class => ProjectPolicy::class`
- `Task::class => TaskPolicy::class`
- `User::class => UserPolicy::class`

---

## Usage in Controllers

### Using authorize() Method:
```php
public function show(Project $project)
{
    // Will throw AuthorizationException if not authorized
    $this->authorize('view', $project);

    return response()->json($project);
}

public function store(StoreProjectRequest $request)
{
    // Check create permission
    $this->authorize('create', Project::class);

    return Project::create($request->validated());
}

public function update(Request $request, Project $project)
{
    // Check update permission
    $this->authorize('update', $project);

    $project->update($request->validated());
    return response()->json($project);
}
```

### Using can() Method:
```php
public function edit(Project $project)
{
    if ($this->user()->cannot('update', $project)) {
        abort(403, 'Unauthorized action.');
    }

    // Show edit form
}

// In routes or controllers:
if (auth()->user()->can('delete', $task)) {
    // Show delete button
}
```

### In Requests:
```php
public function authorize(): bool
{
    return auth()->user()->can('create', Project::class);
}
```

---

## Usage in Views/Frontend

### Check Authorization:
```php
@if(auth()->user()->can('update', $project))
    <button>Edit Project</button>
@endif

@can('delete', $task)
    <button>Delete Task</button>
@endcan

@cannot('create', App\Models\Project::class)
    <p>You don't have permission to create projects</p>
@endcannot
```

---

## Role Hierarchy

### Admin Role
- Can view, create, update, delete all projects
- Can view, create, update, delete all tasks
- Can view and update all users
- Can create and delete users

### Instructor Role
- Can view their own projects
- Can create projects
- Can update and delete their own projects
- Can view, create, update, delete tasks in their projects
- Can view students assigned to their projects
- Can update their own profile

### Student Role
- Can view projects they are members of
- Can view, update, delete tasks assigned to them
- Can create tasks in projects they are members of
- Can only view their own profile
- Can update their own profile

---

## Project Membership

**Project members are tracked via:**
- `ProjectMember` model with pivot table `project_members`
- Relationship: `Project->members()` or `User->projects()`
- Student access to projects is determined by membership, not role alone

**Task membership (for view/create permissions):**
- Student must be a member of the project that contains the task's column/board

---

## Important Notes

1. **Roles are loaded dynamically:** Each policy checks `$user->role` relationship
2. **Admin checks come first:** Admins have broad access across all policies
3. **Ownership is checked second:** Role-specific checks after admin
4. **Membership is checked last:** For students, membership in project/group is required
5. **Task assignment:** Assignees can update/delete their own tasks regardless of other permissions

---

## Security Considerations

1. Always use `$this->authorize()` in controllers for sensitive operations
2. Use policy checks in form requests for input validation
3. Combine middleware for route-level protection
4. Use `@can/@cannot` directives in views for UI consistency
5. Remember: Middleware protects routes, Policies protect resources

---

## Testing Authorization

Example test:
```php
public function test_student_cannot_view_other_projects()
{
    $student = User::factory()->create();
    $student->role_id = Role::where('name', 'student')->first()->id;
    $student->save();

    $otherProject = Project::factory()->create();

    $this->assertFalse($student->can('view', $otherProject));
}

public function test_instructor_can_view_own_projects()
{
    $instructor = User::factory()->create();
    $instructor->role_id = Role::where('name', 'instructor')->first()->id;
    $instructor->save();

    $project = Project::factory()->create(['instructor_id' => $instructor->id]);

    $this->assertTrue($instructor->can('view', $project));
}
```

---

## Troubleshooting

### "This action is unauthorized" error
- Check user has a role assigned
- Verify user role is in the allowed list
- Check policy logic matches your use case
- Ensure relationships are properly loaded

### Policy not being triggered
- Verify `AuthServiceProvider` is registered in `config/app.php`
- Check model class-to-policy mapping is correct
- Ensure you're using `$this->authorize()` or `can()` method

### Task/Column authorization issues
- Verify the relationship chain: Task -> Column -> Board -> Project
- Check that project members are properly assigned
- Test with query debugging to see relationship queries
