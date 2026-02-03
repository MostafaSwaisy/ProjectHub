# API Contract: Global Search

**Endpoint**: `GET /api/search`
**Purpose**: Search across projects and tasks accessible to the user
**Authentication**: Required (Sanctum token)
**Authorization**: Returns only projects/tasks where user is instructor or member

## Request

### HTTP Method
```
GET /api/search?q={query}
```

### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

### Query Parameters
| Parameter | Type | Required | Constraints | Description |
|-----------|------|----------|-------------|-------------|
| `q` | string | Yes | Min 2 chars, max 100 chars | Search query string |

### Request Body
None

## Response

### Success Response (200 OK)

```json
{
  "data": {
    "results": {
      "projects": [
        {
          "id": 42,
          "type": "project",
          "title": "Marketing Campaign",
          "description": "Social media marketing for new product launch",
          "project_id": null,
          "project_title": null,
          "match_in": "title"
        },
        {
          "id": 38,
          "type": "project",
          "title": "Product Development",
          "description": "Marketing research shows demand for new features...",
          "project_id": null,
          "project_title": null,
          "match_in": "description"
        }
      ],
      "tasks": [
        {
          "id": 158,
          "type": "task",
          "title": "Create marketing copy",
          "description": "Write compelling ad copy for Facebook ads",
          "project_id": 42,
          "project_title": "Marketing Campaign",
          "match_in": "title"
        },
        {
          "id": 159,
          "type": "task",
          "title": "Design landing page",
          "description": "Create a marketing-focused landing page with conversion tracking",
          "project_id": 42,
          "project_title": "Marketing Campaign",
          "match_in": "description"
        }
      ],
      "has_more_projects": false,
      "has_more_tasks": true
    }
  }
}
```

**Field Definitions**:

| Field | Type | Description |
|-------|------|-------------|
| `results.projects` | array | Array of project search results (max 10) |
| `results.tasks` | array | Array of task search results (max 10) |
| `results.has_more_projects` | boolean | True if more than 10 projects matched the query |
| `results.has_more_tasks` | boolean | True if more than 10 tasks matched the query |
| `id` | integer | Project or task ID |
| `type` | string | Either "project" or "task" |
| `title` | string | Project/task title |
| `description` | string\|null | Project/task description (may be truncated) |
| `project_id` | integer\|null | For tasks: parent project ID; for projects: null |
| `project_title` | string\|null | For tasks: parent project title; for projects: null |
| `match_in` | string | Where query matched: "title" or "description" |

**Business Logic**:

1. **Query Scope**: Search only projects/tasks where user is instructor or member
2. **Search Pattern**: SQL LIKE with wildcards: `WHERE title LIKE '%{query}%' OR description LIKE '%{query}%'`
3. **Result Ordering**:
   - Exact title match first
   - Partial title match second
   - Description match last
4. **Result Limits**:
   - Max 10 projects
   - Max 10 tasks
   - If more results exist, set `has_more_*` flag
5. **Performance**: Use indexes on `title` and `description` columns

### SQL Queries

**Projects Search**:
```sql
SELECT
    p.id,
    'project' as type,
    p.title,
    p.description,
    NULL as project_id,
    NULL as project_title,
    CASE
        WHEN p.title LIKE :exact_query THEN 'title'
        WHEN p.title LIKE :partial_query THEN 'title'
        ELSE 'description'
    END as match_in
FROM projects p
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE (p.instructor_id = :user_id OR pm.user_id = :user_id)
  AND (p.title LIKE :partial_query OR p.description LIKE :partial_query)
ORDER BY
    CASE WHEN p.title LIKE :exact_query THEN 1 ELSE 2 END,
    CASE WHEN p.title LIKE :partial_query THEN 1 ELSE 2 END,
    p.title
LIMIT 11
-- Fetch 11 to detect if more exist
```

