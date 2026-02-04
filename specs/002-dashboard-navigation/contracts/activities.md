# API Contract: Activity Feed

**Endpoint**: `GET /api/activities`
**Purpose**: Retrieve recent activities from user's projects
**Authentication**: Required (Sanctum token)
**Authorization**: Returns only activities from projects where user is instructor or member
**Priority**: P4 (Optional Enhancement)

## Request

### HTTP Method
```
GET /api/activities?limit={n}
```

### Headers
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

### Query Parameters
| Parameter | Type | Required | Default | Constraints | Description |
|-----------|------|----------|---------|-------------|-------------|
| `limit` | integer | No | 10 | Min: 1, Max: 50 | Number of activities to return |

### Request Body
None

## Response

### Success Response (200 OK)

```json
{
  "data": {
    "activities": [
      {
        "id": 1024,
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com",
          "avatar_url": null
        },
        "action": "completed",
        "subject_type": "task",
        "subject_id": 158,
        "subject_title": "Write documentation",
        "data": null,
        "created_at": "2026-02-03T13:15:00Z",
        "relative_time": "2 hours ago"
      },
      {
        "id": 1023,
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "avatar_url": "https://example.com/avatars/john.jpg"
        },
        "action": "created",
        "subject_type": "project",
        "subject_id": 43,
        "subject_title": "Website Redesign",
        "data": null,
        "created_at": "2026-02-03T10:30:00Z",
        "relative_time": "5 hours ago"
      },
      {
        "id": 1022,
        "user": {
          "id": 3,
          "name": "Bob Johnson",
          "email": "bob@example.com",
          "avatar_url": null
        },
        "action": "updated",
        "subject_type": "task",
        "subject_id": 142,
        "subject_title": "Design landing page",
        "data": {
          "changes": {
            "priority": {"old": "medium", "new": "high"}
          }
        },
        "created_at": "2026-02-03T09:00:00Z",
        "relative_time": "6 hours ago"
      }
    ]
  }
}
```

**Field Definitions**:

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Activity ID |
| `user` | object | User who performed the action (UserSummary) |
| `action` | string | Type of action: "created", "updated", "completed", "commented", "deleted" |
| `subject_type` | string | Type of subject: "project" or "task" |
| `subject_id` | integer | ID of the project or task acted upon |
| `subject_title` | string | Display title of the subject |
| `data` | object\|null | Additional context (e.g., field changes, comment text) |
| `created_at` | string | ISO 8601 timestamp when activity occurred |
| `relative_time` | string | Human-readable relative time (e.g., "2 hours ago", "yesterday") |

**Action Types**:
- `created`: User created a new project or task
- `updated`: User modified project/task fields
- `completed`: User moved task to "Done" column
- `commented`: User added a comment (future feature)
- `deleted`: User deleted a project or task

**Business Logic**:
- Query activities where `subject` (project or task) belongs to user's accessible projects
- Order by `created_at DESC` (most recent first)
- Calculate `relative_time` from `created_at` using human-readable format
- Limit results to requested `limit` (default 10, max 50)

### SQL Query

```sql
SELECT
    a.id,
    a.user_id,
    a.action,
    a.subject_type,
    a.subject_id,
    a.data,
    a.created_at,
    u.name as user_name,
    u.email as user_email,
    u.avatar_url as user_avatar_url,
    CASE
        WHEN a.subject_type = 'project' THEN p.title
        WHEN a.subject_type = 'task' THEN t.title
    END as subject_title
FROM activities a
JOIN users u ON a.user_id = u.id
LEFT JOIN projects p ON a.subject_type = 'project' AND a.subject_id = p.id
LEFT JOIN tasks t ON a.subject_type = 'task' AND a.subject_id = t.id
WHERE (
    (a.subject_type = 'project' AND (
        p.instructor_id = :user_id
        OR EXISTS (SELECT 1 FROM project_members pm WHERE pm.project_id = p.id AND pm.user_id = :user_id)
    ))
    OR
    (a.subject_type = 'task' AND EXISTS (
        SELECT 1 FROM tasks t2
        JOIN columns c ON t2.column_id = c.id
        JOIN boards b ON c.board_id = b.id
        JOIN projects p2 ON b.project_id = p2.id
        LEFT JOIN project_members pm ON p2.id = pm.project_id
        WHERE t2.id = a.subject_id
          AND (p2.instructor_id = :user_id OR pm.user_id = :user_id)
    ))
)
ORDER BY a.created_at DESC
LIMIT :limit
```

### Relative Time Calculation

