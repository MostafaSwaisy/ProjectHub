# RBAC Usage Examples

This document provides practical examples of how to use the RBAC components in your ProjectHub application.

---

## Example 1: ProjectController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects accessible to the user.
     */
    public function index(): JsonResponse
    {
        // No need to check view permission here - policy will be checked in show()
        // But you can filter projects if needed
        $projects = auth()->user()->role->name === 'admin'
            ? Project::all()
            : Project::where('instructor_id', auth()->user()->id)
                ->orWhereHas('members', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })
                ->get();

        return response()->json($projects);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        // Check if user can create projects
        $this->authorize('create', Project::class);

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'instructor_id' => auth()->user()->id,
        ]);

        return response()->json($project, 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        // Check if user can view this project
        $this->authorize('view', $project);

        return response()->json($project->load('boards.columns.tasks'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        // Check if user can update this project
        $this->authorize('update', $project);

        $project->update($request->validated());

        return response()->json($project);
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        // Check if user can delete this project
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(null, 204);
    }
}
```

---

## Example 2: TaskController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Column;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $column = Column::findOrFail($request->column_id);

        // Check if user can create tasks in this column
        $this->authorize('create', $column);

        $task = Task::create([
            'column_id' => $column->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assignee_id' => $request->assignee_id,
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        // Check if user can view this task
        $this->authorize('view', $task);

        return response()->json($task->load('assignee', 'subtasks', 'comments.user'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Check if user can update this task
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json($task);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Check if user can delete this task
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Move a task to a different column or position.
     */
    public function move(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        // Check if user can update this task
        $this->authorize('update', $task);

        $newColumn = Column::findOrFail($request->column_id);

        // Verify user can create in the new column
        $this->authorize('create', $newColumn);

        $task->update([
            'column_id' => $newColumn->id,
            'position' => $request->position ?? $newColumn->tasks()->count(),
        ]);

        return response()->json($task);
    }
}
```

---

## Example 3: UserController (Profile Management)

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display the specified user's profile.
     */
    public function show(User $user): JsonResponse
    {
        // Check if user can view this profile
        $this->authorize('view', $user);

        return response()->json($user->only('id', 'name', 'email', 'role_id'));
    }

    /**
     * Update the specified user's profile.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // Check if user can update this profile
        $this->authorize('update', $user);

        $user->update($request->validated());

        return response()->json($user);
    }

    /**
     * Get current authenticated user.
     */
    public function current(): JsonResponse
    {
        return response()->json(auth()->user()->load('role'));
    }

    /**
     * Get list of students (for instructors only).
     */
    public function listStudents(): JsonResponse
    {
        $instructor = auth()->user();

        // Only instructors and admins can see students
        if (!$instructor->role || !in_array($instructor->role->name, ['instructor', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get students from instructor's projects
        $students = User::whereHas('projects', function ($query) use ($instructor) {
            if ($instructor->role->name === 'instructor') {
                $query->where('instructor_id', $instructor->id);
            }
        })->get();

        return response()->json($students);
    }
}
```

---

## Example 4: Form Requests with Authorization

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only instructors and admins can create projects
        return auth()->user()->can('create', \App\Models\Project::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
```

```php
<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the task from route parameter
        $task = $this->route('task');

        // Check if user can update this task
        return auth()->user()->can('update', $task);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'assignee_id' => 'nullable|exists:users,id',
            'column_id' => 'nullable|exists:columns,id',
            'position' => 'nullable|integer|min:0',
        ];
    }
}
```

---

## Example 5: Route Middleware Usage

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;

// Public routes
Route::post('/login', 'Auth\LoginController@login');
Route::post('/register', 'Auth\RegisterController@register');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Everyone can access their dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Projects - require authentication, specific role checked in controller
    Route::apiResource('projects', ProjectController::class);

    // Tasks - require authentication, specific role checked in controller
    Route::apiResource('tasks', TaskController::class);

    // Profile management
    Route::get('/users/current', 'UserController@current');
    Route::get('/users/{user}', 'UserController@show');
    Route::put('/users/{user}', 'UserController@update');
});

// Instructor-only routes
Route::middleware('auth:sanctum', 'role:instructor,admin')->group(function () {
    Route::get('/analytics/students', 'StudentController@list');
    Route::post('/projects', [ProjectController::class, 'store']);
});

// Admin-only routes
Route::middleware('auth:sanctum', 'role:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/users', [AdminController::class, 'createUser']);
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
});
```

---

## Example 6: Vue Component with @can Directives

```vue
<template>
  <div class="project-detail">
    <div class="header">
      <h1>{{ project.title }}</h1>

      <!-- Edit button only visible if user can update -->
      <div v-can="'update'" :model="project" class="actions">
        <button @click="editProject" class="btn-primary">
          Edit Project
        </button>
      </div>

      <!-- Delete button only visible if user can delete -->
      <div v-can="'delete'" :model="project" class="actions">
        <button @click="deleteProject" class="btn-danger">
          Delete Project
        </button>
      </div>
    </div>

    <!-- Project content -->
    <div class="content">
      <p>{{ project.description }}</p>

      <!-- Add task button visible if user is in project -->
      <div v-if="canCreateTasks" class="add-task">
        <button @click="openAddTaskModal" class="btn-secondary">
          + Add Task
        </button>
      </div>

      <!-- Kanban board -->
      <KanbanBoard :project="project" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
  project: Object,
});

