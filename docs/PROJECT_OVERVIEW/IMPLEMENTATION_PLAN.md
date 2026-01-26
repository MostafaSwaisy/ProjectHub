# ProjectHub Analytics - Implementation Plan

## Project Overview

**Stack:** Laravel 12 (Backend) + Vite + Tailwind CSS 4 (Frontend)
**Database:** SQLite (dev) / MySQL/PostgreSQL (prod)
**Authentication:** JWT (Laravel Sanctum)
**Real-time:** Laravel Broadcasting with Pusher/Soketi

---

## Phase 1: Foundation & Authentication (Priority: Critical)

### 1.1 Database Schema Setup

#### Migrations to Create:

```
database/migrations/
├── create_roles_table.php
├── add_role_to_users_table.php
├── create_projects_table.php
├── create_project_members_table.php
├── create_boards_table.php
├── create_columns_table.php
├── create_tasks_table.php
├── create_subtasks_table.php
├── create_comments_table.php
├── create_labels_table.php
├── create_task_labels_table.php
├── create_activities_table.php
├── create_notifications_table.php
├── create_user_preferences_table.php
└── create_password_reset_tokens_table.php (exists)
```

#### Core Models:

| Model | Key Fields | Relationships |
|-------|------------|---------------|
| User | name, email, password, role_id, avatar | hasMany: projects, tasks, comments, activities |
| Role | name (admin, instructor, student), permissions | hasMany: users |
| Project | title, description, instructor_id, timeline_status, budget_status | belongsTo: instructor, hasMany: boards, members |
| Board | project_id, title | belongsTo: project, hasMany: columns |
| Column | board_id, title, position, wip_limit | belongsTo: board, hasMany: tasks |
| Task | column_id, title, description, assignee_id, priority, due_date, position | belongsTo: column, assignee; hasMany: subtasks, comments, labels |
| Subtask | task_id, title, is_completed, position | belongsTo: task |
| Comment | task_id, user_id, parent_id, body, edited_at | belongsTo: task, user, parent |
| Label | name, color, project_id | belongsToMany: tasks |
| Activity | user_id, project_id, type, subject_type, subject_id, data | polymorphic |
| Notification | user_id, type, data, read_at | belongsTo: user |

### 1.2 Authentication Implementation

#### Files to Create:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       ├── RegisterController.php
│   │       ├── LoginController.php
│   │       ├── LogoutController.php
│   │       └── PasswordResetController.php
│   ├── Requests/
│   │   └── Auth/
│   │       ├── RegisterRequest.php
│   │       └── LoginRequest.php
│   └── Middleware/
│       ├── RoleMiddleware.php
│       └── EnsureProjectAccess.php
├── Services/
│   └── AuthService.php
└── Policies/
    ├── ProjectPolicy.php
    ├── TaskPolicy.php
    └── UserPolicy.php