**Backend** (Laravel):
```php
// In Activity model or resource
public function getRelativeTimeAttribute()
{
    return $this->created_at->diffForHumans();
    // Returns: "2 hours ago", "yesterday", "3 weeks ago", etc.
}
```

**Frontend** (JavaScript - if calculated client-side):
```javascript
// utils/time.js
export function getRelativeTime(timestamp) {
  const now = new Date()
  const then = new Date(timestamp)
  const diffMs = now - then
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffHour = Math.floor(diffMin / 60)
  const diffDay = Math.floor(diffHour / 24)
  const diffWeek = Math.floor(diffDay / 7)

  if (diffSec < 60) return 'just now'
  if (diffMin < 60) return `${diffMin} minute${diffMin > 1 ? 's' : ''} ago`
  if (diffHour < 24) return `${diffHour} hour${diffHour > 1 ? 's' : ''} ago`
  if (diffDay === 1) return 'yesterday'
  if (diffDay < 7) return `${diffDay} days ago`
  if (diffWeek < 4) return `${diffWeek} week${diffWeek > 1 ? 's' : ''} ago`
  return then.toLocaleDateString()
}
```

### Error Responses

#### 400 Bad Request
```json
{
  "error": {
    "message": "Limit must be between 1 and 50",
    "code": "INVALID_LIMIT"
  }
}
```

**Trigger**: `limit` parameter is < 1 or > 50

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
    "message": "Failed to fetch activities",
    "code": "ACTIVITIES_FETCH_ERROR"
  }
}
```

**Trigger**: Database query failure

## Frontend Integration

### Display Format

```vue
<!-- ActivityFeed.vue -->
<template>
  <div class="activity-feed">
    <h3>Recent Activity</h3>
    <div v-if="loading" class="skeleton-loader" />
    <div v-else-if="error" class="error-state">{{ error }}</div>
    <div v-else-if="activities.length === 0" class="empty-state">
      No recent activity
    </div>
    <div v-else class="activity-list">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="activity-item"
        @click="navigateToSubject(activity)"
      >
        <Avatar :user="activity.user" />
        <div class="activity-content">
          <p class="activity-text">
            <strong>{{ activity.user.name }}</strong>
            {{ formatAction(activity.action) }}
            <span class="subject-type">{{ activity.subject_type }}</span>
            "{{ activity.subject_title }}"
          </p>
          <span class="activity-time">{{ activity.relative_time }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Avatar from '@/components/Avatar.vue'

const activities = ref([])
const loading = ref(false)
const error = ref(null)

const formatAction = (action) => {
  const actions = {
    created: 'created',
    updated: 'updated',
    completed: 'completed',
    commented: 'commented on',
    deleted: 'deleted'
  }
  return actions[action] || action
}

const navigateToSubject = (activity) => {
  if (activity.subject_type === 'project') {
    router.push(`/projects/${activity.subject_id}/kanban`)
  } else if (activity.subject_type === 'task') {
    // Need to fetch project ID for task - or include in response
    // For now, could be added to data field
    router.push(`/projects/${activity.data?.project_id}/kanban?highlight=${activity.subject_id}`)
  }
}

const fetchActivities = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/api/activities', { params: { limit: 10 } })
    activities.value = response.data.data.activities
  } catch (err) {
    error.value = err.response?.data?.error?.message || 'Failed to load activities'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchActivities()
})
</script>
```

### Auto-Refresh (Optional)

```javascript
// Automatically refresh every 30 seconds
let refreshInterval = null