const emit = defineEmits(['edit', 'delete']);
const { can } = usePermissions();

// Check if user can create tasks in this project
const canCreateTasks = computed(() => {
  return can('create', 'Task');
});

const editProject = () => {
  if (can('update', props.project)) {
    emit('edit', props.project);
  }
};

const deleteProject = () => {
  if (can('delete', props.project)) {
    emit('delete', props.project);
  }
};
</script>
```

---

## Example 7: Composable for Permission Checking (Frontend)

```javascript
// resources/js/composables/usePermissions.js

import { ref } from 'vue';

export function usePermissions() {
  const currentUser = ref(null);

  // Load current user on mount
  const loadCurrentUser = async () => {
    const response = await fetch('/api/users/current');
    currentUser.value = await response.json();
  };

  // Check if user can perform action
  const can = (action, resource) => {
    if (!currentUser.value) return false;

    const role = currentUser.value.role?.name;

    // Admin can do everything
    if (role === 'admin') return true;

    // Role-specific checks
    const permissions = {
      'Project.create': ['instructor', 'admin'],
      'Project.update': ['instructor', 'admin'],
      'Project.delete': ['instructor', 'admin'],
      'Task.create': ['instructor', 'admin', 'student'],
      'Task.update': ['instructor', 'admin', 'student'],
      'Task.delete': ['instructor', 'admin', 'student'],
    };

    const key = `${resource}.${action}`;
    return permissions[key]?.includes(role) || false;
  };

  const cannot = (action, resource) => {
    return !can(action, resource);
  };

  return {
    currentUser,
    can,
    cannot,
    loadCurrentUser,
  };
}
```

---

## Example 8: Error Handling

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle authorization exceptions
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => 'This action is unauthorized.',
                'status' => 403,
            ], 403);
        }

        return parent::render($request, $exception);
    }
}
```

---

## Quick Reference

### Checking in Controllers
```php
// Check if user can perform action (throws exception if false)
$this->authorize('create', Project::class);
$this->authorize('view', $project);
$this->authorize('update', $task);

// Check if user can perform action (returns boolean)
auth()->user()->can('delete', $project)
auth()->user()->cannot('update', $task)
```

### Checking in Requests
```php
public function authorize(): bool
{
    return auth()->user()->can('create', Project::class);
}
```

### Checking in Views
```blade
@can('create', App\Models\Project::class)
    <button>Create Project</button>
@endcan

@cannot('delete', $project)
    <p>You cannot delete this project</p>
@endcannot

@if(auth()->user()->can('update', $project))
    <button>Edit</button>
@endif
```

### Checking in Routes
```php
Route::middleware('auth:sanctum', 'role:admin,instructor')->get('/admin', ...);
```

---

## Testing Authorization

```php
public function test_user_can_update_own_profile()
{
    $user = User::factory()->create();

    $this->assertTrue($user->can('update', $user));
}

public function test_admin_can_delete_any_user()
{
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $user = User::factory()->create();

    $this->assertTrue($admin->can('delete', $user));
}

public function test_student_cannot_create_project()
{
    $student = User::factory()->create(['role_id' => $studentRole->id]);

    $this->assertFalse($student->can('create', Project::class));
}
```

---

## Common Patterns

### Pattern 1: Filtering Resources
```php
// Only show resources user can view
$projects = Project::where(function ($query) {
    $user = auth()->user();

    if ($user->role->name === 'admin') {
        return; // Admin sees all
    }

    if ($user->role->name === 'instructor') {
        $query->where('instructor_id', $user->id);
    } else {
        $query->whereHas('members', fn($q) => $q->where('user_id', $user->id));
    }
})->get();
```

### Pattern 2: Bulk Authorization Check
```php
// Check multiple resources
$tasks = Task::all();
$userTasks = $tasks->filter(function ($task) {
    return auth()->user()->can('view', $task);
});
```

### Pattern 3: Conditional Field Visibility
```php
// Only include sensitive fields if user can view
return response()->json([
    'id' => $user->id,
    'name' => $user->name,
    'email' => auth()->user()->can('view', $user) ? $user->email : null,
    'role' => auth()->user()->can('view', $user) ? $user->role : null,
]);
```