```

#### API Routes (routes/api.php):

```php
// Public routes
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);
Route::post('/password/forgot', [PasswordResetController::class, 'sendLink']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LogoutController::class, 'store']);
    Route::get('/user', [UserController::class, 'current']);
    // ... other protected routes
});
```

#### Validation Rules:

- **Email:** required, email, unique:users
- **Password:** required, min:8, regex:/[0-9]/ (at least one number)
- **Name:** required, string, max:255
- **Role:** required, in:admin,instructor,student

### 1.3 Role-Based Access Control

#### Middleware Implementation:

```php
// RoleMiddleware.php
public function handle($request, Closure $next, ...$roles)
{
    if (!$request->user() || !in_array($request->user()->role->name, $roles)) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You do not have permission to access this resource.'
            ]
        ], 403);
    }
    return $next($request);
}
```

#### Policy Examples:

```php
// ProjectPolicy.php
public function view(User $user, Project $project): bool
{
    return $user->isAdmin()
        || $project->instructor_id === $user->id
        || $project->members->contains($user);
}
```

---

## Phase 2: Project & Board Management (Priority: High)

### 2.1 Project CRUD

#### Controllers:

```
app/Http/Controllers/
├── ProjectController.php          # CRUD for projects
├── ProjectMemberController.php    # Add/remove members
└── ProjectHealthController.php    # Health matrix data
```

#### API Endpoints:

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| GET | /api/projects | List user's projects | All |
| POST | /api/projects | Create project | Admin, Instructor |
| GET | /api/projects/{id} | Get project details | Member |
| PUT | /api/projects/{id} | Update project | Owner |
| DELETE | /api/projects/{id} | Delete project | Owner, Admin |
| GET | /api/projects/{id}/health | Get health status | Instructor |
| POST | /api/projects/{id}/members | Add member | Owner |
| DELETE | /api/projects/{id}/members/{userId} | Remove member | Owner |

### 2.2 Kanban Board

#### Controllers:

```
app/Http/Controllers/
├── BoardController.php
├── ColumnController.php
└── TaskController.php
```

#### Task Endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/boards/{id}/tasks | Get all board tasks |
| POST | /api/tasks | Create task |
| PUT | /api/tasks/{id} | Update task |
| PUT | /api/tasks/{id}/move | Move task (column/position) |
| DELETE | /api/tasks/{id} | Delete/archive task |

#### Move Task Logic:

```php
// TaskController@move
public function move(MoveTaskRequest $request, Task $task)
{
    $column = Column::findOrFail($request->column_id);

    // Check WIP limit
    if ($column->wip_limit > 0 && $column->tasks()->count() >= $column->wip_limit) {
        return response()->json([
            'success' => false,
            'error' => ['code' => 'WIP_LIMIT_EXCEEDED', 'message' => '...']
        ], 422);
    }

    // Update position and column
    $task->update([
        'column_id' => $column->id,
        'position' => $request->position
    ]);

    // Reorder other tasks
    // Log activity

    return TaskResource::make($task);
}
```

### 2.3 Subtasks & Comments

#### Subtask Endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/tasks/{id}/subtasks | List subtasks |
| POST | /api/tasks/{id}/subtasks | Create subtask |
| PUT | /api/subtasks/{id} | Update subtask |
| PUT | /api/subtasks/{id}/toggle | Toggle completion |
| DELETE | /api/subtasks/{id} | Delete subtask |

#### Comment Endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/tasks/{id}/comments | List comments (threaded) |
| POST | /api/tasks/{id}/comments | Create comment |
| PUT | /api/comments/{id} | Edit comment (within 15 min) |
| DELETE | /api/comments/{id} | Delete comment |

#### @Mention Processing:

```php
// CommentService.php
public function processsMentions(Comment $comment): void
{
    preg_match_all('/@(\w+)/', $comment->body, $matches);

    foreach ($matches[1] as $username) {
        $user = User::where('name', $username)->first();
        if ($user) {
            $user->notifications()->create([
                'type' => 'mention',
                'data' => [
                    'comment_id' => $comment->id,
                    'task_id' => $comment->task_id,
                    'mentioned_by' => $comment->user_id
                ]
            ]);
            // Broadcast notification
        }
    }
}
```

---

## Phase 3: Dashboard & Analytics (Priority: High)

### 3.1 Summary Statistics API

#### Controller: DashboardController.php

```php
public function stats(Request $request)
{
    $user = $request->user();
    $projects = $user->accessibleProjects();

    return [
        'success' => true,
        'data' => [
            'projects' => [
                'count' => $projects->count(),
                'trend' => $this->getProjectTrend($projects, 7),
                'change' => $this->calculateChange($projects, 'created_at', 7)
            ],
            'tasks' => [
                'active' => Task::whereIn('project_id', $projects->pluck('id'))
                    ->whereNot('status', 'completed')->count(),
                'completion_rate' => $this->getCompletionRate($projects),
                'change' => ...
            ],
            'team_online' => $this->getOnlineMembers($projects),
            'overdue' => Task::whereIn('project_id', $projects->pluck('id'))
                ->where('due_date', '<', now())
                ->whereNot('status', 'completed')
                ->count()
        ]
    ];
}
```

### 3.2 Project Health Matrix

#### Endpoint: GET /api/dashboard/health-matrix

```php
public function healthMatrix(Request $request)
{
    $projects = $request->user()->accessibleProjects();

    $matrix = [];
    $timelineStatuses = ['behind', 'on_track', 'ahead'];
    $budgetStatuses = ['over_budget', 'on_budget', 'under_budget'];

    foreach ($budgetStatuses as $budget) {
        foreach ($timelineStatuses as $timeline) {
            $matrix[$budget][$timeline] = $projects
                ->where('timeline_status', $timeline)
                ->where('budget_status', $budget)
                ->count();
        }
    }

    return ['success' => true, 'data' => $matrix];
}
```

### 3.3 Progress Chart

#### Endpoint: GET /api/dashboard/progress?range=30

Returns weekly task completion data with cumulative progress.

### 3.4 Activity Feed

#### Endpoint: GET /api/activities?filter=all&page=1

```php
public function index(Request $request)
{
    $query = Activity::whereIn('project_id', $request->user()->accessibleProjectIds())
        ->with(['user:id,name,avatar', 'project:id,title'])
        ->latest();

    if ($filter = $request->filter) {
        $query->where('type', $filter);
    }

    return ActivityResource::collection(
        $query->paginate(20)
    );
}
```

### 3.5 Deadline Heatmap

#### Endpoint: GET /api/dashboard/deadlines?month=2026-01

```php
public function deadlines(Request $request)
{
    $startOfMonth = Carbon::parse($request->month)->startOfMonth();
    $endOfMonth = Carbon::parse($request->month)->endOfMonth();

    $deadlines = Task::whereIn('project_id', $request->user()->accessibleProjectIds())
        ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
        ->selectRaw('DATE(due_date) as date, COUNT(*) as count')
        ->groupBy('date')
        ->get()
        ->keyBy('date');

    return ['success' => true, 'data' => $deadlines];
}
```

---

## Phase 4: Student Performance Analytics (Priority: Medium)

### 4.1 Student Selector

#### Endpoint: GET /api/students?search=john

Returns list of students accessible to the instructor with recent view tracking.

### 4.2 Performance Radar Chart

#### Endpoint: GET /api/students/{id}/performance

```php
public function performance(User $student)
{
    return [
        'success' => true,
        'data' => [
            'student' => [
                'code_quality' => $this->calculateCodeQuality($student),
                'deadline_adherence' => $this->calculateDeadlineAdherence($student),
                'collaboration' => $this->calculateCollaboration($student),
                'documentation' => $this->calculateDocumentation($student),
                'problem_solving' => $this->calculateProblemSolving($student),
                'communication' => $this->calculateCommunication($student)
            ],
            'class_average' => $this->getClassAverage($student->currentProject)
        ]
    ];
}
```

### 4.3 Contribution Graph

#### Endpoint: GET /api/students/{id}/contributions?year=2026

Returns daily contribution counts in GitHub-style format.

### 4.4 Task Completion Funnel

#### Endpoint: GET /api/students/{id}/funnel

```php
public function funnel(User $student)
{
    $tasks = $student->assignedTasks();

    $stages = [
        'assigned' => $tasks->count(),
        'started' => $tasks->whereNotNull('started_at')->count(),
        'in_progress' => $tasks->where('status', 'in_progress')->count(),
        'review' => $tasks->where('status', 'review')->count(),
        'completed' => $tasks->where('status', 'completed')->count()
    ];

    return [
        'success' => true,
        'data' => [
            'stages' => $stages,
            'conversion_rates' => $this->calculateConversionRates($stages),
            'bottleneck' => $this->identifyBottleneck($stages)
        ]
    ];
}
```

### 4.5 Comparative Metrics Table

#### Endpoint: GET /api/students/{id}/metrics

Returns metrics with class comparison and percentile ranks.

### 4.6 Skills Gap Analysis

#### Endpoint: GET /api/students/{id}/skills-gap

### 4.7 AI Insights

#### Endpoint: GET /api/students/{id}/insights

```php
public function insights(User $student)
{
    $recentActivity = $student->activities()->recent()->get();
    $metrics = $this->calculateMetrics($student);

    $insights = $this->aiService->generateInsights([
        'activity' => $recentActivity,
        'metrics' => $metrics,
        'trends' => $this->calculateTrends($student)
    ]);

    return ['success' => true, 'data' => $insights];
}
```

---

## Phase 5: Frontend Implementation (Priority: High)

### 5.1 Frontend Setup

#### Install Dependencies:

```bash
npm install vue@3 vue-router@4 pinia @vueuse/core
npm install @headlessui/vue @heroicons/vue
npm install chart.js vue-chartjs
npm install @dnd-kit/core @dnd-kit/sortable  # For drag & drop
npm install date-fns
npm install -D @types/node
```

#### Directory Structure:

```
resources/js/
├── app.js                    # Main entry
├── router/
│   └── index.js              # Vue Router setup
├── stores/
│   ├── auth.js               # Auth state (Pinia)
│   ├── projects.js           # Projects state
│   ├── tasks.js              # Tasks/board state
│   └── notifications.js      # Notifications state
├── composables/
│   ├── useAuth.js
│   ├── useApi.js
│   ├── useToast.js
│   └── useTheme.js
├── components/
│   ├── common/
│   │   ├── AppLayout.vue
│   │   ├── Navbar.vue
│   │   ├── Sidebar.vue
│   │   ├── ThemeToggle.vue
│   │   ├── GlobalSearch.vue
│   │   ├── NotificationBell.vue
│   │   ├── Toast.vue
│   │   └── SkeletonLoader.vue
│   ├── dashboard/
│   │   ├── SummaryStats.vue
│   │   ├── StatCard.vue
│   │   ├── ProjectHealthMatrix.vue
│   │   ├── ProgressChart.vue
│   │   ├── ActivityFeed.vue
│   │   └── DeadlineHeatmap.vue
│   ├── board/
│   │   ├── KanbanBoard.vue
│   │   ├── BoardHeader.vue
│   │   ├── BoardColumn.vue
│   │   ├── TaskCard.vue
│   │   ├── TaskDetailPanel.vue
│   │   ├── SubtaskList.vue
│   │   ├── CommentThread.vue
│   │   └── Swimlanes.vue
│   └── analytics/
│       ├── StudentSelector.vue
│       ├── RadarChart.vue
│       ├── ContributionGraph.vue
│       ├── TaskFunnel.vue
│       ├── MetricsTable.vue
│       ├── SkillsGap.vue
│       └── AIInsights.vue
├── pages/
│   ├── auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   └── ResetPassword.vue
│   ├── Dashboard.vue
│   ├── Projects.vue
│   ├── ProjectBoard.vue
│   └── StudentAnalytics.vue
└── utils/
    ├── api.js                # Axios instance
    └── helpers.js