onMounted(() => {
  fetchActivities()
  refreshInterval = setInterval(() => {
    fetchActivities()
  }, 30000) // 30 seconds
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
```

## Laravel Controller Example

```php
// app/Http/Controllers/ActivityController.php
namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        // Validation
        if ($limit < 1 || $limit > 50) {
            return response()->json([
                'error' => [
                    'message' => 'Limit must be between 1 and 50',
                    'code' => 'INVALID_LIMIT'
                ]
            ], 400);
        }

        $user = $request->user();

        // Get user's accessible project IDs
        $projectIds = DB::table('projects')
            ->select('id')
            ->where('instructor_id', $user->id)
            ->orWhereExists(function($query) use ($user) {
                $query->select(DB::raw(1))
                      ->from('project_members')
                      ->whereColumn('project_members.project_id', 'projects.id')
                      ->where('project_members.user_id', $user->id);
            })
            ->pluck('id');

        // Fetch activities
        $activities = Activity::with(['user:id,name,email,avatar_url'])
            ->where(function($query) use ($projectIds) {
                $query->where(function($q) use ($projectIds) {
                    // Project activities
                    $q->where('subject_type', 'project')
                      ->whereIn('subject_id', $projectIds);
                })->orWhere(function($q) use ($projectIds) {
                    // Task activities (tasks belonging to accessible projects)
                    $q->where('subject_type', 'task')
                      ->whereIn('subject_id', function($subquery) use ($projectIds) {
                          $subquery->select('tasks.id')
                                   ->from('tasks')
                                   ->join('columns', 'tasks.column_id', '=', 'columns.id')
                                   ->join('boards', 'columns.board_id', '=', 'boards.id')
                                   ->whereIn('boards.project_id', $projectIds);
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Hydrate subject titles
        $activities->each(function($activity) {
            if ($activity->subject_type === 'project') {
                $project = DB::table('projects')->where('id', $activity->subject_id)->first();
                $activity->subject_title = $project?->title ?? '[Deleted Project]';
            } elseif ($activity->subject_type === 'task') {
                $task = DB::table('tasks')->where('id', $activity->subject_id)->first();
                $activity->subject_title = $task?->title ?? '[Deleted Task]';
            }
            $activity->relative_time = $activity->created_at->diffForHumans();
        });

        return response()->json([
            'data' => [
                'activities' => $activities
            ]
        ]);
    }
}
```

## Activity Creation (For Reference)

**When to create activities**:
- Project created → `action: 'created', subject_type: 'project'`
- Project updated → `action: 'updated', subject_type: 'project'`
- Project deleted → `action: 'deleted', subject_type: 'project'`
- Task created → `action: 'created', subject_type: 'task'`
- Task updated → `action: 'updated', subject_type: 'task'`
- Task moved to "Done" column → `action: 'completed', subject_type: 'task'`
- Task deleted → `action: 'deleted', subject_type: 'task'`

**Example** (Project creation):
```php
// In ProjectController@store
$project = Project::create([...]);

Activity::create([
    'user_id' => $request->user()->id,
    'action' => 'created',
    'subject_type' => 'project',
    'subject_id' => $project->id,
    'data' => null
]);
```

**Example** (Task completion):
```php
// In TaskController@update (when moving to Done column)
if ($newColumn->title === 'Done' && $oldColumn->title !== 'Done') {
    Activity::create([
        'user_id' => $request->user()->id,
        'action' => 'completed',
        'subject_type' => 'task',
        'subject_id' => $task->id,
        'data' => [
            'project_id' => $task->column->board->project_id // For navigation
        ]
    ]);
}
```

## Testing Requirements

```php
// tests/Feature/ActivityControllerTest.php

public function test_activities_returns_only_user_accessible_activities()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $project1 = Project::factory()->create(['instructor_id' => $user1->id]);
    $project2 = Project::factory()->create(['instructor_id' => $user2->id]);

    Activity::create([
        'user_id' => $user1->id,
        'action' => 'created',
        'subject_type' => 'project',
        'subject_id' => $project1->id
    ]);

    Activity::create([
        'user_id' => $user2->id,
        'action' => 'created',
        'subject_type' => 'project',
        'subject_id' => $project2->id
    ]);

    $response = $this->actingAs($user1)->getJson('/api/activities');

    $response->assertStatus(200)
             ->assertJsonCount(1, 'data.activities')
             ->assertJsonFragment(['subject_id' => $project1->id])
             ->assertJsonMissing(['subject_id' => $project2->id]);
}

public function test_activities_respects_limit_parameter()
{
    $user = User::factory()->create();
    $project = Project::factory()->create(['instructor_id' => $user->id]);

    // Create 20 activities
    for ($i = 0; $i < 20; $i++) {
        Activity::create([
            'user_id' => $user->id,
            'action' => 'updated',
            'subject_type' => 'project',
            'subject_id' => $project->id,
            'created_at' => now()->subMinutes($i)
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/activities?limit=5');

    $response->assertStatus(200)
             ->assertJsonCount(5, 'data.activities');
}
```

## Performance Optimization

### Database Indexes Required
```sql
-- Activities table
CREATE INDEX idx_activities_subject_type_id ON activities(subject_type, subject_id);
CREATE INDEX idx_activities_created_at ON activities(created_at DESC);
CREATE INDEX idx_activities_user_id ON activities(user_id);
```

### Caching Strategy
- **No caching** - Activities should be real-time
- Consider pagination for users with hundreds of activities
- Auto-refresh limited to 30-second intervals

## Related Endpoints
- [Dashboard Stats](dashboard-stats.md) - Dashboard where activity feed appears
- [Projects](projects.md) - Navigation target for project activities

## Changelog
- 2026-02-03: Initial contract definition (optional feature, P4 priority)