**Tasks Search**:
```sql
SELECT
    t.id,
    'task' as type,
    t.title,
    t.description,
    p.id as project_id,
    p.title as project_title,
    CASE
        WHEN t.title LIKE :exact_query THEN 'title'
        WHEN t.title LIKE :partial_query THEN 'title'
        ELSE 'description'
    END as match_in
FROM tasks t
JOIN columns c ON t.column_id = c.id
JOIN boards b ON c.board_id = b.id
JOIN projects p ON b.project_id = p.id
LEFT JOIN project_members pm ON p.id = pm.project_id
WHERE (p.instructor_id = :user_id OR pm.user_id = :user_id)
  AND (t.title LIKE :partial_query OR t.description LIKE :partial_query)
ORDER BY
    CASE WHEN t.title LIKE :exact_query THEN 1 ELSE 2 END,
    CASE WHEN t.title LIKE :partial_query THEN 1 ELSE 2 END,
    t.title
LIMIT 11
-- Fetch 11 to detect if more exist
```

**Query Parameters**:
- `:user_id` = authenticated user ID
- `:exact_query` = `query` (no wildcards, for exact match detection)
- `:partial_query` = `%query%` (case-insensitive match)

### Error Responses

#### 400 Bad Request
```json
{
  "error": {
    "message": "Search query must be at least 2 characters",
    "code": "SEARCH_QUERY_TOO_SHORT"
  }
}
```

**Trigger**: Query parameter `q` is less than 2 characters

---

#### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHENTICATED"
  }
}
```

**Trigger**: Missing or invalid Sanctum token

---

#### 500 Internal Server Error
```json
{
  "error": {
    "message": "Search failed",
    "code": "SEARCH_ERROR"
  }
}
```

**Trigger**: Database query failure or timeout

## Frontend Integration

### Debouncing
```javascript
// composables/useSearch.js
import { ref, watch } from 'vue'
import { debounce } from 'lodash'
import axios from 'axios'

export function useSearch() {
  const query = ref('')
  const results = ref({ projects: [], tasks: [], has_more_projects: false, has_more_tasks: false })
  const loading = ref(false)
  const error = ref(null)

  const performSearch = debounce(async (q) => {
    if (q.length < 2) {
      results.value = { projects: [], tasks: [], has_more_projects: false, has_more_tasks: false }
      return
    }

    loading.value = true
    error.value = null

    try {
      const response = await axios.get('/api/search', { params: { q } })
      results.value = response.data.data.results
    } catch (err) {
      error.value = err.response?.data?.error?.message || 'Search failed'
    } finally {
      loading.value = false
    }
  }, 300) // 300ms debounce

  watch(query, (newQuery) => {
    performSearch(newQuery)
  })

  return { query, results, loading, error }
}
```

### Navigation on Click
```javascript
// Handle project result click
function navigateToProject(projectId) {
  router.push(`/projects/${projectId}/kanban`)
}

