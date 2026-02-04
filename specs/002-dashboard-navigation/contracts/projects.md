# API Contract: Projects Management

**Purpose**: CRUD operations for projects with authorization checks
**Authentication**: Required (Sanctum token)
**Base Path**: `/api/projects`

---

## List Projects

**Endpoint**: `GET /api/projects`
**Purpose**: Retrieve list of projects where user is instructor or member
**Authorization**: User must be authenticated

### Request

#### HTTP Method
```
GET /api/projects
```

#### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `limit` | integer | No | Max number of results (default: all, used for pagination) |
| `offset` | integer | No | Number of results to skip (default: 0) |

#### Request Body
None

### Response

#### Success Response (200 OK)

```json
{
  "data": {
    "projects": [
      {
        "id": 42,
        "title": "Q1 Marketing Campaign",
        "description": "Launch social media ads for new product line targeting millennials",
        "timeline_status": "At Risk",
        "budget_status": "Within Budget",
        "created_at": "2026-01-15T10:30:00Z",
        "updated_at": "2026-02-03T14:22:00Z",
        "instructor": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "avatar_url": "https://example.com/avatars/john.jpg"
        },
        "members": [
          {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "avatar_url": null
          }
        ],
        "total_members": 2,
        "task_completion": {
          "total": 15,
          "completed": 8,
          "percentage": 53.33
        }
      }
    ]
  }
}
```

**Field Definitions**:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Project ID |
| `title` | string | Project title (max 100 chars) |
| `description` | string\|null | Project description (max 500 chars) |
| `timeline_status` | string | Enum: "On Track", "At Risk", "Delayed" |
| `budget_status` | string | Enum: "Within Budget", "Over Budget" |
| `created_at` | string | ISO 8601 timestamp |
| `updated_at` | string | ISO 8601 timestamp |
| `instructor` | object | Project owner (UserSummary) |
| `members` | array | Project members (array of UserSummary, max 5 for performance) |
| `total_members` | integer | Total count including instructor |
| `task_completion.total` | integer | Total tasks in project |
| `task_completion.completed` | integer | Tasks in "Done" column |
| `task_completion.percentage` | float | Completion percentage (0-100) |

**Business Logic**:
- Query projects where `instructor_id = user.id` OR user exists in `project_members`
- Order by `updated_at DESC` (most recent first)
- Eager load instructor, members (first 5), and calculate task stats
- `task_completion.percentage = (completed / total * 100)` OR 0 if no tasks

#### Error Responses

##### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

---

## Get Single Project

**Endpoint**: `GET /api/projects/{id}`
**Purpose**: Retrieve detailed information for a specific project
**Authorization**: User must be instructor or member of the project

### Request

#### HTTP Method
```
GET /api/projects/{id}
```

#### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Project ID |

### Response

#### Success Response (200 OK)

```json
{
  "data": {
    "project": {
      "id": 42,
      "title": "Q1 Marketing Campaign",
      "description": "Launch social media ads for new product line targeting millennials",
      "timeline_status": "At Risk",
      "budget_status": "Within Budget",
      "created_at": "2026-01-15T10:30:00Z",
      "updated_at": "2026-02-03T14:22:00Z",
      "instructor": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": "https://example.com/avatars/john.jpg"
      },
      "members": [
        {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com",
          "avatar_url": null
        }
      ],
      "total_members": 2,
      "task_completion": {
        "total": 15,
        "completed": 8,
        "percentage": 53.33
      }
    }
  }
}
```

**Same structure as list response, but returns all members (not limited to 5)**

#### Error Responses

##### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

##### 403 Forbidden
```json
{
  "error": {
    "message": "You do not have access to this project",
    "code": "PROJECT_ACCESS_DENIED"
  }
}
```

**Trigger**: User is not instructor or member of the project

##### 404 Not Found
```json
{
  "error": {
    "message": "Project not found",
    "code": "PROJECT_NOT_FOUND"
  }
}
```

**Trigger**: Project ID does not exist

---

## Create Project

**Endpoint**: `POST /api/projects`
**Purpose**: Create a new project (user becomes instructor)
**Authorization**: User must be authenticated

