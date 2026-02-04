# API Contract: Dashboard Statistics

**Endpoint**: `GET /api/dashboard/stats`
**Purpose**: Retrieve aggregate statistics for the authenticated user's dashboard
**Authentication**: Required (Sanctum token)
**Authorization**: User must be authenticated

## Request

### HTTP Method
```
GET /api/dashboard/stats
```

### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

### Query Parameters
None

### Request Body
None

## Response

### Success Response (200 OK)

```json
{
  "data": {
    "stats": {
      "total_projects": 5,
      "active_tasks": 23,
      "team_members": 8,
      "overdue_tasks": 3
    }
  }
}
```

**Field Definitions**:

| Field | Type | Description |
|-------|------|-------------|
| `total_projects` | integer | Count of projects where user is instructor OR member (via project_members table) |
| `active_tasks` | integer | Count of tasks NOT in "Done" or "Archived" columns across user's projects |
| `team_members` | integer | Distinct count of OTHER users who are members of user's projects (excludes self) |
| `overdue_tasks` | integer | Count of tasks with `due_date < today` AND NOT in "Done"/"Archived" columns |

**Business Logic**:

- **Total Projects**:
  ```sql
  SELECT COUNT(DISTINCT p.id)
  FROM projects p
  LEFT JOIN project_members pm ON p.id = pm.project_id
  WHERE p.instructor_id = :user_id OR pm.user_id = :user_id
  ```

- **Active Tasks**:
  ```sql
  SELECT COUNT(t.id)
  FROM tasks t
  JOIN columns c ON t.column_id = c.id
  JOIN boards b ON c.board_id = b.id
  JOIN projects p ON b.project_id = p.id
  LEFT JOIN project_members pm ON p.id = pm.project_id
  WHERE (p.instructor_id = :user_id OR pm.user_id = :user_id)
    AND c.title NOT IN ('Done', 'Archived')
  ```

- **Team Members**:
  ```sql
  SELECT COUNT(DISTINCT pm.user_id)
  FROM project_members pm
  WHERE pm.project_id IN (
    SELECT p.id FROM projects p
    WHERE p.instructor_id = :user_id
    UNION
    SELECT pm2.project_id FROM project_members pm2 WHERE pm2.user_id = :user_id
  ) AND pm.user_id != :user_id
  ```

- **Overdue Tasks**:
  ```sql
  SELECT COUNT(t.id)
  FROM tasks t
  JOIN columns c ON t.column_id = c.id
  JOIN boards b ON c.board_id = b.id
  JOIN projects p ON b.project_id = p.id
  LEFT JOIN project_members pm ON p.id = pm.project_id
  WHERE (p.instructor_id = :user_id OR pm.user_id = :user_id)
    AND t.due_date < CURRENT_DATE
    AND c.title NOT IN ('Done', 'Archived')
  ```

### Error Responses

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
    "message": "Failed to fetch dashboard statistics",
    "code": "DASHBOARD_FETCH_ERROR"
  }
}
```

**Trigger**: Database query failure or unexpected server error

## Implementation Notes

### Performance Optimization
- **Cache**: Store results in Laravel cache for 5 minutes per user
  ```php
  Cache::remember("dashboard.stats.{$userId}", 300, function() {
      // Execute queries
  });
  ```
- **Indexes Required**: Ensure indexes exist on:
  - `projects.instructor_id`
  - `project_members.user_id`
  - `project_members.project_id`
  - `tasks.column_id`
  - `tasks.due_date`
  - `columns.board_id`
  - `boards.project_id`

### Laravel Controller Example
```php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = Cache::remember("dashboard.stats.{$user->id}", 300, function() use ($user) {
            // Get project IDs user has access to
            $projectIds = DB::table('projects')
                ->select('projects.id')
                ->leftJoin('project_members', 'projects.id', '=', 'project_members.project_id')
                ->where('projects.instructor_id', $user->id)
                ->orWhere('project_members.user_id', $user->id)
                ->pluck('id')
                ->unique();

            // Total projects
            $totalProjects = $projectIds->count();

            // Active tasks
            $activeTasks = DB::table('tasks')
                ->join('columns', 'tasks.column_id', '=', 'columns.id')
                ->join('boards', 'columns.board_id', '=', 'boards.id')
                ->whereIn('boards.project_id', $projectIds)
                ->whereNotIn('columns.title', ['Done', 'Archived'])
                ->count();

            // Team members
            $teamMembers = DB::table('project_members')
                ->whereIn('project_id', $projectIds)
                ->where('user_id', '!=', $user->id)
                ->distinct('user_id')
                ->count('user_id');

            // Overdue tasks
            $overdueTasks = DB::table('tasks')
                ->join('columns', 'tasks.column_id', '=', 'columns.id')
                ->join('boards', 'columns.board_id', '=', 'boards.id')
                ->whereIn('boards.project_id', $projectIds)
                ->where('tasks.due_date', '<', now()->toDateString())
                ->whereNotIn('columns.title', ['Done', 'Archived'])
                ->count();

            return [
                'total_projects' => $totalProjects,
                'active_tasks' => $activeTasks,
                'team_members' => $teamMembers,
                'overdue_tasks' => $overdueTasks
            ];
        });

        return response()->json([
            'data' => [
                'stats' => $stats
            ]
        ]);
    }
}
```

### Testing Requirements

**Feature Test** (PHPUnit):
```php
public function test_dashboard_stats_returns_correct_counts()
{
    $user = User::factory()->create();
    $projects = Project::factory()->count(3)->create(['instructor_id' => $user->id]);

    // Create tasks in various states...

    $response = $this->actingAs($user)->getJson('/api/dashboard/stats');

    $response->assertStatus(200)
             ->assertJson([
                 'data' => [
                     'stats' => [
                         'total_projects' => 3,
                         'active_tasks' => 10,
                         'team_members' => 0,
                         'overdue_tasks' => 2
                     ]
                 ]
             ]);
}
```

## Related Endpoints
- [Projects List](projects.md#list-projects) - For project details
- [Search](search.md) - For searching projects/tasks

## Changelog
- 2026-02-03: Initial contract definition