// Handle task result click
function navigateToTask(result) {
  router.push(`/projects/${result.project_id}/kanban?highlight=${result.id}`)
}
```

## Laravel Controller Example

```php
// app/Http/Controllers/SearchController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        // Validation
        if (!$query || strlen($query) < 2) {
            return response()->json([
                'error' => [
                    'message' => 'Search query must be at least 2 characters',
                    'code' => 'SEARCH_QUERY_TOO_SHORT'
                ]
            ], 400);
        }

        $user = $request->user();
        $searchPattern = "%{$query}%";

        // Search projects
        $projectResults = DB::table('projects as p')
            ->select([
                'p.id',
                DB::raw("'project' as type"),
                'p.title',
                'p.description',
                DB::raw('NULL as project_id'),
                DB::raw('NULL as project_title'),
                DB::raw("CASE
                    WHEN p.title LIKE '{$query}' THEN 'title'
                    WHEN p.title LIKE '{$searchPattern}' THEN 'title'
                    ELSE 'description'
                END as match_in")
            ])
            ->leftJoin('project_members as pm', 'p.id', '=', 'pm.project_id')
            ->where(function($q) use ($user) {
                $q->where('p.instructor_id', $user->id)
                  ->orWhere('pm.user_id', $user->id);
            })
            ->where(function($q) use ($searchPattern) {
                $q->where('p.title', 'LIKE', $searchPattern)
                  ->orWhere('p.description', 'LIKE', $searchPattern);
            })
            ->orderByRaw("CASE WHEN p.title LIKE '{$query}' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE WHEN p.title LIKE '{$searchPattern}' THEN 1 ELSE 2 END")
            ->orderBy('p.title')
            ->limit(11)
            ->get();

        // Search tasks
        $taskResults = DB::table('tasks as t')
            ->select([
                't.id',
                DB::raw("'task' as type"),
                't.title',
                't.description',
                'p.id as project_id',
                'p.title as project_title',
                DB::raw("CASE
                    WHEN t.title LIKE '{$query}' THEN 'title'
                    WHEN t.title LIKE '{$searchPattern}' THEN 'title'
                    ELSE 'description'
                END as match_in")
            ])
            ->join('columns as c', 't.column_id', '=', 'c.id')
            ->join('boards as b', 'c.board_id', '=', 'b.id')
            ->join('projects as p', 'b.project_id', '=', 'p.id')
            ->leftJoin('project_members as pm', 'p.id', '=', 'pm.project_id')
            ->where(function($q) use ($user) {
                $q->where('p.instructor_id', $user->id)
                  ->orWhere('pm.user_id', $user->id);
            })
            ->where(function($q) use ($searchPattern) {
                $q->where('t.title', 'LIKE', $searchPattern)
                  ->orWhere('t.description', 'LIKE', $searchPattern);
            })
            ->orderByRaw("CASE WHEN t.title LIKE '{$query}' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE WHEN t.title LIKE '{$searchPattern}' THEN 1 ELSE 2 END")
            ->orderBy('t.title')
            ->limit(11)
            ->get();

        // Check if more results exist
        $hasMoreProjects = $projectResults->count() > 10;
        $hasMoreTasks = $taskResults->count() > 10;

        return response()->json([
            'data' => [
                'results' => [
                    'projects' => $projectResults->take(10)->values(),
                    'tasks' => $taskResults->take(10)->values(),
                    'has_more_projects' => $hasMoreProjects,
                    'has_more_tasks' => $hasMoreTasks
                ]
            ]
        ]);
    }
}
```

## Performance Optimization

### Database Indexes Required
```sql
-- Projects table
CREATE INDEX idx_projects_title ON projects(title);
CREATE INDEX idx_projects_instructor_id ON projects(instructor_id);

-- Tasks table
CREATE INDEX idx_tasks_title ON tasks(title);

-- Project Members table
CREATE INDEX idx_project_members_user_id ON project_members(user_id);
CREATE INDEX idx_project_members_project_id ON project_members(project_id);
```

### Caching Strategy
- **No caching** - Search results should be real-time
- Frontend debouncing (300ms) reduces API load
- Rate limiting: 60 requests per minute per user (Laravel default)

## Testing Requirements

```php
// tests/Feature/SearchControllerTest.php

public function test_search_returns_matching_projects_and_tasks()
{
    $user = User::factory()->create();
    $project = Project::factory()->create([
        'title' => 'Marketing Campaign',
        'instructor_id' => $user->id
    ]);
    $task = Task::factory()->create([
        'title' => 'Create marketing materials'
    ]);

    $response = $this->actingAs($user)->getJson('/api/search?q=marketing');

    $response->assertStatus(200)
             ->assertJsonFragment(['title' => 'Marketing Campaign'])
             ->assertJsonFragment(['title' => 'Create marketing materials']);
}

public function test_search_rejects_short_queries()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/search?q=a');

    $response->assertStatus(400)
             ->assertJson(['error' => ['code' => 'SEARCH_QUERY_TOO_SHORT']]);
}

public function test_search_only_returns_user_accessible_projects()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $project1 = Project::factory()->create(['instructor_id' => $user1->id, 'title' => 'Test Project 1']);
    $project2 = Project::factory()->create(['instructor_id' => $user2->id, 'title' => 'Test Project 2']);

    $response = $this->actingAs($user1)->getJson('/api/search?q=test');

    $response->assertStatus(200)
             ->assertJsonFragment(['title' => 'Test Project 1'])
             ->assertJsonMissing(['title' => 'Test Project 2']);
}
```

## Related Endpoints
- [Projects](projects.md) - Project details navigation target
- [Dashboard](dashboard-stats.md) - Search called from top navbar

## Changelog
- 2026-02-03: Initial contract definition