### Request

#### HTTP Method
```
POST /api/projects
```

#### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
Content-Type: application/json
```

#### Request Body
```json
{
  "title": "Website Redesign",
  "description": "Modernize company website with new branding and improved UX",
  "timeline_status": "On Track",
  "budget_status": "Within Budget"
}
```

**Field Definitions**:

| Field | Type | Required | Constraints |
|-------|------|----------|-------------|
| `title` | string | Yes | 1-100 characters, plain text (no HTML) |
| `description` | string | No | Max 500 characters, plain text |
| `timeline_status` | string | No | Enum: "On Track", "At Risk", "Delayed" (default: "On Track") |
| `budget_status` | string | No | Enum: "Within Budget", "Over Budget" (default: "Within Budget") |

### Response

#### Success Response (201 Created)

```json
{
  "data": {
    "project": {
      "id": 43,
      "title": "Website Redesign",
      "description": "Modernize company website with new branding and improved UX",
      "timeline_status": "On Track",
      "budget_status": "Within Budget",
      "created_at": "2026-02-03T15:30:00Z",
      "updated_at": "2026-02-03T15:30:00Z",
      "instructor": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": null
      },
      "members": [],
      "total_members": 1,
      "task_completion": {
        "total": 0,
        "completed": 0,
        "percentage": 0
      }
    }
  }
}
```

**Business Logic**:
- Set `instructor_id = authenticated_user.id`
- Generate timestamps (`created_at`, `updated_at`)
- Return newly created project with full structure

#### Error Responses

##### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

##### 422 Unprocessable Entity (Validation Errors)
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "errors": {
      "title": ["The title field is required."],
      "timeline_status": ["The timeline status must be one of: On Track, At Risk, Delayed."]
    }
  }
}
```

**Validation Rules**:
- `title`: required, string, max:100, no HTML tags
- `description`: nullable, string, max:500
- `timeline_status`: nullable, in:On Track,At Risk,Delayed
- `budget_status`: nullable, in:Within Budget,Over Budget

---

## Update Project

**Endpoint**: `PUT /api/projects/{id}`
**Purpose**: Update an existing project
**Authorization**: User must be instructor or member of the project

### Request

#### HTTP Method
```
PUT /api/projects/{id}
```

#### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
Content-Type: application/json
```

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Project ID |

#### Request Body
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "timeline_status": "At Risk",
  "budget_status": "Within Budget"
}
```

**All fields are optional** - only include fields you want to update

**Field Constraints**: Same as Create Project

### Response

#### Success Response (200 OK)

```json
{
  "data": {
    "project": {
      "id": 42,
      "title": "Updated Title",
      "description": "Updated description",
      "timeline_status": "At Risk",
      "budget_status": "Within Budget",
      "created_at": "2026-01-15T10:30:00Z",
      "updated_at": "2026-02-03T15:45:00Z",
      "instructor": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": null
      },
      "members": [...],
      "total_members": 2,
      "task_completion": {...}
    }
  }
}
```

**Business Logic**:
- Update `updated_at` timestamp
- Return updated project with full structure

#### Error Responses

##### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

##### 403 Forbidden
```json
{
  "error": {
    "message": "You do not have permission to update this project",
    "code": "PROJECT_UPDATE_DENIED"
  }
}
```

**Trigger**: User is not instructor or member of the project

##### 404 Not Found
```json
{
  "error": {
    "message": "Project not found",
    "code": "PROJECT_NOT_FOUND"
  }
}
```

##### 422 Unprocessable Entity
```json
{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "errors": {
      "title": ["The title must not exceed 100 characters."]
    }
  }
}
```

---

## Delete Project

**Endpoint**: `DELETE /api/projects/{id}`
**Purpose**: Delete a project (hard delete)
**Authorization**: User must be instructor (NOT just member)

### Request

#### HTTP Method
```
DELETE /api/projects/{id}
```

#### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

#### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Project ID |

### Response

#### Success Response (200 OK)

```json
{
  "message": "Project deleted successfully"
}
```