```

### 5.2 Key Component Implementations

#### TaskCard.vue (Drag & Drop):

```vue
<template>
  <div
    :class="[
      'task-card bg-white dark:bg-gray-800 rounded-lg shadow-sm',
      'border-l-4 cursor-grab hover:shadow-md transition-shadow',
      priorityBorderClass
    ]"
    :draggable="true"
    @dragstart="onDragStart"
    @click="openDetailPanel"
  >
    <div class="p-3">
      <h4 class="text-sm font-medium line-clamp-2">{{ task.title }}</h4>

      <div class="flex items-center gap-2 mt-2">
        <img v-if="task.assignee" :src="task.assignee.avatar" class="w-6 h-6 rounded-full" />
        <span :class="['text-xs', isOverdue ? 'text-red-500' : 'text-gray-500']">
          {{ formatDate(task.due_date) }}
        </span>
      </div>

      <div v-if="task.subtasks_count" class="mt-2">
        <div class="h-1 bg-gray-200 rounded">
          <div class="h-1 bg-blue-500 rounded" :style="{ width: subtaskProgress + '%' }"></div>
        </div>
      </div>

      <div class="flex gap-1 mt-2 flex-wrap">
        <span v-for="label in displayLabels" :key="label.id"
              :style="{ backgroundColor: label.color }"
              class="px-2 py-0.5 text-xs rounded text-white">
          {{ label.name }}
        </span>
        <span v-if="overflowCount > 0" class="text-xs text-gray-500">+{{ overflowCount }}</span>
      </div>
    </div>
  </div>