**Business Logic**:
- Check user is project instructor (NOT member)
- Delete project (cascades to boards, columns, tasks via foreign keys)
- Return success message

#### Error Responses

##### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

##### 403 Forbidden
```json
{
  "error": {
    "message": "Only the project instructor can delete this project",
    "code": "PROJECT_DELETE_DENIED"
  }
}
```

**Trigger**: User is member but NOT instructor

##### 404 Not Found
```json
{
  "error": {
    "message": "Project not found",
    "code": "PROJECT_NOT_FOUND"
  }
}
```

---

## Laravel Controller Example

```php
// app/Http/Controllers/ProjectController.php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('instructor_id', $request->user()->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $request->user()->id))
            ->with(['instructor', 'members' => fn($q) => $q->limit(5)])
            ->withCount(['tasks as total_tasks', 'completedTasks as completed_tasks'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'projects' => ProjectResource::collection($projects)
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $project = Project::with(['instructor', 'members'])
            ->withCount(['tasks as total_tasks', 'completedTasks as completed_tasks'])
            ->findOrFail($id);

        // Authorization check
        $hasAccess = $project->instructor_id === $request->user()->id
            || $project->members->contains('id', $request->user()->id);

        if (!$hasAccess) {
            return response()->json([
                'error' => [
                    'message' => 'You do not have access to this project',
                    'code' => 'PROJECT_ACCESS_DENIED'
                ]
            ], 403);
        }

        return response()->json([
            'data' => [
                'project' => new ProjectResource($project)
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'timeline_status' => 'nullable|in:On Track,At Risk,Delayed',
            'budget_status' => 'nullable|in:Within Budget,Over Budget'
        ]);

        $project = Project::create([
            ...$validated,
            'instructor_id' => $request->user()->id,
            'timeline_status' => $validated['timeline_status'] ?? 'On Track',
            'budget_status' => $validated['budget_status'] ?? 'Within Budget'
        ]);

        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => [
                'project' => new ProjectResource($project)
            ]
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Authorization check
        $hasAccess = $project->instructor_id === $request->user()->id
            || $project->members->contains('id', $request->user()->id);

        if (!$hasAccess) {
            return response()->json([
                'error' => [
                    'message' => 'You do not have permission to update this project',
                    'code' => 'PROJECT_UPDATE_DENIED'
                ]
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'description' => 'sometimes|nullable|string|max:500',
            'timeline_status' => 'sometimes|in:On Track,At Risk,Delayed',
            'budget_status' => 'sometimes|in:Within Budget,Over Budget'
        ]);

        $project->update($validated);
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => [
                'project' => new ProjectResource($project)
            ]
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Only instructor can delete
        if ($project->instructor_id !== $request->user()->id) {
            return response()->json([
                'error' => [
                    'message' => 'Only the project instructor can delete this project',
                    'code' => 'PROJECT_DELETE_DENIED'
                ]
            ], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }
}
```

## Testing Requirements

```php
// tests/Feature/ProjectControllerTest.php

public function test_user_can_list_their_projects()
{
    $user = User::factory()->create();
    $projects = Project::factory()->count(3)->create(['instructor_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/projects');

    $response->assertStatus(200)
             ->assertJsonCount(3, 'data.projects');
}

public function test_member_cannot_delete_project()
{
    $instructor = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['instructor_id' => $instructor->id]);
    $project->members()->attach($member->id);

    $response = $this->actingAs($member)->deleteJson("/api/projects/{$project->id}");

    $response->assertStatus(403)
             ->assertJson(['error' => ['code' => 'PROJECT_DELETE_DENIED']]);
    $this->assertDatabaseHas('projects', ['id' => $project->id]);
}

public function test_instructor_can_delete_project()
{
    $instructor = User::factory()->create();
    $project = Project::factory()->create(['instructor_id' => $instructor->id]);

    $response = $this->actingAs($instructor)->deleteJson("/api/projects/{$project->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
}
```

## Related Endpoints
- [Dashboard Stats](dashboard-stats.md) - Uses project counts
- [Search](search.md) - Searches projects

## Changelog
- 2026-02-03: Initial contract definition