</template>
```

#### ThemeToggle.vue:

```vue
<script setup>
import { useTheme } from '@/composables/useTheme'
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline'

const { isDark, toggle } = useTheme()
</script>

<template>
  <button @click="toggle" class="p-2 rounded-lg transition-colors duration-300">
    <SunIcon v-if="isDark" class="w-5 h-5" />
    <MoonIcon v-else class="w-5 h-5" />
  </button>
</template>
```

### 5.3 Real-time Updates

#### Setup Laravel Echo:

```js
// bootstrap.js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
})
```

#### Listen for Events:

```js
// In board component
Echo.private(`project.${projectId}`)
    .listen('TaskMoved', (e) => {
        // Update board state
    })
    .listen('TaskCreated', (e) => {
        // Add new task
    })
    .listen('CommentAdded', (e) => {
        // Update comments
    })
```

---

## Phase 6: Common Components (Priority: Medium)

### 6.1 Toast Notifications

```js
// composables/useToast.js
import { ref } from 'vue'

const toasts = ref([])
let id = 0

export function useToast() {
    const add = (message, type = 'info', duration = 5000) => {
        const toast = { id: ++id, message, type }
        toasts.value.push(toast)

        setTimeout(() => {
            remove(toast.id)
        }, duration)
    }

    const remove = (id) => {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    return { toasts, add, remove, success: (m) => add(m, 'success'), error: (m) => add(m, 'error') }
}
```

### 6.2 Global Search

- Ctrl+K keyboard shortcut
- Debounced search API call
- Results grouped by type
- Keyboard navigation

### 6.3 Notifications Bell

- Real-time via WebSocket
- Dropdown with recent notifications
- Mark as read functionality
- Badge with unread count

---

## Implementation Order & Dependencies

```
Week 1-2: Phase 1 (Foundation)
├── Database migrations
├── Models & relationships
├── Auth controllers
├── RBAC middleware
└── API response formatting

Week 3-4: Phase 2 (Projects & Boards)
├── Project CRUD
├── Board/Column management
├── Task CRUD & drag-drop API
├── Subtasks & Comments
└── Activity logging

Week 5-6: Phase 3 (Dashboard)
├── Dashboard stats API
├── Health matrix
├── Progress charts
├── Activity feed
└── Deadline heatmap

Week 7-8: Phase 4 (Analytics)
├── Student selector
├── Performance radar
├── Contribution graph
├── Funnel chart
├── Metrics table
├── Skills gap
└── AI insights integration

Week 9-10: Phase 5 (Frontend Core)
├── Vue setup & routing
├── Auth pages
├── Dashboard components
├── Kanban board UI
└── Drag & drop implementation

Week 11-12: Phase 6 (Polish)
├── Theme toggle (dark/light)
├── RTL support
├── Global search
├── Notifications
├── Toast system
├── Performance optimization
└── Testing & bug fixes
```

---

## Testing Strategy

### Backend (PHPUnit):

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   ├── LoginTest.php
│   │   └── PasswordResetTest.php
│   ├── Project/
│   │   ├── ProjectCrudTest.php
│   │   └── ProjectAccessTest.php
│   ├── Board/
│   │   ├── TaskCrudTest.php
│   │   └── TaskMoveTest.php
│   └── Dashboard/
│       └── StatsTest.php
└── Unit/
    ├── PolicyTest.php
    └── ServiceTest.php
```

### Frontend (Vitest + Vue Test Utils):

```
resources/js/__tests__/
├── components/
│   ├── TaskCard.spec.js
│   ├── KanbanBoard.spec.js
│   └── ThemeToggle.spec.js
└── composables/
    ├── useAuth.spec.js
    └── useToast.spec.js
```

---

## API Response Format

All endpoints follow this structure:

```json
// Success
{
  "success": true,
  "data": { ... },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100
    }
  }
}

// Error
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The email field is required.",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

---

## Environment Variables

```env
# .env additions
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

JWT_SECRET=
JWT_TTL=60

AI_INSIGHTS_API_KEY=  # For AI insights feature
```

---

## Performance Considerations

1. **Database Indexing:** Add indexes on frequently queried columns (user_id, project_id, status, due_date)
2. **Eager Loading:** Always use `with()` to prevent N+1 queries
3. **Caching:** Cache dashboard stats with 5-minute TTL
4. **Pagination:** All list endpoints paginated (20 items default)
5. **Lazy Loading:** Use Vue's `defineAsyncComponent` for heavy components
6. **Image Optimization:** Compress avatars, use WebP format

---

## Security Checklist

- [x] JWT token expiration
- [x] CSRF protection
- [x] Input validation on all endpoints
- [x] SQL injection prevention (Eloquent ORM)
- [x] XSS prevention (Vue's default escaping)
- [x] Rate limiting on auth endpoints
- [x] Secure password hashing (bcrypt)
- [x] Role-based access control
- [x] Resource authorization policies
